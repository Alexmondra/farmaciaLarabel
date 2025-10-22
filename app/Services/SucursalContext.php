<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class SucursalContext
{
    private ?int $sucursalId = null;

    public function setSucursalId(int $sucursalId): void
    {
        $this->sucursalId = $sucursalId;
        Session::put('sucursal_actual_id', $sucursalId);
    }

    public function getSucursalId(): ?int
    {
        if ($this->sucursalId !== null) {
            return $this->sucursalId;
        }

        // Intentar obtener de la sesión
        $this->sucursalId = Session::get('sucursal_actual_id');

        // Si no hay en sesión, usar la del usuario
        if (!$this->sucursalId && auth()->check()) {
            $user = auth()->user();
            $this->sucursalId = $user->sucursal_id;

            // Guardar en sesión para próximas requests
            if ($this->sucursalId) {
                Session::put('sucursal_actual_id', $this->sucursalId);
            }
        }

        return $this->sucursalId;
    }

    public function hasSucursal(): bool
    {
        return $this->getSucursalId() !== null;
    }

    public function clear(): void
    {
        $this->sucursalId = null;
        Session::forget('sucursal_actual_id');
    }

    // Para aplicar scope automáticamente en consultas
    public function scopeQuery($query)
    {
        if ($this->hasSucursal() && method_exists($query->getModel(), 'scopeWhereSucursal')) {
            return $query->whereSucursal($this->sucursalId);
        }
        return $query;
    }
}
