<?php

namespace App\Policies;

use App\Models\Medicamento;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class MedicamentoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('productos.ver');
    }

    public function view(User $user, Medicamento $medicamento): bool
    {
        // Puede ver si tiene permiso y el medicamento está en alguna de sus sucursales
        if (!$user->can('productos.ver')) return false;

        $sucursalesUsuario = $user->sucursales->pluck('id')->toArray();
        return $medicamento->sucursales()->whereIn('sucursal_id', $sucursalesUsuario)->exists();
    }

    public function create(User $user): bool
    {
        return $user->can('productos.crear') && $user->sucursales()->exists();
    }

    public function update(User $user, Medicamento $medicamento): bool
    {
        // Puede editar si tiene permiso y el medicamento está en alguna de sus sucursales
        if (!$user->can('productos.editar')) return false;

        $sucursalesUsuario = $user->sucursales->pluck('id')->toArray();
        return $medicamento->sucursales()->whereIn('sucursal_id', $sucursalesUsuario)->exists();
    }

    public function delete(User $user, Medicamento $medicamento): bool
    {
        if (!$user->can('productos.borrar')) return false;

        $sucursalesUsuario = $user->sucursales->pluck('id')->toArray();
        return $medicamento->sucursales()->whereIn('sucursal_id', $sucursalesUsuario)->exists();
    }
}
