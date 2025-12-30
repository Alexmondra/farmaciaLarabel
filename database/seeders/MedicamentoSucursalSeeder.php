<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Models\Sucursal;
use App\Models\Inventario\Medicamento;
use App\Models\Inventario\MedicamentoSucursal;
use App\Models\Inventario\Lote;

class MedicamentoSucursalSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('database/seeders/data/mundo_farma_inventario.xls');

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true); // A,B,C...

        $headerRowIndex = $this->findHeaderRow($rows);
        $map = $this->buildHeaderMap($rows[$headerRowIndex] ?? []);

        // 1) Traer sucursales reales de tu BD (en orden)
        $localSucursalIds = Sucursal::orderBy('id')->pluck('id')->values()->all();
        if (count($localSucursalIds) === 0) {
            $this->command?->warn('No hay sucursales en la BD. Crea tus sucursales primero.');
            return;
        }

        // 2) Armar mapeo: id_externo => sucursal_id_local (en orden de aparición)
        $externalToLocal = [];
        $cursor = 0;

        for ($i = $headerRowIndex + 1; $i <= count($rows); $i++) {
            $r = $rows[$i];

            $barcode = $this->cleanBarcode($r[$map['CODIGO PRODUCTO']] ?? null);
            $nombre  = trim((string)($r[$map['NOMBRE PRODUCTO']] ?? ''));

            if (!$barcode || $nombre === '') continue;

            $externalAlmacenId = trim((string)($r[$map['ID ALMACEN/SUCURSAL']] ?? ''));
            if ($externalAlmacenId === '') continue;

            if (!isset($externalToLocal[$externalAlmacenId])) {
                if (!isset($localSucursalIds[$cursor])) {
                    $this->command?->warn("Hay más IDs externos que sucursales locales. External={$externalAlmacenId} se omitirá.");
                } else {
                    $externalToLocal[$externalAlmacenId] = $localSucursalIds[$cursor];
                    $cursor++;
                }
            }
        }

        // 3) Recorrer y sembrar pivote + lote
        for ($i = $headerRowIndex + 1; $i <= count($rows); $i++) {
            $r = $rows[$i];

            $barcode = $this->cleanBarcode($r[$map['CODIGO PRODUCTO']] ?? null);
            $nombre  = trim((string)($r[$map['NOMBRE PRODUCTO']] ?? ''));

            if (!$barcode || $nombre === '') continue;

            $externalAlmacenId = trim((string)($r[$map['ID ALMACEN/SUCURSAL']] ?? ''));
            if ($externalAlmacenId === '' || !isset($externalToLocal[$externalAlmacenId])) continue;

            $sucursalId = (int) $externalToLocal[$externalAlmacenId];

            // Buscar medicamento por codigo_barra
            $med = Medicamento::where('codigo_barra', $barcode)->first();
            if (!$med) {
                continue;
            }

            // stock minimo (si viene vacío, 0)
            $stockMin = (int) floor((float) ($r[$map['STOCK MINIMO']] ?? 0));

            // Precio de venta: usaremos PRECIO CON IGV del XLS
            $precioVenta = $this->toDecimal($r[$map['PRECIO CON IGV']] ?? null) ?? 0;

            // Asegurar pivote medicamento_sucursal
            MedicamentoSucursal::firstOrCreate(
                [
                    'medicamento_id' => $med->id,
                    'sucursal_id'    => $sucursalId,
                ],
                [
                    'stock_minimo'   => max(0, $stockMin),
                    // Precio del XLS (con IGV)
                    'precio_venta'   => $precioVenta,
                    'precio_blister' => 0,
                    'precio_caja'    => 0,
                    'activo'         => true,
                    'updated_by'     => 1,
                ]
            );

            // Lote: stock_actual solo si > 0
            $stockActual = (int) floor((float) ($r[$map['STOCK ACTUAL']] ?? 0));
            if ($stockActual <= 0) {
                // negativos o cero NO se registran
                continue;
            }

            $fechaVenc = $this->parseFecha($r[$map['FECHA VENCIMIENTO']] ?? null);
            $precioCompra = $this->toDecimal($r[$map['PRECIO COMPRA']] ?? null);
            $memo = trim((string)($r[$map['DETALLE - MEMO']] ?? ''));

            $existsLote = Lote::where('medicamento_id', $med->id)
                ->where('sucursal_id', $sucursalId)
                ->whereDate('fecha_vencimiento', $fechaVenc ?: '1900-01-01')
                ->where('stock_actual', $stockActual)
                ->exists();

            if ($existsLote) continue;

            Lote::create([
                'medicamento_id'     => $med->id,
                'sucursal_id'        => $sucursalId,
                'codigo_lote'        => 'MIG-' . $externalAlmacenId . '-' . $i,
                'stock_actual'       => $stockActual,
                'fecha_vencimiento'  => $fechaVenc,
                'precio_compra'      => $precioCompra ?? 0,
                'observaciones'      => $memo !== '' ? $memo : null,
            ]);
        }
    }

    private function findHeaderRow(array $rows): int
    {
        foreach ($rows as $idx => $cols) {
            $joined = strtoupper(implode(' ', array_map('strval', $cols)));
            if (str_contains($joined, 'CODIGO PRODUCTO') && str_contains($joined, 'NOMBRE PRODUCTO')) {
                return (int)$idx;
            }
        }
        return 1;
    }

    private function buildHeaderMap(array $header): array
    {
        $norm = [];
        foreach ($header as $col => $name) {
            $k = strtoupper(trim((string)$name));
            if ($k !== '') $norm[$k] = $col;
        }

        return [
            'CODIGO PRODUCTO'        => $norm['CODIGO PRODUCTO'] ?? 'D',
            'NOMBRE PRODUCTO'        => $norm['NOMBRE PRODUCTO'] ?? 'E',
            'ID ALMACEN/SUCURSAL'    => $norm['ID ALMACEN/SUCURSAL'] ?? 'O',
            'STOCK ACTUAL'           => $norm['STOCK ACTUAL'] ?? 'K',
            'STOCK MINIMO'           => $norm['STOCK MINIMO'] ?? 'L',
            'FECHA VENCIMIENTO'      => $norm['FECHA VENCIMIENTO'] ?? 'Y',
            'DETALLE - MEMO'         => $norm['DETALLE - MEMO'] ?? 'M',
            'PRECIO COMPRA'          => $norm['PRECIO COMPRA'] ?? 'N',
            'PRECIO CON IGV'         => $norm['PRECIO CON IGV'] ?? 'I',
        ];
    }

    private function cleanBarcode($v): ?string
    {
        $s = trim((string)$v);
        if ($s === '') return null;
        $s = preg_replace('/\s+/', '', $s);
        $digits = preg_replace('/\D+/', '', $s);
        return $digits !== '' ? $digits : $s;
    }

    private function parseFecha($v): ?string
    {
        $s = trim((string)$v);
        if ($s === '') return null;

        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $s)) {
            [$d, $m, $y] = explode('-', $s);
            return "{$y}-{$m}-{$d}";
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
            return $s;
        }

        return null;
    }

    private function toDecimal($v): ?float
    {
        if ($v === null) return null;
        $s = trim((string)$v);
        if ($s === '') return null;
        $s = str_replace(',', '', $s);
        if (!is_numeric($s)) return null;
        return (float)$s;
    }
}
