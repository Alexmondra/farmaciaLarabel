<?php
// ARCHIVO: ComprasLotesSeeder.php (MODIFICADO Y DINÁMICO)

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ComprasLotesSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('detalle_compras')->truncate();
        DB::table('compras')->truncate();
        DB::table('lotes')->truncate();
        Schema::enableForeignKeyConstraints();

        $ahora = Carbon::now();

        // --- 1. Obtener Datos Maestros ---
        $user = DB::table('users')->first();
        $proveedor = DB::table('proveedores')->first();

        // Obtenemos las 3 sucursales
        $sucursales = DB::table('sucursales')->orderBy('id')->limit(3)->pluck('id');

        // Obtenemos la info de los 5 medicamentos base para saber su código
        $medsInfo = DB::table('medicamentos')->orderBy('id')->limit(5)->get(['id', 'codigo']);

        // Costos base por código (para usarlos con variación)
        $costosBase = [
            'PARA500' => 1.10,
            'IBU400'  => 1.95,
            'AMOX500' => 3.00,
            'LORA10'  => 1.05,
            'OMEP20'  => 2.10,
        ];

        if ($sucursales->isEmpty() || $medsInfo->isEmpty() || !$user || !$proveedor) {
            $this->command->warn('Faltan datos maestros (Sucursales, Medicamentos, User o Proveedor).');
            return;
        }

        // --- 2. Bucle principal: 1 Compra por cada SUCURSAL ---
        foreach ($sucursales as $sucursalId) {

            // --- 2a. Crear la Factura (Compra) para ESTA sucursal ---
            $compraId = DB::table('compras')->insertGetId([
                'sucursal_id' => $sucursalId,
                'proveedor_id' => $proveedor->id,
                'user_id' => $user->id,
                'numero_factura_proveedor' => 'F001-' . str_pad($sucursalId, 4, '0', STR_PAD_LEFT),
                'fecha_recepcion' => $ahora->copy()->subDays(rand(1, 30)), // Fechas variables
                'costo_total_factura' => 0, // Se actualiza al final
                'estado' => 'recibida',
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            $totalFactura = 0;

            // --- 2b. Tomamos los 5 productos asignados a ESTA sucursal ---
            $medsSucursal = DB::table('medicamento_sucursal')
                ->where('sucursal_id', $sucursalId)
                ->get();

            // --- 2c. CAMBIO: Decidir cuántos medicamentos comprar (de 2 a 5) ---
            $cantidadTiposAComprar = rand(2, $medsSucursal->count());
            $medsComprados = $medsSucursal->random($cantidadTiposAComprar);


            // --- 2d. Iterar sobre los medicamentos ALEATORIOS seleccionados ---
            foreach ($medsComprados as $ms) {

                // --- 2e. CAMBIO: Decidir cuántos lotes comprar (1 o 2) ---
                $numeroDeLotes = rand(1, 2);

                // --- 2f. Bucle para crear los lotes (1 o 2) ---
                for ($i = 0; $i < $numeroDeLotes; $i++) {

                    // Datos dinámicos para el lote
                    $medInfo = $medsInfo->firstWhere('id', $ms->medicamento_id);
                    $baseCosto = $costosBase[$medInfo->codigo] ?? 1.50;

                    $cantidad = rand(20, 150); // Stock aleatorio
                    $costoUnitario = round($baseCosto + (rand(-10, 15) / 100.0), 2); // Costo con variación
                    $fechaVenc = $ahora->copy()->addMonths(rand(6, 24)); // Vencimiento aleatorio

                    $totalFactura += ($cantidad * $costoUnitario);

                    // 3a. Crear el Lote (El inventario físico)
                    $loteId = DB::table('lotes')->insertGetId([
                        'medicamento_id' => $ms->medicamento_id,
                        'sucursal_id' => $ms->sucursal_id,
                        'codigo_lote' => 'L-S' . $ms->sucursal_id . '-M' . $ms->medicamento_id . '-' . ($i + 1),
                        'stock_actual' => $cantidad,
                        'fecha_vencimiento' => $fechaVenc,
                        'precio_compra' => $costoUnitario,
                        'precio_oferta' => null,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ]);

                    // 3b. Crear el Detalle de Compra (El historial)
                    DB::table('detalle_compras')->insert([
                        'compra_id' => $compraId,
                        'lote_id' => $loteId,
                        'cantidad_recibida' => $cantidad,
                        'precio_compra_unitario' => $costoUnitario,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ]);
                }
            }

            // --- 2g. Actualizar el Total de la Factura ---
            DB::table('compras')->where('id', $compraId)->update([
                'costo_total_factura' => round($totalFactura, 2)
            ]);
        } // Fin del bucle de sucursales
    }
}
