<?php

namespace App\Exports\Ventas;

use App\Models\Ventas\DetalleVenta;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VentasDetallesSheet implements FromQuery, WithHeadings, WithMapping, WithChunkReading, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(private array $filtros) {}

    public function title(): string
    {
        return 'Detalles';
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

        $q = DetalleVenta::query()
            ->select('detalle_ventas.*')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->whereBetween('ventas.fecha_emision', [$inicio, $fin])
            ->with([
                'venta.cliente',
                'venta.usuario',
                'venta.sucursal',
                'medicamento',
                'lote'
            ]);

        // Permisos
        if (!empty($idsFiltro)) {
            $q->whereIn('ventas.sucursal_id', $idsFiltro);
        }

        // Filtros UI
        if (!empty($sucursalId)) {
            $q->where('ventas.sucursal_id', $sucursalId);
        }

        if (!empty($search)) {
            $busqueda = $search;
            $q->where(function ($qq) use ($busqueda) {
                $qq->where('ventas.serie', 'like', "%$busqueda%")
                    ->orWhere('ventas.numero', 'like', "%$busqueda%")
                    ->orWhere('clientes.nombre', 'like', "%$busqueda%")
                    ->orWhere('clientes.apellidos', 'like', "%$busqueda%")
                    ->orWhere('clientes.razon_social', 'like', "%$busqueda%")
                    ->orWhere('clientes.documento', 'like', "%$busqueda%");
            });
        }

        return $q->orderBy('ventas.fecha_emision', 'desc')->orderBy('detalle_ventas.id', 'asc');
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Hora',
            'Sucursal',
            'Comprobante',
            'Cliente',
            'Vendedor',
            'Medicamento',
            'Laboratorio',
            'Lote',
            'Vencimiento',
            'Cantidad (unid)',
            'Presentación (inferida)',
            'Unid x Caja',
            'Unid x Blister',
            'Cant. Cajas',
            'Cant. Blisters',
            'Precio Unit',
            'Dscto Unit',
            'Valor Unit (base)',
            'IGV',
            'Tipo Afect.',
            'Subt Bruto',
            'Subt Dscto',
            'Subt Neto'
        ];
    }

    private function inferirPresentacion(?int $cantidad, ?int $unidCaja, ?int $unidBlister): string
    {
        $cantidad = (int) ($cantidad ?? 0);

        if ($unidCaja && $unidCaja > 0 && $cantidad % $unidCaja === 0) return 'CAJA';
        if ($unidBlister && $unidBlister > 0 && $cantidad % $unidBlister === 0) return 'BLISTER';
        return 'UNIDAD';
    }

    public function map($det): array
    {
        $venta = $det->venta;
        $med   = $det->medicamento;
        $lote  = $det->lote;

        $fecha = optional($venta?->fecha_emision);
        $sucursal = optional($venta?->sucursal)->nombre ?? '';
        $comprobante = trim(($venta?->tipo_comprobante ?? '') . ' ' . ($venta?->serie ?? '') . '-' . ($venta?->numero ?? ''));

        $cliente = optional($venta?->cliente)->nombre_completo ?? 'Público';
        $vendedor = optional($venta?->usuario)->name ?? '';

        $unidCaja    = (int) (optional($med)->unidades_por_envase ?? 0);
        $unidBlister = (int) (optional($med)->unidades_por_blister ?? 0);
        $cantidad    = (int) ($det->cantidad ?? 0);

        $presentacion = $this->inferirPresentacion($cantidad, $unidCaja, $unidBlister);

        $cantCajas = ($presentacion === 'CAJA' && $unidCaja > 0) ? ($cantidad / $unidCaja) : '';
        $cantBlis  = ($presentacion === 'BLISTER' && $unidBlister > 0) ? ($cantidad / $unidBlister) : '';

        return [
            $fecha?->format('d/m/Y'),
            $fecha?->format('h:i A'),

            $sucursal,
            $comprobante,
            $cliente,
            $vendedor,

            optional($med)->nombre ?? '',
            optional($med)->laboratorio ?? '',

            optional($lote)->codigo_lote ?? '',
            optional($lote?->fecha_vencimiento)?->format('d/m/Y') ?? '',

            $cantidad,
            $presentacion,

            $unidCaja ?: '',
            $unidBlister ?: '',

            $cantCajas,
            $cantBlis,

            (float) $det->precio_unitario,
            (float) $det->descuento_unitario,

            (float) $det->valor_unitario,
            (float) $det->igv,
            (string) $det->tipo_afectacion,

            (float) $det->subtotal_bruto,
            (float) $det->subtotal_descuento,
            (float) $det->subtotal_neto,
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:Y1')->getFont()->setBold(true);
        $sheet->freezePane('A2');
        return [];
    }
}
