<?php

namespace App\Exports\Ventas;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class VentasHistorialExport implements WithMultipleSheets
{
    public function __construct(private array $filtros) {}

    public function sheets(): array
    {
        $modo = $this->filtros['modo'] ?? 'ambos';

        $sheets = [];
        if ($modo === 'ventas' || $modo === 'ambos') {
            $sheets[] = new VentasResumenSheet($this->filtros);
        }
        if ($modo === 'detalles' || $modo === 'ambos') {
            $sheets[] = new VentasDetallesSheet($this->filtros);
        }

        return $sheets;
    }
}
