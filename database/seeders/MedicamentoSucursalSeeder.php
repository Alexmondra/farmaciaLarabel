<?php
// ARCHIVO: MedicamentoSucursalSeeder.php (ACTUALIZADO)

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class MedicamentoSucursalSeeder extends Seeder
{
    public function run(): void
    {
        // CAMBIO: Obtener las primeras 3 sucursales
        $sucursales = DB::table('sucursales')->orderBy('id')->limit(3)->pluck('id');

        if ($sucursales->count() < 3) {
            $this->command->warn('Se esperaban 3 sucursales, pero se encontraron ' . $sucursales->count() . '.');
            if ($sucursales->isEmpty()) {
                $this->command->warn('No se encontraron sucursales.');
                return;
            }
        }

        // 5 medicamentos
        $meds = DB::table('medicamentos')->orderBy('id')->limit(5)->get(['id', 'codigo']);
        if ($meds->count() < 5) {
            $this->command->warn('Se requieren 5 medicamentos para este seeder.');
            return;
        }

        Schema::disableForeignKeyConstraints();
        DB::table('medicamento_sucursal')->truncate();
        Schema::enableForeignKeyConstraints();

        $ahora = Carbon::now();
        $userId = DB::table('users')->value('id');

        $precios = [
            'PARA500' => 1.80,
            'IBU400'  => 2.50,
            'AMOX500' => 3.90,
            'LORA10'  => 1.60,
            'OMEP20'  => 2.80,
        ];

        $rows = [];

        // CAMBIO: Bucle anidado para recorrer sucursales Y medicamentos
        foreach ($sucursales as $sucursalId) {
            foreach ($meds as $med) {
                $rows[] = [
                    'medicamento_id' => $med->id,
                    'sucursal_id'    => $sucursalId, // Se usa el ID de la sucursal actual
                    'stock_minimo'   => 10,
                    'precio_venta'   => $precios[$med->codigo] ?? 2.00,
                    'activo'         => true,
                    'updated_by'     => $userId,
                    'created_at'     => $ahora,
                    'updated_at'     => $ahora,
                ];
            }
        }

        DB::table('medicamento_sucursal')->insert($rows);
    }
}
