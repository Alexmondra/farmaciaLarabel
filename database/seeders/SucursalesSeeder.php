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
                // --- 1. SEDE PRINCIPAL (LIMA) ---
                'codigo'              => '0000',
                'nombre'              => 'FARMACIA CENTRAL - LIMA',

                'ubigeo'              => '150122',
                'departamento'        => 'LIMA',
                'provincia'           => 'LIMA',
                'distrito'            => 'MIRAFLORES',
                'direccion'           => 'Av. Jose Larco 123',
                'telefono'            => '(01) 444-1111',
                'email'               => 'ventas.lima@mifarmacia.com',
                'impuesto_porcentaje' => 18.00,
                'activo'              => true,

                // CONFIGURACIÓN DE SERIES (Familia 01)
                'serie_factura'       => 'F001',
                'serie_boleta'        => 'B001',
                'serie_nc_factura'    => 'FC01', // Nuevo: Nota Crédito Factura
                'serie_nc_boleta'     => 'BC01', // Nuevo: Nota Crédito Boleta
                'serie_guia'          => 'T001', // Nuevo: Guía Remisión (Empieza con T)
                'serie_ticket'        => 'TK01', // Interno
            ],
            [
                // --- 2. SUCURSAL NORTE (CHICLAYO) ---
                'codigo'              => '0001',
                'nombre'              => 'SUCURSAL NORTE - CHICLAYO',

                'ubigeo'              => '140101',
                'departamento'        => 'LAMBAYEQUE',
                'provincia'           => 'CHICLAYO',
                'distrito'            => 'CHICLAYO',
                'direccion'           => 'Calle San Jose 456',
                'telefono'            => '(074) 222-3333',
                'email'               => 'ventas.chiclayo@mifarmacia.com',
                'impuesto_porcentaje' => 18.00,
                'activo'              => true,

                // CONFIGURACIÓN DE SERIES (Familia 02)
                'serie_factura'       => 'F002',
                'serie_boleta'        => 'B002',
                'serie_nc_factura'    => 'FC02', // Nuevo
                'serie_nc_boleta'     => 'BC02', // Nuevo
                'serie_guia'          => 'T002', // Nuevo
                'serie_ticket'        => 'TK02', // Interno (Corregido de T002 a TK02)
            ],
            [
                // --- 3. SUCURSAL SUR (AREQUIPA) ---
                'codigo'              => '0002',
                'nombre'              => 'SUCURSAL SUR - AREQUIPA',

                'ubigeo'              => '040126',
                'departamento'        => 'AREQUIPA',
                'provincia'           => 'AREQUIPA',
                'distrito'            => 'YANAHUARA',
                'direccion'           => 'Av. Ejercito 789',
                'telefono'            => '(054) 555-6666',
                'email'               => 'ventas.arequipa@mifarmacia.com',
                'impuesto_porcentaje' => 18.00,
                'activo'              => true,

                // CONFIGURACIÓN DE SERIES (Familia 03)
                'serie_factura'       => 'F003',
                'serie_boleta'        => 'B003',
                'serie_nc_factura'    => 'FC03', // Nuevo
                'serie_nc_boleta'     => 'BC03', // Nuevo
                'serie_guia'          => 'T003', // Nuevo
                'serie_ticket'        => 'TK03', // Interno (Corregido de T003 a TK03)
            ]
        ];

        foreach ($sucursales as $data) {
            Sucursal::updateOrCreate(
                ['codigo' => $data['codigo']],
                $data
            );
        }
    }
}
