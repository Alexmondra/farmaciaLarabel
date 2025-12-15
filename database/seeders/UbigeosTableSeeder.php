<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class UbigeosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ubigeos')->truncate();
        // Ruta donde se encuentra tu archivo CSV
        // Asegúrate de que el nombre de archivo y la ruta sean correctos.
        $csvFile = database_path('seeders/data/UBIGEO-2022_1891-distritos.csv');

        if (!File::exists($csvFile)) {
            $this->command->error("El archivo CSV de Ubigeos no se encuentra en: {$csvFile}");
            return;
        }

        // Leer y parsear el CSV
        $file = fopen($csvFile, 'r');

        // Leer la cabecera para ignorarla y obtener el orden de las columnas
        $header = fgetcsv($file, 1000, ',');

        $insertData = [];

        // Asegúrate de que el orden de las columnas en tu CSV sea el siguiente (según tu ejemplo):
        // 0: "IDDIST", 1: "NOMBDEP", 2: "NOMBPROV", 3: "NOMBDIST", 4: "NOM_CAPITAL (LEGAL)", 5: "COD_ REG_NAT", 6: "REGION NATURAL"

        while (($row = fgetcsv($file, 1000, ',')) !== FALSE) {
            // Verificación básica de que la fila tenga suficientes columnas
            if (count($row) < 4) {
                continue;
            }

            $insertData[] = [
                // Mapeo de columnas del CSV a tu tabla 'ubigeos'
                'codigo'       => $row[0], // "IDDIST"
                'departamento' => $row[1], // "NOMBDEP"
                'provincia'    => $row[2], // "NOMBPROV"
                'distrito'     => $row[3], // "NOMBDIST"
                'capital'      => $row[4] ?? null,
                'region_natural' => $row[6] ?? null,
            ];

            // Insertar en bloques para optimizar el rendimiento (opcional pero recomendado)
            if (count($insertData) >= 500) {
                DB::table('ubigeos')->insertOrIgnore($insertData); // <-- CAMBIO A insertOrIgnore
                $insertData = [];
            }
        }

        // Insertar los datos restantes
        if (!empty($insertData)) {
            DB::table('ubigeos')->insertOrIgnore($insertData);
        }

        fclose($file);
        $this->command->info('Tabla de Ubigeos importada exitosamente.');
    }
}
