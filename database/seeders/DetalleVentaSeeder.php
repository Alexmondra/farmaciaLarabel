<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ventas\DetalleVenta;
use App\Models\Ventas\Venta;
use App\Models\Inventario\Medicamento;
use App\Models\Inventario\Lote;

class DetalleVentaSeeder extends Seeder
{
    public function run(): void
    {
        $ventas = Venta::all();
        // Cargamos todos los lotes que SÍ tienen stock
        $lotesConStock = Lote::where('stock_actual', '>', 0)->get();

        // Si no hay lotes o medicamentos, no podemos hacer nada.
        if ($lotesConStock->isEmpty() || Medicamento::count() == 0) {
            $this->command->error('No se encontraron Lotes con stock o Medicamentos. Ejecuta esos seeders primero.');
            return;
        }

        foreach ($ventas as $venta) {
            $cantDetalles = rand(1, 4); // 1 a 4 productos por venta

            for ($i = 0; $i < $cantDetalles; $i++) {

                // 1. Encontrar un lote válido
                $lote = $lotesConStock->random();

                if ($lote->stock_actual <= 0) {
                    continue;
                }

                $med = $lote->medicamento;
                if (!$med) {
                    continue;
                }

                // 2. Calcular cantidades y precios
                $cantidad = rand(1, min(3, $lote->stock_actual));

                // PRECIO FINAL (Con IGV incluido)
                $precioUnitario = $med->precio_venta ?? rand(5, 20);

                // --- NUEVA LÓGICA SUNAT (Cálculo inverso) ---
                // Asumimos IGV 18% para el Seeder por defecto
                $valorUnitario = $precioUnitario / 1.18; // Base Imponible
                $igvUnitario   = $precioUnitario - $valorUnitario; // Impuesto
                // ---------------------------------------------

                // Descuentos (Lógica simple)
                $descuentoUnitario = (rand(1, 10) == 1) ? round($precioUnitario * 0.05, 2) : 0;

                // 3. Calcular subtotales
                $subtotalBruto = $cantidad * $precioUnitario;
                $subtotalDescuento = $cantidad * $descuentoUnitario;
                $subtotalNeto = $subtotalBruto - $subtotalDescuento;

                // 4. Crear el detalle de venta con LOS NUEVOS CAMPOS
                DetalleVenta::create([
                    'venta_id'          => $venta->id,
                    'medicamento_id'    => $med->id,
                    'lote_id'           => $lote->id,
                    'cantidad'          => $cantidad,

                    // Datos para el Cliente
                    'precio_unitario'   => $precioUnitario,
                    'descuento_unitario' => $descuentoUnitario,

                    // Datos para SUNAT (¡Lo que te faltaba!)
                    'valor_unitario'    => $valorUnitario,
                    'igv'               => $igvUnitario,
                    'tipo_afectacion'   => '10', // Código '10' = Gravado - Operación Onerosa

                    // Totales
                    'subtotal_bruto'    => $subtotalBruto,
                    'subtotal_descuento' => $subtotalDescuento,
                    'subtotal_neto'     => $subtotalNeto,
                ]);

                // 5. Reducir el stock del lote
                $lote->decrement('stock_actual', $cantidad);
                $lote->stock_actual -= $cantidad;
            }
        }

        // 7. Actualizar los totales en la tabla 'ventas' (PADRE)
        // Ahora también debemos sumarizar op_gravada y total_igv
        foreach (Venta::all() as $venta) {
            $venta->load('detalles');

            if ($venta->detalles->count() > 0) {
                // Totales de Dinero
                $venta->total_bruto     = $venta->detalles->sum('subtotal_bruto');
                $venta->total_descuento = $venta->detalles->sum('subtotal_descuento');
                $venta->total_neto      = $venta->detalles->sum('subtotal_neto');

                // Totales de Impuestos (Recálculo inverso sobre el total neto final)
                // Es más preciso hacerlo sobre el total neto para evitar errores de redondeo de centavos
                $venta->op_gravada      = $venta->total_neto / 1.18;
                $venta->total_igv       = $venta->total_neto - $venta->op_gravada;
                $venta->porcentaje_igv  = 18.00;

                // Si hubiera exonerado, iría en op_exonerada, pero por ahora asumimos todo gravado en el seeder
                $venta->op_exonerada    = 0;
                $venta->op_inafecta     = 0;

                $venta->save();
            } else {
                // Si la venta quedó vacía por falta de stock en lotes, la borramos para no dejar basura
                $venta->delete();
            }
        }
    }
}
