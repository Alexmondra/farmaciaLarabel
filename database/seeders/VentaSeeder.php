<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ventas\Venta;
use App\Models\Ventas\Cliente;
use Carbon\Carbon; // Importante para manejar fechas

class VentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = Cliente::all();

        if ($clientes->isEmpty()) {
            $this->command->error('No se encontraron clientes. Por favor, ejecuta el ClienteSeeder primero.');
            return;
        }

        for ($i = 1; $i <= 5; $i++) {

            // Generamos totales realistas y variados
            $totalBruto = round(rand(5000, 20000) / 100, 2); // Entre 50.00 y 200.00
            $descuento = ($i % 2 == 0) ? round($totalBruto * 0.1, 2) : 0; // 10% descuento en ventas pares
            $totalNeto = $totalBruto - $descuento;

            // Fechas variadas para que coincidan con las cajas cerradas
            // Asumimos que las ID 1, 2, 3 de caja_sesion son de días anteriores
            $fecha = match ($i) {
                1 => Carbon::now()->subDays(3)->setHour(10),
                2 => Carbon::now()->subDays(2)->setHour(11),
                3 => Carbon::now()->subDays(1)->setHour(12),
                4 => Carbon::now()->setHour(9), // Caja abierta de hoy
                5 => Carbon::now()->setHour(15), // Caja abierta de hoy
                default => Carbon::now(),
            };

            Venta::create([
                // --- Tus Requisitos ---
                'user_id' => 1, // Todas del User 1 (Vendedor)
                'sucursal_id' => rand(1, 3), // Sucursal varía entre 1 y 3

                // --- Campos Corregidos ---
                'cliente_id' => $clientes->random()->id,
                'caja_sesion_id' => $i, // Asocia a las cajas 1-5

                'tipo_comprobante' => ($i % 2 == 0) ? 'FACTURA' : 'BOLETA', // Variamos comprobante
                'serie' => ($i % 2 == 0) ? 'F001' : 'B001',
                'numero' => $i, // Número correlativo simple
                'fecha_emision' => $fecha,

                // --- Campos Faltantes (Modelo) ---
                'total_bruto' => $totalBruto,
                'total_descuento' => $descuento,
                'total_neto' => $totalNeto,
                'medio_pago' => ($i % 3 == 0) ? 'TARJETA' : 'EFECTIVO', // Variamos medio de pago
                'estado' => 'EMITIDA',
                'observaciones' => null,
            ]);
        }
    }
}
