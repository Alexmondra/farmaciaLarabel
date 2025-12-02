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

        // 5. Respuesta AJAX (Solo Tabla) o Full (Página entera)
        if ($request->ajax()) {
            return view('inventario.medicamentos._index_tabla', $data)->render();
        }

        return view('inventario.medicamentos.index', $data);
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

        // Obtener la sucursal activa del usuario
        $user = \Illuminate\Support\Facades\Auth::user();
        // Si no usas SucursalResolver, puedes usar $user->sucursal_id directamente
        $sucursalId = $user->sucursal_id ?? 1;

        // Consulta optimizada
        $medicamentos = Medicamento::query()
            ->with(['categoria']) // Cargamos la categoría para obtener su nombre
            ->with(['sucursales' => function ($q) use ($sucursalId) {
                // Cargamos solo el precio/stock de la sucursal actual
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

            // PREPARAR IMAGEN: Tu modelo usa 'imagen_path', hay que convertirlo a URL
            $imgUrl = null;
            if ($med->imagen_path) {
                $imgUrl = asset('storage/' . $med->imagen_path);
            }

            return [
                'id' => $med->id,
                'text' => $med->nombre . ' - ' . ($med->presentacion ?? ''),

                // AQUÍ ESTÁ LA MAGIA: Enviamos TODOS los datos corregidos al JS
                'full_data' => [
                    'id'                  => $med->id,
                    'nombre'              => $med->nombre,
                    'codigo'              => $med->codigo ?? 'S/C',
                    'codigo_barra'        => $med->codigo_barra ?? '',

                    // CORRECCIÓN LABORATORIO: En tu modelo es un string, no una relación
                    'laboratorio'         => $med->laboratorio ?? '',

                    // CORRECCIÓN CATEGORÍA: Verificamos si existe la relación
                    'categoria'           => $med->categoria ? $med->categoria->nombre : 'Sin Categoría',

                    'presentacion'        => $med->presentacion ?? '',
                    'concentracion'       => $med->concentracion ?? '',
                    'registro_sanitario'  => $med->registro_sanitario ?? '',
                    'descripcion'         => $med->descripcion ?? '',
                    'unidades_por_envase' => $med->unidades_por_envase ?? 1,

                    // DATOS DE SUCURSAL
                    'stock_actual'        => $sucursalData->stock_actual ?? 0,
                    'precio_venta'        => $sucursalData->precio_venta ?? 0,

                    // IMAGEN CORRECTA
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
            'nombre'              => 'required|string|max:180|unique:medicamentos,nombre', // Valida nombre repetido
            'codigo_barra'        => 'nullable|string|max:50|unique:medicamentos,codigo_barra', // Valida código barras repetido
            'laboratorio'         => 'nullable|string|max:120',
            'categoria_id'        => 'nullable|exists:categorias,id',
            'presentacion'        => 'nullable|string|max:120',
            'concentracion'       => 'nullable|string|max:100',
            'registro_sanitario'  => 'nullable|string|max:60',
            'descripcion'         => 'nullable|string',
            'unidades_por_envase' => 'required|integer|min:1',
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
            'nombre'              => $request->nombre,
            'codigo_barra'        => $request->codigo_barra,
            'laboratorio'         => $request->laboratorio,
            'categoria_id'        => $request->categoria_id,
            'presentacion'        => $request->presentacion,
            'concentracion'       => $request->concentracion,
            'registro_sanitario'  => $request->registro_sanitario,
            'descripcion'         => $request->descripcion,
            'unidades_por_envase' => $request->unidades_por_envase,
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
}
