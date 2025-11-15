<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Inventario\Medicamento;
use App\Models\Inventario\Lote;
use App\Models\Sucursal;
use App\Services\SucursalResolver;

class MedicamentoController extends Controller
{
    protected SucursalResolver $sucursalResolver;

    public function __construct(SucursalResolver $sucursalResolver)
    {
        $this->sucursalResolver = $sucursalResolver;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $q    = trim($request->get('q', ''));

        // 1) Resolver contexto de sucursal desde el servicio
        $ctx = $this->sucursalResolver->resolverPara($user);

        $esAdmin              = $ctx['es_admin'];
        $idsFiltroSucursales  = $ctx['ids_filtro'];            // null | [] | [id,...]
        $sucursalSeleccionada = $ctx['sucursal_seleccionada']; // modelo Sucursal o null

        // 2) Consulta base de medicamentos
        $query = Medicamento::query()
            ->with(['categoria'])
            ->when($q, function ($s) use ($q) {
                $s->where(function ($w) use ($q) {
                    $w->where('nombre', 'like', "$q%")
                        ->orWhere('codigo', 'like', "$q%")
                        ->orWhere('codigo_barra', 'like', "$q%")
                        ->orWhere('laboratorio', 'like', "$q%");
                });
            });

        // 3) Filtrar medicamentos según sucursales que puede ver

        if (is_array($idsFiltroSucursales)) {

            if (count($idsFiltroSucursales) === 0) {
                // Usuario sin sucursales
                $query->whereRaw('1 = 0');
            } else {

                // Medicamentos que existan en esas sucursales
                $query->whereHas('sucursales', function ($w) use ($idsFiltroSucursales) {
                    $w->whereIn('sucursal_id', $idsFiltroSucursales);
                });

                // Cargar solo esas sucursales (para nombres y pivot)
                $query->with(['sucursales' => function ($q_suc) use ($idsFiltroSucursales) {
                    $q_suc->whereIn('sucursales.id', $idsFiltroSucursales);
                }]);
            }
        } elseif ($idsFiltroSucursales === null) {
            // Admin sin filtro -> todas las sucursales del medicamento
            $query->with('sucursales');
        }

        // 4) Si HAY sucursal seleccionada -> usar withSum (subconsulta) para stock_unico
        if ($sucursalSeleccionada) {
            $sid = $sucursalSeleccionada->id;

            $query->withSum(
                ['lotes as stock_unico' => function ($q_lotes) use ($sid) {
                    $q_lotes->where('sucursal_id', $sid);
                }],
                'stock_actual'
            );

            // Nos aseguramos de cargar solo esa sucursal en la relación (con pivot)
            $query->with(['sucursales' => function ($q_suc) use ($sid) {
                $q_suc->where('sucursales.id', $sid);
            }]);
        }

        // 5) Ejecutar consulta paginada
        $medicamentos = $query
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        // 6) Post-procesar según haya o no sucursalSeleccionada

        if ($sucursalSeleccionada) {

            // ✅ Caso: una sucursal en contexto
            $medicamentos->getCollection()->transform(function ($m) use ($sucursalSeleccionada) {

                // Buscar la sucursal seleccionada dentro de la relación
                $suc   = $m->sucursales->firstWhere('id', $sucursalSeleccionada->id);
                $pivot = $suc?->pivot;

                // Precio desde pivot
                $m->precio_v = $pivot?->precio_venta;

                // stock_unico viene de withSum (puede venir null)
                $m->stock_unico = (int) ($m->stock_unico ?? 0);

                // Por consistencia, rellenamos stock_por_sucursal con un solo registro
                $m->stock_por_sucursal = collect([[
                    'sucursal_id'   => $sucursalSeleccionada->id,
                    'sucursal_name' => $sucursalSeleccionada->nombre,
                    'stock'         => $m->stock_unico,
                ]]);

                return $m;
            });
        } else {

            // ✅ Caso: NO hay sucursal seleccionada -> desglose por sucursal

            // IDs de medicamentos que están en esta página
            $idsMedicamentos = $medicamentos->pluck('id');

            // Consulta agregada a LOTES (una sola vez para todos los de la página)
            $stocksRaw = Lote::select(
                'medicamento_id',
                'sucursal_id',
                DB::raw('SUM(stock_actual) as stock')
            )
                ->whereIn('medicamento_id', $idsMedicamentos)
                ->when(is_array($idsFiltroSucursales) && count($idsFiltroSucursales) > 0, function ($q) use ($idsFiltroSucursales) {
                    $q->whereIn('sucursal_id', $idsFiltroSucursales);
                })
                ->groupBy('medicamento_id', 'sucursal_id')
                ->get();

            // Agrupamos por medicamento
            $stocksPorMedicamento = $stocksRaw->groupBy('medicamento_id');

            // Transformamos los medicamentos agregando stock_por_sucursal
            $medicamentos->getCollection()->transform(function ($m) use ($stocksPorMedicamento) {

                $rows = $stocksPorMedicamento->get($m->id, collect());

                // Mapa de sucursales ya cargadas (para nombres)
                $mapSucursales = $m->sucursales->keyBy('id');

                $m->stock_por_sucursal = $rows->map(function ($row) use ($mapSucursales) {
                    $sucursal = $mapSucursales->get($row->sucursal_id);
                    $nombre   = $sucursal ? $sucursal->nombre : ('Sucursal ID ' . $row->sucursal_id);

                    return [
                        'sucursal_id'   => $row->sucursal_id,
                        'sucursal_name' => $nombre,
                        'stock'         => (int) $row->stock,
                    ];
                })->values();

                // En este modo no hay un solo precio ni un solo stock_unico
                $m->precio_v    = null;
                $m->stock_unico = null;

                return $m;
            });
        }

        return view('inventario.medicamentos.index', [
            'medicamentos'           => $medicamentos,
            'q'                      => $q,
            'esAdmin'                => $esAdmin,
            'sucursalSeleccionada'   => $sucursalSeleccionada,
            'idsFiltroSucursales'    => $idsFiltroSucursales,
        ]);
    }


    public function show($id)
    {
        $user = Auth::user();

        // 1) Resolver contexto de sucursal (igual que en index)
        $ctx = $this->sucursalResolver->resolverPara($user);

        $esAdmin              = $ctx['es_admin'];
        $idsFiltroSucursales  = $ctx['ids_filtro'];            // null | [] | [id,...]
        $sucursalSeleccionada = $ctx['sucursal_seleccionada']; // modelo Sucursal o null

        // 2) Obtener el medicamento con su categoría
        $medicamento = Medicamento::with('categoria')->findOrFail($id);

        // 3) Sucursales en las que se puede ver este medicamento según el contexto

        $relSucursales = $medicamento->sucursales(); // belongsToMany

        if (is_array($idsFiltroSucursales) && count($idsFiltroSucursales) > 0) {
            $relSucursales->whereIn('sucursales.id', $idsFiltroSucursales);
        }
        // si idsFiltroSucursales es null y es admin -> no filtramos (todas las sucursales del medicamento)

        $sucursales = $relSucursales->get();

        // 4) LOTES del medicamento (filtrados por las sucursales del contexto)

        $lotesQuery = Lote::where('medicamento_id', $medicamento->id);

        if (is_array($idsFiltroSucursales) && count($idsFiltroSucursales) > 0) {
            $lotesQuery->whereIn('sucursal_id', $idsFiltroSucursales);
        }
        // admin sin filtro: ve todos los lotes de todas las sucursales del medicamento

        $lotes = $lotesQuery
            ->orderBy('sucursal_id')
            ->orderBy('fecha_vencimiento')
            ->get();

        // 5) Agrupamos lotes por sucursal para la vista
        $lotesPorSucursal = $lotes->groupBy('sucursal_id');

        // 6) Construimos un arreglo de detalle por sucursal:
        //    sucursal, precio_venta (pivot), stock_total, lotes
        $sucursalesDetalle = $sucursales->map(function ($sucursal) use ($lotesPorSucursal) {
            $lotesSucursal = $lotesPorSucursal->get($sucursal->id, collect());
            $stockTotal    = $lotesSucursal->sum('stock_actual');
            $precioVenta   = $sucursal->pivot->precio_venta ?? null;

            return [
                'sucursal'    => $sucursal,
                'stock_total' => $stockTotal,
                'precio'      => $precioVenta,
                'lotes'       => $lotesSucursal,

            ];
        });

        return view('inventario.medicamentos.show', [
            'medicamento'         => $medicamento,
            'esAdmin'             => $esAdmin,
            'sucursalSeleccionada' => $sucursalSeleccionada,
            'sucursalesDetalle'   => $sucursalesDetalle,
        ]);
    }



    public function lookup(Request $request)
    {
        $q = trim($request->query('q') ?? $request->query('term') ?? '');

        if ($q === '') {
            return response()->json(['exact' => null, 'suggestions' => []]);
        }

        // Consulta base: selecciona solo campos de medicamento
        $base = \App\Models\Inventario\Medicamento::query()
            ->select([
                'id',
                'codigo',
                'nombre',
                'categoria_id',
                'laboratorio',
                'presentacion',
                'forma_farmaceutica',
                'concentracion',
                'registro_sanitario',
                'codigo_barra', // importante: singular
                'descripcion',
                \DB::raw('imagen_path as imagen_url'),
            ]);

        // Coincidencia exacta por código interno, nombre o código de barra
        $exact = (clone $base)
            ->where('codigo', $q)
            ->orWhere('nombre', $q)
            ->orWhere('codigo_barra', $q)
            ->first();

        // Sugerencias si no hay exacto
        $suggestions = [];
        if (!$exact) {
            $like = '%' . str_replace(' ', '%', $q) . '%';
            $suggestions = (clone $base)
                ->where(function ($w) use ($like) {
                    $w->where('nombre', 'like', $like)
                        ->orWhere('codigo', 'like', $like)
                        ->orWhere('codigo_barra', 'like', $like);
                })
                ->limit(10)
                ->get();
        }

        return response()->json([
            'exact' => $exact,
            'suggestions' => $suggestions,
        ]);
    }


    public function create()
    {
        $categorias = \App\Models\Inventario\Categoria::orderBy('nombre')->get();
        $user = Auth::user();
        $esAdmin = $user->hasRole('Administrador');
        $sucursales = $esAdmin
            ? Sucursal::orderBy('nombre')->get()
            : $user->sucursales()->select('sucursales.*')->orderBy('sucursales.nombre')->get();

        return view('inventario.medicamentos.create',  compact('categorias', 'sucursales'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Determinar si es medicamento existente o nuevo
        $medicamentoExistente = $request->filled('medicamento_id');

        $rules = [
            'sucursal_id' => ['required', 'integer', 'exists:sucursales,id'],
            // Pivot
            'precio' => ['nullable', 'numeric', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'ubicacion' => ['nullable', 'string', 'max:120'],
            // Lote inicial
            'lote_codigo' => ['nullable', 'string', 'max:80'],
            'cantidad_inicial' => ['nullable', 'integer', 'min:0'],
            'fecha_vencimiento' => ['nullable', 'date'],
        ];

        // Si es medicamento NUEVO, validar campos del medicamento
        if (!$medicamentoExistente) {
            $rules = array_merge($rules, [
                'codigo' => ['required', 'string', 'max:50', 'unique:medicamentos,codigo'],
                'codigo_barras' => ['nullable', 'string', 'max:50'],
                'nombre' => ['required', 'string', 'max:150'],
                'laboratorio' => ['nullable', 'string', 'max:150'],
                'categoria_id' => ['nullable', 'integer', 'exists:categorias,id'],
                'imagen' => ['nullable', 'image', 'max:2048'],
            ]);
        } else {
            // Si es existente, solo validar que exista
            $rules['medicamento_id'] = ['required', 'integer', 'exists:medicamentos,id'];
        }

        $data = $request->validate($rules);
        $sucursalId = (int)$data['sucursal_id'];

        // Verificar permisos de sucursal
        if (!$user->hasRole('Administrador')) {
            $permitidas = $user->sucursales()->pluck('sucursales.id')->toArray();
            if (!in_array($sucursalId, $permitidas, true)) {
                return back()->withErrors('No tienes permiso para esa sucursal.')->withInput();
            }
        }

        DB::transaction(function () use ($data, $sucursalId, $request, $medicamentoExistente) {
            // Obtener o crear el medicamento
            if ($medicamentoExistente) {
                // Medicamento existente
                $m = Medicamento::findOrFail($data['medicamento_id']);

                // Verificar si ya está asociado a esta sucursal
                if ($m->sucursales()->where('sucursal_id', $sucursalId)->exists()) {
                    throw new \Exception('Este medicamento ya está registrado en la sucursal seleccionada.');
                }
            } else {
                // Medicamento NUEVO
                $imagenPath = null;
                if ($request->hasFile('imagen')) {
                    $imagenPath = $request->file('imagen')->store('medicamentos', 'public');
                }

                $m = Medicamento::create([
                    'codigo' => $data['codigo'],
                    'codigo_barras' => $data['codigo_barras'] ?? null,
                    'nombre' => $data['nombre'],
                    'laboratorio' => $data['laboratorio'] ?? null,
                    'categoria_id' => $data['categoria_id'] ?? null,
                    'imagen_path' => $imagenPath,
                    'user_id' => auth()->id(), // Agregar esto
                ]);
            }

            // Asociar a la sucursal con datos pivot
            $pivot = array_filter([
                'precio_venta' => $data['precio'] ?? null,
                'stock_minimo' => $data['stock_minimo'] ?? null,
                'ubicacion' => $data['ubicacion'] ?? null,
                'updated_by' => auth()->id(),
            ], fn($v) => !is_null($v));

            $m->sucursales()->attach($sucursalId, $pivot);

            // Crear lote inicial si hay cantidad
            if (!empty($data['cantidad_inicial'])) {
                Lote::create([
                    'medicamento_id' => $m->id,
                    'sucursal_id' => $sucursalId,
                    'codigo_lote' => $data['lote_codigo'] ?? 'LOTE-' . now()->format('YmdHis'),
                    'cantidad_inicial' => (int)$data['cantidad_inicial'],
                    'cantidad_actual' => (int)$data['cantidad_inicial'],
                    'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
                    'estado' => 'vigente',
                ]);
            }
        });

        $mensaje = $medicamentoExistente
            ? 'Lote agregado al medicamento existente.'
            : 'Medicamento creado correctamente.';

        return redirect()
            ->route('inventario.medicamentos.index')
            ->with('success', $mensaje);
    }
}
