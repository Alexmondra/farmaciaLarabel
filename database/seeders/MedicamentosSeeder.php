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
        // Tomar un user_id válido (primero)
        $userId = DB::table('users')->orderBy('id')->value('id');
        if (!$userId) {
            $this->command->warn('No hay usuarios. Crea al menos 1 usuario antes de este seeder.');
            return;
        }

        // Categorías disponibles (rotaremos entre ellas)
        $categoriaIds = DB::table('categorias')->orderBy('id')->pluck('id')->all();
        if (empty($categoriaIds)) {
            $this->command->warn('No hay categorías. Crea categorías antes de este seeder.');
            return;
        }

        Schema::disableForeignKeyConstraints();
        DB::table('medicamentos')->truncate();
        Schema::enableForeignKeyConstraints();

        $ahora = Carbon::now();

        // 5 medicamentos (completa todos los campos)
        $meds = [
            [
                'codigo'             => 'PARA500',
                'nombre'             => 'Paracetamol 500 mg',
                'forma_farmaceutica' => 'Tableta',
                'concentracion'      => '500 mg',
                'presentacion'       => 'Caja x 100 tabletas',
                'laboratorio'        => 'Medifarma',
                'registro_sanitario' => 'NSK-25487',
                'codigo_barra'       => '7750000000001',
                'descripcion'        => 'Analgésico y antipirético.',
                'imagen_path'        => 'images/medicamentos/paracetamol.jpg',
            ],
            [
                'codigo'             => 'IBU400',
                'nombre'             => 'Ibuprofeno 400 mg',
                'forma_farmaceutica' => 'Tableta recubierta',
                'concentracion'      => '400 mg',
                'presentacion'       => 'Caja x 50 tabletas',
                'laboratorio'        => 'Roche',
                'registro_sanitario' => 'NSK-27891',
                'codigo_barra'       => '7750000000002',
                'descripcion'        => 'Antiinflamatorio no esteroideo (AINE).',
                'imagen_path'        => 'images/medicamentos/ibuprofeno.jpg',
            ],
            [
                'codigo'             => 'AMOX500',
                'nombre'             => 'Amoxicilina 500 mg',
                'forma_farmaceutica' => 'Cápsula',
                'concentracion'      => '500 mg',
                'presentacion'       => 'Blíster x 10 cápsulas',
                'laboratorio'        => 'Farmindustria',
                'registro_sanitario' => 'NSK-28977',
                'codigo_barra'       => '7750000000003',
                'descripcion'        => 'Antibiótico penicilínico de amplio espectro.',
                'imagen_path'        => 'images/medicamentos/amoxicilina.jpg',
            ],
            [
                'codigo'             => 'LORA10',
                'nombre'             => 'Loratadina 10 mg',
                'forma_farmaceutica' => 'Tableta',
                'concentracion'      => '10 mg',
                'presentacion'       => 'Blíster x 10 tabletas',
                'laboratorio'        => 'Bayer',
                'registro_sanitario' => 'NSK-30011',
                'codigo_barra'       => '7750000000004',
                'descripcion'        => 'Antihistamínico para alergias.',
                'imagen_path'        => 'images/medicamentos/loratadina.jpg',
            ],
            [
                'codigo'             => 'OMEP20',
                'nombre'             => 'Omeprazol 20 mg',
                'forma_farmaceutica' => 'Cápsula',
                'concentracion'      => '20 mg',
                'presentacion'       => 'Caja x 28 cápsulas',
                'laboratorio'        => 'Sandoz',
                'registro_sanitario' => 'NSK-31122',
                'codigo_barra'       => '7750000000005',
                'descripcion'        => 'Inhibidor de la bomba de protones para reflujo.',
                'imagen_path'        => 'images/medicamentos/omeprazol.jpg',
            ],
        ];

        // Insertar rotando categorías existentes
        $toInsert = [];
        foreach ($meds as $i => $m) {
            $toInsert[] = array_merge($m, [
                'categoria_id' => $categoriaIds[$i % count($categoriaIds)],
                'user_id'      => $userId,
                'activo'       => true,
                'created_at'   => $ahora,
                'updated_at'   => $ahora,
            ]);
        }

        DB::table('medicamentos')->insert($toInsert);
    }
}
