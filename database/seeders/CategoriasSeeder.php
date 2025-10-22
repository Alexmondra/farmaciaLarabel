<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventario\Categoria;

class CategoriasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Analgésicos',
                'descripcion' => 'Medicamentos para aliviar el dolor',
                'activo' => true
            ],
            [
                'nombre' => 'Antibióticos',
                'descripcion' => 'Medicamentos para combatir infecciones bacterianas',
                'activo' => true
            ],
            [
                'nombre' => 'Antiinflamatorios',
                'descripcion' => 'Medicamentos para reducir la inflamación',
                'activo' => true
            ],
            [
                'nombre' => 'Antihistamínicos',
                'descripcion' => 'Medicamentos para tratar alergias',
                'activo' => true
            ],
            [
                'nombre' => 'Antipiréticos',
                'descripcion' => 'Medicamentos para reducir la fiebre',
                'activo' => true
            ],
            [
                'nombre' => 'Antitusivos',
                'descripcion' => 'Medicamentos para aliviar la tos',
                'activo' => true
            ],
            [
                'nombre' => 'Broncodilatadores',
                'descripcion' => 'Medicamentos para abrir las vías respiratorias',
                'activo' => true
            ],
            [
                'nombre' => 'Cardiovasculares',
                'descripcion' => 'Medicamentos para el sistema cardiovascular',
                'activo' => true
            ],
            [
                'nombre' => 'Digestivos',
                'descripcion' => 'Medicamentos para problemas digestivos',
                'activo' => true
            ],
            [
                'nombre' => 'Dermatológicos',
                'descripcion' => 'Medicamentos para problemas de la piel',
                'activo' => true
            ],
            [
                'nombre' => 'Diuréticos',
                'descripcion' => 'Medicamentos que aumentan la producción de orina',
                'activo' => true
            ],
            [
                'nombre' => 'Endocrinos',
                'descripcion' => 'Medicamentos para el sistema endocrino',
                'activo' => true
            ],
            [
                'nombre' => 'Gastrointestinales',
                'descripcion' => 'Medicamentos para el tracto gastrointestinal',
                'activo' => true
            ],
            [
                'nombre' => 'Hematológicos',
                'descripcion' => 'Medicamentos para trastornos de la sangre',
                'activo' => true
            ],
            [
                'nombre' => 'Hormonales',
                'descripcion' => 'Medicamentos que contienen hormonas',
                'activo' => true
            ],
            [
                'nombre' => 'Inmunosupresores',
                'descripcion' => 'Medicamentos que suprimen el sistema inmunológico',
                'activo' => true
            ],
            [
                'nombre' => 'Neurológicos',
                'descripcion' => 'Medicamentos para el sistema nervioso',
                'activo' => true
            ],
            [
                'nombre' => 'Oftalmológicos',
                'descripcion' => 'Medicamentos para problemas oculares',
                'activo' => true
            ],
            [
                'nombre' => 'Otorrinolaringológicos',
                'descripcion' => 'Medicamentos para oído, nariz y garganta',
                'activo' => true
            ],
            [
                'nombre' => 'Psiquiátricos',
                'descripcion' => 'Medicamentos para trastornos mentales',
                'activo' => true
            ],
            [
                'nombre' => 'Respiratorios',
                'descripcion' => 'Medicamentos para el sistema respiratorio',
                'activo' => true
            ],
            [
                'nombre' => 'Reumatológicos',
                'descripcion' => 'Medicamentos para enfermedades reumáticas',
                'activo' => true
            ],
            [
                'nombre' => 'Urológicos',
                'descripcion' => 'Medicamentos para el sistema urinario',
                'activo' => true
            ],
            [
                'nombre' => 'Vitaminas y Suplementos',
                'descripcion' => 'Suplementos vitamínicos y minerales',
                'activo' => true
            ],
            [
                'nombre' => 'Cuidado Personal',
                'descripcion' => 'Productos de higiene y cuidado personal',
                'activo' => true
            ],
            [
                'nombre' => 'Primeros Auxilios',
                'descripcion' => 'Productos para primeros auxilios',
                'activo' => true
            ],
            [
                'nombre' => 'Maternidad e Infantil',
                'descripcion' => 'Productos para embarazadas y bebés',
                'activo' => true
            ],
            [
                'nombre' => 'Adultos Mayores',
                'descripcion' => 'Medicamentos específicos para adultos mayores',
                'activo' => true
            ],
            [
                'nombre' => 'Medicina Natural',
                'descripcion' => 'Productos de medicina natural y homeopatía',
                'activo' => true
            ],
            [
                'nombre' => 'Equipos Médicos',
                'descripcion' => 'Equipos y dispositivos médicos',
                'activo' => true
            ]
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }

        $this->command->info('✅ Se han creado ' . count($categorias) . ' categorías de medicamentos');
    }
}
