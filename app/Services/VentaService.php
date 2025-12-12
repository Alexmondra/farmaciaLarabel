<?php

namespace App\Services;

use App\Models\Ventas\Venta;
use App\Models\Ventas\DetalleVenta;
use App\Models\Ventas\CajaSesion;
use App\Models\Ventas\Cliente;
use App\Models\Ventas\NotaCredito;
use App\Models\Configuracion;
use App\Models\Inventario\Lote;
use App\Models\Inventario\Medicamento; // Agregado
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

    #borrar para inicar una dato
    public function registrarVenta(User $user, array $data): Venta
    {
        return DB::transaction(function () use ($user, $data) {

            // 1. CARGAR CAJA Y SUCURSAL
            $caja = CajaSesion::with('sucursal')
                ->where('id', $data['caja_sesion_id'])
                ->where('estado', 'ABIERTO')
                ->first();

            if (!$caja) throw new Exception("La caja cerrada o no existe.");

            $sucursal = $caja->sucursal;
            $esZonaAmazonia = ($sucursal->impuesto_porcentaje == 0);

            // 2. PROCESAR ITEMS
            $items = json_decode($data['items'], true);
            $itemsProcesados = [];

            $rawOpGravada = 0;
            $rawOpExonerada = 0;
            $rawIgv = 0;
            $rawTotalVenta = 0;

            foreach ($items as $index => $item) {
                $loteId = $item['id'] ?? $item['lote_id'] ?? null;
                if (!$loteId) throw new Exception("Error Item #" . ($index + 1));

                $precioVenta = (float) ($item['precio_venta'] ?? $item['precio_unitario'] ?? 0);
                $cantidad = (int) $item['cantidad'];
                $subtotalItem = $precioVenta * $cantidad;

                // --- BUSCAR MEDICAMENTO (PLAN A + PLAN B) ---
                $medicamentoId = $item['medicamento_id'] ?? null;
                $medicamento = null;

                if ($medicamentoId) {
                    $medicamento = Medicamento::find($medicamentoId);
                } elseif ($loteId) {
                    $loteTemp = Lote::with('medicamento')->find($loteId);
                    if ($loteTemp) {
                        $medicamento = $loteTemp->medicamento;
                        $medicamentoId = $medicamento->id;
                    }
                }

                // --- LÓGICA DE IMPUESTOS ---
                $esExonerado = $esZonaAmazonia || ($medicamento && !$medicamento->afecto_igv);

                if ($esExonerado) {
                    // Exonerado (Código 20)
                    $valorUnitario = $precioVenta;
                    $igvItemTotal = 0;
                    $codigoAfectacion = '20';

                    $rawOpExonerada += $subtotalItem;
                } else {
                    // Gravado (Código 10)
                    $valorUnitario = $precioVenta / 1.18;
                    $igvItemTotal = ($precioVenta - $valorUnitario) * $cantidad;
                    $codigoAfectacion = '10';

                    $rawOpGravada += ($valorUnitario * $cantidad);
                    $rawIgv += $igvItemTotal;
                }

                $rawTotalVenta += $subtotalItem;

                $itemsProcesados[] = [
                    'lote_id' => $loteId,
                    'medicamento_id' => $medicamentoId,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioVenta,
                    'valor_unitario' => $valorUnitario,
                    'igv' => $igvItemTotal,
                    'subtotal_neto' => $subtotalItem,
                    'subtotal_bruto' => $valorUnitario * $cantidad, // <--- CORREGIDO AQUÍ
                    'tipo_afectacion' => $codigoAfectacion
                ];
            }
            // 3. DESCUENTOS
            $descuentoDinero = isset($data['descuento_puntos']) ? (float)$data['descuento_puntos'] : 0;
            $puntosUsados = isset($data['puntos_usados']) ? (int)$data['puntos_usados'] : 0;

            if ($descuentoDinero > $rawTotalVenta) $descuentoDinero = $rawTotalVenta;

            $factorAjuste = ($rawTotalVenta > 0) ? (1 - ($descuentoDinero / $rawTotalVenta)) : 1;

            $finalOpGravada = $rawOpGravada * $factorAjuste;
            $finalOpExonerada = $rawOpExonerada * $factorAjuste;
            $finalIgv = $rawIgv * $factorAjuste;
            $finalTotalNeto = $rawTotalVenta - $descuentoDinero;

            // 4. SERIE Y NUMERO
            $tipoComp = $data['tipo_comprobante'];
            $serie = ($tipoComp == 'FACTURA' ? $sucursal->serie_factura : $sucursal->serie_boleta) ?: 'B001';

            $ultimoCorrelativo = Venta::where('tipo_comprobante', $tipoComp)->where('serie', $serie)->max('numero');
            $nuevoNumero = $ultimoCorrelativo ? ($ultimoCorrelativo + 1) : 1;


            $montoRecibido = 0;
            if ($data['medio_pago'] === 'EFECTIVO') {
                $montoRecibido = !empty($data['paga_con']) ? (float)$data['paga_con'] : $finalTotalNeto;
            } else {
                $montoRecibido = $finalTotalNeto;
            }
            // 5. CREAR VENTA
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
                'monto_recibido'   => round($montoRecibido, 2),
                'referencia_pago'  => $data['referencia_pago'] ?? null,
                'estado'           => 'EMITIDA',
                'total_bruto'     => round($finalOpGravada + $finalOpExonerada, 2),
                'total_descuento' => round($descuentoDinero, 2),
                'total_neto'      => round($finalTotalNeto, 2),
                'op_gravada'   => round($finalOpGravada, 2),
                'op_exonerada' => round($finalOpExonerada, 2),
                'op_inafecta'  => 0,
                'total_igv'    => round($finalIgv, 2),
                'porcentaje_igv' => $sucursal->impuesto_porcentaje,
            ]);

            // 6. GUARDAR DETALLES
            foreach ($itemsProcesados as $item) {
                $lote = Lote::lockForUpdate()->find($item['lote_id']);
                if (!$lote || $lote->stock_actual < $item['cantidad']) throw new Exception("Stock insuficiente");

                $lote->decrement('stock_actual', $item['cantidad']);

                DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'lote_id'         => $item['lote_id'],
                    'medicamento_id'  => $item['medicamento_id'] ?? $lote->medicamento_id,
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'valor_unitario'  => $item['valor_unitario'],
                    'igv'             => $item['igv'] * $factorAjuste,
                    'subtotal_neto'   => $item['subtotal_neto'],
                    'subtotal_bruto'  => $item['valor_unitario'] * $item['cantidad'],
                    'subtotal_descuento' => 0,
                    // AQUÍ ESTÁ LA CORRECCIÓN VISUAL:
                    'tipo_afectacion' => $item['tipo_afectacion']
                ]);
            }

            // 7. PUNTOS
            if ($data['cliente_id']) {
                $cliente = Cliente::find($data['cliente_id']);
                if ($cliente) {
                    if ($puntosUsados > 0) $cliente->decrement('puntos', min($cliente->puntos, $puntosUsados));

                    $config = Configuracion::first();
                    $ratio = $config->puntos_por_moneda ?? 1;
                    $puntosGanados = intval($finalTotalNeto * $ratio);
                    if ($puntosGanados > 0) $cliente->increment('puntos', $puntosGanados);
                }
            }

            // 8. SUNAT
            if ($tipoComp == 'BOLETA' || $tipoComp == 'FACTURA') {
                $this->sunatService->transmitirAComprobante($venta);
            }

            return $venta;
        });
    }

    public function anularVenta(User $user, Venta $venta, string $motivo): NotaCredito
    {
        return DB::transaction(function () use ($user, $venta, $motivo) {

            // A. DEVOLVER STOCK (Reversión de Inventario)
            foreach ($venta->detalles as $detalle) {
                // Buscamos el lote original
                $lote = Lote::find($detalle->lote_id);
                if ($lote) {
                    $lote->increment('stock_actual', $detalle->cantidad);
                }
            }

            // B. PREPARAR DATOS DE LA NOTA
            $sucursal = $venta->sucursal;

            // 1. Definir Serie (Según si anulamos Boleta o Factura)
            // Asumimos que ya agregaste las columnas a tu tabla sucursales como acordamos
            if ($venta->tipo_comprobante === 'FACTURA') {
                $serieNota = $sucursal->serie_nc_factura; // Ej: FC01
                $tipoDocAfectado = '01';
            } else {
                $serieNota = $sucursal->serie_nc_boleta;  // Ej: BC01
                $tipoDocAfectado = '03';
            }

            if (empty($serieNota)) {
                throw new Exception("La sucursal no tiene configurada una serie para Notas de Crédito.");
            }

            // 2. Calcular Correlativo (Buscamos el último de ESTA serie y sumamos 1)
            $ultimoCorrelativo = NotaCredito::where('serie', $serieNota)
                ->where('sucursal_id', $sucursal->id) // Opcional si la serie es única globalmente, pero más seguro así
                ->max('numero');

            $nuevoNumero = $ultimoCorrelativo ? ($ultimoCorrelativo + 1) : 1;

            // C. CREAR EL REGISTRO EN BD
            $notaCredito = NotaCredito::create([
                'venta_id'           => $venta->id,
                'sucursal_id'        => $sucursal->id,
                'serie'              => $serieNota,
                'numero'             => $nuevoNumero,
                'fecha_emision'      => now(),
                'tipo_nota'          => '07', // Crédito
                'cod_motivo'         => '01', // Anulación de la operación
                'descripcion_motivo' => $motivo,
                'sunat_exito'        => false,
            ]);

            // D. ACTUALIZAR VENTA ORIGINAL
            $venta->estado = 'ANULADO';
            $venta->save();

            // E. ENVIAR A SUNAT
            $this->sunatService->transmitirNotaCredito($notaCredito, $venta);

            return $notaCredito;
        });
    }
}
