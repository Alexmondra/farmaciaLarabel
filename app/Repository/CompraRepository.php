<?php

namespace App\Repository;

use App\Models\Compras\Compra;
use App\Models\Compras\DetalleCompra;
use App\Models\Inventario\Lote;
use Illuminate\Support\Facades\DB;

class CompraRepository
{
    /**
     * Registra una compra completa (cabecera + detalles + lotes)
     *
     * @param  array  $dataCabecera   datos de la tabla compras
     * @param  array  $items          array de filas de detalle
     * @return \App\Models\Compra
     */
    public function registrarCompra(array $dataCabecera, array $items): Compra
    {
        return DB::transaction(function () use ($dataCabecera, $items) {

            // 1) Crear cabecera de compra
            /** @var Compra $compra */
            $compra = Compra::create($dataCabecera);

            // 2) Recorrer cada item de detalle
            foreach ($items as $item) {

                // Crear detalle
                $detalle = new DetalleCompra([
                    'medicamento_id'         => $item['medicamento_id'],
                    'cantidad'               => $item['cantidad'],
                    'precio_compra_unitario' => $item['precio_compra_unitario'],
                    'codigo_lote'            => $item['codigo_lote'] ?? null,
                    'fecha_vencimiento'      => $item['fecha_vencimiento'] ?? null,
                    'ubicacion'              => $item['ubicacion'] ?? null,
                ]);

                $compra->detalles()->save($detalle);

                // 3) Actualizar / crear lote
                if (!empty($item['codigo_lote'])) {

                    $lote = Lote::firstOrNew([
                        'medicamento_id' => $item['medicamento_id'],
                        'sucursal_id'    => $compra->sucursal_id,
                        'codigo_lote'    => $item['codigo_lote'],
                    ]);

                    // Si es nuevo, inicializamos campos
                    if (!$lote->exists) {
                        $lote->stock_actual    = 0;
                        $lote->precio_compra   = $item['precio_compra_unitario'] ?? null;
                        $lote->fecha_vencimiento = $item['fecha_vencimiento'] ?? null;
                        $lote->ubicacion       = $item['ubicacion'] ?? null;
                    }

                    // Sumamos stock
                    $lote->stock_actual += (int) $item['cantidad'];

                    // Actualizamos info básica si viene
                    if (!empty($item['fecha_vencimiento'])) {
                        $lote->fecha_vencimiento = $item['fecha_vencimiento'];
                    }

                    if (!empty($item['ubicacion'])) {
                        $lote->ubicacion = $item['ubicacion'];
                    }

                    if (!empty($item['precio_compra_unitario'])) {
                        $lote->precio_compra = $item['precio_compra_unitario'];
                    }

                    $lote->save();
                }
            }

            return $compra;
        });
    }
}
