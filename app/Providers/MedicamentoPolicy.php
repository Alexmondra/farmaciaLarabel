<?php

namespace App\Policies;

use App\Models\Medicamento;
use App\Models\User;
use App\Services\SucursalContext;

class MedicamentoPolicy
{
    protected $sucursalContext;

    public function __construct()
    {
        // Obtener el contexto de sucursal del container
        $this->sucursalContext = app(SucursalContext::class);
    }

    public function viewAny(User $user): bool
    {
        // Puede ver la lista si tiene permiso y tiene sucursal asignada
        return $user->can('medicamentos.ver') && $this->sucursalContext->hasSucursal();
    }

    public function view(User $user, Medicamento $medicamento): bool
    {
        // Puede ver si tiene permiso y el medicamento es de su sucursal
        return $user->can('medicamentos.ver') &&
            $medicamento->sucursal_id === $this->sucursalContext->getSucursalId();
    }

    public function create(User $user): bool
    {
        // Puede crear si tiene permiso y tiene sucursal asignada
        return $user->can('medicamentos.crear') && $this->sucursalContext->hasSucursal();
    }

    public function update(User $user, Medicamento $medicamento): bool
    {
        // Puede editar si tiene permiso y el medicamento es de su sucursal
        return $user->can('medicamentos.editar') &&
            $medicamento->sucursal_id === $this->sucursalContext->getSucursalId();
    }

    public function delete(User $user, Medicamento $medicamento): bool
    {
        // Puede eliminar si tiene permiso y el medicamento es de su sucursal
        return $user->can('medicamentos.borrar') &&
            $medicamento->sucursal_id === $this->sucursalContext->getSucursalId();
    }
}
