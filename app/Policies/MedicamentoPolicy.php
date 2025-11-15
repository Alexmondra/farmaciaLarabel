<?php

namespace App\Policies;

// 1. Asegúrate que la ruta sea 'Inventario' (con N)
use App\Models\Inventario\Medicamento;
use App\Models\User;

class MedicamentoPolicy
{
    /**
     * Determina si el usuario puede ver la lista de medicamentos.
     */
    public function viewAny(User $user): bool
    {
        // Puede ver la lista si tiene el permiso (el middleware 'sucursal.scope'
        // ya se encarga de validar que haya una sucursal activa)
        return $user->can('medicamentos.ver');
    }

    /**
     * Determina si el usuario puede ver un medicamento específico.
     * Esto es para validar si el medicamento pertenece a sus sucursales.
     */
    public function view(User $user, Medicamento $medicamento): bool
    {
        if ($user->hasRole('Administrador')) {
            return true;
        }

        // Revisa si el medicamento está en CUALQUIERA de las sucursales asignadas al usuario
        $sucursalesUsuario = $user->sucursales()->pluck('sucursales.id');

        return $medicamento->sucursales()
            ->whereIn('sucursal_id', $sucursalesUsuario)
            ->exists();
    }

    /**
     * Determina si el usuario puede crear medicamentos.
     */
    public function create(User $user): bool
    {
        // Puede crear si tiene el permiso
        return $user->can('medicamentos.crear');
    }

    /**
     * Determina si el usuario puede actualizar un medicamento.
     */
    public function update(User $user, Medicamento $medicamento): bool
    {
        // Para editar, usamos la misma lógica que para 'ver'
        // (Debe tener permiso Y el medicamento debe estar en sus sucursales)
        return $user->can('medicamentos.editar') && $this->view($user, $medicamento);
    }

    /**
     * Determina si el usuario puede eliminar un medicamento.
     */
    public function delete(User $user, Medicamento $medicamento): bool
    {
        // Misma lógica
        return $user->can('medicamentos.borrar') && $this->view($user, $medicamento);
    }
}
