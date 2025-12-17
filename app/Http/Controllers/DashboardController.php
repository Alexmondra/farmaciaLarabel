<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\SucursalResolver;
use App\Models\Ventas\Venta;
use App\Models\Ventas\DetalleVenta;
use App\Models\Inventario\Lote;
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

        // 1. Resolver contexto (¿Es admin global o local?)
        $ctx = $this->sucursalResolver->resolverPara($user);
        $sucursalesIds = $ctx['ids_filtro'];

        // Query Base para Ventas (Excluyendo anuladas)
        $ventasQuery = Venta::query()->where('estado', '!=', 'ANULADO');

        if (!empty($sucursalesIds)) {
            $ventasQuery->whereIn('sucursal_id', $sucursalesIds);
        }

        // --- KPI 1: VENTAS DE HOY ---
        $ventasHoy = (clone $ventasQuery)
            ->whereDate('fecha_emision', Carbon::today())
            ->sum('total_neto');

        // --- KPI 2: VENTAS DEL MES ---
        $ventasMes = (clone $ventasQuery)
            ->whereMonth('fecha_emision', Carbon::now()->month)
            ->whereYear('fecha_emision', Carbon::now()->year)
            ->sum('total_neto');

        // --- KPI 3: CANTIDAD TICKETS HOY ---
        $ticketsHoy = (clone $ventasQuery)
            ->whereDate('fecha_emision', Carbon::today())
            ->count();

        // --- GRÁFICO: VENTAS ÚLTIMOS 7 DÍAS ---
        $ventasSemana = (clone $ventasQuery)
            ->select(DB::raw('DATE(fecha_emision) as fecha'), DB::raw('SUM(total_neto) as total'))
            ->where('fecha_emision', '>=', Carbon::now()->subDays(7))
            ->groupBy('fecha')
            ->orderBy('fecha', 'asc')
            ->get();

        $chartLabels = $ventasSemana->pluck('fecha');
        $chartData   = $ventasSemana->pluck('total');

        // --- RANKING DE SUCURSALES (Para Admin/Dueño) ---
        // Muestra cuánto vendió cada sucursal HOY
        $rankingSucursales = (clone $ventasQuery)
            ->select('sucursal_id', DB::raw('SUM(total_neto) as total_dia'), DB::raw('COUNT(*) as transacciones'))
            ->whereDate('fecha_emision', Carbon::today())
            ->groupBy('sucursal_id')
            ->with('sucursal')
            ->orderByDesc('total_dia')
            ->get();

        // --- ALERTAS DE VENCIMIENTO (Top 5 Urgentes) ---
        $alertasVencimiento = Lote::query()
            ->when(!empty($sucursalesIds), fn($q) => $q->whereIn('sucursal_id', $sucursalesIds))
            ->where('stock_actual', '>', 0)
            ->whereDate('fecha_vencimiento', '>=', Carbon::today()) // No vencidos aun
            ->whereDate('fecha_vencimiento', '<=', Carbon::today()->addDays(30)) // Próximos 30 días
            ->orderBy('fecha_vencimiento', 'asc')
            ->with(['medicamento', 'sucursal'])
            ->limit(5)
            ->get();

        // --- TOP 5 PRODUCTOS MÁS VENDIDOS (DEL MES) ---
        $topProductos = DetalleVenta::select(
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

        // --- ÚLTIMAS 5 VENTAS ---
        $ultimasVentas = (clone $ventasQuery)
            ->with(['sucursal', 'usuario'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'ventasHoy',
            'ventasMes',
            'ticketsHoy',
            'chartLabels',
            'chartData',
            'topProductos',
            'ultimasVentas',
            'ctx',
            'rankingSucursales',
            'alertasVencimiento'
        ));
    }
}
