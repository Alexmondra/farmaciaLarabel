<?php

namespace Database\Seeders;

use App\Models\Inventario\Medicamento;
use App\Models\Inventario\Categoria; // ajusta namespace si es distinto
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MedicamentosSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('database/seeders/data/mundo_farma_inventario.xls');

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true); // A,B,C...

        // 1) Encontrar la fila header (donde dice "CODIGO PRODUCTO", etc.)
        $headerRowIndex = $this->findHeaderRow($rows);

        $headers = $rows[$headerRowIndex];
        $map = $this->buildHeaderMap($headers);

        // 2) Recorrer data desde la fila siguiente al header
        $userId = 1; // <-- pon tu user_id dueño de los medicamentos

        // Para evitar códigos repetidos en esta corrida
        $usedCodigos = [];
        for ($i = $headerRowIndex + 1; $i <= count($rows); $i++) {
            $r = $rows[$i];

            $barcode = $this->cleanBarcode($r[$map['CODIGO PRODUCTO']] ?? null);
            $nombre  = trim((string)($r[$map['NOMBRE PRODUCTO']] ?? ''));

            // Saltar filas vacías / basura
            if (!$barcode || $nombre === '') continue;

            $marca   = trim((string)($r[$map['MARCA']] ?? ''));
            $memo    = trim((string)($r[$map['DETALLE - MEMO']] ?? ''));
            $igvCode = trim((string)($r[$map['CODIGO - TIPO DE IGV']] ?? ''));

            // (Opcional) categoría por nombre
            $catNombre = trim((string)($r[$map['NOMBRE CATEGORIA']] ?? ''));
            $categoriaId = null;
            if ($catNombre !== '') {
                $categoriaId = Categoria::firstOrCreate(
                    ['nombre' => $catNombre],
                    ['user_id' => $userId]
                )->id;
            }

            $afectoIgv = ($igvCode === '' || $igvCode === '20'); // ajusta si quieres otro mapeo

            // Evitar duplicados por código de barra
            $exists = Medicamento::where('codigo_barra', $barcode)->exists();
            if ($exists) continue;

            Medicamento::create([
                // codigo autogenerado (con prefijo del nombre) <= 30
                'codigo' => $this->genCodigoFromNombre($nombre, $usedCodigos),

                'codigo_barra' => $barcode,
                'nombre' => Str::limit($nombre, 180, ''),
                'laboratorio' => Str::limit($marca, 120, ''),
                'descripcion' => $memo !== '' ? $memo : null,

                'categoria_id' => $categoriaId,
                'user_id' => $userId,

                'afecto_igv' => $afectoIgv,
                'unidades_por_envase' => 1,
                'receta_medica' => false,
                'activo' => true,
            ]);
        }
    }

    private function genCodigoFromNombre(string $nombre, array &$usedCodigos): string
    {
        // 4–6 letras del nombre + '-' + 4 números. Ej: NAPR-4821
        $base = $this->slugLetters($nombre);
        $base = strtoupper($base);

        // Tomar entre 4 y 6 letras (preferimos 6 si hay)
        $len = strlen($base);
        if ($len >= 6) {
            $prefix = substr($base, 0, 6);
        } elseif ($len >= 4) {
            $prefix = substr($base, 0, 4);
        } else {
            $prefix = str_pad($base, 4, 'X');
        }

        for ($try = 0; $try < 50; $try++) {
            $suffix = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $codigo = $prefix . '-' . $suffix;

            if (isset($usedCodigos[$codigo])) continue;
            if (Medicamento::where('codigo', $codigo)->exists()) continue;

            $usedCodigos[$codigo] = true;
            return $codigo;
        }

        // Fallback ultra-seguro (<=30 chars)
        $fallback = (string) Str::ulid();
        $usedCodigos[$fallback] = true;
        return $fallback;
    }

    private function slugLetters(string $text): string
    {
        // sin acentos, solo letras (A-Z)
        $t = Str::ascii($text);
        $t = preg_replace('/[^A-Za-z]+/', '', $t);
        return $t ?? '';
    }

    private function cleanBarcode($v): ?string
    {
        $s = trim((string)$v);
        if ($s === '') return null;

        $s = preg_replace('/\s+/', '', $s);

        $digits = preg_replace('/\D+/', '', $s);
        return $digits !== '' ? $digits : $s;
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
            'CODIGO PRODUCTO' => $norm['CODIGO PRODUCTO'] ?? 'D',
            'NOMBRE PRODUCTO' => $norm['NOMBRE PRODUCTO'] ?? 'E',
            'MARCA' => $norm['MARCA'] ?? 'C',
            'DETALLE - MEMO' => $norm['DETALLE - MEMO'] ?? 'M',
            'CODIGO - TIPO DE IGV' => $norm['CODIGO - TIPO DE IGV'] ?? 'F',
            'NOMBRE CATEGORIA' => $norm['NOMBRE CATEGORIA'] ?? 'B',
        ];
    }
}
