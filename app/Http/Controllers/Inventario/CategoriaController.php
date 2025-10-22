<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
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

    public function create()
    {
        $categoria = new Categoria(['activo' => true]);
        return view('inventario.categorias.create', compact('categoria'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'activo'      => ['nullable', 'boolean'],
        ]);
        $data['activo'] = $request->has('activo');

        Categoria::create($data);

        return redirect()->route('inventario.categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Categoria $categoria)
    {
        return view('inventario.categorias.edit', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'activo'      => ['nullable', 'boolean'],
        ]);
        $data['activo'] = $request->has('activo');

        $categoria->update($data);

        return redirect()->route('inventario.categorias.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->delete();

        return redirect()->route('inventario.categorias.index')
            ->with('success', 'Categoría eliminada.');
    }
}
