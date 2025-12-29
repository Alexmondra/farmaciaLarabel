<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Inventario\Medicamento;
use App\Services\SucursalResolver;
use App\Repositories\MedicamentoRepository;
use App\Http\Requests\Inventario\MedicamentoRequest;


class MedicamentoController extends Controller
{
    protected SucursalResolver $sucursalResolver;
    protected MedicamentoRepository $medicamentoRepo;

    public function __construct(SucursalResolver $sucursalResolver, MedicamentoRepository $medicamentoRepo)
    {
        $this->sucursalResolver = $sucursalResolver;
        $this->medicamentoRepo  = $medicamentoRepo;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $q   = trim($request->get('q', ''));
        $ctx = $this->sucursalResolver->resolverPara($user);

        // Delegamos al repositorio
        $medicamentos = $this->medicamentoRepo->buscarMedicamentos($q, $ctx);

        $data = [
            'medicamentos'         => $medicamentos,
            'q'                    => $q,
            'esAdmin'              => $ctx['es_admin'],
            'sucursalSeleccionada' => $ctx['sucursal_seleccionada'],
        ];

        if ($request->ajax()) {
            return view('inventario.medicamentos._index_tabla', $data)->render();
        }

        return view('inventario.medicamentos.index', $data);
    }

    public function indexGeneral(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        $mode = $request->get('mode', 'auto');

        $query = Medicamento::with('categoria');

        if ($q !== '') {
            if ($mode === 'auto') {
                $digits = preg_replace('/\D+/', '', $q);
                // Buscamos en ambos códigos de barra (Caja y Blíster)
                if ($digits !== '' && strlen($digits) >= 8) {
                    $query->where(function ($sub) use ($digits) {
                        $sub->where('codigo_barra', $digits)
                            ->orWhere('codigo_barra_blister', $digits) // <--- NUEVO
                            ->orWhere('codigo', $digits);
                    });
                } else {
                    $query->where(function ($sub) use ($q) {
                        $sub->where('nombre', 'LIKE', "%$q%")
                            ->orWhere('presentacion', 'LIKE', "%$q%");
                    });
                }
            } else {
                // (Mantenemos tu lógica simple para los otros modos por brevedad)
                $query->where('nombre', 'LIKE', "%$q%");
            }
        }

        $medicamentos = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('inventario.medicamentos.general.general', compact('medicamentos', 'q', 'mode'));
    }

    public function storeRapido(MedicamentoRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('imagen')) {
            $data['imagen_path'] = $request->file('imagen')->store('medicamentos', 'public');
        }
        $data['user_id']       = auth()->id();
        $data['activo']        = true;
        $data['afecto_igv']    = $request->has('afecto_igv');
        $data['receta_medica'] = $request->has('receta_medica');

        $medicamento = Medicamento::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Creado correctamente.',
            'data'    => $medicamento
        ]);
    }

    public function updateRapido(MedicamentoRequest $request, $id)
    {
        $med = Medicamento::findOrFail($id);

        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            $data['imagen_path'] = $request->file('imagen')->store('medicamentos', 'public');
        } else {
            // Importante: No sobrescribir imagen si no enviaron una nueva
            unset($data['imagen']);
        }

        $data['afecto_igv']    = $request->has('afecto_igv');
        $data['receta_medica'] = $request->has('receta_medica');

        // Limpiar llave foránea vacía
        $data['categoria_id']  = $request->categoria_id ?: null;

        $med->update($data);
        $med->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Actualizado correctamente.',
            'data'    => $med
        ]);
    }

    // AGREGAR ESTO EN MedicamentoController.php

    public function show($id)
    {
        $ctx = $this->sucursalResolver->resolverPara(Auth::user());

        $detalle = $this->medicamentoRepo->detalle($id, $ctx);

        return view('inventario.medicamentos.show', [
            'medicamento'         => $detalle['medicamento'],
            'sucursalesDetalle'   => $detalle['sucursalesDetalle'],
            'sucursalSeleccionada' => $ctx['sucursal_seleccionada'],
            'esAdmin'             => $ctx['es_admin'],
        ]);
    }

    public function lookup(Request $request)
    {
        $term = trim($request->get('term') ?: $request->get('q'));

        if (empty($term)) {
            return response()->json(['results' => []]);
        }

        $sucursalId = Auth::user()->sucursal_id ?? 1;

        $medicamentos = Medicamento::query()
            ->with(['categoria'])
            ->with(['sucursales' => function ($q) use ($sucursalId) {
                $q->where('sucursales.id', $sucursalId);
            }])
            ->where(function ($query) use ($term) {
                $query->where('nombre', 'LIKE', "%$term%")
                    ->orWhere('codigo', 'LIKE', "$term%")
                    ->orWhere('codigo_barra', 'LIKE', "$term%");
            })
            ->where('activo', true)
            ->limit(20)
            ->get();

        $results = $medicamentos->map(function ($med) {
            $sucursalData = $med->sucursales->first()->pivot ?? null;
            $imgUrl = $med->imagen_path ? asset('storage/' . $med->imagen_path) : null;

            return [
                'id' => $med->id,
                'text' => $med->nombre . ' - ' . ($med->presentacion ?? ''),

                // === DATOS COMPLETOS PARA JS ===
                'full_data' => [
                    'id'                  => $med->id,
                    'nombre'              => $med->nombre,
                    'codigo'              => $med->codigo ?? 'S/C',
                    'codigo_barra'        => $med->codigo_barra ?? '',
                    'laboratorio'         => $med->laboratorio ?? '',

                    // CORRECCIÓN 1: Agregamos categoria_id para que el Select funcione
                    'categoria_id'        => $med->categoria_id,
                    'categoria'           => $med->categoria ? $med->categoria->nombre : 'Sin Categoría',
                    'forma_farmaceutica'  => $med->forma_farmaceutica ?? '',
                    'presentacion'        => $med->presentacion ?? '',
                    'concentracion'       => $med->concentracion ?? '',
                    'registro_sanitario'  => $med->registro_sanitario ?? '',
                    'descripcion'         => $med->descripcion ?? '',
                    'unidades_por_envase' => $med->unidades_por_envase ?? 1,
                    'afecto_igv'          => $med->afecto_igv, // Booleano

                    'stock_actual'        => $sucursalData->stock_actual ?? 0,
                    'precio_venta'        => $sucursalData->precio_venta ?? 0,
                    'imagen_url'          => $imgUrl
                ]
            ];
        });

        return response()->json(['results' => $results]);
    }
}
