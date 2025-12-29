<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Inventario\Medicamento;
use App\Models\Inventario\Categoria;
use App\Models\Inventario\Lote;
use App\Models\Sucursal;
use App\Services\SucursalResolver;
use App\Repositories\MedicamentoRepository;


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
        $min = $request->get('min'); // Nuevo
        $max = $request->get('max'); // Nuevo

        $ctx = $this->sucursalResolver->resolverPara($user);

        $ctx['min'] = $min;
        $ctx['max'] = $max;

        // 3. Delegamos el trabajo sucio al Repositorio
        $medicamentos = $this->medicamentoRepo->buscarMedicamentos($q, $ctx);

        // 4. Preparamos datos para la vista
        $data = [
            'medicamentos'         => $medicamentos,
            'q'                    => $q, // Para mantener el texto en el input
            'esAdmin'              => $ctx['es_admin'],
            'sucursalSeleccionada' => $ctx['sucursal_seleccionada'],
            'idsFiltroSucursales'  => $ctx['ids_filtro'],
        ];

        if ($request->ajax()) {
            return view('inventario.medicamentos._index_tabla', $data)->render();
        }

        return view('inventario.medicamentos.index', $data);
    }

    public function indexGeneral(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        $mode = $request->get('mode', 'auto'); // auto|nombre|codigo|laboratorio|todo

        $query = Medicamento::with('categoria');

        if ($q !== '') {

            // AUTO: si parece código (solo números, 8-14 dígitos) => código/barra exacto
            if ($mode === 'auto') {
                $digits = preg_replace('/\D+/', '', $q);

                if ($digits !== '' && strlen($digits) >= 8 && strlen($digits) <= 14) {
                    $query->where(function ($sub) use ($digits) {
                        $sub->where('codigo_barra', $digits)
                            ->orWhere('codigo', $digits);
                    });
                } else {
                    // texto => nombre + (presentación opcional)
                    $query->where(function ($sub) use ($q) {
                        $sub->where('nombre', 'LIKE', "%$q%")
                            ->orWhere('presentacion', 'LIKE', "%$q%");
                    });
                }
            }

            // MODO MANUAL (cuando el usuario elige)
            elseif ($mode === 'nombre') {
                $query->where('nombre', 'LIKE', "%$q%");
            } elseif ($mode === 'codigo') {
                $digits = preg_replace('/\D+/', '', $q);
                $query->where(function ($sub) use ($digits, $q) {
                    // si viene numérico, usa digits; si no, usa texto
                    if ($digits !== '') {
                        $sub->where('codigo_barra', $digits)->orWhere('codigo', $digits);
                    } else {
                        $sub->where('codigo', 'LIKE', "%$q%")
                            ->orWhere('codigo_barra', 'LIKE', "%$q%");
                    }
                });
            } elseif ($mode === 'laboratorio') {
                $query->where('laboratorio', 'LIKE', "%$q%");
            } elseif ($mode === 'todo') {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'LIKE', "%$q%")
                        ->orWhere('codigo', 'LIKE', "%$q%")
                        ->orWhere('codigo_barra', 'LIKE', "%$q%")
                        ->orWhere('laboratorio', 'LIKE', "%$q%")
                        ->orWhere('presentacion', 'LIKE', "%$q%")
                        ->orWhere('descripcion', 'LIKE', "%$q%");
                });
            }
        }

        $medicamentos = $query->orderBy('nombre', 'asc')->paginate(20);

        return view('inventario.medicamentos.general.general', compact('medicamentos', 'q', 'mode'));
    }



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

    public function storeRapido(Request $request)
    {
        // 1. VALIDACIÓN RIGUROSA
        $request->validate([
            'codigo'              => 'required|string|max:30|unique:medicamentos,codigo',
            'codigo_digemid'      => 'nullable|string|max:50',
            'nombre'              => 'required|string|max:180|unique:medicamentos,nombre', // Valida nombre repetido
            'codigo_barra'        => 'nullable|string|max:50|unique:medicamentos,codigo_barra', // Valida código barras repetido
            'laboratorio'         => 'nullable|string|max:120',
            'categoria_id'        => 'nullable|exists:categorias,id',
            'presentacion'        => 'nullable|string|max:120',
            'concentracion'       => 'nullable|string|max:100',
            'registro_sanitario'  => 'nullable|string|max:60',
            'descripcion'         => 'nullable|string',
            'unidades_por_envase' => 'required|integer|min:1',
            'afecto_igv'          => 'boolean',
            'imagen'              => 'nullable|image|max:2048', // Valida imagen (máx 2MB, jpg/png)
        ], [
            // Mensajes personalizados para que el usuario entienda qué pasó
            'codigo.unique'       => 'El Código Interno ya existe.',
            'nombre.unique'       => 'Ya existe un medicamento con ese Nombre.',
            'codigo_barra.unique' => 'Ese Código de Barras ya pertenece a otro producto.',
            'imagen.image'        => 'El archivo debe ser una imagen válida.',
            'imagen.max'          => 'La imagen no debe pesar más de 2MB.'
        ]);

        $urlImagen = null;
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('medicamentos', 'public');
            $urlImagen = asset('storage/' . $path);
        }

        // 3. CREAR MEDICAMENTO (Solo datos maestros)
        $medicamento = \App\Models\Inventario\Medicamento::create([
            'codigo'              => $request->codigo,
            'codigo_digemid'      => $request->codigo_digemid,
            'nombre'              => $request->nombre,
            'codigo_barra'        => $request->codigo_barra,
            'laboratorio'         => $request->laboratorio,
            'categoria_id'        => $request->categoria_id,
            'presentacion'        => $request->presentacion,
            'concentracion'       => $request->concentracion,
            'registro_sanitario'  => $request->registro_sanitario,
            'descripcion'         => $request->descripcion,
            'unidades_por_envase' => $request->unidades_por_envase,
            'afecto_igv'          => $request->has('afecto_igv') ? 1 : 0,
            'imagen_url'          => $urlImagen, // Guardamos la URL aquí
            'user_id'             => auth()->id(),
            'activo'              => true

        ]);

        return response()->json([
            'success' => true,
            'message' => 'Medicamento registrado con éxito.',
            'data'    => $medicamento
        ]);
    }


    public function updateRapido(Request $request, $id)
    {
        $med = Medicamento::findOrFail($id);

        $request->validate([
            'codigo'       => 'required|string|max:30|unique:medicamentos,codigo,' . $id,
            'codigo_digemid' => 'nullable|string|max:50',
            'nombre'       => 'required|string|max:180|unique:medicamentos,nombre,' . $id,
            'unidades_por_envase' => 'required|integer|min:1',
            // Validamos que sea booleano o 0/1
            'afecto_igv'   => 'sometimes|boolean',
        ]);

        $data = $request->except(['imagen']);

        // 1. CORRECCIÓN CATEGORÍA: Convertir cadena vacía a NULL para que no falle
        $data['categoria_id'] = $request->categoria_id ?: null;

        // 2. CORRECCIÓN IGV: Forzamos 1 o 0 según si el checkbox viajó o no
        $data['afecto_igv'] = $request->has('afecto_igv') ? 1 : 0;

        // Imagen
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('medicamentos', 'public');
            $data['imagen_path'] = $path;
        }

        $med->update($data);

        // 3. REFRESCO DE DATOS: Recargamos relaciones para devolver el objeto COMPLETO al JS
        // Esto es vital para que el modal muestre la info nueva sin recargar la página.
        $sucursalId = Auth::user()->sucursal_id ?? 1;

        $med->load(['categoria', 'sucursales' => function ($q) use ($sucursalId) {
            $q->where('sucursales.id', $sucursalId);
        }]);

        $sucursalData = $med->sucursales->first()->pivot ?? null;
        $imgUrl = $med->imagen_path ? asset('storage/' . $med->imagen_path) : null;

        // Construimos la misma estructura 'full_data' que usas en 'lookup'
        $fullData = [
            'id'                  => $med->id,
            'nombre'              => $med->nombre,
            'codigo'              => $med->codigo ?? 'S/C',
            'codigo_digemid'      => $med->codigo_digemid ?? '',
            'codigo_barra'        => $med->codigo_barra ?? '',
            'laboratorio'         => $med->laboratorio ?? '',
            'categoria_id'        => $med->categoria_id,
            'categoria_nombre'    => $med->categoria ? $med->categoria->nombre : 'Sin Categoría',
            'presentacion'        => $med->presentacion ?? '',
            'concentracion'       => $med->concentracion ?? '',
            'registro_sanitario'  => $med->registro_sanitario ?? '',
            'descripcion'         => $med->descripcion ?? '',
            'unidades_por_envase' => $med->unidades_por_envase ?? 1,
            'afecto_igv'          => $med->afecto_igv,
            'stock_actual'        => $sucursalData->stock_actual ?? 0,
            'precio_venta'        => $sucursalData->precio_venta ?? 0,
            'imagen_url'          => $imgUrl
        ];

        return response()->json([
            'success' => true,
            'message' => 'Actualizado correctamente.',
            'data'    => $fullData
        ]);
    }
}
