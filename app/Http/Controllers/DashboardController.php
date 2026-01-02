<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\SucursalResolver;
use App\Models\Ventas\Venta;
use App\Models\Ventas\DetalleVenta;
use App\Models\Inventario\Lote;
use App\Models\Sucursal;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $sucursalResolver;

    public function __construct(SucursalResolver $sucursalResolver)
    {
        $this->sucursalResolver = $sucursalResolver;
    }

    public function index()
    {
        $user = Auth::user();

        if (!$user->can('reportes.ver')) {
            return redirect()->route('cajas.index')
                ->with('info', 'No tienes permisos para ver el dashboard, se te ha redirigido a Caja.');
        }

        // 1. Resolver contexto
        $ctx = $this->sucursalResolver->resolverPara($user);
        $sucursalesIds = $ctx['ids_filtro'];

        // Query Base
        $ventasQuery = Venta::query()->where('estado', '!=', 'ANULADO');

        if (!empty($sucursalesIds)) {
            $ventasQuery->whereIn('sucursal_id', $sucursalesIds);
        }

        // --- KPIS BÁSICOS ---
        $ventasHoy = (clone $ventasQuery)->whereDate('fecha_emision', Carbon::today())->sum('total_neto');
        $ventasMes = (clone $ventasQuery)->whereMonth('fecha_emision', Carbon::now()->month)->sum('total_neto');
        $ticketsHoy = (clone $ventasQuery)->whereDate('fecha_emision', Carbon::today())->count();
        $ticketsAyer = (clone $ventasQuery)->whereDate('fecha_emision', Carbon::yesterday())->count();

        // --- LÓGICA AVANZADA DE GRÁFICOS (7 DÍAS) ---

        // 1. Generar los últimos 7 días como base (para que no falten fechas en el gráfico)
        $fechasBase = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $fechasBase[$date->format('Y-m-d')] = [
                'label' => $date->format('d/m'),
                'valor' => 0
            ];
        }

        // 2. Obtener ventas globales agrupadas por día
        $rawVentasSemana = (clone $ventasQuery)
            ->select(DB::raw('DATE(fecha_emision) as fecha'), DB::raw('SUM(total_neto) as total'))
            ->where('fecha_emision', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->groupBy('fecha')
            ->get()
            ->keyBy('fecha'); // Indexamos por fecha para fácil acceso

        // 3. Rellenar los datos globales (merge con fechas base)
        $globalData = [];
        $chartLabels = [];
        foreach ($fechasBase as $fechaYMD => $info) {
            $total = isset($rawVentasSemana[$fechaYMD]) ? $rawVentasSemana[$fechaYMD]->total : 0;
            $globalData[] = $total;
            $chartLabels[] = $info['label'];
        }

        // 4. Obtener datos individuales POR SUCURSAL (Solo si es Admin o ve varias)
        $datasetsPorSucursal = [];

        // Si no hay filtro específico (ve todas) o tiene acceso a varias, traemos el detalle
        $sucursalesAAnalizar = (!empty($sucursalesIds))
            ? Sucursal::whereIn('id', $sucursalesIds)->get()
            : Sucursal::all();

        foreach ($sucursalesAAnalizar as $suc) {
            // Consulta específica por sucursal
            $ventasSuc = Venta::query()
                ->where('sucursal_id', $suc->id)
                ->where('estado', '!=', 'ANULADO')
                ->where('fecha_emision', '>=', Carbon::now()->subDays(6)->startOfDay())
                ->select(DB::raw('DATE(fecha_emision) as fecha'), DB::raw('SUM(total_neto) as total'))
                ->groupBy('fecha')
                ->pluck('total', 'fecha'); // Devuelve array [fecha => total]

            // Rellenar ceros para esta sucursal
            $dataSuc = [];
            foreach ($fechasBase as $fechaYMD => $info) {
                $dataSuc[] = $ventasSuc[$fechaYMD] ?? 0;
            }

            $datasetsPorSucursal[] = [
                'id' => $suc->id,
                'nombre' => $suc->nombre,
                'data' => $dataSuc,
                'color' => $this->getColorPorId($suc->id) // Función auxiliar o random
            ];
        }

        // --- RANKING SUCURSALES (TABLA) ---
        $rankingSucursales = (clone $ventasQuery)
            ->select('sucursal_id', DB::raw('SUM(total_neto) as total_dia'), DB::raw('COUNT(*) as transacciones'))
            ->whereDate('fecha_emision', Carbon::today())
            ->groupBy('sucursal_id')
            ->with('sucursal')
            ->orderByDesc('total_dia')
            ->get();

        // --- ALERTAS DE VENCIMIENTO ---
        $alertasVencimiento = Lote::query()
            ->when(!empty($sucursalesIds), fn($q) => $q->whereIn('sucursal_id', $sucursalesIds))
            ->where('stock_actual', '>', 0)
            ->whereDate('fecha_vencimiento', '>=', Carbon::today())
            ->whereDate('fecha_vencimiento', '<=', Carbon::today()->addDays(45))
            ->orderBy('fecha_vencimiento', 'asc')
            ->with(['medicamento', 'sucursal'])
            ->limit(10)
            ->get();

        $alertasStock = Lote::query()
            ->when(!empty($sucursalesIds), fn($q) => $q->whereIn('sucursal_id', $sucursalesIds))
            ->where('stock_actual', '>', 0)
            ->where('stock_actual', '<=', 10) // <--- UMBRAL DE BAJO STOCK
            ->with(['medicamento', 'sucursal'])
            ->orderBy('stock_actual', 'asc') // Los que tienen menos primero
            ->limit(10)
            ->get();

        $topProductos = DetalleVenta::select(
            'medicamentos.id',
            'medicamentos.nombre',
            'medicamentos.imagen_path',
            DB::raw('SUM(detalle_ventas.cantidad) as total_vendido'),
            DB::raw('SUM(detalle_ventas.subtotal_bruto) as total_dinero')
        )
            ->join('ventas', 'ventas.id', '=', 'detalle_ventas.venta_id')
            ->join('medicamentos', 'medicamentos.id', '=', 'detalle_ventas.medicamento_id')
            ->where('ventas.estado', '!=', 'ANULADO')
            ->whereMonth('ventas.fecha_emision', Carbon::now()->month)
            ->when(!empty($sucursalesIds), fn($q) => $q->whereIn('ventas.sucursal_id', $sucursalesIds))
            ->groupBy('medicamentos.id', 'medicamentos.nombre', 'medicamentos.imagen_path')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        // --- ÚLTIMAS VENTAS ---
        $ultimasVentas = (clone $ventasQuery)
            ->with(['sucursal', 'usuario'])
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return view('dashboard', compact(
            'ventasHoy',
            'ventasMes',
            'ticketsHoy',
            'ticketsAyer',
            'chartLabels',
            'globalData',
            'datasetsPorSucursal',
            'rankingSucursales',
            'alertasVencimiento',
            'alertasStock',
            'topProductos',
            'ultimasVentas',
            'ctx'
        ));
    }

    // Pequeño helper para dar colores consistentes a las sucursales
    private function getColorPorId($id)
    {
        // Paleta de colores "Neon" que se ven bien en modo oscuro y claro
        $colors = [
            '#4e73df',
            '#1cc88a',
            '#36b9cc',
            '#f6c23e',
            '#e74a3b',
            '#6f42c1',
            '#fd7e14',
            '#20c997',
        ];
        return $colors[$id % count($colors)];
    }
}
