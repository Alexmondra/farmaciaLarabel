<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('database/seeders/data/CATALOGO_GTIN_v4.csv');

        if (!file_exists($path)) {
            $this->command->error("No se encontró el CSV en: {$path}");
            return;
        }

        $now = Carbon::now();

        // Detecta delimitador automáticamente (, o ;)
        $delimiter = $this->detectDelimiter($path);

        $file = new \SplFileObject($path);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);
        $file->setCsvControl($delimiter);

        // 1) Leer encabezados
        $headers = $file->fgetcsv();
        if (!$headers || count($headers) < 2) {
            $this->command->error("CSV inválido o sin encabezados: {$path}");
            return;
        }

        $headers = array_map(fn($h) => strtoupper(trim((string)$h)), $headers);

        $idxTipo = array_search('TIPOPRODUCTO', $headers, true);
        $idxSit  = array_search('SITUACION', $headers, true); // si existe

        if ($idxTipo === false) {
            $this->command->error("No existe la columna TIPOPRODUCTO en el CSV.");
            return;
        }

        // 2) Recolectar categorías únicas (TODAS)
        $categorias = []; // key: nombre normalizado

        while (!$file->eof()) {
            $row = $file->fgetcsv();
            if (!$row || $row === [null]) continue;

            $tipo = strtoupper(trim((string)($row[$idxTipo] ?? '')));
            if ($tipo === '') continue;

            // Si existe SITUACION, puedes elegir incluir también INACTIVOS.
            // Aquí cargamos igual la categoría, pero puedes marcar activo si hay ACTIVO/INACTIVO:
            $sit = $idxSit !== false ? strtoupper(trim((string)($row[$idxSit] ?? ''))) : '';

            // Guardar en el set
            // Nota: guardamos la versión original en mayúsculas por consistencia
            $categorias[$tipo] = [
                'nombre' => $tipo,
                'activo' => ($sit === 'ACTIVO' || $sit === '') ? 1 : 1, // categorías siempre activas (recomendado)
            ];
        }

        if (empty($categorias)) {
            $this->command->warn("No se encontraron categorías (TIPOPRODUCTO) para precargar.");
            return;
        }

        // 3) Insertar/Actualizar categorías (sin requerir UNIQUE)
        DB::beginTransaction();
        try {
            $count = 0;

            foreach ($categorias as $cat) {
                DB::table('categorias')->updateOrInsert(
                    ['nombre' => $cat['nombre']],
                    [
                        'descripcion' => 'Auto import desde CATALOGO_GTIN_v4.csv',
                        'activo'      => 1,
                        'created_at'  => $now, // si existe, no pasa nada grave; si quieres preservar created_at, dímelo
                        'updated_at'  => $now,
                    ]
                );
                $count++;
            }

            DB::commit();
            $this->command->info("CategoriasSeeder OK. Categorías cargadas/actualizadas: {$count}");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error("Error en CategoriasSeeder: " . $e->getMessage());
        }
    }

    private function detectDelimiter(string $path): string
    {
        $sample = file_get_contents($path, false, null, 0, 4096) ?: '';
        $commas = substr_count($sample, ',');
        $semis  = substr_count($sample, ';');
        return $semis > $commas ? ';' : ',';
    }
}
