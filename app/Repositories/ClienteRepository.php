<?php

namespace App\Repositories;

use App\Models\Ventas\Cliente;

class ClienteRepository
{

    public function getStats()
    {
        return [
            'total'    => Cliente::count(),
            'personas' => Cliente::where('tipo_documento', '!=', 'RUC')->count(), // Asumimos todo lo que no es RUC es persona
            'empresas' => Cliente::where('tipo_documento', 'RUC')->count(),
        ];
    }

    /**
     * Lógica avanzada de búsqueda y filtrado
     */
    public function search($filters = [], $perPage = 10)
    {
        $query = Cliente::where('activo', true);

        // 1. Filtro por Tipo (Botones)
        if (isset($filters['type']) && $filters['type'] !== 'all') {
            if ($filters['type'] === 'RUC') {
                $query->where('tipo_documento', 'RUC');
            } elseif ($filters['type'] === 'persona') {
                $query->where('tipo_documento', '!=', 'RUC');
            }
        }

        // 2. Filtro por Texto (Input Buscador)
        if (isset($filters['q']) && !empty($filters['q'])) {
            $busqueda = $filters['q'];
            $query->where(function ($q) use ($busqueda) {
                $q->where('documento', 'like', "%$busqueda%")
                    ->orWhere('nombre', 'like', "%$busqueda%")
                    ->orWhere('apellidos', 'like', "%$busqueda%")
                    ->orWhere('razon_social', 'like', "%$busqueda%");
            });
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * Verifica si un documento existe (excluyendo un ID opcional)
     */
    public function checkDocumento($documento, $exceptId = null)
    {
        $query = Cliente::where('documento', $documento);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->first();
    }
}
