<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{

    public function index()
    {
        $categorias = Categoria::orderBy('nombre')->get();

        return view('inventario.categorias.index', compact('categorias'));
    }


    /**
     * Formulario de creación.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            // 'activo' no hace falta validarlo aquí si lo manejamos abajo
        ]);

        // El checkbox no envía nada si no está marcado, así que usamos has()
        $data['activo'] = $request->has('activo');

        Categoria::create($data);

        return redirect()
            ->route('inventario.categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Actualizar categoría (Viene del Modal).
     */
    public function update(Request $request, Categoria $categoria)
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
        ]);

        $data['activo'] = $request->has('activo');

        $categoria->update($data);

        return redirect()
            ->route('inventario.categorias.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    /**
     * Eliminar categoría.
     */
    public function destroy(Categoria $categoria)
    {
        $categoria->delete();

        return redirect()
            ->route('inventario.categorias.index')
            ->with('success', 'Categoría eliminada.');
    }
}
