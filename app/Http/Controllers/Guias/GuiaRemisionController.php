<?php

namespace App\Http\Controllers\Guias;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Ventas\Venta;
use App\Models\Guias\GuiaRemision;
use App\Models\Guias\DetalleGuiaRemision;
use App\Http\Requests\Guias\GuiaRequest;
use App\Models\Sucursal;
use App\Models\Configuracion;
use App\Services\SucursalResolver;
use App\Models\Inventario\Lote;
use App\Services\GuiaService;
use App\Services\ComprobanteService;

// --- FALTABAN ESTAS IMPORTACIONES ---
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GuiaRemisionController extends Controller
{
    protected SucursalResolver $sucursalResolver;
    protected GuiaService $guiaService; // <--- Propiedad para el servicio

    public function __construct(SucursalResolver $sucursalResolver, GuiaService $guiaService)
    {
        $this->sucursalResolver = $sucursalResolver;
        $this->guiaService = $guiaService;
    }
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Resolver el contexto de la sucursal
        $ctx = $this->sucursalResolver->resolverPara($user);
        $sucursalOrigen = $ctx['sucursal_seleccionada']; // La sucursal activa en sesión
        $permiteCrear   = (bool) $sucursalOrigen;

        // 2. Iniciar Query
        $query = GuiaRemision::with(['sucursal', 'cliente'])
            ->orderBy('fecha_emision', 'desc');

        // 3. Lógica de Filtrado por Sucursal (CORREGIDA)
        // Si el usuario seleccionó una sucursal específica, filtramos por esa.
        // Si no (es admin viendo todo), usamos los ids_filtro.
        if ($sucursalOrigen) {
            // Muestra guías emitidas por esta sucursal O guías que llegan a esta sucursal (por ubigeo)
            $ubigeo = $sucursalOrigen->ubigeo;
            $id = $sucursalOrigen->id;

            $query->where(function ($q) use ($id, $ubigeo) {
                $q->where('sucursal_id', $id); // Salidas
                if ($ubigeo) {
                    $q->orWhere('ubigeo_llegada', $ubigeo); // Llegadas
                }
            });
        } elseif ($ctx['ids_filtro'] !== null) {
            // Fallback: Si no seleccionó sucursal específica, usa sus permisos
            $ids = $ctx['ids_filtro'];
            $ubigeos = Sucursal::whereIn('id', $ids)->pluck('ubigeo')->filter()->values()->all();

            $query->where(function ($q) use ($ids, $ubigeos) {
                $q->whereIn('sucursal_id', $ids);
                if (!empty($ubigeos)) {
                    $q->orWhereIn('ubigeo_llegada', $ubigeos);
                }
            });
        }

        // 4. Búsqueda y Fechas
        if ($request->filled('search_q')) {
            $q = $request->search_q;

            $partes = explode('-', $q);

            $query->where(function ($sub) use ($q, $partes) {
                $sub->where('serie', 'like', "%$q%")
                    ->orWhereHas('cliente', function ($c) use ($q) {
                        $c->where('razon_social', 'like', "%$q%")
                            ->orWhere('nombre', 'like', "%$q%");
                    });

                if (count($partes) === 2) {
                    $serie = trim($partes[0]);
                    $numeroInput = (int) trim($partes[1]);

                    $sub->orWhere(function ($qDoc) use ($serie, $numeroInput) {
                        $qDoc->where('serie', $serie)
                            ->where('numero', $numeroInput); // Compara como entero
                    });
                }
            });

            // NOTA: Si hay búsqueda de texto, ignoramos el filtro de fechas para mostrar lo que se busca.
            $desde = null;
            $hasta = null;
        } else {
            // Si NO hay búsqueda de texto, aplicamos el filtro de fechas por defecto
            $desde = $request->get('fecha_desde', now()->startOfMonth()->format('Y-m-d'));
            $hasta = $request->get('fecha_hasta', now()->format('Y-m-d'));

            $query->whereBetween('fecha_emision', ["$desde 00:00:00", "$hasta 23:59:59"]);
        }


        $guias = $query->paginate(15);

        return view('guias.index', compact('guias', 'sucursalOrigen', 'permiteCrear'))
            ->with('fecha_desde', $desde)
            ->with('fecha_hasta', $hasta);
    }


    public function create(Request $request)
    {
        $user = Auth::user();

        $sucursalOrigen = $this->obtenerSucursalOrigen($user);

        if (!$sucursalOrigen) {
            return redirect()->route('guias.index')
                ->with('error', 'Selecciona una sucursal para crear la guía.');
        }

        $configuracion = Configuracion::first();
        $sucursalesDestino = Sucursal::where('id', '!=', $sucursalOrigen->id)->get();

        $serie = $sucursalOrigen->serie_guia ?? 'T001';
        $ultimoCorrelativo = GuiaRemision::where('serie', $serie)->max('numero');
        $numero = $ultimoCorrelativo ? ($ultimoCorrelativo + 1) : 1;

        $venta = null;
        if ($request->has('venta_id')) {
            $venta = Venta::with(['cliente', 'detalles.medicamento'])->find($request->venta_id);
        }

        return view('guias.create', compact(
            'sucursalOrigen',
            'sucursalesDestino',
            'configuracion',
            'serie',
            'numero',
            'venta'
        ));
    }

    public function store(GuiaRequest $request)
    {
        try {
            $user = Auth::user();
            $sucursal = $this->obtenerSucursalOrigen($user);
            if (!$sucursal) return back()->with('error', 'Sin sucursal asignada.');

            $data = $request->validated();

            // EL SERVICIO HACE TODO (Guardar -> Stock -> SUNAT)
            $guia = $this->guiaService->registrarGuia($user, $sucursal, $data);

            // RESPUESTA INTELIGENTE
            if ($guia->sunat_exito) {
                $msg = "Guía {$guia->serie}-{$guia->numero} CREADA y ACEPTADA por SUNAT.";
                $tipo = 'success';
            } else {
                // Guardó pero falló SUNAT (Amarillo)
                $msg = "Guía guardada, pero SUNAT respondió: " . $guia->mensaje_sunat;
                $tipo = 'warning';
            }

            return redirect()->route('guias.index')->with($tipo, $msg);
        } catch (\Exception $e) {
            // Error fatal (Rojo)
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function recibir(GuiaRequest $request, $id)
    {
        try {
            $user = Auth::user();
            $data = $request->validated();

            $guia = $this->guiaService->recepcionarGuia($id, $data, $user);

            return back()->with('success', "Guía N° {$guia->serie}-{$guia->numero} recepcionada exitosamente. Stock actualizado.");
        } catch (\Exception $e) {
            return back()->with('error', "Fallo al recibir guía: " . $e->getMessage());
        }
    }
    public function anular(GuiaRequest $request, $id)
    {
        try {
            $user = Auth::user();
            $motivo = $request->input('motivo_anulacion');

            $guia = $this->guiaService->anularGuia($id, $motivo, $user);

            return back()->with('success', "Guía N° {$guia->serie}-{$guia->numero} anulada exitosamente. Stock revertido.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Si hay error de validación (e.g., fecha_recepcion < fecha_traslado),
            // Laravel lo maneja automáticamente, pero si el flujo está roto, esto ayuda.
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // Para errores lógicos de negocio (e.g., stock insuficiente, guía ya recibida)
            return back()->with('error', "Fallo al recibir guía: " . $e->getMessage());
        }
    }

    private function obtenerSucursalOrigen($user)
    {
        $sucSession = session('sucursal_id');
        if ($sucSession) return Sucursal::find($sucSession);
        return null;
    }



    /// pdf aqui 

    public function verPdf($id, ComprobanteService $pdfService)
    {
        try {
            // 1. Cargar la Guía con las relaciones necesarias
            $guia = GuiaRemision::with([
                'detalles.lote', // Si los detalles tienen relación a lote
                'cliente',
                'sucursal',
                'usuario',
            ])->findOrFail($id);

            return $pdfService->generarGuiaPdf($guia, 'stream');
        } catch (Exception $e) {
            return back()->with('error', "Fallo al generar el PDF: " . $e->getMessage());
        }
    }



    public function buscarVenta(Request $request)
    {
        try {
            $q = $request->input('q');
            if (!$q) return response()->json(['error' => 'Ingrese serie y número'], 400);

            // 2. Separar Serie y Número
            // Acepta guión, espacio o pegado si es inteligente, pero asumimos guion por ahora
            $partes = explode('-', $q);
            if (count($partes) != 2) {
                return response()->json(['error' => 'Formato incorrecto. Use: B001-45'], 400);
            }
            $serie = trim($partes[0]); // "B001"
            $numeroInput = trim($partes[1]); // "2" o "00002"

            // 3. Buscar la Venta (Inteligente)
            // Buscamos coincidencia exacta O conversión a número para evitar problemas de ceros
            $venta = Venta::with(['cliente', 'detalles.medicamento'])
                ->where('serie', $serie)
                ->where(function ($query) use ($numeroInput) {
                    $query->where('numero', $numeroInput) // Coincidencia exacta string
                        ->orWhere('numero', (int)$numeroInput); // Coincidencia numérica
                })
                ->first();

            if (!$venta) {
                return response()->json(['error' => 'No se encontró la venta ' . $q], 404);
            }

            // 4. Preparar Items (Protegido contra nulos)
            $items = [];
            foreach ($venta->detalles as $det) {
                $prod = $det->medicamento; // Puede ser null si se borró el medicamento

                $items[] = [
                    'medicamento_id' => $prod ? $prod->id : null,
                    'codigo'         => $prod ? $prod->codigo : 'N/A',
                    'descripcion'    => $prod ? ($prod->nombre . ' ' . ($prod->presentacion ?? '')) : ($det->descripcion ?? 'Producto Desconocido'),
                    'cantidad'       => (float) $det->cantidad
                ];
            }

            // 5. Preparar Cliente (Protegido contra ventas sin cliente)
            $cliente = $venta->cliente;

            if ($cliente) {
                $nombreCliente = $cliente->razon_social ?: ($cliente->nombre . ' ' . $cliente->apellidos);
                $docCliente = $cliente->documento;
                $dirCliente = $cliente->direccion;
                $ubiCliente = $cliente->ubigeo;
                $idCliente = $cliente->id;
            } else {
                // Caso Venta Rápida / Cliente Varios
                $nombreCliente = 'CLIENTE VARIOS';
                $docCliente = '00000000';
                $dirCliente = '';
                $ubiCliente = '';
                $idCliente = null;
            }

            // 6. Retornar JSON
            return response()->json([
                'venta_id' => $venta->id,
                'cliente' => [
                    'id' => $idCliente,
                    'nombre' => trim($nombreCliente),
                    'documento' => $docCliente,
                    'direccion' => $dirCliente,
                    'ubigeo' => $ubiCliente
                ],
                'items' => $items
            ]);
        } catch (\Exception $e) {
            // Loguear el error real para que lo veas en laravel.log
            \Log::error('Error buscando venta: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }

    // En GuiaRemisionController.php

    public function lookupMedicamentos(Request $request)
    {
        $q = $request->get('q');
        $sucursalId = $request->get('sucursal_id');

        // Buscamos medicamentos que coincidan por nombre o código
        // Y cargamos DE UNA VEZ sus lotes con stock en esa sucursal
        $productos = \App\Models\Inventario\Medicamento::with(['lotes' => function ($query) use ($sucursalId) {
            $query->where('sucursal_id', $sucursalId)
                ->where('stock_actual', '>', 0)
                ->orderBy('fecha_vencimiento', 'asc'); // FIFO (Primero en vencer, primero en salir)
        }])
            ->whereHas('lotes', function ($query) use ($sucursalId) {
                // Solo traer productos que tengan stock en esta sucursal
                $query->where('sucursal_id', $sucursalId)->where('stock_actual', '>', 0);
            })
            ->where(function ($query) use ($q) {
                $query->where('nombre', 'like', "%$q%")
                    ->orWhere('codigo', 'like', "$q%"); // Búsqueda rápida por inicio de código
            })
            ->limit(10)
            ->get();

        // Formateamos la respuesta JSON exacta para el JS
        return $productos->map(function ($p) {
            return [
                'id' => $p->id,
                'codigo' => $p->codigo,
                'nombre' => $p->nombre,
                'presentacion' => $p->presentacion,
                // Enviamos los lotes listos
                'lotes' => $p->lotes->map(function ($l) {
                    return [
                        'id' => $l->id,
                        'codigo_lote' => $l->codigo_lote,
                        'vencimiento' => $l->fecha_vencimiento ? $l->fecha_vencimiento->format('Y-m-d') : '-',
                        'stock' => (float)$l->stock_actual
                    ];
                })
            ];
        });
    }
}
