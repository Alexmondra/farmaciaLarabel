<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventario\Medicamento;
use App\Models\Inventario\Categoria;
use App\Models\Sucursal;
use App\Models\User;

class MedicamentosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener categorías
        $analgesicos = Categoria::where('nombre', 'Analgésicos')->first();
        $antibioticos = Categoria::where('nombre', 'Antibióticos')->first();
        $antiinflamatorios = Categoria::where('nombre', 'Antiinflamatorios')->first();
        $antihistaminicos = Categoria::where('nombre', 'Antihistamínicos')->first();
        $antipireticos = Categoria::where('nombre', 'Antipiréticos')->first();
        $cardiovasculares = Categoria::where('nombre', 'Cardiovasculares')->first();
        $digestivos = Categoria::where('nombre', 'Digestivos')->first();
        $respiratorios = Categoria::where('nombre', 'Respiratorios')->first();
        $vitaminas = Categoria::where('nombre', 'Vitaminas y Suplementos')->first();
        $cuidadoPersonal = Categoria::where('nombre', 'Cuidado Personal')->first();

        // Obtener usuario admin
        $user = User::first();

        $medicamentos = [
            // Analgésicos
            [
                'codigo' => 'PAR001',
                'nombre' => 'Paracetamol 500mg',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => '500mg',
                'presentacion' => 'Caja x 20 tabletas',
                'laboratorio' => 'Genfar',
                'registro_sanitario' => 'RS-001-2024',
                'codigo_barra' => '1234567890123',
                'descripcion' => 'Analgésico y antipirético para alivio del dolor y fiebre',
                'categoria_id' => $analgesicos->id,
                'user_id' => $user->id,
                'activo' => true
            ],
            [
                'codigo' => 'IBU001',
                'nombre' => 'Ibuprofeno 400mg',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => '400mg',
                'presentacion' => 'Caja x 20 tabletas',
                'laboratorio' => 'Bayer',
                'registro_sanitario' => 'RS-002-2024',
                'codigo_barra' => '1234567890124',
                'descripcion' => 'Antiinflamatorio no esteroideo para dolor e inflamación',
                'categoria_id' => $antiinflamatorios->id,
                'user_id' => $user->id,
                'activo' => true
            ],
            [
                'codigo' => 'ASP001',
                'nombre' => 'Ácido Acetilsalicílico 100mg',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => '100mg',
                'presentacion' => 'Caja x 30 tabletas',
                'laboratorio' => 'Bayer',
                'registro_sanitario' => 'RS-003-2024',
                'codigo_barra' => '1234567890125',
                'descripcion' => 'Analgésico, antipirético y antiagregante plaquetario',
                'categoria_id' => $analgesicos->id,
                'user_id' => $user->id,
                'activo' => true
            ],

            // Antibióticos
            [
                'codigo' => 'AMO001',
                'nombre' => 'Amoxicilina 500mg',
                'forma_farmaceutica' => 'Cápsula',
                'concentracion' => '500mg',
                'presentacion' => 'Caja x 21 cápsulas',
                'laboratorio' => 'Genfar',
                'registro_sanitario' => 'RS-004-2024',
                'codigo_barra' => '1234567890126',
                'descripcion' => 'Antibiótico de amplio espectro para infecciones bacterianas',
                'categoria_id' => $antibioticos->id,
                'user_id' => $user->id,
                'activo' => true
            ],
            [
                'codigo' => 'AZI001',
                'nombre' => 'Azitromicina 500mg',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => '500mg',
                'presentacion' => 'Caja x 3 tabletas',
                'laboratorio' => 'Pfizer',
                'registro_sanitario' => 'RS-005-2024',
                'codigo_barra' => '1234567890127',
                'descripcion' => 'Antibiótico macrólido para infecciones respiratorias',
                'categoria_id' => $antibioticos->id,
                'user_id' => $user->id,
                'activo' => true
            ],

            // Antihistamínicos
            [
                'codigo' => 'LOR001',
                'nombre' => 'Loratadina 10mg',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => '10mg',
                'presentacion' => 'Caja x 10 tabletas',
                'laboratorio' => 'Schering-Plough',
                'registro_sanitario' => 'RS-006-2024',
                'codigo_barra' => '1234567890128',
                'descripcion' => 'Antihistamínico para el tratamiento de alergias',
                'categoria_id' => $antihistaminicos->id,
                'user_id' => $user->id,
                'activo' => true
            ],

            // Cardiovasculares
            [
                'codigo' => 'LOS001',
                'nombre' => 'Losartán 50mg',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => '50mg',
                'presentacion' => 'Caja x 30 tabletas',
                'laboratorio' => 'Merck',
                'registro_sanitario' => 'RS-007-2024',
                'codigo_barra' => '1234567890129',
                'descripcion' => 'Antihipertensivo bloqueador del receptor de angiotensina',
                'categoria_id' => $cardiovasculares->id,
                'user_id' => $user->id,
                'activo' => true
            ],

            // Digestivos
            [
                'codigo' => 'OME001',
                'nombre' => 'Omeprazol 20mg',
                'forma_farmaceutica' => 'Cápsula',
                'concentracion' => '20mg',
                'presentacion' => 'Caja x 14 cápsulas',
                'laboratorio' => 'AstraZeneca',
                'registro_sanitario' => 'RS-008-2024',
                'codigo_barra' => '1234567890130',
                'descripcion' => 'Inhibidor de la bomba de protones para úlceras gástricas',
                'categoria_id' => $digestivos->id,
                'user_id' => $user->id,
                'activo' => true
            ],

            // Respiratorios
            [
                'codigo' => 'SAL001',
                'nombre' => 'Salbutamol 100mcg',
                'forma_farmaceutica' => 'Inhalador',
                'concentracion' => '100mcg',
                'presentacion' => 'Inhalador x 200 dosis',
                'laboratorio' => 'GlaxoSmithKline',
                'registro_sanitario' => 'RS-009-2024',
                'codigo_barra' => '1234567890131',
                'descripcion' => 'Broncodilatador para el tratamiento del asma',
                'categoria_id' => $respiratorios->id,
                'user_id' => $user->id,
                'activo' => true
            ],

            // Vitaminas
            [
                'codigo' => 'VIT001',
                'nombre' => 'Vitamina C 1000mg',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => '1000mg',
                'presentacion' => 'Frasco x 100 tabletas',
                'laboratorio' => 'Bayer',
                'registro_sanitario' => 'RS-010-2024',
                'codigo_barra' => '1234567890132',
                'descripcion' => 'Suplemento de vitamina C para fortalecer el sistema inmunológico',
                'categoria_id' => $vitaminas->id,
                'user_id' => $user->id,
                'activo' => true
            ],
            [
                'codigo' => 'VIT002',
                'nombre' => 'Vitamina D3 1000 UI',
                'forma_farmaceutica' => 'Cápsula',
                'concentracion' => '1000 UI',
                'presentacion' => 'Frasco x 60 cápsulas',
                'laboratorio' => 'Nature Made',
                'registro_sanitario' => 'RS-011-2024',
                'codigo_barra' => '1234567890133',
                'descripcion' => 'Suplemento de vitamina D3 para la salud ósea',
                'categoria_id' => $vitaminas->id,
                'user_id' => $user->id,
                'activo' => true
            ],

            // Cuidado Personal
            [
                'codigo' => 'ALC001',
                'nombre' => 'Alcohol 70%',
                'forma_farmaceutica' => 'Solución',
                'concentracion' => '70%',
                'presentacion' => 'Frasco x 500ml',
                'laboratorio' => 'Genfar',
                'registro_sanitario' => 'RS-012-2024',
                'codigo_barra' => '1234567890134',
                'descripcion' => 'Antiséptico para desinfección de heridas',
                'categoria_id' => $cuidadoPersonal->id,
                'user_id' => $user->id,
                'activo' => true
            ],
            [
                'codigo' => 'GAS001',
                'nombre' => 'Gasas Estériles 10x10cm',
                'forma_farmaceutica' => 'Gasas',
                'concentracion' => '10x10cm',
                'presentacion' => 'Paquete x 10 unidades',
                'laboratorio' => 'Johnson & Johnson',
                'registro_sanitario' => 'RS-013-2024',
                'codigo_barra' => '1234567890135',
                'descripcion' => 'Gasas estériles para curación de heridas',
                'categoria_id' => $cuidadoPersonal->id,
                'user_id' => $user->id,
                'activo' => true
            ]
        ];

        foreach ($medicamentos as $medicamento) {
            Medicamento::updateOrCreate(
                ['codigo' => $medicamento['codigo']],
                $medicamento
            );
        }

        $this->command->info('✅ Se han creado ' . count($medicamentos) . ' medicamentos de ejemplo');

        // Agregar algunos medicamentos a sucursales si existen
        $sucursales = Sucursal::all();
        if ($sucursales->count() > 0) {
            $medicamentosCreados = Medicamento::all();
            
            foreach ($sucursales as $sucursal) {
                foreach ($medicamentosCreados->take(5) as $medicamento) {
                    // Verificar si ya existe la relación
                    if (!$medicamento->sucursales()->where('sucursal_id', $sucursal->id)->exists()) {
                        $medicamento->sucursales()->attach($sucursal->id, [
                            'precio_compra' => rand(5, 50),
                            'precio_venta' => rand(10, 80),
                            'stock_actual' => rand(10, 100),
                            'stock_minimo' => rand(5, 20),
                            'ubicacion' => 'Estante ' . chr(65 + rand(0, 5)) . '-' . rand(1, 10),
                            'updated_by' => $user->id
                        ]);
                    }
                }
            }
            
            $this->command->info('✅ Se han agregado medicamentos a las sucursales');
        }
    }
}