<?php

namespace App\Services;

use App\Models\Sucursal;
use App\Models\User;

class SucursalResolver
{
    /**
     * Devuelve información sobre cómo filtrar por sucursales.
     *
     * @return array{
     *   es_admin: bool,
     *   ids_filtro: ?array,        // null = todas, [] = ninguna, [1,2,...] = esas sucursales
     *   sucursal_seleccionada: ?Sucursal
     * }
     */
    public function resolverPara(User $user): array
    {
        $esAdmin = $user->hasRole('Administrador');
        $sucursalSeleccionadaId = session('sucursal_id');

        // 1) Si hay sucursal seleccionada en sesión -> usamos SOLO esa
        if (!empty($sucursalSeleccionadaId)) {
            $sucursal = Sucursal::find($sucursalSeleccionadaId);

            return [
                'es_admin'             => $esAdmin,
                'ids_filtro'           => $sucursal ? [$sucursal->id] : [],
                'sucursal_seleccionada' => $sucursal,
            ];
        }

        // 2) Admin SIN sucursal seleccionada -> ver TODAS (sin filtro)
        if ($esAdmin) {
            return [
                'es_admin'             => true,
                'ids_filtro'           => null,   // null = no filtrar por sucursal
                'sucursal_seleccionada' => null,
            ];
        }

        // 3) Usuario normal SIN sucursal seleccionada -> todas las que tenga asignadas
        $user->loadMissing('sucursales');

        $ids = $user->sucursales->pluck('id')->all(); // puede ser [1], [1,2], ...

        return [
            'es_admin'             => false,
            'ids_filtro'           => $ids,  // si solo tiene 1, será [id]; si 2, [id1,id2]
            'sucursal_seleccionada' => null,
        ];
    }
}
