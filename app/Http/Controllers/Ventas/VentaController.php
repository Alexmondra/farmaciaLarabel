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
            $busqueda = trim($request->search_q);
            $query->where(function ($q) use ($busqueda) {
                $q->whereHas('cliente', fn($c) => $c->where('nombre', 'LIKE', "%$busqueda%"));

                if (is_numeric($busqueda)) {
                    $q->orWhereRaw('CAST(numero AS UNSIGNED) = ?', [(int)$busqueda]);
                } elseif (str_contains($busqueda, '-')) {
                    $partes = explode('-', $busqueda);
                    if (count($partes) == 2) {
                        $serie = trim($partes[0]);
                        $numero = trim($partes[1]);

                        if (is_numeric($numero)) {
                            $q->orWhere(function ($sub) use ($serie, $numero) {
                                $sub->where('serie', 'LIKE', "%$serie%")
                                    ->whereRaw('CAST(numero AS UNSIGNED) = ?', [(int)$numero]);
                            });
                        }
                    }
                    $q->orWhere(DB::raw("CONCAT(serie, '-', numero)"), 'LIKE', "%$busqueda%");
                } else {
                    $q->orWhere('serie', 'LIKE', "%$busqueda%");
                }
            });
        } else {
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

        try {
            $venta = $this->ventaService->registrarVenta($user, $data);

            return redirect()->route('ventas.show', $venta->id)
                ->with('success', 'Venta registrada correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['general' => $e->getMessage()])->withInput();
        }
    }

    public function printTicket($id)
    {
        $user = Auth::user();
        $config = Configuracion::first();

        $venta = Venta::with(['detalles.medicamento', 'cliente', 'usuario', 'sucursal'])
            ->findOrFail($id);

        $this->validarAccesoVenta($user, $venta);

        $data = $this->armarDataImpresion($venta, $config);

        return view('ventas.ventas.print_ticket', $data);
    }

    public function printA4($id)
    {
        $user = Auth::user();
        $config = Configuracion::first();

        $venta = Venta::with(['detalles.medicamento', 'cliente', 'usuario', 'sucursal'])
            ->findOrFail($id);

        $this->validarAccesoVenta($user, $venta);

        $data = $this->armarDataImpresion($venta, $config);

        return view('ventas.ventas.print_a4', $data);
    }

    /**
     * MISMA validación que tu show()
     */
    private function validarAccesoVenta($user, Venta $venta): void
    {
        if (!$user->hasRole('Administrador')) {
            $tieneAcceso = DB::table('sucursal_user')
                ->where('user_id', $user->id)
                ->where('sucursal_id', $venta->sucursal_id)
                ->exists();

            if (!$tieneAcceso) {
                abort(403, 'Esta venta pertenece a otra sucursal y no tienes asignado acceso.');
            }
        }
    }

    private function armarDataImpresion(Venta $venta, Configuracion $config): array
    {

        $rutaLogo = $config->ruta_logo;
        $logoBase64 = $this->obtenerImagenBase64($rutaLogo);
        $rucEmisor = $config->empresa_ruc ?? '20000000001';
        $tipoDoc   = $venta->tipo_comprobante == 'FACTURA' ? '01' : '03';
        $fecha     = $venta->fecha_emision->format('Y-m-d');
        $clienteDocType = strlen($venta->cliente->documento) == 11 ? '6' : '1';
        $hash      = $venta->hash ?? '';

        $qrData = "{$rucEmisor}|{$tipoDoc}|{$venta->serie}|{$venta->numero}|{$venta->total_igv}|{$venta->total_neto}|{$fecha}|{$clienteDocType}|{$venta->cliente->documento}|{$hash}|";
        $qrBase64 = base64_encode(QrCode::format('svg')->size(150)->generate($qrData));

        $formatter = new NumeroALetras();
        $total = (float) $venta->total_neto;
        $entero = (int) floor($total);
        $decimal = (int) round(($total - $entero) * 100);

        if ($decimal === 100) {
            $entero += 1;
            $decimal = 0;
        }

        $letras = $formatter->toWords($entero);
        $montoLetras = "SON: " . Str::upper($letras) . " CON " . str_pad((string)$decimal, 2, '0', STR_PAD_LEFT) . "/100 SOLES";

        $ratio = $config->puntos_por_moneda ?? 1;
        $puntosGanados = intval($venta->total_neto * $ratio);
        $mensajePie = $config->mensaje_ticket ?? 'GRACIAS POR SU PREFERENCIA';

        return compact('venta', 'qrBase64', 'montoLetras', 'logoBase64', 'config', 'puntosGanados', 'mensajePie');
    }
    public function show($id)
    {
        $user = Auth::user();

        $config = Configuracion::first();

        $venta = Venta::with(['detalles.medicamento', 'cliente', 'usuario', 'sucursal'])
            ->findOrFail($id);

        if (!$user->hasRole('Administrador')) {
            $tieneAcceso = \DB::table('sucursal_user')
                ->where('user_id', $user->id)
                ->where('sucursal_id', $venta->sucursal_id)
                ->exists();

            if (!$tieneAcceso) {
                abort(403, 'Esta venta pertenece a otra sucursal y no tienes asignado acceso.');
            }
        }
        $rutaLogo = $venta->sucursal->imagen_sucursal ?? $config->ruta_logo;
        $logoBase64 = $this->obtenerImagenBase64($rutaLogo);
        $rucEmisor = $config->empresa_ruc ?? '20000000001';
        $tipoDoc   = $venta->tipo_comprobante == 'FACTURA' ? '01' : '03';
        $fecha     = $venta->fecha_emision->format('Y-m-d');
        $clienteDocType = strlen($venta->cliente->documento) == 11 ? '6' : '1';
        $hash      = $venta->hash ?? '';

        $qrData = "{$rucEmisor}|{$tipoDoc}|{$venta->serie}|{$venta->numero}|{$venta->total_igv}|{$venta->total_neto}|{$fecha}|{$clienteDocType}|{$venta->cliente->documento}|{$hash}|";

        $qrBase64 = base64_encode(QrCode::format('svg')->size(150)->generate($qrData));
        $formatter = new NumeroALetras();
        $entero = floor($venta->total_neto);
        $decimal = round(($venta->total_neto - $entero) * 100);
        $letras = $formatter->toWords($entero);
        $montoLetras = "SON: " . Str::upper($letras) . " CON {$decimal}/100 SOLES";
        return view('ventas.ventas.show', compact('venta', 'qrBase64', 'montoLetras', 'logoBase64', 'config'));
    }

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

        $term = trim((string)$request->q);
        $sucursalId = $request->sucursal_id;

        if (empty($term) && empty($request->categoria_id)) {
            return response()->json([]);
        }

        $query = \App\Models\Inventario\Medicamento::query()
            ->withoutGlobalScopes()
            ->where('activo', true);

        $query->with(['sucursales' => function ($q) use ($sucursalId) {
            $q->where('sucursal_id', $sucursalId);
        }]);

        if ($term) {
            // Detectamos si es código de barras (Numérico y largo)
            $esCodigoBarra = (is_numeric($term) && strlen($term) >= 5);

            if ($esCodigoBarra) {
                // --- MODO ESCÁNER: BÚSQUEDA EXACTA ---
                $query->where(function ($q) use ($term) {
                    $q->where('codigo_barra', '=', $term) // Uso de '=' en lugar de LIKE
                        ->orWhere('codigo_barra_blister', '=', $term)
                        ->orWhere('codigo', '=', $term);
                });
                $query->limit(1); // Solo necesitamos el primero si es exacto
            } else {
                // --- MODO TEXTO: BÚSQUEDA POR PARECIDO ---
                $query->where(function ($q) use ($term) {
                    $q->where('nombre', 'LIKE', "%{$term}%")
                        ->orWhere('codigo', 'LIKE', "%{$term}%")
                        ->orWhere('laboratorio', 'LIKE', "%{$term}%")
                        ->orWhere('descripcion', 'LIKE', "%{$term}%");
                });
                $query->limit(20);
            }
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        $resultados = $query->get()->map(function ($med) {
            $pivot = $med->sucursales->first()->pivot ?? null;
            $estaAsignado = !is_null($pivot);

            return [
                'asignado'                => $estaAsignado,
                'medicamento_sucursal_id' => $estaAsignado ? $med->sucursales->first()->id : null,
                'medicamento_id'          => $med->id,
                'nombre'                  => $med->nombre,
                'codigo'                  => $med->codigo,
                'presentacion'            => $med->forma_farmaceutica ?? '',
                'precio_venta'            => $estaAsignado ? (float) $pivot->precio_venta : 0.00,
            ];
        });

        return response()->json($resultados);
    }


    public function lookupLotes(Request $request)
    {
        $request->validate([
            'medicamento_id' => 'required|integer',
            'sucursal_id'    => 'required|integer',
        ]);

        $hoy = now()->toDateString();
        $sucursalId = $request->sucursal_id;
        $medicamentoId = $request->medicamento_id;

        // =========================================================
        // 1. OBTENER O CREAR RELACIÓN (CON HERENCIA DE PRECIOS)
        // =========================================================

        // Primero verificamos si YA existe en esta sucursal para no hacer consultas extra
        $ms = MedicamentoSucursal::with('medicamento')
            ->where('medicamento_id', $medicamentoId)
            ->where('sucursal_id', $sucursalId)
            ->first();

        if (!$ms) {
            $referencia = MedicamentoSucursal::where('medicamento_id', $medicamentoId)
                ->where('sucursal_id', '!=', $sucursalId)
                ->where('activo', true)
                ->where('precio_venta', '>', 0)
                ->orderBy('updated_at', 'desc')
                ->first();

            // Preparamos los valores por defecto
            $datosCreacion = [
                'stock_minimo'   => 0,
                'activo'         => true,
                'precio_venta'   => $referencia ? $referencia->precio_venta : 0,
                'precio_blister' => $referencia ? $referencia->precio_blister : 0,
                'precio_caja'    => $referencia ? $referencia->precio_caja : 0,
            ];

            // Creamos el registro con esos datos
            $ms = MedicamentoSucursal::create([
                'medicamento_id' => $medicamentoId,
                'sucursal_id'    => $sucursalId
            ] + $datosCreacion);

            // Cargamos la relación para leer factores abajo
            $ms->load('medicamento');
        }

        // =========================================================
        // 2. PREPARAR DATOS DE SALIDA
        // =========================================================

        $precios = [
            'unidad'  => (float) $ms->precio_venta,
            'blister' => (float) $ms->precio_blister,
            'caja'    => (float) $ms->precio_caja,
        ];

        $factores = [
            'blister' => $ms->medicamento->unidades_por_blister ?? 0,
            'caja'    => $ms->medicamento->unidades_por_envase ?? 1,
        ];

        // =========================================================
        // 3. BÚSQUEDA DE LOTES (STOCK)
        // =========================================================
        $lotes = \App\Models\Inventario\Lote::where('medicamento_id', $medicamentoId)
            ->where('sucursal_id', $sucursalId)
            ->where('stock_actual', '>', 0)
            ->where(function ($q) use ($hoy) {
                $q->whereDate('fecha_vencimiento', '>=', $hoy)
                    ->orWhereNull('fecha_vencimiento');
            })
            ->orderByRaw('fecha_vencimiento IS NULL, fecha_vencimiento ASC')
            ->limit(5)
            ->get();

        // =========================================================
        // 4. RESPALDO (SI NO HAY STOCK, CREAR LOTE VIRTUAL O NUEVO)
        // =========================================================
        if ($lotes->isEmpty()) {
            // Buscar lote antiguo para reutilizar datos visuales
            $loteRespaldo = \App\Models\Inventario\Lote::where('medicamento_id', $medicamentoId)
                ->where('sucursal_id', $sucursalId)
                ->orderBy('id', 'desc')
                ->first();

            // Si es totalmente nuevo y no tiene historial de lotes, creamos uno base
            if (!$loteRespaldo) {
                $loteRespaldo = \App\Models\Inventario\Lote::create([
                    'medicamento_id'    => $medicamentoId,
                    'sucursal_id'       => $sucursalId,
                    'codigo_lote'       => 'INI-' . date('dm'), // Código corto
                    'fecha_vencimiento' => null,
                    'stock_actual'      => 0,
                    'ubicacion'         => 'MOSTRADOR',
                    'precio_compra'     => 0,
                    'observaciones'     => 'Auto-creado al Asignar'
                ]);
            }
            $lotes = collect([$loteRespaldo]);
        }

        // =========================================================
        // 5. MAPEO FINAL
        // =========================================================
        $data = $lotes->map(function ($lote) use ($precios, $factores) {
            return [
                'id'                => $lote->id,
                'codigo_lote'       => $lote->codigo_lote,
                'fecha_vencimiento' => optional($lote->fecha_vencimiento)->format('d/m/Y') ?? 'SIN FECHA',
                'stock_actual'      => (int) $lote->stock_actual,
                'ubicacion'         => $lote->ubicacion ?? 'General',
                'precios'           => $precios,
                'factores'          => $factores,
                'virtual'           => false,
            ];
        });

        return response()->json($data);
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
