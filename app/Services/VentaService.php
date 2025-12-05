<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Ventas\Venta;
use App\Models\Ventas\DetalleVenta;
use App\Models\Ventas\CajaSesion;
use App\Models\Ventas\Cliente;
use App\Models\Inventario\Lote;
use App\Models\Configuracion; // <--- Importamos el modelo
use Exception;

class VentaService
{
    /**
     * Proceso principal de creación de venta
     */
    public function registrarVenta($user, array $data)
    {
        $items = json_decode($data['items'], true);

        // 1. Pre-Validación
        $this->validarStockPrevio($items);

        return DB::transaction(function () use ($user, $data, $items) {

            // 2. Obtener y Bloquear Caja
            $caja = $this->bloquearCaja($user, $data['caja_sesion_id']);

            // --- NUEVO: DETERMINAR SERIE SEGÚN COMPROBANTE ---
            $serie = '';
            switch ($data['tipo_comprobante']) {
                case 'BOLETA':
                    $serie = $caja->sucursal->serie_boleta;
                    break;
                case 'FACTURA':
                    $serie = $caja->sucursal->serie_factura;
                    break;
                case 'TICKET':
                    // Nota: Asegúrate de que en tu BD el campo se llame igual (serie_ticket o serie_tiket)
                    $serie = $caja->sucursal->serie_ticket ?? $caja->sucursal->serie_tiket;
                    break;
                default:
                    throw new Exception("Tipo de comprobante no válido.");
            }

            // Validar que la sucursal tenga configurada la serie
            if (empty($serie)) {
                throw new Exception("La sucursal no tiene configurada una serie para {$data['tipo_comprobante']}.");
            }
            // --------------------------------------------------

            // 3. Procesar Items
            $resultadoItems = $this->procesarItems($items, $caja);

            // 4. Calcular Totales
            $totales = $this->calcularTotalesFinales($resultadoItems, $data);

            // 5. Gestión de Puntos
            $this->procesarPuntosCliente($data['cliente_id'], $data, $totales['total_neto']);

            // 6. Crear Venta
            $venta = Venta::create([
                'caja_sesion_id'   => $caja->id,
                'sucursal_id'      => $caja->sucursal_id,
                'cliente_id'       => $data['cliente_id'],
                'user_id'          => $user->id,
                'tipo_comprobante' => $data['tipo_comprobante'],

                // --- AQUÍ USAMOS LA SERIE DINÁMICA ---
                'serie'            => $serie,
                'numero'           => $this->obtenerCorrelativo($serie, $data['tipo_comprobante']),
                // -------------------------------------

                'fecha_emision'    => now(),

                // Totales Tributarios
                'op_gravada'       => $totales['op_gravada'],
                'op_exonerada'     => $totales['op_exonerada'],
                'op_inafecta'      => 0,
                'total_igv'        => $totales['total_igv'],
                'porcentaje_igv'   => $caja->sucursal->impuesto_porcentaje,

                // Totales Finales
                'total_bruto'      => $totales['total_bruto'],
                'total_descuento'  => $totales['descuento_puntos'],
                'total_neto'       => $totales['total_neto'],

                'medio_pago'       => $data['medio_pago'],
                'estado'           => 'EMITIDA',
            ]);

            // 7. Guardar Detalles
            $venta->detalles()->saveMany($resultadoItems['detalles']);

            return $venta;
        });
    }

    private function validarStockPrevio(array $items)
    {
        foreach ($items as $item) {
            $loteInfo = DB::table('lotes')
                ->select('stock_actual', 'codigo_lote')
                ->where('id', $item['lote_id'])
                ->first();


            if (!$loteInfo || $loteInfo->stock_actual < $item['cantidad']) {
                $loteCodigo = $loteInfo->codigo_lote ?? '?';
                return back()->withErrors("Stock insuficiente para: {$item['nombre']} (Lote: $loteCodigo)")->withInput();
            }
        }
    }

    private function bloquearCaja($user, $cajaSesionId)
    {
        return CajaSesion::where('id', $cajaSesionId)
            ->where('user_id', $user->id)
            ->where('estado', 'ABIERTO')
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function procesarItems(array $items, $caja)
    {
        $detalles = [];
        $acumuladores = [
            'bruto'        => 0,
            'op_gravada'   => 0,
            'op_exonerada' => 0,
            'igv'          => 0
        ];

        foreach ($items as $item) {
            $cantidad = (int)$item['cantidad'];

            $lote = Lote::with('medicamento')
                ->where('id', $item['lote_id'])
                ->where('sucursal_id', $caja->sucursal_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lote->stock_actual < $cantidad) {
                throw new Exception("Stock insuficiente: {$item['nombre']}");
            }

            $lote->decrement('stock_actual', $cantidad);

            $precioVenta = (float)$item['precio_venta'];
            $subtotal    = $cantidad * $precioVenta;

            $esExoneradoProd = !$lote->medicamento->afecto_igv;
            $esExoneradoSuc  = $caja->sucursal->impuesto_porcentaje == 0;

            if ($esExoneradoProd || $esExoneradoSuc) {
                $valorUnitario    = $precioVenta;
                $igvUnitario      = 0;
                $tipoAfectacion   = '20';
                $acumuladores['op_exonerada'] += $subtotal;
            } else {
                $valorUnitario    = $precioVenta / 1.18;
                $igvUnitario      = $precioVenta - $valorUnitario;
                $tipoAfectacion   = '10';
                $acumuladores['op_gravada'] += ($valorUnitario * $cantidad);
                $acumuladores['igv']        += ($igvUnitario * $cantidad);
            }

            $acumuladores['bruto'] += $subtotal;

            $detalles[] = new DetalleVenta([
                'lote_id'         => $lote->id,
                'medicamento_id'  => $lote->medicamento_id,
                'cantidad'        => $cantidad,
                'precio_unitario' => $precioVenta,
                'valor_unitario'  => $valorUnitario,
                'igv'             => $igvUnitario,
                'tipo_afectacion' => $tipoAfectacion,
                'subtotal_bruto'  => $subtotal,
                'subtotal_neto'   => $subtotal,
            ]);
        }

        return ['detalles' => $detalles, 'acumuladores' => $acumuladores];
    }

    private function calcularTotalesFinales($resultadoItems, $data)
    {
        $acum = $resultadoItems['acumuladores'];
        $descuentoPuntos = isset($data['descuento_puntos']) ? (float)$data['descuento_puntos'] : 0;

        return [
            'op_gravada'       => $acum['op_gravada'],
            'op_exonerada'     => $acum['op_exonerada'],
            'total_igv'        => $acum['igv'],
            'total_bruto'      => $acum['bruto'],
            'descuento_puntos' => $descuentoPuntos,
            'total_neto'       => $acum['bruto'] - $descuentoPuntos
        ];
    }

    /**
     * Sub-proceso: Maneja Puntos (GASTAR y GANAR) de forma dinámica
     */
    private function procesarPuntosCliente($clienteId, $data, $totalPagado)
    {
        // 1. Validar Cliente
        if (!$clienteId || $clienteId <= 1) return;
        $cliente = Cliente::find($clienteId);
        if (!$cliente) return;

        // 2. GASTAR PUNTOS (Redimir)
        if (!empty($data['puntos_usados']) && $data['puntos_usados'] > 0) {
            if ($cliente->puntos >= $data['puntos_usados']) {
                $cliente->decrement('puntos', $data['puntos_usados']);
            } else {
                throw new Exception("El cliente no tiene suficientes puntos para el descuento.");
            }
        }

        if ($totalPagado > 0) {

            $config = Configuracion::first();
            $puntosPorMoneda = $config ? $config->puntos_por_moneda : 1;

            if ($puntosPorMoneda > 0) {
                $puntosGanados = floor($totalPagado * $puntosPorMoneda);

                if ($puntosGanados > 0) {
                    $cliente->increment('puntos', $puntosGanados);
                }
            }
        }
    }

    private function obtenerCorrelativo($serie, $tipoComprobante)
    {
        // Buscamos la última venta que tenga ESTA serie y ESTE tipo
        // Usamos lockForUpdate para evitar duplicados si dos venden a la vez
        $ultimoNumero = Venta::where('tipo_comprobante', $tipoComprobante)
            ->where('serie', $serie)
            ->lockForUpdate()
            ->max('numero');

        return $ultimoNumero ? ($ultimoNumero + 1) : 1;
    }
}
