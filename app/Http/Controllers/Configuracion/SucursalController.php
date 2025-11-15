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





    public function elegir()
    {
        $user = auth()->user();
        $user->load('sucursales');

        if ($user->sucursales->isEmpty()) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'No tienes sucursales asignadas.');
        }

        return view('sucursales.elegir', [
            'sucursales' => $user->sucursales,
        ]);
    }

    public function guardarEleccion(Request $request)
    {
        $request->validate([
            'sucursal_id' => 'required|integer',
        ]);

        $user = auth()->user();
        $user->load('sucursales');

        $sucursal = $user->sucursales->firstWhere('id', $request->sucursal_id);

        if (!$sucursal) {
            return back()->withErrors([
                'sucursal_id' => 'Esta sucursal no pertenece al usuario.',
            ]);
        }

        session([
            'sucursal_id' => $sucursal->id,
            'sucursal_nombre' => $sucursal->nombre,
        ]);

        return redirect()->route('dashboard');
    }


    public function cambiarDesdeSelect(Request $request)
    {
        // Puede venir vacío (para limpiar filtro) o entero (para fijar sucursal)
        $request->validate([
            'sucursal_id' => 'nullable|integer',
        ]);

        $user = auth()->user();
        $user->load('sucursales');

        // 1) Si NO viene sucursal_id -> limpiar selección (ver todas las que permite el resolver)
        if (!$request->filled('sucursal_id')) {
            session()->forget(['sucursal_id', 'sucursal_nombre']);

            return redirect()
                ->back()
                ->with('success', 'Filtro de sucursal eliminado. Ahora ves todas las sucursales permitidas.');
        }

        // 2) Si viene sucursal_id -> fijar esa sucursal
        $esAdmin = method_exists($user, 'hasRole') ? $user->hasRole('Administrador') : false;
        $id = (int) $request->sucursal_id;

        if ($esAdmin) {
            // Admin puede elegir cualquier sucursal
            $sucursal = \App\Models\Sucursal::find($id);
        } else {
            // Usuario normal: solo sus sucursales
            $sucursal = $user->sucursales->firstWhere('id', $id);
        }

        if (!$sucursal) {
            return redirect()->back()->with('error', 'Sucursal no válida.');
        }

        session([
            'sucursal_id'     => $sucursal->id,
            'sucursal_nombre' => $sucursal->nombre,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Sucursal cambiada a: ' . $sucursal->nombre);
    }
}
