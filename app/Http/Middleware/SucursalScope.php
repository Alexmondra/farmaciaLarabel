<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SucursalScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) return abort(401);

        // Admin: sin límites de sucursal
        if ($user->hasRole('Administrador')) {
            return $next($request);
        }

        // necesita sucursal activa en sesión
        $activa = session('sucursal_id');
        if (!$activa) {
            return redirect()->back()->with('error', 'Selecciona una sucursal para continuar.');
        }

        // debe tener la sucursal asignada
        if (!$user->tieneSucursal((int)$activa)) {
            abort(403, 'No tienes acceso a la sucursal seleccionada.');
        }

        return $next($request);
    }
}
