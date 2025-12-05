<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sucursal;

class SucursalesSeeder extends Seeder
{
    public function run(): void
    {
        $sucursales = [
            [
                // --- SEDE PRINCIPAL (OBLIGATORIO CÓDIGO 0000) ---
                'codigo'              => '0000', // Código SUNAT Principal
                'nombre'              => 'FARMACIA CENTRAL - LIMA',

                // Ubicación (Lima / Lima / Miraflores)
                'ubigeo'              => '150122',
                'departamento'        => 'LIMA',
                'provincia'           => 'LIMA',
                'distrito'            => 'MIRAFLORES',
                'direccion'           => 'Av. Jose Larco 123',

                // Contacto
                'telefono'            => '(01) 444-1111',
                'email'               => 'ventas.lima@mifarmacia.com',

                // Configuración Fiscal
                'impuesto_porcentaje' => 18.00,
                'serie_boleta'        => 'B001',
                'serie_factura'       => 'F001',
                'serie_ticket'        => 'T001',
                'activo'              => true,
            ],
            [
                // --- SUCURSAL 1 (NORTE) ---
                'codigo'              => '0001', // Código SUNAT Anexo 1
                'nombre'              => 'SUCURSAL NORTE - CHICLAYO',

                // Ubicación (Lambayeque / Chiclayo / Chiclayo)
                'ubigeo'              => '140101',
                'departamento'        => 'LAMBAYEQUE',
                'provincia'           => 'CHICLAYO',
                'distrito'            => 'CHICLAYO',
                'direccion'           => 'Calle San Jose 456',

                // Contacto
                'telefono'            => '(074) 222-3333',
                'email'               => 'ventas.chiclayo@mifarmacia.com',

                // Configuración Fiscal (Series diferentes para no mezclar)
                'impuesto_porcentaje' => 18.00,
                'serie_boleta'        => 'B002',
                'serie_factura'       => 'F002',
                'serie_ticket'        => 'T002',
                'activo'              => true,
            ],
            [
                // --- SUCURSAL 2 (SUR) ---
                'codigo'              => '0002', // Código SUNAT Anexo 2
                'nombre'              => 'SUCURSAL SUR - AREQUIPA',

                // Ubicación (Arequipa / Arequipa / Yanahuara)
                'ubigeo'              => '040126',
                'departamento'        => 'AREQUIPA',
                'provincia'           => 'AREQUIPA',
                'distrito'            => 'YANAHUARA',
                'direccion'           => 'Av. Ejercito 789',

                // Contacto
                'telefono'            => '(054) 555-6666',
                'email'               => 'ventas.arequipa@mifarmacia.com',

                // Configuración Fiscal
                'impuesto_porcentaje' => 18.00,
                'serie_boleta'        => 'B003',
                'serie_factura'       => 'F003',
                'serie_ticket'        => 'T003',
                'activo'              => true,
            ]
        ];

        foreach ($sucursales as $data) {
            // Usamos updateOrCreate para no duplicar si corres el seeder dos veces
            Sucursal::updateOrCreate(
                ['codigo' => $data['codigo']], // Busca por código SUNAT
                $data
            );
        }
    }
}
