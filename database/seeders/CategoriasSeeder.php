<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/categorias.sql');

        if (!File::exists($path)) {
            $this->command->error("No existe el archivo categorias.sql");
            return;
        }

        DB::unprepared(File::get($path));

        $this->command->info('Categorías cargadas correctamente.');
    }
}
