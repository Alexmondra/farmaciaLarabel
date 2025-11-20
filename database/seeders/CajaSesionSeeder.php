<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ventas\CajaSesion;
use Carbon\Carbon; // Importante para manejar fechas

class CajaSesionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- SESIONES CERRADAS (Días anteriores) ---

        // 1. Sesión cerrada: Cierre perfecto
        $fecha1 = Carbon::now()->subDays(3);
        $saldoInicial1 = 150.00;
        $saldoTeorico1 = 1850.50;
        $saldoReal1 = 1850.50;
        CajaSesion::create([
            'sucursal_id' => 1,
            'user_id' => 1, // Cajero 1
            'fecha_apertura' => $fecha1->copy()->setHour(8)->setMinute(0),
            'saldo_inicial' => $saldoInicial1,
            'fecha_cierre' => $fecha1->copy()->setHour(17)->setMinute(5),
            'saldo_teorico' => $saldoTeorico1,
            'saldo_real' => $saldoReal1,
            'diferencia' => $saldoReal1 - $saldoTeorico1, // 0.00
            'estado' => 'CERRADO',
            'observaciones' => 'Cierre de caja sin novedades.',
        ]);

        // 2. Sesión cerrada: Faltante
        $fecha2 = Carbon::now()->subDays(2);
        $saldoInicial2 = 100.00;
        $saldoTeorico2 = 1200.00;
        $saldoReal2 = 1195.50; // Faltaron 4.50
        CajaSesion::create([
            'sucursal_id' => 2,
            'user_id' => 1, // Cajero 2
            'fecha_apertura' => $fecha2->copy()->setHour(9)->setMinute(0),
            'saldo_inicial' => $saldoInicial2,
            'fecha_cierre' => $fecha2->copy()->setHour(18)->setMinute(0),
            'saldo_teorico' => $saldoTeorico2,
            'saldo_real' => $saldoReal2,
            'diferencia' => $saldoReal2 - $saldoTeorico2, // -4.50
            'estado' => 'CERRADO',
            'observaciones' => 'Faltante de 4.50 reportado a supervisor.',
        ]);

        // 3. Sesión cerrada: Sobrante
        $fecha3 = Carbon::now()->subDays(1);
        $saldoInicial3 = 200.00;
        $saldoTeorico3 = 2100.75;
        $saldoReal3 = 2110.00; // Sobraron 9.25
        CajaSesion::create([
            'sucursal_id' => 3,
            'user_id' => 1, // Cajero 1 de nuevo
            'fecha_apertura' => $fecha3->copy()->setHour(8)->setMinute(30),
            'saldo_inicial' => $saldoInicial3,
            'fecha_cierre' => $fecha3->copy()->setHour(17)->setMinute(30),
            'saldo_teorico' => $saldoTeorico3,
            'saldo_real' => $saldoReal3,
            'diferencia' => $saldoReal3 - $saldoTeorico3, // 9.25
            'estado' => 'CERRADO',
            'observaciones' => 'Sobrante de 9.25 en caja.',
        ]);

        // --- SESIONES ABIERTAS (Hoy) ---

        // 4. Sesión abierta: Turno mañana
        CajaSesion::create([
            'sucursal_id' => 1, // Misma sucursal 1
            'user_id' => 1,
            'fecha_apertura' => Carbon::now()->setHour(8)->setMinute(0),
            'saldo_inicial' => 150.00,
            'fecha_cierre' => null,
            'saldo_teorico' => null,
            'saldo_real' => null,
            'diferencia' => null,
            'estado' => 'ABIERTO',
            'observaciones' => 'Inicio turno mañana.',
        ]);

        // 5. Sesión abierta: Turno tarde
        CajaSesion::create([
            'sucursal_id' => 2, // Misma sucursal 2
            'user_id' => 1,
            'fecha_apertura' => Carbon::now()->setHour(14)->setMinute(0),
            'saldo_inicial' => 100.00,
            'fecha_cierre' => null,
            'saldo_teorico' => null,
            'saldo_real' => null,
            'diferencia' => null,
            'estado' => 'ABIERTO',
            'observaciones' => 'Inicio turno tarde.',
        ]);
    }
}
