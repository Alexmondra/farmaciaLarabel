<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Inventario\MedicamentoSucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Exports\DigemidExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class DigemidReporteController extends Controller
{
    private $columnasMaestras = [
        'cod_establecimiento' => 'Cód. Estab. (Sucursal)',
        'codigo_digemid'      => 'Cód. DIGEMID (Prod)',
        'nombre'              => 'Producto',
        'laboratorio'         => 'Laboratorio',
        'presentacion'        => 'Presentación',
        'precio_venta'        => 'Precio Venta',
        'stock_computado'     => 'Stock Disponible',
        'estado'              => 'Estado'
    ];

    public function index(Request $request)
    {
        // 1. Obtener Sucursal
        $sucursalId = Session::get('sucursal_id') ?? auth()->user()->sucursal_id;

        // Validación: Si no hay sucursal, mostramos error
        if (!$sucursalId) {
            if ($request->ajax()) {
                return '<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>Selecciona una sucursal primero.</div>';
            }
            return view('reportes.digemid.index', [
                'sinSucursal' => true,
                'resultados' => collect([]),
                'columnasDisponibles' => $this->columnasMaestras,
                'colsSeleccionadas' => []
            ]);
        }

        // 2. Columnas
        $colsDefault = ['cod_establecimiento', 'codigo_digemid', 'nombre', 'laboratorio', 'precio_venta', 'stock_computado', 'estado'];
        $colsSeleccionadas = $request->input('cols', $colsDefault);

        // 3. Consulta Blindada
        $query = $this->construirConsulta($request, $sucursalId);
        $resultados = $query->paginate(20)->withQueryString();

        // 4. AJAX
        if ($request->ajax()) {
            return view('reportes.digemid._tabla', [
                'resultados' => $resultados,
                'colsSeleccionadas' => $colsSeleccionadas,
                'columnasDisponibles' => $this->columnasMaestras
            ]);
        }

        // 5. Normal
        return view('reportes.digemid.index', [
            'sinSucursal' => false,
            'resultados' => $resultados,
            'columnasDisponibles' => $this->columnasMaestras,
            'colsSeleccionadas' => $colsSeleccionadas
        ]);
    }

    private function construirConsulta(Request $request, $sucursalId)
    {
        // SQL para calcular stock
        $sqlStock = '(SELECT COALESCE(SUM(stock_actual), 0) 
                      FROM lotes 
                      WHERE lotes.medicamento_id = medicamento_sucursal.medicamento_id 
                      AND lotes.sucursal_id = medicamento_sucursal.sucursal_id)';

        // === AQUÍ ESTÁ LA SOLUCIÓN DEL ERROR "VACÍO" ===
        // Usamos un array en el select para forzar el orden y evitar colisiones
        $query = MedicamentoSucursal::query()
            ->select([
                'medicamento_sucursal.*', // Mantenemos los datos del pivot (precio, activo, ids)
                'sucursales.cod_establecimiento_digemid as sucursal_cod_digemid', // Traemos el código de sucursal con alias
                DB::raw("{$sqlStock} as stock_computado") // Calculamos el stock
            ])
            ->with(['medicamento']) // Cargamos la relación
            ->where('medicamento_sucursal.sucursal_id', $sucursalId);

        // JOINS (Necesarios para filtrar y ordenar)
        $query->join('medicamentos', 'medicamento_sucursal.medicamento_id', '=', 'medicamentos.id');
        $query->join('sucursales', 'medicamento_sucursal.sucursal_id', '=', 'sucursales.id');

        // --- FILTROS ---

        // 1. Estado
        $estado = $request->input('estado_filtro', 'activos');
        if ($estado === 'activos') {
            $query->where('medicamento_sucursal.activo', true);
        } elseif ($estado === 'inactivos') {
            $query->where('medicamento_sucursal.activo', false);
        }

        // 2. Disponibilidad (Stock)
        $stockFiltro = $request->input('stock_filtro', 'todos');
        if ($stockFiltro === 'con_stock') {
            $query->whereRaw("{$sqlStock} > 0");
        }

        // 3. Buscador
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('medicamentos.nombre', 'LIKE', "%{$term}%")
                    ->orWhere('medicamentos.codigo', 'LIKE', "%{$term}%")
                    ->orWhere('medicamentos.codigo_digemid', 'LIKE', "%{$term}%")
                    ->orWhere('medicamentos.laboratorio', 'LIKE', "%{$term}%");
            });
        }

        // Ordenar A-Z
        $query->orderBy('medicamentos.nombre', 'asc');

        return $query;
    }

    public function exportar(Request $request)
    {
        $sucursalId = Session::get('sucursal_id') ?? auth()->user()->sucursal_id;
        if (!$sucursalId) {
            return back()->withErrors(['sucursal' => 'Selecciona una sucursal primero.']);
        }

        // ✅ Columnas (por defecto: todas las maestras)
        $colsDefault = array_keys($this->columnasMaestras);

        $colsSeleccionadas = $request->input('cols', $colsDefault);
        $colsSeleccionadas = is_array($colsSeleccionadas) ? $colsSeleccionadas : $colsDefault;

        // ✅ Sanitiza: solo permite columnas existentes
        $colsSeleccionadas = array_values(array_intersect($colsSeleccionadas, $colsDefault));
        if (empty($colsSeleccionadas)) {
            $colsSeleccionadas = $colsDefault;
        }

        // Consulta base (NO ejecutamos aún)
        $query = $this->construirConsulta($request, $sucursalId);

        $format = strtolower($request->input('format', 'excel'));

        // Nombre de archivo
        $nombreBase = 'monitor_digemid_' . now()->format('Ymd_His');

        // ✅ Umbrales
        $pdfWarnLimit = 500;   // desde aquí mostramos advertencia y pedimos confirmación
        $pdfHardCap   = 50000;  // desde aquí bloqueamos sí o sí para proteger el servidor

        // =========================
        //          PDF
        // =========================
        if ($format === 'pdf') {

            $confirmPdf = filter_var($request->query('confirm_pdf', false), FILTER_VALIDATE_BOOLEAN);

            // ✅ IMPORTANTE: primero solo contamos (mucho más liviano)
            $totalFilas = (clone $query)->count();

            // ✅ Hard cap de protección
            if ($totalFilas > $pdfHardCap) {
                return redirect()->to(url()->previous())->withErrors([
                    'pdf' => "El reporte tiene {$totalFilas} filas. Para proteger el servidor, filtra más o exporta en Excel."
                ]);
            }

            if ($totalFilas > $pdfWarnLimit && !$confirmPdf) {

                $params = $request->query(); // querystring actual del export

                $excelParams = $params;
                $excelParams['format'] = 'excel';
                unset($excelParams['confirm_pdf']);

                // Link PDF confirmado
                $pdfParams = $params;
                $pdfParams['format'] = 'pdf';
                $pdfParams['confirm_pdf'] = 1;

                $excelUrl = url()->current() . '?' . http_build_query($excelParams);
                $pdfUrl   = url()->current() . '?' . http_build_query($pdfParams);

                return redirect()->to(url()->previous())->with('pdf_confirm', [
                    'total'     => $totalFilas,
                    'warnLimit' => $pdfWarnLimit,
                    'excel_url' => $excelUrl,
                    'pdf_url'   => $pdfUrl,
                ]);
            }

            ini_set('memory_limit', '1024M');
            set_time_limit(300);

            $resultados = $query->get();

            $pdf = Pdf::loadView('reportes.digemid.pdf', [
                'resultados'          => $resultados,
                'colsSeleccionadas'   => $colsSeleccionadas,
                'columnasDisponibles' => $this->columnasMaestras,
                'limitePdf'           => $pdfWarnLimit,
            ])->setPaper('a4', 'landscape');

            return $pdf->download($nombreBase . '.pdf');
        }

        // =========================
        //          EXCEL
        // =========================
        $resultados = $query->get();

        return Excel::download(
            new DigemidExport($resultados, $colsSeleccionadas, $this->columnasMaestras),
            $nombreBase . '.xlsx'
        );
    }
}
