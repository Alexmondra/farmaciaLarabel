<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SucursalController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $sucursales = Sucursal::query()
            ->when($q, fn($w) => $w->where('nombre', 'like', "%$q%")
                ->orWhere('codigo', 'like', "%$q%")
                ->orWhere('direccion', 'like', "%$q%"))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('configuracion.sucursales.index', compact('sucursales', 'q'));
    }

    public function create()
    {
        return view('configuracion.sucursales.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:20', 'unique:sucursales,codigo'],
            'nombre' => ['required', 'string', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $data['activo'] = $request->boolean('activo');

        Sucursal::create($data);

        return redirect()->route('configuracion.sucursales.index')->with('success', 'Sucursal registrada correctamente.');
    }

    public function edit(Sucursal $sucursal)
    {
        return view('configuracion.sucursales.edit', compact('sucursal'));
    }

    public function update(Request $request, Sucursal $sucursal)
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:20', Rule::unique('sucursales', 'codigo')->ignore($sucursal->id)],
            'nombre' => ['required', 'string', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $data['activo'] = $request->boolean('activo');

        $sucursal->update($data);

        return redirect()->route('configuracion.sucursales.index')->with('success', 'Sucursal actualizada correctamente.');
    }

    public function destroy(Sucursal $sucursal)
    {
        $sucursal->delete();
        return redirect()->route('configuracion.sucursales.index')->with('success', 'Sucursal eliminada.');
    }
}
