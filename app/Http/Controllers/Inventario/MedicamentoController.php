<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Inventario\Medicamento;
use App\Models\Inventario\Lote;
use App\Models\Sucursal;

class MedicamentoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $esAdmin = $user->hasRole('Administrador');
        $q = trim($request->get('q', ''));
        $sucursalFiltro = $request->get('sucursal_id');

        $sucursalesDisponibles = $esAdmin
            ? Sucursal::orderBy('nombre')->get()
            : $user->sucursales()->select('sucursales.*')->orderBy('sucursales.nombre')->get();

        if ($request->has('sucursal_id')) {
            session(['sucursal_id' => $sucursalFiltro]);
        } else {
            $sucursalFiltro = session('sucursal_id');
        }
        if (!$esAdmin && empty($sucursalFiltro)) {
            $sucursalFiltro = optional($sucursalesDisponibles->first())->id;
            session(['sucursal_id' => $sucursalFiltro]);
        }

        $query = Medicamento::query()
            ->with(['categoria'])
            ->when($q, function ($s) use ($q) {
                $s->where(function ($w) use ($q) {
                    $w->where('nombre', 'like', "%$q%")
                        ->orWhere('codigo', 'like', "%$q%")
                        ->orWhere('codigo_barras', 'like', "%$q%")
                        ->orWhere('laboratorio', 'like', "%$q%");
                });
            });

        if ($esAdmin) {
            if (!empty($sucursalFiltro)) {
                $query->whereHas('sucursales', fn($w) => $w->where('sucursal_id', $sucursalFiltro));
            }
        } else {
            $ids = $sucursalesDisponibles->pluck('id');
            $query->whereHas('sucursales', fn($w) => $w->whereIn('sucursal_id', $ids));
        }

        $medicamentos = $query->orderBy('nombre')->paginate(12)->withQueryString();

        $medicamentos->getCollection()->transform(function ($m) use ($esAdmin, $sucursalFiltro) {
            $m->loadMissing(['sucursales', 'lotes']);
            if ($esAdmin && empty($sucursalFiltro)) {
                $m->stock_total = (int)$m->lotes->sum('cantidad_actual');
                $m->desglose_stock = $m->lotes->groupBy('sucursal_id')->map->sum('cantidad_actual')->toArray();
            } else {
                $sid = (int)$sucursalFiltro;
                $m->stock = (int)$m->lotes->where('sucursal_id', $sid)->sum('cantidad_actual');
                $pivot = $m->sucursales()->where('sucursal_id', $sid)->first()?->pivot;
                $m->precio_v = $pivot?->precio_venta;
                $m->precio_c = $pivot?->precio_compra;
                $m->ubicacion = $pivot?->ubicacion;
            }
            return $m;
        });

        $usuarioMasDeUnaSucursal = $sucursalesDisponibles->count() > 1;

        return view('inventario.medicamentos.index', compact(
            'medicamentos',
            'q',
            'sucursalFiltro',
            'sucursalesDisponibles',
            'esAdmin',
            'usuarioMasDeUnaSucursal'
        ));
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

    public function show(Medicamento $medicamento)
    {
        $medicamento->load(['categoria', 'lotes.sucursal', 'sucursales']);
        // Agregados por sucursal para la vista
        $bySucursal = $medicamento->lotes->groupBy('sucursal_id')->map(function ($g) {
            return [
                'stock' => (int)$g->sum('cantidad_actual'),
                'lotes' => $g->sortBy('fecha_vencimiento')->values(),
            ];
        });

        return view('inventario.medicamentos.show', [
            'medicamento' => $medicamento,
            'bySucursal'  => $bySucursal,
        ]);
    }
}
