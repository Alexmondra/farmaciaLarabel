<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MedicamentoSucursal;

class ReporteInventarioController extends Controller
{
    public function vencimientos(Request $request)
    {
        $sucursalId = Session::get('sucursal_id');
        $search = $request->input('search');

        $query = Lote::porVencer($sucursalId)
            ->with(['medicamento', 'sucursal']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                // Buscar por Código de Lote
                $q->where('codigo_lote', 'LIKE', "%{$search}%")
                    // O por Nombre del Medicamento
                    ->orWhereHas('medicamento', function ($sub) use ($search) {
                        $sub->where('nombre', 'LIKE', "%{$search}%")
                            ->orWhere('laboratorio', 'LIKE', "%{$search}%");
                    });
            });
        }

        $lotes = $query->orderBy('fecha_vencimiento', 'asc')
            ->paginate(10)
            ->appends(['search' => $search]);

        return view('reportes.inventario.vencimientos', compact('lotes', 'sucursalId', 'search'));
    }

    public function stockBajo(Request $request)
    {
        $sucursalId = Session::get('sucursal_id');

        // Parámetros de la URL
        $search = $request->input('search');
        $filtro = $request->input('filtro'); // 'todos', 'bajos', 'agotados'

        // 1. Iniciamos el Scope base (que ya tiene la columna stock_computado y el filtro del mínimo)
        $query = MedicamentoSucursal::conStockBajo($sucursalId)
            ->with(['medicamento', 'sucursal']); // Quitamos 'lotes' aquí, no lo necesitamos para calcular, ya lo hace el scope

        // 2. Buscador (Nombre, Código, Laboratorio)
        if ($search) {
            $query->whereHas('medicamento', function ($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                    ->orWhere('codigo', 'LIKE', "%{$search}%")
                    ->orWhere('laboratorio', 'LIKE', "%{$search}%");
            });
        }

        // 3. DEFINICIÓN DE LA SUB-CONSULTA (La misma que usamos en el Modelo)
        // Necesitamos esto para filtrar Agotados vs Bajos vía SQL
        $sqlStockReal = '(SELECT COALESCE(SUM(stock_actual), 0) 
                          FROM lotes 
                          WHERE lotes.medicamento_id = medicamento_sucursal.medicamento_id 
                          AND lotes.sucursal_id = medicamento_sucursal.sucursal_id)';

        // 4. Aplicar Filtros de Estado
        if ($filtro === 'agotados') {
            // Solo los que tienen 0
            $query->whereRaw("{$sqlStockReal} = 0");
        } elseif ($filtro === 'bajos') {
            // Los que tienen más de 0 pero menos del mínimo
            $query->whereRaw("{$sqlStockReal} > 0");
        }
        // Si es 'todos', no agregamos nada extra (el scope ya filtra que sea <= minimo)

        // 5. PAGINACIÓN (La clave para no saturar)
        // Usamos paginate() en lugar de get(). 
        // appends() sirve para que al cambiar de página 1 a 2, no se pierdan los filtros.
        $stocks = $query->paginate(10)->appends($request->all());

        return view('reportes.inventario.stock_bajo', compact('stocks', 'sucursalId', 'search', 'filtro'));
    }
}
