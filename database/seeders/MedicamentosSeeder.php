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
        $usedCodigos = [];   // controla duplicados del campo `codigo` en esta corrida
        $usedBarcodes = [];  // controla duplicados del campo `codigo_barra` en esta corrida

        for ($i = $headerRowIndex + 1; $i <= count($rows); $i++) {
            $r = $rows[$i];

            $rawCodigoProducto = trim((string)($r[$map['CODIGO PRODUCTO']] ?? ''));
            $nombre  = trim((string)($r[$map['NOMBRE PRODUCTO']] ?? ''));

            // Saltar filas vacías / basura
            if ($nombre === '' || $this->isGarbageNombre($nombre)) continue;

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

            // --- REGLA PRINCIPAL ---
            // 1) Si CODIGO PRODUCTO es barcode válido => va a codigo_barra. Si ya existe en BD => OMITIR.
            // 2) Si NO es barcode (ej: JJLKALKASD) => va a codigo. Si ya existe en BD => OMITIR. codigo_barra = NULL.
            // 3) Si viene vacío => se crea igual y el codigo se AUTOGENERA. codigo_barra = NULL.
            $parsed = $this->parseCodigoProducto($rawCodigoProducto);
            $barcode = $parsed['barcode']; // puede ser null
            $variant = $parsed['variant']; // puede ser null

            if ($barcode !== null) {
                if (isset($usedBarcodes[$barcode])) continue;
                if (Medicamento::where('codigo_barra', $barcode)->exists()) continue;
                $usedBarcodes[$barcode] = true;

                // codigo interno autogenerado (no depende del barcode)
                $codigo = $this->genCodigoFromNombre($nombre, $usedCodigos);
            } else {
                if ($rawCodigoProducto !== '') {
                    $codigo = $this->fitCodigo($rawCodigoProducto);
                    if ($codigo === '') continue; // por si vino puro espacio

                    if (isset($usedCodigos[$codigo])) continue;
                    if (Medicamento::where('codigo', $codigo)->exists()) continue;

                    $usedCodigos[$codigo] = true;
                } else {
                    // vino vacío => autogenerar
                    $codigo = $this->genCodigoFromNombre($nombre, $usedCodigos);
                }
            }

            // Descripción (memo) + variante si aplica
            $descripcion = $memo !== '' ? $memo : null;
            if ($variant !== null && $variant !== '') {
                $tag = "VARIANTE: " . $variant;
                $descripcion = $descripcion ? ($descripcion . " | " . $tag) : $tag;
            }

            Medicamento::create([
                'codigo' => Str::limit($codigo, 30, ''),

                'codigo_barra' => $barcode, // null si no es barcode
                'nombre' => Str::limit($nombre, 180, ''),
                'laboratorio' => Str::limit($marca, 120, ''),
                'descripcion' => $descripcion,

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

    private function parseCodigoProducto($v): array
    {
        $s = trim((string)$v);
        if ($s === '') return ['barcode' => null, 'variant' => null];

        // quitar espacios internos
        $s = preg_replace('/\s+/', '', $s);

        // barcode puro
        if (preg_match('/^(\d{8}|\d{12}|\d{13}|\d{14})$/', $s)) {
            return ['barcode' => $s, 'variant' => null];
        }

        // barcode + variante (ej: 2770...-A)
        if (preg_match('/^(\d{8}|\d{12}|\d{13}|\d{14})-([A-Za-z0-9]+)$/', $s, $m)) {
            return ['barcode' => $m[1], 'variant' => $m[2]];
        }

        return ['barcode' => null, 'variant' => null];
    }

    private function fitCodigo(string $raw): string
    {
        $s = trim($raw);
        if ($s === '') return '';

        // sin espacios
        $s = preg_replace('/\s+/', '', $s);

        // Si entra en 30, lo usamos tal cual (regla del usuario)
        if (strlen($s) <= 30) return $s;

        // Si es muy largo, lo compactamos para que entre en varchar(30)
        $hash = substr(md5($s), 0, 5);
        $prefix = substr($s, 0, 24);
        return $prefix . '-' . $hash; // total 30
    }

    private function isGarbageNombre(string $nombre): bool
    {
        return (bool) preg_match('/\b(DETALLE|SUBTOTAL|TOTAL|IGV)\b/i', $nombre);
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
