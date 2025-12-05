<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;  // <-- ¡Importante!
use App\Services\SucursalResolver;
use App\Services\VentaService;
use App\Models\Ventas\Venta;
use App\Models\Ventas\CajaSesion;
use App\Models\Ventas\Cliente;      // <-- Nuevo
use App\Models\Inventario\Lote;     // <-- Nuevo 
use App\Models\Sucursal;
use App\Models\Inventario\MedicamentoSucursal;
use App\Models\Inventario\Categoria;


class VentaController extends Controller
{
    protected SucursalResolver $sucursalResolver;
    protected VentaService $ventaService;
    public function __construct(SucursalResolver $sucursalResolver, VentaService $ventaService)
    {
        $this->sucursalResolver = $sucursalResolver;
        $this->ventaService     = $ventaService;
    }

    /**
     * Muestra el índice de Ventas.
     * Solo muestra ventas si hay una caja ABIERTA.
     */
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

        $venta = Venta::with(['detalles.medicamento', 'cliente', 'usuario', 'sucursal'])
            ->findOrFail($id);

        if (!$user->hasRole('Administrador')) {
            if ($venta->sucursal_id !== $user->sucursal_id) {
                abort(403, 'No tienes permiso para ver esta venta.');
            }
        }

        // --- Generar QR ---
        $rucEmisor = $venta->sucursal->ruc ?? '20123456789';
        $tipoDoc   = $venta->tipo_comprobante == 'FACTURA' ? '01' : '03';
        $fecha     = $venta->fecha_emision->format('Y-m-d');
        $clienteDocType = strlen($venta->cliente->documento) == 11 ? '6' : '1';

        $qrString = "{$rucEmisor}|{$tipoDoc}|{$venta->serie}|{$venta->numero}|{$venta->total_igv}|{$venta->total_neto}|{$fecha}|{$clienteDocType}|{$venta->cliente->documento}|";

        // --- Convertir a Letras (Sin NumberFormatter) ---
        $montoLetras = $this->convertirNumeroALetras($venta->total_neto);

        return view('ventas.ventas.show', compact('venta', 'qrString', 'montoLetras'));
    }

    // --- FUNCIÓN AUXILIAR PARA NÚMERO A LETRAS (SIMPLE) ---
    private function convertirNumeroALetras($monto)
    {
        $monto = str_replace(',', '', $monto); // Quitamos comas por si acaso
        $entero = floor($monto);
        $decimal = round(($monto - $entero) * 100);

        // Función básica para enteros hasta 9999 (Suficiente para farmacia común)
        // Si necesitas millones, avísame para ampliarla, pero esto saca del apuro.
        $texto = $this->enteroALetras($entero);

        return "SON: " . strtoupper($texto) . " CON $decimal/100 SOLES";
    }

    private function enteroALetras($n)
    {
        $n = (int)$n;
        if ($n == 0) return 'CERO';

        $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decenas  = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        if ($n < 10) return $unidades[$n];

        if ($n < 20) {
            $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
            return $especiales[$n - 10];
        }

        if ($n < 100) {
            $d = floor($n / 10);
            $u = $n % 10;
            if ($u == 0) return $decenas[$d];
            if ($d == 2) return 'VEINTI' . $unidades[$u]; // Veintiuno, Veintidos...
            return $decenas[$d] . ' Y ' . $unidades[$u];
        }

        if ($n < 1000) {
            $c = floor($n / 100);
            $r = $n % 100;
            if ($n == 100) return 'CIEN';
            return $centenas[$c] . ($r > 0 ? ' ' . $this->enteroALetras($r) : '');
        }

        if ($n < 1000000) {
            $mil = floor($n / 1000);
            $r = $n % 1000;
            $txt = ($mil == 1) ? 'MIL' : $this->enteroALetras($mil) . ' MIL';
            return $txt . ($r > 0 ? ' ' . $this->enteroALetras($r) : '');
        }

        return 'MONTO MUY ALTO';
    }




    public function create(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->sucursalResolver->resolverPara($user);

        // 1. Verificar si hay caja abierta (requisito indispensable)
        $cajaAbierta = CajaSesion::where('user_id', $user->id)
            ->where('estado', 'ABIERTO')
            ->when($ctx['ids_filtro'], function ($q) use ($ctx) {
                $q->whereIn('sucursal_id', $ctx['ids_filtro']);
            })
            ->first();

        // 2. Si no hay caja, redirigir al index (que le mostrará el modal)
        if (!$cajaAbierta) {
            return redirect()->route('ventas.index')
                ->with('error', 'Debes abrir una caja antes de poder registrar una venta.');
        }

        // 3. Cargar clientes para el dropdown
        $categorias = Categoria::select('id', 'nombre')->orderBy('nombre')->get();

        // 4. Mandar a la vista
        return view('ventas.ventas.create', [
            'cajaAbierta' => $cajaAbierta,
            'categorias'    => $categorias,
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
            ->activos() // scopeActivos del modelo MedicamentoSucursal
            // 🔹 Solo medicamentos con al menos un lote disponible en esa sucursal
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
            // scopeBuscar: busca por nombre o código del medicamento
            $query->buscar($request->q);
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
                    'presentacion'            => $ms->medicamento->presentacion ?? null,
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

        // 1. Obtenemos el precio BASE de la sucursal primero (para no depender de relaciones complejas en Lote)
        $precioBase = MedicamentoSucursal::where('medicamento_id', $request->medicamento_id)
            ->where('sucursal_id', $request->sucursal_id)
            ->value('precio_venta'); // Obtiene solo el valor float directo

        $precioBase = $precioBase ? (float)$precioBase : 0.00;

        // 2. Buscamos los lotes
        $lotes = Lote::where('medicamento_id', $request->medicamento_id)
            ->where('sucursal_id', $request->sucursal_id)
            ->where('stock_actual', '>', 0)
            ->where(function ($q) use ($hoy) {
                $q->whereDate('fecha_vencimiento', '>=', $hoy)
                    ->orWhereNull('fecha_vencimiento');
            })
            ->orderBy('fecha_vencimiento', 'asc') // Prioridad: Vencimiento más próximo primero (FIFO/FEFO)
            ->get()
            ->map(function ($lote) use ($precioBase) {
                return [
                    'id'                => $lote->id,
                    'codigo_lote'       => $lote->codigo_lote,
                    'fecha_vencimiento' => optional($lote->fecha_vencimiento)->format('Y-m-d'),
                    'ubicacion'         => $lote->ubicacion,
                    'stock_actual'      => $lote->stock_actual,

                    // Usamos el precio base que buscamos arriba
                    'precio_venta'      => $precioBase,

                    // Si el lote tiene oferta específica, la mandamos
                    'precio_oferta'     => $lote->precio_oferta !== null
                        ? (float) $lote->precio_oferta
                        : null,
                ];
            });

        return response()->json($lotes);
    }
}
