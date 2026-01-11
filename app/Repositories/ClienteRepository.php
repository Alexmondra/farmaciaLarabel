<?php

namespace App\Repositories;

use App\Models\Ventas\Cliente;

class ClienteRepository
{

    public function getStats()
    {
        // También lo quitamos de los contadores para que los números sean reales
        return [
            'total'    => Cliente::where('id', '!=', 1)->count(),
            'personas' => Cliente::where('tipo_documento', '!=', 'RUC')->where('id', '!=', 1)->count(), // Asumimos todo lo que no es RUC es persona
            'empresas' => Cliente::where('tipo_documento', 'RUC')->where('id', '!=', 1)->count(),
        ];
    }

    public function search($filters = [], $perPage = 10)
    {
        // === AQUÍ ESTÁ EL CANDADO ===
        $query = Cliente::where('activo', true)
            ->where('id', '!=', 1);

        // 1. Filtro por Tipo (Botones)
        if (isset($filters['type']) && $filters['type'] !== 'all') {
            if ($filters['type'] === 'RUC') {
                $query->where('tipo_documento', 'RUC');
            } elseif ($filters['type'] === 'persona') {
                $query->where('tipo_documento', '!=', 'RUC');
            }
        }

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


    public function checkDocumento($documento, $exceptId = null)
    {
        $query = Cliente::where('documento', $documento);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->first();
    }
}
