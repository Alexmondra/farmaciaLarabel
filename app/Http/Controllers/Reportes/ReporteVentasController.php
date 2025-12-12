<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ventas\Venta;
use App\Models\Sucursal;
use App\Services\SucursalResolver;
use Carbon\Carbon;
use App\Services\ComprobanteService;


class ReporteVentasController extends Controller
{
    public function ventasDia(Request $request, SucursalResolver $resolver)
    {
        // 1. Resolver permisos
        $acceso = $resolver->resolverPara(auth()->user());

        $sucursalesDisponibles = [];
        if ($acceso['es_admin'] && $acceso['ids_filtro'] === null) {
            $sucursalesDisponibles = \App\Models\Sucursal::where('activo', true)->get();
        } elseif (!empty($acceso['ids_filtro'])) {
            $sucursalesDisponibles = \App\Models\Sucursal::whereIn('id', $acceso['ids_filtro'])->get();
        }

        // 2. Fecha (Por defecto HOY)
        $fecha = $request->input('fecha', \Carbon\Carbon::now()->format('Y-m-d'));

        // 3. Query Base
        $query = Venta::with(['cliente', 'usuario', 'sucursal'])
            ->whereDate('fecha_emision', $fecha);

        // Filtros
        if (!empty($acceso['ids_filtro'])) {
            $query->whereIn('sucursal_id', $acceso['ids_filtro']);
        }
        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }
        if ($request->filled('search')) {
            $busqueda = $request->search;
            $query->where(function ($q) use ($busqueda) {
                $q->where('serie', 'like', "%$busqueda%")
                    ->orWhere('numero', 'like', "%$busqueda%")
                    ->orWhereHas('cliente', function ($c) use ($busqueda) {
                        $c->where('nombre', 'like', "%$busqueda%")
                            ->orWhere('documento', 'like', "%$busqueda%");
                    });
            });
        }

        // 4. CALCULO DE TOTALES (KPIs) - IMPORTANTE: reorder() evita el error SQL
        $stats = $query->clone()
            ->reorder()
            ->where('estado', '!=', 'ANULADO')
            ->selectRaw("
                SUM(total_neto) as total,
                COUNT(*) as cantidad,
                SUM(CASE WHEN medio_pago LIKE '%efectivo%' THEN total_neto ELSE 0 END) as efectivo,
                SUM(CASE WHEN medio_pago LIKE '%yape%' THEN total_neto ELSE 0 END) as yape,
                SUM(CASE WHEN medio_pago LIKE '%plin%' THEN total_neto ELSE 0 END) as plin,
                SUM(CASE WHEN medio_pago LIKE '%tarjeta%' THEN total_neto ELSE 0 END) as tarjeta
            ")
            ->first();

        $kpiData = [
            'total'    => number_format($stats->total ?? 0, 2),
            'count'    => $stats->cantidad ?? 0,
            'efectivo' => number_format($stats->efectivo ?? 0, 2),
            'yape'     => number_format($stats->yape ?? 0, 2),
            'plin'     => number_format($stats->plin ?? 0, 2),
            'tarjeta'  => number_format($stats->tarjeta ?? 0, 2),
        ];

        // 5. Paginación (Aquí sí ordenamos para la lista)
        $ventas = $query->orderBy('fecha_emision', 'desc')->paginate(20);

        // 6. Respuesta AJAX (Para recarga sin parpadeo)
        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('reportes.ventas._tabla', compact('ventas'))->render(),
                'kpi'        => $kpiData
            ]);
        }

        // 7. Vista Normal
        return view('reportes.ventas.dia', compact(
            'ventas',
            'kpiData',
            'fecha',
            'sucursalesDisponibles'
        ));
    }


    public function ventasHistorial(Request $request, SucursalResolver $resolver)
    {
        // 1. Resolver permisos
        $acceso = $resolver->resolverPara(auth()->user());

        $sucursalesDisponibles = [];
        if ($acceso['es_admin'] && $acceso['ids_filtro'] === null) {
            $sucursalesDisponibles = \App\Models\Sucursal::where('activo', true)->get();
        } elseif (!empty($acceso['ids_filtro'])) {
            $sucursalesDisponibles = \App\Models\Sucursal::whereIn('id', $acceso['ids_filtro'])->get();
        }

        // 2. Fechas (Blindado contra errores)
        try {
            $fechaInicio = $request->filled('fecha_inicio')
                ? \Carbon\Carbon::parse($request->fecha_inicio)->startOfDay()
                : \Carbon\Carbon::now()->startOfMonth();
            $fechaFin = $request->filled('fecha_fin')
                ? \Carbon\Carbon::parse($request->fecha_fin)->endOfDay()
                : \Carbon\Carbon::now()->endOfDay();
        } catch (\Exception $e) {
            $fechaInicio = \Carbon\Carbon::now()->startOfMonth();
            $fechaFin    = \Carbon\Carbon::now()->endOfDay();
        }

        // 3. Query Base (Sin Order By todavía)
        $query = Venta::with(['cliente', 'usuario', 'sucursal'])
            ->whereBetween('fecha_emision', [$fechaInicio, $fechaFin]);

        // Filtros
        if (!empty($acceso['ids_filtro'])) {
            $query->whereIn('sucursal_id', $acceso['ids_filtro']);
        }
        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }
        if ($request->filled('search')) {
            $busqueda = $request->search;
            $query->where(function ($q) use ($busqueda) {
                $q->where('serie', 'like', "%$busqueda%")
                    ->orWhere('numero', 'like', "%$busqueda%")
                    ->orWhereHas('cliente', function ($c) use ($busqueda) {
                        $c->where('nombre', 'like', "%$busqueda%")
                            ->orWhere('documento', 'like', "%$busqueda%");
                    });
            });
        }

        // 4. CALCULO DE TOTALES (KPIs) - AQUÍ ESTABA EL ERROR
        // Usamos reorder() para quitar cualquier ordenamiento previo que rompa el SUM
        $stats = $query->clone()
            ->reorder() // <--- ESTO SOLUCIONA EL ERROR SQL
            ->where('estado', '!=', 'ANULADO')
            ->selectRaw("
                SUM(total_neto) as total,
                COUNT(*) as cantidad,
                SUM(CASE WHEN medio_pago LIKE '%efectivo%' THEN total_neto ELSE 0 END) as efectivo,
                SUM(CASE WHEN medio_pago LIKE '%yape%' THEN total_neto ELSE 0 END) as yape,
                SUM(CASE WHEN medio_pago LIKE '%plin%' THEN total_neto ELSE 0 END) as plin,
                SUM(CASE WHEN medio_pago LIKE '%tarjeta%' THEN total_neto ELSE 0 END) as tarjeta
            ")
            ->first();

        $kpiData = [
            'total'    => number_format($stats->total ?? 0, 2),
            'count'    => $stats->cantidad ?? 0,
            'efectivo' => number_format($stats->efectivo ?? 0, 2),
            'yape'     => number_format($stats->yape ?? 0, 2),
            'plin'     => number_format($stats->plin ?? 0, 2),
            'tarjeta'  => number_format($stats->tarjeta ?? 0, 2),
        ];

        // 5. Paginación (Ahora sí aplicamos el orden para la lista)
        $ventas = $query->orderBy('fecha_emision', 'desc')->paginate(20);

        // 6. Respuesta AJAX
        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('reportes.ventas._tabla', compact('ventas'))->render(),
                'kpi'        => $kpiData
            ]);
        }

        // 7. Vista Normal
        $fInicioStr = $fechaInicio->format('Y-m-d');
        $fFinStr    = $fechaFin->format('Y-m-d');

        return view('reportes.ventas.historial', compact(
            'ventas',
            'kpiData',
            'fInicioStr',
            'fFinStr',
            'sucursalesDisponibles'
        ));
    }


    public function ventasAnuladas(Request $request, SucursalResolver $resolver)
    {
        $acceso = $resolver->resolverPara(auth()->user());

        // 1. Sucursales
        $sucursalesDisponibles = [];
        if ($acceso['es_admin'] && $acceso['ids_filtro'] === null) {
            $sucursalesDisponibles = \App\Models\Sucursal::where('activo', true)->get();
        } elseif (!empty($acceso['ids_filtro'])) {
            $sucursalesDisponibles = \App\Models\Sucursal::whereIn('id', $acceso['ids_filtro'])->get();
        }

        // 2. Fechas
        try {
            $fechaInicio = $request->filled('fecha_inicio')
                ? \Carbon\Carbon::parse($request->fecha_inicio)->startOfDay()
                : \Carbon\Carbon::now()->startOfMonth();
            $fechaFin = $request->filled('fecha_fin')
                ? \Carbon\Carbon::parse($request->fecha_fin)->endOfDay()
                : \Carbon\Carbon::now()->endOfDay();
        } catch (\Exception $e) {
            $fechaInicio = \Carbon\Carbon::now()->startOfMonth();
            $fechaFin    = \Carbon\Carbon::now()->endOfDay();
        }

        // 3. Query (SOLO ANULADAS)
        $query = Venta::with(['cliente', 'usuario', 'sucursal'])
            ->where('estado', 'ANULADO') // <--- EL FILTRO CLAVE
            ->whereBetween('fecha_emision', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_emision', 'desc');

        // Filtros extra
        if (!empty($acceso['ids_filtro'])) {
            $query->whereIn('sucursal_id', $acceso['ids_filtro']);
        }
        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }
        if ($request->filled('search')) {
            $busqueda = $request->search;
            $query->where(function ($q) use ($busqueda) {
                $q->where('serie', 'like', "%$busqueda%")
                    ->orWhere('numero', 'like', "%$busqueda%")
                    ->orWhereHas('cliente', function ($c) use ($busqueda) {
                        $c->where('nombre', 'like', "%$busqueda%")
                            ->orWhere('documento', 'like', "%$busqueda%");
                    });
            });
        }

        // 4. KPIs (Dinero Perdido)
        $stats = $query->clone()->reorder()
            ->selectRaw("SUM(total_neto) as total, COUNT(*) as cantidad")
            ->first();

        $kpiData = [
            'total' => number_format($stats->total ?? 0, 2),
            'count' => $stats->cantidad ?? 0
        ];

        // 5. Paginación
        $ventas = $query->paginate(20);

        // 6. AJAX
        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('reportes.ventas._tabla', compact('ventas'))->render(),
                'kpi'        => $kpiData
            ]);
        }

        // 7. Vista
        $fInicioStr = $fechaInicio->format('Y-m-d');
        $fFinStr    = $fechaFin->format('Y-m-d');

        return view('reportes.ventas.anuladas', compact(
            'ventas',
            'kpiData',
            'fInicioStr',
            'fFinStr',
            'sucursalesDisponibles'
        ));
    }

    public function descargarPdf($id, ComprobanteService $pdfService)
    {
        $venta = Venta::with(['detalles.medicamento', 'cliente', 'sucursal'])
            ->findOrFail($id);
        return $pdfService->generarPdf($venta, 'stream');
    }
}
