<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;

class SucursalContextController extends Controller
{
    public function cambiar(Request $request, Sucursal $sucursal)
    {
        $user = $request->user();

        // Admin (rol) ve todo; otros deben tener asignada la sucursal
        if (!$user->hasRole('Administrador') && !$user->tieneSucursal($sucursal->id)) {
            abort(403, 'No tienes acceso a esta sucursal.');
        }

        session(['sucursal_id' => $sucursal->id]);

        return back()->with('success', 'Sucursal activa: '.$sucursal->nombre);
    }
}
