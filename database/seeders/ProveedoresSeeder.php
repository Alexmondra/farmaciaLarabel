<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ProveedoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('proveedores')->truncate();
        Schema::enableForeignKeyConstraints();

        $ahora = Carbon::now();

        DB::table('proveedores')->insert([
            [
                'razon_social' => 'Droguería FARMA-DIST S.A.C.',
                'ruc' => '20501234567',
                'direccion' => 'Av. El Sol 123, Lima',
                'telefono' => '987654321',
                'email' => 'ventas@farmadist.com',
                'activo' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'razon_social' => 'Distribuidora MEDI-PERU S.R.L.',
                'ruc' => '20409876543',
                'direccion' => 'Calle Luna 456, Arequipa',
                'telefono' => '912345678',
                'email' => 'pedidos@mediperu.com',
                'activo' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
        ]);
    }
}
