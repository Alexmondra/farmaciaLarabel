<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ventas\Cliente;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $nombres = [
            ['Juan', 'Perez', 'M'],
            ['Maria', 'Lopez', 'F'],
            ['Carlos', 'Sanchez', 'M'],
            ['Luisa', 'Castillo', 'F'],
            ['Jose', 'Ramos', 'M'],
        ];

        foreach ($nombres as $i => $n) {
            Cliente::create([
                'nombre' => $n[0],
                'apellidos' => $n[1], // CORREGIDO: de 'apellido' a 'apellidos'
                'sexo' => $n[2], // AÑADIDO: Valor para la columna 'sexo'
                'telefono' => '9' . rand(10000000, 99999999),
                'direccion' => 'Direccion ' . ($i + 1),

                // Estos campos están en tu $fillable pero no en el seeder.
                // Los añado como nulos o valores por defecto para que coincidan con el modelo.
                'tipo_documento' => 'DNI',
                'documento' => '7' . rand(1000000, 9999999), // Asumo que DNI y documento son lo mismo aquí
                'fecha_nacimiento' => null,
                'email' => strtolower($n[0]) . '@correo.com',
                'activo' => true,
            ]);
        }
    }
}
