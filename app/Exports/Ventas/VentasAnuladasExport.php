<?php

namespace App\Exports\Ventas;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class VentasAnuladasExport implements WithMultipleSheets
{
    public function __construct(private array $filtros) {}

    public function sheets(): array
    {
        $modo = $this->filtros['modo'] ?? 'ambos';

        $sheets = [];
        if ($modo === 'resumen') {
            return [new VentasAnuladasResumenSimpleSheet($this->filtros)];
        }
        if ($modo === 'ventas' || $modo === 'ambos') {
            $sheets[] = new VentasAnuladasResumenSheet($this->filtros);
        }
        if ($modo === 'detalles' || $modo === 'ambos') {
            $sheets[] = new VentasAnuladasDetallesSheet($this->filtros);
        }

        return $sheets;
    }
}
