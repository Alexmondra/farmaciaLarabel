<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\Sucursal;
use App\Http\Requests\SucursalRequest; // <--- Importamos el Request
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request; // Solo para el index si usas Request genérico allí

class SucursalController extends Controller
{
    public function index()
    {
        $sucursales = Sucursal::orderBy('codigo')->get();

        // Lógica de sugerencias (Solo visual para el usuario al crear)
        $siguienteId = Sucursal::max('id') + 1;
        $numeroSerie = str_pad($siguienteId, 3, '0', STR_PAD_LEFT);

        $sugerenciaBoleta  = 'B' . $numeroSerie;
        $sugerenciaFactura = 'F' . $numeroSerie;
        $sugerenciaTicket  = 'TK' . substr($numeroSerie, 1);

        $sugerenciaNCBoleta  = 'BC' . substr($numeroSerie, 1);
        $sugerenciaNCFactura = 'FC' . substr($numeroSerie, 1);
        $sugerenciaGuia      = 'T' . $numeroSerie;

        // Sugerencia Código
        $ultimoCodigo = Sucursal::max('codigo');
        $sugerenciaCodigo = $ultimoCodigo ? str_pad($ultimoCodigo + 1, 4, '0', STR_PAD_LEFT) : '0000';

        return view('configuracion.sucursales.index', compact(
            'sucursales',
            'sugerenciaBoleta',
            'sugerenciaFactura',
            'sugerenciaTicket',
            'sugerenciaNCBoleta',
            'sugerenciaNCFactura',
            'sugerenciaGuia',
            'sugerenciaCodigo'
        ));
    }

    public function create()
    {
        // Retornamos la vista (las sugerencias no son estrictamente necesarias aquí si las pasas al view o las manejas con JS, 
        // pero el form usa old() o null)
        return view('configuracion.sucursales.create');
    }

    // Usamos SucursalRequest en lugar de Request
    public function store(SucursalRequest $request)
    {
        $data = $request->validated();

        // 2. Imagen
        if ($request->hasFile('imagen_sucursal')) {
            $data['imagen_sucursal'] = $request->file('imagen_sucursal')->store('sucursales', 'public');
        }

        // 3. Crear
        Sucursal::create($data);

        return redirect()->route('configuracion.sucursales.index')
            ->with('success', 'Sucursal registrada correctamente.');
    }

    public function edit(Sucursal $sucursal)
    {
        // Al editar, pasamos el objeto $sucursal a la vista
        return view('configuracion.sucursales.edit', compact('sucursal'));
    }

    // Usamos SucursalRequest aquí también
    public function update(SucursalRequest $request, Sucursal $sucursal)
    {
        // 1. Validar (Automático, ya sabe ignorar el ID actual)
        $data = $request->validated();

        // 2. Imagen
        if ($request->hasFile('imagen_sucursal')) {
            if ($sucursal->imagen_sucursal) {
                Storage::disk('public')->delete($sucursal->imagen_sucursal);
            }
            $data['imagen_sucursal'] = $request->file('imagen_sucursal')->store('sucursales', 'public');
        }

        // 3. Actualizar
        $sucursal->update($data);

        return redirect()->route('configuracion.sucursales.index')
            ->with('success', 'Sucursal actualizada correctamente.');
    }

    public function destroy(Sucursal $sucursal)
    {
        try {
            if ($sucursal->imagen_sucursal) Storage::disk('public')->delete($sucursal->imagen_sucursal);
            $sucursal->delete();
            return redirect()->route('configuracion.sucursales.index')->with('success', 'Sucursal eliminada.');
        } catch (\Exception $e) {
            return redirect()->route('configuracion.sucursales.index')->with('error', 'No se puede eliminar: Tiene datos asociados.');
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
