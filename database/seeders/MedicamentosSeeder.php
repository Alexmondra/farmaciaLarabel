<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class MedicamentosSeeder extends Seeder
{
    public function run(): void
    {
        DB::disableQueryLog();

        $path = base_path('database/seeders/data/CATALOGO_GTIN_v4.csv');
        if (!file_exists($path)) {
            $this->command->error("No se encontró el CSV en: {$path}");
            return;
        }

        // user_id obligatorio en tu tabla medicamentos
        $userId = DB::table('users')->orderBy('id')->value('id');
        if (!$userId) {
            $this->command->warn("No hay usuarios. Crea al menos 1 usuario antes de correr este seeder.");
            return;
        }

        // Si quieres borrar medicamentos antes de cargar (solo en fresh install):
        $truncate = false; // <- pon true si quieres limpiar todo
        if ($truncate) {
            Schema::disableForeignKeyConstraints();
            DB::table('medicamentos')->truncate();
            Schema::enableForeignKeyConstraints();
        }

        // Mapa categorías: nombre (upper) => id
        $categoriaMap = DB::table('categorias')
            ->select('id', 'nombre')
            ->get()
            ->mapWithKeys(fn($r) => [strtoupper(trim($r->nombre)) => (int)$r->id])
            ->all();

        if (empty($categoriaMap)) {
            $this->command->warn("No hay categorías. Corre primero CategoriasSeeder.");
            return;
        }

        $delimiter = $this->detectDelimiter($path);

        $file = new \SplFileObject($path);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);
        $file->setCsvControl($delimiter);

        // Headers
        $headers = $file->fgetcsv();
        if (!$headers || count($headers) < 2) {
            $this->command->error("CSV inválido o sin encabezados: {$path}");
            return;
        }

        $headers = array_map(function ($h) {
            $h = (string)$h;
            $h = preg_replace('/^\xEF\xBB\xBF/', '', $h); // BOM
            return strtoupper(trim($h));
        }, $headers);

        $idx = fn(string $name) => array_search($name, $headers, true);

        $iCodigo  = $idx('CODIGO');               // GTIN
        $iTipo    = $idx('TIPOPRODUCTO');
        $iNombre  = $idx('NOMBRE');
        $iDenom   = $idx('DENOMINACIONCOMUN');
        $iConc    = $idx('CONCENTRACION');
        $iForma   = $idx('FORMAFARMACEUTICA');
        $iLab     = $idx('LABORATORIO');
        $iPais    = $idx('PAIS');
        $iPres    = $idx('PRESENTACION');
        $iUni     = $idx('UNIDADENVASE');
        $iSit     = $idx('SITUACION');
        $iRS      = $idx('NUMREGISTROSANITARIO');

        // Validación mínima
        $required = [
            'CODIGO' => $iCodigo,
            'TIPOPRODUCTO' => $iTipo,
            'NOMBRE' => $iNombre,
            'PAIS' => $iPais,
            'SITUACION' => $iSit,
            'NUMREGISTROSANITARIO' => $iRS,
        ];
        foreach ($required as $col => $pos) {
            if ($pos === false) {
                $this->command->error("Falta columna en CSV: {$col}");
                return;
            }
        }

        $now = Carbon::now();
        $batch = [];
        $batchSize = 1000;
        $processed = 0;
        $skipped = 0;

        while (!$file->eof()) {
            $row = $file->fgetcsv();
            if (!$row || $row === [null]) continue;

            // 1) Solo activos
            $situacion = strtoupper(trim((string)($row[$iSit] ?? '')));
            if ($situacion !== 'ACTIVO') {
                $skipped++;
                continue;
            }

            // 2) Solo medicamentos (si quieres incluir ALIMENTO/COSMETICO, borra este filtro)
            $tipo = strtoupper(trim((string)($row[$iTipo] ?? '')));
            if ($tipo !== 'PRODUCTO FARMACEUTICO') {
                $skipped++;
                continue;
            }

            // 3) Solo los comercializados en Perú: PAIS = PERÚ/PERU (normalizado)
            $paisRaw = trim((string)($row[$iPais] ?? ''));
            $pais = $this->normalizePais($paisRaw);
            if ($pais !== 'PERU') {
                $skipped++;
                continue;
            }

            // 4) Nombre obligatorio
            $nombre = trim((string)($row[$iNombre] ?? ''));
            if ($nombre === '') {
                $skipped++;
                continue;
            }

            // 5) Registro sanitario obligatorio (Perú comercializable)
            $rs = trim((string)($row[$iRS] ?? ''));
            if ($rs === '') {
                $skipped++;
                continue;
            }

            // 6) GTIN/EAN13 obligatorio (13 dígitos)
            $gtinRaw = (string)($row[$iCodigo] ?? '');
            $gtin = $this->digitsOnly($gtinRaw);
            if (strlen($gtin) !== 13) {
                $skipped++;
                continue;
            }

            // codigo = GTIN
            $codigo = $gtin;

            // Resolver categoria_id por TIPOPRODUCTO (PRODUCTO FARMACEUTICO)
            $categoriaId = $categoriaMap[$tipo] ?? null;
            if (!$categoriaId) {
                DB::table('categorias')->updateOrInsert(
                    ['nombre' => $tipo],
                    ['descripcion' => 'Auto import desde CATALOGO_GTIN_v4.csv', 'activo' => 1, 'created_at' => $now, 'updated_at' => $now]
                );
                $categoriaId = (int) DB::table('categorias')->where('nombre', $tipo)->value('id');
                $categoriaMap[$tipo] = $categoriaId;
            }

            $forma = $iForma !== false ? trim((string)($row[$iForma] ?? '')) : '';
            $conc  = $iConc  !== false ? trim((string)($row[$iConc]  ?? '')) : '';
            $pres  = $iPres  !== false ? trim((string)($row[$iPres]  ?? '')) : '';
            $lab   = $iLab   !== false ? trim((string)($row[$iLab]   ?? '')) : '';
            $denom = $iDenom !== false ? trim((string)($row[$iDenom] ?? '')) : '';

            $uniRaw = $iUni !== false ? (string)($row[$iUni] ?? '') : '';
            $unidades = (int)($this->digitsOnly($uniRaw) ?: 1);
            if ($unidades <= 0) $unidades = 1;

            // descripcion = DENOMINACIONCOMUN | PAIS | TIPOPRODUCTO
            $descParts = array_values(array_filter([$denom, 'PERÚ', $tipo], fn($x) => trim((string)$x) !== ''));
            $descripcion = !empty($descParts) ? implode(' | ', $descParts) : null;

            $batch[] = [
                'codigo'              => $this->cut($codigo, 30),
                'nombre'              => $this->cut($nombre, 180),
                'forma_farmaceutica'  => $this->cut($forma, 100) ?: null,
                'concentracion'       => $this->cut($conc, 100) ?: null,
                'presentacion'        => $this->cut($pres, 120) ?: null,
                'laboratorio'         => $this->cut($lab, 120) ?: null,
                'registro_sanitario'  => $this->cut($rs, 60) ?: null,
                'codigo_barra'        => $this->cut($gtin, 50),
                'descripcion'         => $descripcion,
                'unidades_por_envase' => $unidades,
                'afecto_igv'          => 1,
                'imagen_path'         => null,
                'categoria_id'        => $categoriaId,
                'user_id'             => $userId,
                'activo'              => 1,
                'created_at'          => $now,
                'updated_at'          => $now,
            ];

            if (count($batch) >= $batchSize) {
                $this->flushUpsert($batch);
                $processed += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->flushUpsert($batch);
            $processed += count($batch);
        }

        $this->command->info("MedicamentosSeeder (PERÚ+ACTIVO+RS+GTIN13) OK. Upsert: {$processed} | Skipped: {$skipped}");
    }

    private function flushUpsert(array $rows): void
    {
        DB::table('medicamentos')->upsert(
            $rows,
            ['codigo'], // UNIQUE
            [
                'nombre',
                'forma_farmaceutica',
                'concentracion',
                'presentacion',
                'laboratorio',
                'registro_sanitario',
                'codigo_barra',
                'descripcion',
                'unidades_por_envase',
                'afecto_igv',
                'imagen_path',
                'categoria_id',
                'user_id',
                'activo',
                'updated_at'
            ]
        );
    }

    private function detectDelimiter(string $path): string
    {
        $sample = file_get_contents($path, false, null, 0, 4096) ?: '';
        $commas = substr_count($sample, ',');
        $semis  = substr_count($sample, ';');
        return $semis > $commas ? ';' : ',';
    }

    private function digitsOnly(string $value): string
    {
        $s = preg_replace('/\D+/', '', $value ?? '');
        return $s ?: '';
    }

    private function cut(string $value, int $max): string
    {
        $value = trim($value ?? '');
        if ($value === '') return '';
        return mb_substr($value, 0, $max);
    }

    // PERÚ / PERU / "Perú" -> PERU
    private function normalizePais(string $pais): string
    {
        $p = trim($pais);
        if ($p === '') return '';

        $p = mb_strtoupper($p, 'UTF-8');

        // Quitar tildes comunes (basta para PERÚ)
        $p = strtr($p, [
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'Ü' => 'U',
            'Ñ' => 'N',
        ]);

        // Normaliza espacios
        $p = preg_replace('/\s+/', ' ', $p);

        return $p;
    }
}
