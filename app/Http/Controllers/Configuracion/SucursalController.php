<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class SucursalController extends Controller
{
    public function index(Request $request)
    {
        $sucursales = Sucursal::orderBy('nombre')->get();
        $siguienteId = Sucursal::max('id') + 1;
        $numeroSerie = str_pad($siguienteId, 3, '0', STR_PAD_LEFT);

        $sugerenciaBoleta  = 'B' . $numeroSerie; // Ej: B004
        $sugerenciaFactura = 'F' . $numeroSerie; // Ej: F004
        $sugerenciaTiket = 'T' . $numeroSerie;

        return view('configuracion.sucursales.index', compact('sucursales', 'sugerenciaBoleta', 'sugerenciaFactura', 'sugerenciaTiket'));
    }

    public function create()
    {
        return view('configuracion.sucursales.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'              => ['required', 'string', 'max:120'],
            'direccion'           => ['nullable', 'string', 'max:200'],
            'telefono'            => ['nullable', 'string', 'max:30'],
            'impuesto_porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'imagen_sucursal'     => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],

            // --- NUEVOS CAMPOS ---
            'serie_boleta'        => ['required', 'string', 'max:4', 'unique:sucursales,serie_boleta'],
            'serie_factura'       => ['required', 'string', 'max:4', 'unique:sucursales,serie_factura'],
            'serie_ticket'       => ['required', 'string', 'max:4', 'unique:sucursales,serie_ticket'],

            // ---------------------

            'activo'              => ['sometimes', 'boolean'],
        ]);

        // Generar Código Interno (SUC-001)
        $ultimoId = Sucursal::max('id');
        $nuevoNumero = $ultimoId + 1;
        $data['codigo'] = 'SUC-' . str_pad($nuevoNumero, 3, '0', STR_PAD_LEFT);

        $data['activo'] = $request->boolean('activo');

        if ($request->hasFile('imagen_sucursal')) {
            $data['imagen_sucursal'] = $request->file('imagen_sucursal')->store('sucursales', 'public');
        }

        Sucursal::create($data);

        return redirect()->route('configuracion.sucursales.index')
            ->with('success', 'Sucursal registrada correctamente.');
    }

    public function edit(Sucursal $sucursal)
    {
        return view('configuracion.sucursales.edit', compact('sucursal'));
    }

    public function update(Request $request, Sucursal $sucursal)
    {
        $data = $request->validate([
            'nombre'              => ['required', 'string', 'max:120'],
            'direccion'           => ['nullable', 'string', 'max:200'],
            'telefono'            => ['nullable', 'string', 'max:30'],
            'impuesto_porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'imagen_sucursal'     => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],

            // --- VALIDAR SERIES (Ignorando el actual para no dar error de unique) ---
            'serie_boleta'        => ['required', 'string', 'max:4', 'unique:sucursales,serie_boleta,' . $sucursal->id],
            'serie_factura'       => ['required', 'string', 'max:4', 'unique:sucursales,serie_factura,' . $sucursal->id],
            'serie_ticket'       => ['required', 'string', 'max:4', 'unique:sucursales,serie_ticket,' . $sucursal->id],

            // -----------------------------------------------------------------------

            'activo'              => ['sometimes', 'boolean'],
        ]);

        $data['activo'] = $request->boolean('activo');

        if ($request->hasFile('imagen_sucursal')) {
            if ($sucursal->imagen_sucursal) {
                Storage::disk('public')->delete($sucursal->imagen_sucursal);
            }
            $data['imagen_sucursal'] = $request->file('imagen_sucursal')->store('sucursales', 'public');
        }

        $sucursal->update($data);

        return redirect()->route('configuracion.sucursales.index')
            ->with('success', 'Sucursal actualizada correctamente.');
    }

    public function destroy(Sucursal $sucursal)
    {
        try {
            // 1. Intentamos eliminar la imagen y el registro
            if ($sucursal->imagen_sucursal) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($sucursal->imagen_sucursal);
            }

            $sucursal->delete();

            return redirect()->route('configuracion.sucursales.index')
                ->with('success', 'Sucursal eliminada correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {

            if ($e->getCode() == "23000") {
                return redirect()->route('configuracion.sucursales.index')
                    ->with('error', 'No se puede eliminar: Esta sucursal tiene movimientos, cajas o usuarios asociados.');
            }

            return redirect()->route('configuracion.sucursales.index')
                ->with('error', 'Ocurrió un error inesperado al eliminar.');
        }
    }

    // ---------------------------------------------------------------
    // MÉTODOS DE SELECCIÓN (SIN CAMBIOS, SOLO COPIADOS PARA MANTENER)
    // ---------------------------------------------------------------

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
        $request->validate([
            'sucursal_id' => 'nullable|integer',
        ]);

        $user = auth()->user();
        $user->load('sucursales');

        if (!$request->filled('sucursal_id')) {
            session()->forget(['sucursal_id', 'sucursal_nombre']);
            return redirect()
                ->back()
                ->with('success', 'Filtro de sucursal eliminado.');
        }

        $esAdmin = method_exists($user, 'hasRole') ? $user->hasRole('Administrador') : false;
        $id = (int) $request->sucursal_id;

        if ($esAdmin) {
            $sucursal = \App\Models\Sucursal::find($id);
        } else {
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
