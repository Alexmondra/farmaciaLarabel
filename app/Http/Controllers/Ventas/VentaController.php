<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\SucursalResolver;
use App\Services\VentaService;
use App\Models\Ventas\Venta;
use App\Models\Ventas\CajaSesion;
use App\Models\Ventas\Cliente;
use App\Models\Inventario\Lote;
use App\Models\Sucursal;
use App\Models\Inventario\MedicamentoSucursal;
use App\Models\Inventario\Categoria;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Luecano\NumeroALetras\NumeroALetras;
use Illuminate\Support\Str;
use App\Mail\ComprobanteMailable;
use Illuminate\Support\Facades\Mail;



class VentaController extends Controller
{
    protected SucursalResolver $sucursalResolver;
    protected VentaService $ventaService;
    public function __construct(SucursalResolver $sucursalResolver, VentaService $ventaService)
    {
        $this->sucursalResolver = $sucursalResolver;
        $this->ventaService     = $ventaService;
    }

    public function enviarEmail($id)
    {
        try {
            $venta = Venta::with('cliente')->findOrFail($id);

            if (!$venta->cliente->email) {
                return response()->json(['message' => 'El cliente no tiene email.'], 400);
            }

            Mail::to($venta->cliente->email)->send(new ComprobanteMailable($venta));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    public function index(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->sucursalResolver->resolverPara($user);

        // 1. Query Base
        $query = Venta::with(['cliente', 'usuario', 'sucursal'])
            ->orderBy('fecha_emision', 'desc');

        // 2. Filtro de Sucursal (Contexto)
        if ($ctx['ids_filtro']) {
            $query->whereIn('sucursal_id', $ctx['ids_filtro']);
        }

        // 3. Lógica de Búsqueda Inteligente
        if ($request->filled('search_q')) {
            // A. Si el usuario escribe algo (Ticket o Cliente), buscamos en TODO el historial
            $busqueda = $request->search_q;
            $query->where(function ($q) use ($busqueda) {
                $q->where('numero', 'LIKE', "%$busqueda%")
                    ->orWhere(DB::raw("CONCAT(serie, '-', numero)"), 'LIKE', "%$busqueda%")
                    ->orWhereHas('cliente', fn($c) => $c->where('nombre', 'LIKE', "%$busqueda%"));
            });
        } else {
            // B. Si NO busca nada específico, filtramos por FECHA (Por defecto: HOY)
            $desde = $request->get('fecha_desde', now()->format('Y-m-d'));
            $hasta = $request->get('fecha_hasta', now()->format('Y-m-d'));

            $query->whereBetween('fecha_emision', [
                $desde . ' 00:00:00',
                $hasta . ' 23:59:59'
            ]);
        }

        // 4. Paginación
        $ventas = $query->paginate(20);

        $cajaAbierta = CajaSesion::where('user_id', $user->id)
            ->where('estado', 'ABIERTO')
            ->first();

        // 6. Sucursales (Para el modal de abrir caja si fuera necesario)
        $sucursalesParaApertura = $ctx['es_admin']
            ? Sucursal::orderBy('nombre')->get()
            : $user->sucursales()->orderBy('nombre')->get();

        return view('ventas.ventas.index', [
            'ventas'                 => $ventas,
            'cajaAbierta'            => $cajaAbierta,
            'sucursalesParaApertura' => $sucursalesParaApertura,
            'esAdmin'                => $ctx['es_admin'],
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. VALIDACIÓN HTTP (Lo básico)
        $data = $request->validate([
            'caja_sesion_id'   => ['required', 'integer', 'exists:caja_sesiones,id'],
            'cliente_id'       => ['nullable', 'integer', 'exists:clientes,id'],
            'tipo_comprobante' => ['required', 'string', 'in:BOLETA,FACTURA,TICKET'],
            'medio_pago'       => ['required', 'string', 'in:EFECTIVO,TARJETA,YAPE,PLIN'],
            'items'            => ['required', 'string'],
            'puntos_usados'    => ['nullable', 'integer', 'min:0'],
            'descuento_puntos' => ['nullable', 'numeric', 'min:0'],
            'referencia_pago'  => ['nullable', 'string', 'max:50'],
            'paga_con'         => ['nullable', 'numeric'],

        ]);

        // Ajuste de datos básicos
        if (empty($data['cliente_id'])) {
            $data['cliente_id'] = 1;
        }

        if (json_decode($data['items']) == null) {
            return back()->withErrors('El carrito está vacío o es inválido.');
        }



        // 2. DELEGAR AL SERVICIO
        try {
            // Toda la magia ocurre aquí dentro
            $venta = $this->ventaService->registrarVenta($user, $data);

            return redirect()->route('ventas.show', $venta->id)
                ->with('success', 'Venta registrada correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['general' => $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $user = Auth::user();

        // 1. Cargar Configuración Global
        $config = Configuracion::first();

        $venta = Venta::with(['detalles.medicamento', 'cliente', 'usuario', 'sucursal'])
            ->findOrFail($id);

        // 2. Permisos
        if (!$user->hasRole('Administrador')) {
            $tieneAcceso = \DB::table('sucursal_user')
                ->where('user_id', $user->id)
                ->where('sucursal_id', $venta->sucursal_id)
                ->exists();

            if (!$tieneAcceso) {
                abort(403, 'Esta venta pertenece a otra sucursal y no tienes asignado acceso.');
            }
        }

        // --- 3. LOGICA DEL LOGO (Igual que el PDF) ---
        // Prioridad: Si la sucursal tiene logo propio, úsalo. Si no, usa el general.
        $rutaLogo = $venta->sucursal->imagen_sucursal ?? $config->ruta_logo;

        // Convertimos a Base64 para evitar problemas de rutas rotas
        $logoBase64 = $this->obtenerImagenBase64($rutaLogo);


        // --- 4. QR LOCAL ---
        $rucEmisor = $config->empresa_ruc ?? '20000000001';
        $tipoDoc   = $venta->tipo_comprobante == 'FACTURA' ? '01' : '03';
        $fecha     = $venta->fecha_emision->format('Y-m-d');
        $clienteDocType = strlen($venta->cliente->documento) == 11 ? '6' : '1';
        $hash      = $venta->hash ?? '';

        $qrData = "{$rucEmisor}|{$tipoDoc}|{$venta->serie}|{$venta->numero}|{$venta->total_igv}|{$venta->total_neto}|{$fecha}|{$clienteDocType}|{$venta->cliente->documento}|{$hash}|";

        $qrBase64 = base64_encode(QrCode::format('svg')->size(150)->generate($qrData));

        // --- 5. MONTO EN LETRAS ---
        // Usamos la librería directo aquí, más limpio.
        $formatter = new NumeroALetras();
        $entero = floor($venta->total_neto);
        $decimal = round(($venta->total_neto - $entero) * 100);
        $letras = $formatter->toWords($entero);
        $montoLetras = "SON: " . Str::upper($letras) . " CON {$decimal}/100 SOLES";

        // Pasamos 'logoBase64' a la vista
        return view('ventas.ventas.show', compact('venta', 'qrBase64', 'montoLetras', 'logoBase64', 'config'));
    }

    /**
     * Método Auxiliar Privado para convertir imágenes a Base64
     * (Pon esto al final de tu VentaController class)
     */
    private function obtenerImagenBase64($rutaRelativa)
    {
        if (!$rutaRelativa) {
            return null;
        }

        $path = null;

        // Buscar en Storage (recomendado)
        if (file_exists(storage_path('app/public/' . $rutaRelativa))) {
            $path = storage_path('app/public/' . $rutaRelativa);
        }
        // Fallback: Buscar en public directo (legacy)
        elseif (file_exists(public_path($rutaRelativa))) {
            $path = public_path($rutaRelativa);
        }

        if (!$path) {
            return null;
        }

        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }


    public function create(Request $request)
    {
        // 1. BLOQUEO DE SEGURIDAD: ¿Hay sucursal seleccionada en la barra superior?
        if (!session()->has('sucursal_id')) {
            return redirect()->route('cajas.index')
                ->with('error', '⛔ ACCESO DENEGADO: Por favor, selecciona una sucursal en la parte superior antes de vender.');
        }

        $user = Auth::user();
        $sucursalActualId = session('sucursal_id');

        // 2. BUSQUEDA ESTRICTA:
        // Buscamos una caja que sea MÍA, esté ABIERTA y sea DE LA SUCURSAL ACTUAL.
        $cajaAbierta = CajaSesion::where('user_id', $user->id)
            ->where('estado', 'ABIERTO')
            ->where('sucursal_id', $sucursalActualId) // <--- ESTA ES LA CLAVE (Filtro Geográfico)
            ->first();

        // 3. SI NO SE ENCUENTRA CAJA VÁLIDA PARA ESTA SUCURSAL
        if (!$cajaAbierta) {

            // Verificamos si tiene una caja abierta "perdida" en OTRA sucursal
            // para darle un mensaje de error más útil.
            $cajaEnOtroLado = CajaSesion::where('user_id', $user->id)
                ->where('estado', 'ABIERTO')
                ->with('sucursal')
                ->first();

            if ($cajaEnOtroLado) {
                // CASO A: Tiene caja abierta, PERO NO AQUÍ.
                $nombreOtraSucursal = $cajaEnOtroLado->sucursal->nombre ?? 'otra ubicación';

                return redirect()->route('cajas.index')
                    ->with('error', "⚠️ ALERTA: Tienes una caja abierta en '{$nombreOtraSucursal}'. No puedes vender aquí hasta que cierres esa caja o cambies de sucursal.");
            }

            // CASO B: No tiene ninguna caja abierta en ningún lado.
            return redirect()->route('cajas.index')
                ->with('info', '👋 Hola. Para empezar a registrar ventas en esta sucursal, primero debes abrir tu caja.');
        }

        // 4. SI PASÓ TODAS LAS VALIDACIONES: Cargar datos y mostrar vista
        $categorias = Categoria::select('id', 'nombre')->orderBy('nombre')->get();

        return view('ventas.ventas.create', [
            'cajaAbierta' => $cajaAbierta,
            'categorias'  => $categorias,
        ]);
    }

    /**
     * Busca lotes disponibles para un medicamento en una sucursal.
     * MÉTODO AJAX
     */


    public function lookupMedicamentos(Request $request)
    {
        $request->validate([
            'sucursal_id'  => ['required', 'integer', 'exists:sucursales,id'],
            'q'            => ['nullable', 'string', 'max:100'],
            'categoria_id' => ['nullable', 'integer'],
        ]);

        $hoy = now()->format('Y-m-d');

        $query = MedicamentoSucursal::with('medicamento')
            ->where('sucursal_id', $request->sucursal_id)
            ->activos()
            ->whereExists(function ($sub) use ($request, $hoy) {
                $sub->selectRaw(1)
                    ->from('lotes')
                    ->whereColumn('lotes.medicamento_id', 'medicamento_sucursal.medicamento_id')
                    ->where('lotes.sucursal_id', $request->sucursal_id)
                    ->where('lotes.stock_actual', '>', 0)
                    ->where(function ($q2) use ($hoy) {
                        $q2->whereDate('lotes.fecha_vencimiento', '>=', $hoy)
                            ->orWhereNull('lotes.fecha_vencimiento');
                    });
            });

        if ($request->filled('q')) {
            $term = $request->q;

            $query->whereHas('medicamento', function ($sub) use ($term) {

                if (is_numeric($term) && strlen($term) >= 8) {
                    $sub->where(function ($q) use ($term) {
                        $q->where('codigo_barra', $term)
                            ->orWhere('codigo', $term);
                    });
                } else {
                    $sub->where(function ($q) use ($term) {
                        $q->where('nombre', 'LIKE', "%{$term}%")
                            ->orWhere('codigo', 'LIKE', "%{$term}%")
                            ->orWhere('laboratorio', 'LIKE', "%{$term}%")
                            ->orWhere('codigo_barra', 'LIKE', "%{$term}%");
                    });
                }
            });
        }

        if ($request->filled('categoria_id')) {
            $categoriaId = $request->categoria_id;
            $query->whereHas('medicamento', function ($q) use ($categoriaId) {
                $q->where('categoria_id', $categoriaId);
            });
        }

        $medicamentos = $query->orderBy('id', 'desc')
            ->limit(30)
            ->get()
            ->map(function ($ms) {
                return [
                    'medicamento_sucursal_id' => $ms->id,
                    'medicamento_id'          => $ms->medicamento_id,
                    'nombre'                  => $ms->medicamento->nombre,
                    'codigo'                  => $ms->medicamento->codigo,
                    'presentacion'            => $ms->medicamento->forma_farmaceutica ?? null,
                    'precio_venta'            => (float) $ms->precio_venta,
                ];
            });

        return response()->json($medicamentos);
    }

    public function lookupLotes(Request $request)
    {
        $request->validate([
            'medicamento_id' => 'required|integer',
            'sucursal_id'    => 'required|integer',
        ]);

        $hoy = now()->format('Y-m-d');

        // 1. OBTENER PRECIOS Y DATOS DEL PRODUCTO
        $ms = MedicamentoSucursal::with('medicamento') // Cargamos la relación del medicamento
            ->where('medicamento_id', $request->medicamento_id)
            ->where('sucursal_id', $request->sucursal_id)
            ->first();

        if (!$ms) return response()->json([]);

        // Preparamos datos maestros
        $precios = [
            'unidad'  => (float) $ms->precio_venta,
            'blister' => (float) $ms->precio_blister,
            'caja'    => (float) $ms->precio_caja
        ];

        $factores = [
            'blister' => $ms->medicamento->unidades_por_blister ?? 0,
            'caja'    => $ms->medicamento->unidades_por_envase ?? 1
        ];

        // 2. BUSCAR LOTES
        $lotes = Lote::where('medicamento_id', $request->medicamento_id)
            ->where('sucursal_id', $request->sucursal_id)
            ->where('stock_actual', '>', 0)
            ->where(function ($q) use ($hoy) {
                $q->whereDate('fecha_vencimiento', '>=', $hoy)
                    ->orWhereNull('fecha_vencimiento');
            })
            ->orderBy('fecha_vencimiento', 'asc')
            ->get()
            ->map(function ($lote) use ($precios, $factores) {
                return [
                    'id'                => $lote->id,
                    'codigo_lote'       => $lote->codigo_lote,
                    'fecha_vencimiento' => optional($lote->fecha_vencimiento)->format('d/m/Y'),
                    'stock_actual'      => $lote->stock_actual, // Stock siempre en unidades

                    'precios'           => $precios,
                    'factores'          => $factores,
                    'precio_oferta'     => $lote->precio_oferta ? (float)$lote->precio_oferta : null,
                ];
            });

        return response()->json($lotes);
    }




    public function anular(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $venta = Venta::with(['detalles', 'sucursal', 'cliente'])->findOrFail($id);

            // Validaciones básicas de seguridad
            if ($venta->estado === 'ANULADO') {
                return back()->with('error', 'Esta venta ya está anulada.');
            }
            $notaCredito = $this->ventaService->anularVenta($user, $venta, "Solicitud del cliente");

            $mensaje = 'Venta anulada correctamente. ';
            if ($notaCredito->sunat_exito) {
                $mensaje .= 'Nota de Crédito enviada y aceptada por SUNAT.';
            } else {
                $mensaje .= 'Nota generada, pero hubo un error al enviar a SUNAT. Revisa el historial.';
            }

            return back()->with('success', $mensaje);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al anular: ' . $e->getMessage());
        }
    }
}
