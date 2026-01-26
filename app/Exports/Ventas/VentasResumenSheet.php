<?php

namespace App\Exports\Ventas;

use App\Models\Ventas\Venta;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VentasResumenSheet implements FromQuery, WithHeadings, WithMapping, WithChunkReading, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(private array $filtros) {}

    public function title(): string
    {
        return 'Ventas';
    }

    public function query()
    {
        $inicioStr = $this->filtros['fecha_inicio'] ?? null;
        $finStr    = $this->filtros['fecha_fin'] ?? null;

        $inicio = $inicioStr ? \Carbon\Carbon::parse($inicioStr) : now()->startOfMonth();
        $fin    = $finStr    ? \Carbon\Carbon::parse($finStr)    : now()->endOfDay();

        $sucursalId = $this->filtros['sucursal_id'] ?? null;
        $search = $this->filtros['search'] ?? null;
        $idsFiltro = $this->filtros['ids_filtro'] ?? null;

        $q = Venta::query()
            ->with(['cliente', 'usuario', 'sucursal'])
            ->whereBetween('fecha_emision', [$inicio, $fin]);

        // Permisos
        if (!empty($idsFiltro)) {
            $q->whereIn('sucursal_id', $idsFiltro);
        }

        // Filtros UI
        if (!empty($sucursalId)) {
            $q->where('sucursal_id', $sucursalId);
        }

        if (!empty($search)) {
            $busqueda = $search;
            $q->where(function ($qq) use ($busqueda) {
                $qq->where('serie', 'like', "%$busqueda%")
                    ->orWhere('numero', 'like', "%$busqueda%")
                    ->orWhereHas('cliente', function ($c) use ($busqueda) {
                        $c->where('nombre', 'like', "%$busqueda%")
                            ->orWhere('apellidos', 'like', "%$busqueda%")
                            ->orWhere('razon_social', 'like', "%$busqueda%")
                            ->orWhere('documento', 'like', "%$busqueda%");
                    });
            });
        }

        return $q->orderBy('fecha_emision', 'desc');
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Hora',
            'Sucursal',
            'Tipo',
            'Serie',
            'Número',
            'Cliente Doc',
            'Cliente',
            'Vendedor',
            'Medio Pago',
            'Ref. Pago',
            'Estado',
            'Op Gravada',
            'Op Exonerada',
            'Op Inafecta',
            'IGV',
            '% IGV',
            'Total Bruto',
            'Total Descuento',
            'Total Neto',
            'Obs.'
        ];
    }

    public function map($venta): array
    {
        $clienteDoc = optional($venta->cliente)->documento ?? '';
        $clienteNom = optional($venta->cliente)->nombre_completo ?? 'Público';
        $vendedor   = optional($venta->usuario)->name ?? '';
        $sucursal   = optional($venta->sucursal)->nombre ?? '';

        return [
            optional($venta->fecha_emision)->format('d/m/Y'),
            optional($venta->fecha_emision)->format('h:i A'),

            $sucursal,

            $venta->tipo_comprobante,
            $venta->serie,
            $venta->numero,

            $clienteDoc,
            $clienteNom,

            $vendedor,

            $venta->medio_pago,
            $venta->referencia_pago,

            $venta->estado,

            (float) $venta->op_gravada,
            (float) $venta->op_exonerada,
            (float) $venta->op_inafecta,

            (float) $venta->total_igv,
            (float) $venta->porcentaje_igv,

            (float) $venta->total_bruto,
            (float) $venta->total_descuento,
            (float) $venta->total_neto,

            $venta->observaciones,
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo header
        $sheet->getStyle('A1:U1')->getFont()->setBold(true);
        $sheet->freezePane('A2');
        return [];
    }
}
