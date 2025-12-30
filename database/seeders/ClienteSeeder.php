<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ventas\Cliente;
use Illuminate\Support\Facades\DB;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        Cliente::firstOrCreate(
            ['id' => 1],
            [
                'nombre'           => 'PÚBLICO',
                'apellidos'        => 'GENERAL',
                'tipo_documento'   => '0',
                'documento'        => '00000000',
                'sexo'             => 'M',
                'telefono'         => null,
                'direccion'        => '-',
                'email'            => null,
                'fecha_nacimiento' => null,
                'activo'           => true,
            ]
        );
    }
}
