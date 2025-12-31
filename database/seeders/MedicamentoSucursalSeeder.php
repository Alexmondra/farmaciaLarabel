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
        $rows = $sheet->toArray(null, true, true, true);

        $headerRowIndex = $this->findHeaderRow($rows);
        $map = $this->buildHeaderMap($rows[$headerRowIndex] ?? []);

        // 1) Sucursales locales
        $localSucursalIds = Sucursal::orderBy('id')->pluck('id')->values()->all();
        if (count($localSucursalIds) === 0) {
            $this->command?->warn('No hay sucursales en la BD.');
            return;
        }

        // 2) Mapeo Sucursal Externa -> Local
        $externalToLocal = [];
        $cursor = 0;

        // Primer barrido solo para mapear sucursales
        for ($i = $headerRowIndex + 1; $i <= count($rows); $i++) {
            $r = $rows[$i];
            // Usamos limpieza suave para permitir letras (JJLKALKASD) en la búsqueda
            $rawCode = $this->cleanInput($r[$map['CODIGO PRODUCTO']] ?? null);
            $nombre  = trim((string)($r[$map['NOMBRE PRODUCTO']] ?? ''));

            if (!$rawCode || $nombre === '') continue;

            $externalAlmacenId = trim((string)($r[$map['ID ALMACEN/SUCURSAL']] ?? ''));
            if ($externalAlmacenId === '') continue;

            if (!isset($externalToLocal[$externalAlmacenId])) {
                if (isset($localSucursalIds[$cursor])) {
                    $externalToLocal[$externalAlmacenId] = $localSucursalIds[$cursor];
                    $cursor++;
                }
            }
        }

        // 3) Recorrer y sembrar
        for ($i = $headerRowIndex + 1; $i <= count($rows); $i++) {
            $r = $rows[$i];

            $rawCode = $this->cleanInput($r[$map['CODIGO PRODUCTO']] ?? null);
            $nombre  = trim((string)($r[$map['NOMBRE PRODUCTO']] ?? ''));

            if (!$rawCode || $nombre === '') continue;

            $externalAlmacenId = trim((string)($r[$map['ID ALMACEN/SUCURSAL']] ?? ''));
            if ($externalAlmacenId === '' || !isset($externalToLocal[$externalAlmacenId])) continue;

            $sucursalId = (int) $externalToLocal[$externalAlmacenId];

            // --- LÓGICA DE BÚSQUEDA HÍBRIDA ---
            // 1. Intentar buscar por CODIGO DE BARRA
            $med = Medicamento::where('codigo_barra', $rawCode)->first();

            // 2. Si no existe, buscar por CODIGO INTERNO (para los casos JJLKALKASD)
            if (!$med) {
                $med = Medicamento::where('codigo', $rawCode)->first();
            }

            // Si no lo encuentra en ninguno de los dos, se salta
            if (!$med) {
                continue;
            }
            // -----------------------------------

            $stockMin = (int) floor((float) ($r[$map['STOCK MINIMO']] ?? 0));

            // --- LÓGICA DE PRECIO Y REDONDEO ---
            // Leemos PRECIO SIN IGV
            $rawPrecio = $this->toDecimal($r[$map['PRECIO SIN IGV']] ?? null) ?? 0;

            // Redondeo: 5.5 -> 5 | 5.6 -> 6 | 5.4 -> 5
            // PHP_ROUND_HALF_DOWN hace exactamente que el .5 vaya hacia abajo
            $precioVentaEntero = (int) round($rawPrecio, 0, PHP_ROUND_HALF_DOWN);

            MedicamentoSucursal::firstOrCreate(
                [
                    'medicamento_id' => $med->id,
                    'sucursal_id'    => $sucursalId,
                ],
                [
                    'stock_minimo'   => max(0, $stockMin),
                    'precio_venta'   => $precioVentaEntero, // Guardamos el entero redondeado
                    'precio_blister' => 0,
                    'precio_caja'    => 0,
                    'activo'         => true,
                    'updated_by'     => 1,
                ]
            );

            // Lote: stock_actual
            $stockActual = (int) floor((float) ($r[$map['STOCK ACTUAL']] ?? 0));
            if ($stockActual <= 0) continue;

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

    // --- MÉTODOS AUXILIARES ---

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
            // Cambiado: Ahora buscamos SIN IGV, ajusta la letra 'H' si en tu excel es otra columna
            'PRECIO SIN IGV'         => $norm['PRECIO SIN IGV'] ?? 'H',
        ];
    }

    // Limpieza suave: quita espacios pero deja letras y números para la búsqueda interna
    private function cleanInput($v): ?string
    {
        $s = trim((string)$v);
        if ($s === '') return null;
        // Solo quita espacios internos (Code 123 -> Code123)
        return preg_replace('/\s+/', '', $s);
    }

    // Mantenemos cleanBarcode solo si necesitas estricto en otro lado, 
    // pero para la lógica principal usamos cleanInput.

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
        $s = str_replace(',', '', $s); // quitar comas de miles
        if (!is_numeric($s)) return null;
        return (float)$s;
    }
}
