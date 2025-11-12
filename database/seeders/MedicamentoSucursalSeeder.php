<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class MedicamentoSucursalSeeder extends Seeder
{
    public function run(): void
    {
        // Tomar 3 sucursales (primeras 3)
        $sucursales = DB::table('sucursales')->orderBy('id')->limit(3)->pluck('id')->all();
        if (count($sucursales) < 3) {
            $this->command->warn('Se requieren 3 sucursales mínimas para este seeder.');
            return;
        }

        // IDs de medicamentos por código
        $meds = DB::table('medicamentos')->pluck('id', 'codigo')->all();
        if (count($meds) < 5) {
            $this->command->warn('Se requieren 5 medicamentos para este seeder.');
            return;
        }

        Schema::disableForeignKeyConstraints();
        DB::table('medicamento_sucursal')->truncate();
        Schema::enableForeignKeyConstraints();

        $ahora = Carbon::now();

        // Precio base por medicamento (puedes ajustar)
        $precios = [
            'PARA500' => 1.80,
            'IBU400'  => 2.50,
            'AMOX500' => 3.90,
            'LORA10'  => 1.60,
            'OMEP20'  => 2.80,
        ];

        $rows = [];
        foreach ($precios as $codigo => $precio) {
            foreach ($sucursales as $idx => $sucursalId) {
                $rows[] = [
                    'medicamento_id' => $meds[$codigo],
                    'sucursal_id'    => $sucursalId,
                    'stock_total'    => 0,       // LotesSeeder lo recalcula
                    'precio_venta'   => $precio + ($idx * 0.10), // leve variación por sucursal
                    'estado'         => 'vigente',
                    'created_at'     => $ahora,
                    'updated_at'     => $ahora,
                ];
            }
        }

        DB::table('medicamento_sucursal')->insert($rows);
    }
}
