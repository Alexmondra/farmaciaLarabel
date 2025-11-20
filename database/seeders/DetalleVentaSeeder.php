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
                // Seleccionamos un lote al azar de los que tienen stock
                $lote = $lotesConStock->random();

                // Si por alguna razón se agota en esta misma ejecución
                if ($lote->stock_actual <= 0) {
                    continue; // Saltar al siguiente item
                }

                // 2. Obtener el medicamento (ya lo tenemos a través del lote)
                $med = $lote->medicamento;
                // Si el medicamento no se encuentra (datos corruptos), saltar
                if (!$med) {
                    continue;
                }

                // 3. Calcular cantidades y precios
                // Nos aseguramos de no vender más de lo que hay, máximo 3 unidades
                $cantidad = rand(1, min(3, $lote->stock_actual));

                // Usar el precio de venta del medicamento, o uno aleatorio si no existe
                $precioUnitario = $med->precio_venta ?? rand(5, 20);

                // 10% de probabilidad de un descuento del 5%
                $descuentoUnitario = (rand(1, 10) == 1) ? round($precioUnitario * 0.05, 2) : 0;

                // 4. Calcular subtotales (CORRECCIÓN PRINCIPAL)
                $subtotalBruto = $cantidad * $precioUnitario;
                $subtotalDescuento = $cantidad * $descuentoUnitario;
                $subtotalNeto = $subtotalBruto - $subtotalDescuento;

                // 5. Crear el detalle de venta con las columnas correctas
                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'medicamento_id' => $med->id,
                    'lote_id' => $lote->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'descuento_unitario' => $descuentoUnitario,
                    'subtotal_bruto' => $subtotalBruto,
                    'subtotal_descuento' => $subtotalDescuento,
                    'subtotal_neto' => $subtotalNeto,
                    // 'subtotal' => 0, // <-- ESTA ERA LA LÍNEA DEL ERROR
                ]);

                // 6. Reducir el stock del lote (TU PETICIÓN)
                // Usamos decrement para una actualización segura en la BD
                $lote->decrement('stock_actual', $cantidad);
                // Actualizamos también la colección en memoria para el resto del bucle
                $lote->stock_actual -= $cantidad;
            }
        }

        // 7. Actualizar los totales en la tabla 'ventas'
        // (El modelo Venta ya tiene total_bruto, total_descuento, total_neto)
        foreach (Venta::all() as $venta) {
            // Recargamos la relación 'detalles'
            $venta->load('detalles');

            $venta->total_bruto = $venta->detalles->sum('subtotal_bruto');
            $venta->total_descuento = $venta->detalles->sum('subtotal_descuento');
            $venta->total_neto = $venta->detalles->sum('subtotal_neto');
            $venta->save();
        }
    }
}
