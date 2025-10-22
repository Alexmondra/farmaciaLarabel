<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarSucursal
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si el usuario no tiene sucursal (ej. Administrador general), acceso total
        if (!$user || !$user->sucursal_id) {
            return $next($request);
        }

        // Si la ruta tiene parámetro sucursal_id o similar, validarlo
        $sucursalParam = $request->route('sucursal') ?? $request->get('sucursal_id');
        if ($sucursalParam && (int)$sucursalParam !== (int)$user->sucursal_id) {
            abort(403, 'Acceso denegado a esta sucursal.');
        }

        return $next($request);
    }
}
