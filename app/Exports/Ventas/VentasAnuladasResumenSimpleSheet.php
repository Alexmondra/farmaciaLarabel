<?php

namespace App\Exports\Ventas;

use App\Models\Ventas\Venta;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class VentasAnuladasResumenSimpleSheet implements FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithChunkReading
{
    public function __construct(private array $filtros) {}

    public function title(): string
    {
        return 'Resumen Anuladas';
    }

    public function query()
    {
        $inicio = \Carbon\Carbon::parse($this->filtros['fecha_inicio'] ?? now()->startOfMonth());
        $fin    = \Carbon\Carbon::parse($this->filtros['fecha_fin'] ?? now()->endOfDay());

        $q = Venta::query()
            ->select('id', 'fecha_emision', 'serie', 'numero', 'total_neto', 'estado')
            ->with('notasCredito')
            ->where('estado', 'ANULADO')
            ->whereBetween('fecha_emision', [$inicio, $fin]);

        if (!empty($this->filtros['ids_filtro'])) {
            $q->whereIn('sucursal_id', $this->filtros['ids_filtro']);
        }

        if (!empty($this->filtros['sucursal_id'])) {
            $q->where('sucursal_id', $this->filtros['sucursal_id']);
        }

        return $q->orderBy('fecha_emision', 'desc');
    }

    public function headings(): array
    {
        return ['Fecha', 'Comprobante', 'N.Crédito', 'Total Anulado', 'Estado'];
    }

    public function map($venta): array
    {
        $nc = $venta->notasCredito->first();
        $ncSerie = $nc ? ($nc->serie . '-' . $nc->numero) : '';

        return [
            optional($venta->fecha_emision)->format('d/m/Y H:i'),
            $venta->serie . '-' . $venta->numero,
            $ncSerie,
            (float) $venta->total_neto,
            $venta->estado
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
