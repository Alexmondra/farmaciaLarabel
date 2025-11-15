<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Listado de categorías con búsqueda.
     */
    public function index(Request $request)
    {
        $q = $request->input('q');

        $categorias = Categoria::when($q, function ($query) use ($q) {
            $query->where(function ($qq) use ($q) {
                $qq->where('nombre', 'like', "%{$q}%")
                    ->orWhere('descripcion', 'like', "%{$q}%");
            });
        })
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('inventario.categorias.index', compact('categorias', 'q'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        // Por defecto activo = true
        $categoria = new Categoria(['activo' => true]);
        return view('inventario.categorias.create', compact('categoria'));
    }

    /**
     * Guardar nueva categoría.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'activo'      => ['nullable', 'boolean'],
        ]);

        // Checkbox: si no viene, es false
        $data['activo'] = $request->has('activo');

        Categoria::create($data);

        return redirect()
            ->route('inventario.categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Categoria $categoria)
    {
        return view('inventario.categorias.edit', compact('categoria'));
    }

    /**
     * Actualizar categoría existente.
     */
    public function update(Request $request, Categoria $categoria)
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'activo'      => ['nullable', 'boolean'],
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
