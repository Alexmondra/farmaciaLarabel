<?php

namespace App\Services;

use App\Models\Ventas\Venta;
use App\Models\Ventas\DetalleVenta;
use App\Models\Ventas\CajaSesion;
use App\Models\Ventas\Cliente;
use App\Models\Configuracion;
use App\Models\Inventario\Lote;
use App\Models\User;
use App\Services\Sunat\SunatService;
use Illuminate\Support\Facades\DB;
use Exception;

class VentaService
{
    protected $sunatService;

    public function __construct(SunatService $sunatService)
    {
        $this->sunatService = $sunatService;
    }

    public function registrarVenta(User $user, array $data): Venta
    {
        return DB::transaction(function () use ($user, $data) {

            // ---------------------------------------------------
            // 1. CARGAR CAJA Y SUCURSAL
            // ---------------------------------------------------
            $caja = CajaSesion::with('sucursal')
                ->where('id', $data['caja_sesion_id'])
                ->where('estado', 'ABIERTO')
                ->first();

            if (!$caja) throw new Exception("La caja seleccionada está cerrada o no existe.");
            if (!$caja->sucursal) throw new Exception("La caja abierta no tiene sucursal asignada.");

            $sucursal = $caja->sucursal;

            // ---------------------------------------------------
            // 2. PROCESAR ITEMS
            // ---------------------------------------------------
            $items = json_decode($data['items'], true);
            $itemsProcesados = [];

            // Variable 1: Suma total de los productos (Precio Regular)
            $sumaPrecioVentaTotal = 0;

            foreach ($items as $index => $item) {
                $loteId = $item['id'] ?? $item['lote_id'] ?? null;
                if (!$loteId) throw new Exception("Error en Item #" . ($index + 1) . ": Falta ID del producto.");

                $precioVenta = (float) ($item['precio_venta'] ?? $item['precio_unitario'] ?? 0);
                $cantidad = (int) $item['cantidad'];

                // Cálculos base por item

                // Obtenemos el factor (Ej: 1.18 o 1.00)
                $factor = 1 + ($sucursal->impuesto_porcentaje / 100);
                // Cálculos
                $valorUnitario = $precioVenta / $factor;
                $igvUnitario   = $precioVenta - $valorUnitario;

                $subtotalNeto = $precioVenta * $cantidad;
                $subtotalBruto = $valorUnitario * $cantidad;
                $igvTotal = $igvUnitario * $cantidad;

                $sumaPrecioVentaTotal += $subtotalNeto;

                $itemsProcesados[] = [
                    'lote_id' => $loteId,
                    'medicamento_id' => $item['medicamento_id'] ?? null,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioVenta,
                    'valor_unitario' => $valorUnitario,
                    'igv' => $igvTotal,
                    'subtotal_neto' => $subtotalNeto,
                    'subtotal_bruto' => $subtotalBruto
                ];
            }

            // ---------------------------------------------------
            // 3. APLICAR DESCUENTOS POR PUNTOS
            // ---------------------------------------------------
            $descuentoDinero = isset($data['descuento_puntos']) ? (float)$data['descuento_puntos'] : 0;
            $puntosUsados = isset($data['puntos_usados']) ? (int)$data['puntos_usados'] : 0;

            // Seguridad: El descuento no puede ser mayor al total
            if ($descuentoDinero > $sumaPrecioVentaTotal) {
                $descuentoDinero = $sumaPrecioVentaTotal;
            }

            // Variable 2: Total que realmente paga el cliente (Variable UNIFICADA)
            $totalPagarCliente = $sumaPrecioVentaTotal - $descuentoDinero;
            // Recálculo de Bases para SUNAT (Proporcional)
            $opGravadaFinal = $totalPagarCliente / 1.18;
            $totalIgvFinal = $totalPagarCliente - $opGravadaFinal;


            // ---------------------------------------------------
            // 4. GENERAR SERIE Y NUMERO
            // ---------------------------------------------------
            $tipoComp = $data['tipo_comprobante'];
            $serie = ($tipoComp == 'FACTURA' ? $sucursal->serie_factura : $sucursal->serie_boleta)
                ?: ($tipoComp == 'FACTURA' ? 'F001' : 'B001');

            $ultimoCorrelativo = Venta::where('tipo_comprobante', $tipoComp)
                ->where('serie', $serie)
                ->max('numero');
            $nuevoNumero = $ultimoCorrelativo ? ($ultimoCorrelativo + 1) : 1;

            // ---------------------------------------------------
            // 5. CREAR VENTA (GUARDANDO LOS DATOS CORRECTOS)
            // ---------------------------------------------------
            $venta = Venta::create([
                'caja_sesion_id'   => $caja->id,
                'sucursal_id'      => $sucursal->id,
                'cliente_id'       => $data['cliente_id'],
                'user_id'          => $user->id,
                'tipo_comprobante' => $tipoComp,
                'serie'            => $serie,
                'numero'           => $nuevoNumero,
                'fecha_emision'    => now(),
                'medio_pago'       => $data['medio_pago'],
                'referencia_pago'  => $data['referencia_pago'] ?? null,
                'estado'           => 'EMITIDA',

                // Totales
                'total_bruto'     => round($opGravadaFinal, 2),
                'total_descuento' => round($descuentoDinero, 2), // ¡Aquí usamos $descuentoDinero!
                'total_neto'      => round($totalPagarCliente, 2), // ¡Aquí usamos $totalPagarCliente!

                // Impuestos
                'op_gravada'   => ($sucursal->impuesto_porcentaje > 0) ? $opGravadaFinal : 0,
                'op_exonerada' => ($sucursal->impuesto_porcentaje == 0) ? $totalPagarCliente : 0, // Todo va aquí
                'op_inafecta'     => 0,
                'total_igv'    => ($sucursal->impuesto_porcentaje > 0) ? $totalIgvFinal : 0,
                'porcentaje_igv' => $sucursal->impuesto_porcentaje,
            ]);

            // ---------------------------------------------------
            // 6. GUARDAR DETALLES
            // ---------------------------------------------------
            foreach ($itemsProcesados as $item) {
                $lote = Lote::lockForUpdate()->find($item['lote_id']);

                if (!$lote || $lote->stock_actual < $item['cantidad']) {
                    throw new Exception("Stock insuficiente para el lote: " . $item['lote_id']);
                }

                $lote->decrement('stock_actual', $item['cantidad']);

                DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'lote_id'         => $item['lote_id'],
                    'medicamento_id'  => $item['medicamento_id'] ?? $lote->medicamento_id,
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'valor_unitario'  => $item['valor_unitario'],
                    'igv'             => $item['igv'],
                    'subtotal_neto'   => $item['subtotal_neto'],
                    'subtotal_bruto'  => $item['subtotal_bruto'],
                    'subtotal_descuento' => 0
                ]);
            }

            // ---------------------------------------------------
            // 7. ACTUALIZAR PUNTOS DEL CLIENTE
            // ---------------------------------------------------
            if ($data['cliente_id']) {
                $cliente = Cliente::find($data['cliente_id']);
                if ($cliente) {

                    // A. RESTAR PUNTOS (Si usó descuento)
                    if ($puntosUsados > 0) {
                        if ($cliente->puntos >= $puntosUsados) {
                            $cliente->decrement('puntos', $puntosUsados);
                        } else {
                            $cliente->update(['puntos' => 0]);
                        }
                    }

                    // B. SUMAR PUNTOS (Por la nueva compra)
                    $config = Configuracion::first();
                    $ratio = $config->puntos_por_moneda ?? 1;

                    // Usamos la misma variable $totalPagarCliente del paso 3
                    $puntosGanados = intval($totalPagarCliente * $ratio);

                    if ($puntosGanados > 0) {
                        $cliente->increment('puntos', $puntosGanados);
                    }
                }
            }

            // ---------------------------------------------------
            // 8. ENVIAR A SUNAT
            // ---------------------------------------------------
            if ($tipoComp == 'BOLETA' || $tipoComp == 'FACTURA') {
                $this->sunatService->transmitirAComprobante($venta);
            }

            return $venta;
        });
    }
}
