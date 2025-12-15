<?php

namespace App\Http\Controllers\Guias;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // <--- FALTABA ESTO
use App\Models\Ventas\Venta;
use App\Models\Guias\GuiaRemision;
use App\Models\Guias\DetalleGuiaRemision;
use App\Http\Requests\Guias\GuiaRequest;
use App\Models\Sucursal;
use App\Models\Configuracion;
use App\Services\SucursalResolver;
use App\Models\Inventario\Lote;
use App\Services\GuiaService;

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
            $query->where(function ($sub) use ($q) {
                $sub->where('numero', 'like', "%$q%")
                    ->orWhere('serie', 'like', "%$q%")
                    ->orWhereHas('cliente', function ($c) use ($q) {
                        $c->where('razon_social', 'like', "%$q%")
                            ->orWhere('nombre', 'like', "%$q%");
                    });
            });
        } else {
            // CORRECCIÓN DE FECHAS:
            // Por defecto: Desde el 1ro del mes actual hasta hoy.
            // Antes tenías solo 'now()', por eso salía vacío si la guía era de ayer.
            $desde = $request->get('fecha_desde', now()->startOfMonth()->format('Y-m-d'));
            $hasta = $request->get('fecha_hasta', now()->format('Y-m-d'));

            $query->whereBetween('fecha_emision', ["$desde 00:00:00", "$hasta 23:59:59"]);
        }

        $guias = $query->paginate(15);

        // Pasamos variables extra para que la vista mantenga el filtro de fecha visible
        return view('guias.index', compact('guias', 'sucursalOrigen', 'permiteCrear'))
            ->with('fecha_desde', $request->get('fecha_desde', now()->startOfMonth()->format('Y-m-d')))
            ->with('fecha_hasta', $request->get('fecha_hasta', now()->format('Y-m-d')));
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
            $data = $request->validated();

            // $this->guiaService->recepcionarGuia($id, $data);

            return back()->with('success', 'Guía recepcionada correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function anular(GuiaRequest $request, $id)
    {
        $motivo = $request->input('motivo_anulacion');
        return back()->with('success', 'Anulada.');
    }


    private function obtenerSucursalOrigen($user)
    {
        $sucSession = session('sucursal_id');
        if ($sucSession) return Sucursal::find($sucSession);
        return null;
    }

    public function imprimir($id)
    {
        // 1. Buscamos la guía con todas sus relaciones
        $guia = GuiaRemision::with(['sucursal', 'cliente', 'detalles', 'usuario'])->findOrFail($id);

        // 2. Datos de configuración (Empresa)
        $config = Configuracion::first();

        // 3. Generación del QR para SUNAT
        $rucEmisor = $config->empresa_ruc ?? '20600000001';
        $tipoDoc   = '09'; // Código de Guía de Remisión
        $fecha     = $guia->fecha_emision->format('Y-m-d');
        $docDestino = $guia->doc_destinatario ?? '00000000';
        $tipoDocDestino = strlen($docDestino) == 11 ? '6' : '1';

        $qrData = "{$rucEmisor}|{$tipoDoc}|{$guia->serie}|{$guia->numero}|0.00|0.00|{$fecha}|{$tipoDocDestino}|{$docDestino}|";
        $qrBase64 = base64_encode(QrCode::format('svg')->size(150)->generate($qrData));

        // 4. Logo (opcional)
        $logoBase64 = null;
        $rutaLogo = public_path($config->ruta_logo ?? 'vendor/adminlte/dist/img/AdminLTELogo.png');
        if (file_exists($rutaLogo)) {
            $type = pathinfo($rutaLogo, PATHINFO_EXTENSION);
            $data = file_get_contents($rutaLogo);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        // 5. Cargar la vista PDF (Asegúrate de tener creada la vista 'guias.pdf.a4')
        $pdf = Pdf::loadView('guias.pdf.a4', compact('guia', 'config', 'qrBase64', 'logoBase64'));

        // 6. Mostrar en el navegador (stream)
        return $pdf->stream("GUIA-{$guia->serie}-{$guia->numero}.pdf");
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
