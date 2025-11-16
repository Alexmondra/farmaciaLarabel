<?php

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Models\Compras\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class ProveedorController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:proveedores.ver')->only(['index', 'show']);
        $this->middleware('can:proveedores.crear')->only(['create', 'store']);
        $this->middleware('can:proveedores.editar')->only(['edit', 'update']);
        $this->middleware('can:proveedores.borrar')->only('destroy');
    }

    public function index(Request $request)
    {
        $busqueda = trim($request->input('buscar', ''));

        $proveedores = Proveedor::query()
            ->when($busqueda, function ($query) use ($busqueda) {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('razon_social', 'like', "%{$busqueda}%")
                        ->orWhere('ruc', 'like', "%{$busqueda}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('inventario.proveedores.index', compact('proveedores', 'busqueda'));
    }

    public function create()
    {
        return view('inventario.proveedores.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        Proveedor::create($data);

        return redirect()
            ->route('inventario.proveedores.index')
            ->with('success', 'Proveedor creado exitosamente.');
    }

    public function show(Proveedor $proveedor)
    {
        return view('inventario.proveedores.show', compact('proveedor'));
    }

    public function edit(Proveedor $proveedor)
    {
        return view('inventario.proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $data = $this->validatedData($request, $proveedor);

        $proveedor->update($data);

        return redirect()
            ->route('inventario.proveedores.index')
            ->with('success', 'Proveedor actualizado exitosamente.');
    }

    public function destroy(Proveedor $proveedor)
    {
        try {
            $proveedor->delete();

            return redirect()
                ->route('inventario.proveedores.index')
                ->with('success', 'Proveedor eliminado exitosamente.');
        } catch (QueryException $e) {
            return redirect()
                ->route('inventario.proveedores.index')
                ->with('error', 'No se puede eliminar el proveedor porque está siendo utilizado en otros registros.');
        }
    }

    protected function validatedData(Request $request, ?Proveedor $proveedor = null): array
    {
        $rules = [
            'razon_social' => 'required|string|max:180',
            'ruc' => [
                'required',
                'string',
                'size:11',
                Rule::unique('proveedores', 'ruc')->ignore(optional($proveedor)->id),
            ],
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'activo' => 'nullable|boolean',
        ];

        $data = $request->validate($rules);

        // checkbox: si no viene, false; si viene, true
        $data['activo'] = $request->boolean('activo');

        return $data;
    }
}
