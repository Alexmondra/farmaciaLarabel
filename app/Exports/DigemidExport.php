<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DigemidExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private Collection $rows,
        private array $colsSeleccionadas,
        private array $columnasMaestras
    ) {}

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return array_map(fn($k) => $this->columnasMaestras[$k] ?? $k, $this->colsSeleccionadas);
    }

    public function map($row): array
    {
        $out = [];

        foreach ($this->colsSeleccionadas as $key) {
            switch ($key) {
                case 'cod_establecimiento':
                    $out[] = $row->sucursal_cod_digemid ?? 'S/N';
                    break;

                case 'codigo_digemid':
                    $out[] = $row->medicamento->codigo_digemid ?? '--';
                    break;

                case 'precio_venta':
                    $out[] = number_format((float)($row->precio_venta ?? 0), 2, '.', '');
                    break;

                case 'stock_computado':
                    $out[] = (int)($row->stock_computado ?? 0);
                    break;

                case 'estado':
                    $out[] = $row->activo ? 'Activo' : 'Inactivo';
                    break;

                default:
                    $out[] = $row->medicamento->$key ?? $row->$key ?? '--';
                    break;
            }
        }

        return $out;
    }
}
