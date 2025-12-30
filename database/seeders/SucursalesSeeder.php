<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sucursal;

class SucursalesSeeder extends Seeder
{
    public function run(): void
    {
        $sucursales = [
            // ==========================================================
            // SUCURSAL 1: LIMA (PRINCIPAL)
            // ==========================================================
            [
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
                // Series Familia 01
                'serie_factura'       => 'F001',
                'serie_boleta'        => 'B001',
                'serie_nc_factura'    => 'FC01',
                'serie_nc_boleta'     => 'BC01',
                'serie_guia'          => 'T001',
                'serie_ticket'        => 'TK01',
            ],

            // ==========================================================
            // SUCURSAL 2: CHICLAYO (NORTE 1)
            // ==========================================================
            [
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
                // Series Familia 02
                'serie_factura'       => 'F002',
                'serie_boleta'        => 'B002',
                'serie_nc_factura'    => 'FC02',
                'serie_nc_boleta'     => 'BC02',
                'serie_guia'          => 'T002',
                'serie_ticket'        => 'TK02',
            ],

            // ==========================================================
            // SUCURSAL 3: AREQUIPA (SUR 1)
            // ==========================================================
            [
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
                // Series Familia 03
                'serie_factura'       => 'F003',
                'serie_boleta'        => 'B003',
                'serie_nc_factura'    => 'FC03',
                'serie_nc_boleta'     => 'BC03',
                'serie_guia'          => 'T003',
                'serie_ticket'        => 'TK03',
            ],

            // ==========================================================
            // SUCURSAL 4: TRUJILLO (NORTE 2)
            // ==========================================================
            [
                'codigo'              => '0003',
                'nombre'              => 'SUCURSAL NORTE - TRUJILLO',
                'ubigeo'              => '130101',
                'departamento'        => 'LA LIBERTAD',
                'provincia'           => 'TRUJILLO',
                'distrito'            => 'TRUJILLO',
                'direccion'           => 'Av. España 245, Centro Histórico',
                'telefono'            => '(044) 201-5555',
                'email'               => 'ventas.trujillo@mifarmacia.com',
                'impuesto_porcentaje' => 18.00,
                'activo'              => true,
                // Series Familia 04
                'serie_factura'       => 'F004',
                'serie_boleta'        => 'B004',
                'serie_nc_factura'    => 'FC04',
                'serie_nc_boleta'     => 'BC04',
                'serie_guia'          => 'T004',
                'serie_ticket'        => 'TK04',
            ],

            // ==========================================================
            // SUCURSAL 5: CUSCO (SUR 2)
            // ==========================================================
            [
                'codigo'              => '0004',
                'nombre'              => 'SUCURSAL SUR - CUSCO',
                'ubigeo'              => '080101',
                'departamento'        => 'CUSCO',
                'provincia'           => 'CUSCO',
                'distrito'            => 'CUSCO',
                'direccion'           => 'Av. El Sol 900',
                'telefono'            => '(084) 231-9999',
                'email'               => 'ventas.cusco@mifarmacia.com',
                'impuesto_porcentaje' => 18.00,
                'activo'              => true,
                // Series Familia 05
                'serie_factura'       => 'F005',
                'serie_boleta'        => 'B005',
                'serie_nc_factura'    => 'FC05',
                'serie_nc_boleta'     => 'BC05',
                'serie_guia'          => 'T005',
                'serie_ticket'        => 'TK05',
            ],

            // ==========================================================
            // SUCURSAL 6: PIURA (NORTE 3)
            // ==========================================================
            [
                'codigo'              => '0005',
                'nombre'              => 'SUCURSAL NORTE - PIURA',
                'ubigeo'              => '200101',
                'departamento'        => 'PIURA',
                'provincia'           => 'PIURA',
                'distrito'            => 'PIURA',
                'direccion'           => 'Av. Grau 550',
                'telefono'            => '(073) 304-8888',
                'email'               => 'ventas.piura@mifarmacia.com',
                'impuesto_porcentaje' => 18.00,
                'activo'              => true,
                // Series Familia 06
                'serie_factura'       => 'F006',
                'serie_boleta'        => 'B006',
                'serie_nc_factura'    => 'FC06',
                'serie_nc_boleta'     => 'BC06',
                'serie_guia'          => 'T006',
                'serie_ticket'        => 'TK06',
            ],
        ];

        foreach ($sucursales as $data) {
            Sucursal::updateOrCreate(
                ['codigo' => $data['codigo']], // Busca por código único
                $data
            );
        }
    }
}
