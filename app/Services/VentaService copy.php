<?php

namespace App\Services;

use App\Models\Ventas\Venta;
use App\Models\Ventas\DetalleVenta;
use App\Models\Ventas\CajaSesion;
use App\Models\Ventas\Cliente;
use App\Models\Ventas\NotaCredito;
use App\Models\Configuracion;
use App\Models\Inventario\Lote;
use App\Models\Inventario\Medicamento;
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

            // 1. CARGAR CAJA Y SUCURSAL
            $caja = CajaSesion::with('sucursal')
                ->where('id', $data['caja_sesion_id'])
                ->where('estado', 'ABIERTO')
                ->first();

            if (!$caja) throw new Exception("La caja cerrada o no existe.");

            $sucursal = $caja->sucursal;
            $esZonaAmazonia = ($sucursal->impuesto_porcentaje == 0);

            // ---------------------------------------------------------
            // CORRECCIÓN: LÓGICA DE CLIENTE
            // Solo ponemos el ID 1 si el dato viene vacío o nulo.
            // Si viene un ID (ej: 50), respetamos ese ID.
            // ---------------------------------------------------------
            $clienteId = !empty($data['cliente_id']) ? $data['cliente_id'] : 1;

            // 2. PROCESAR ITEMS
            $items = json_decode($data['items'], true);
            if (!is_array($items) || empty($items)) {
                throw new Exception("El carrito está vacío o es inválido.");
            }

            $itemsProcesados = [];

            // Acumuladores
            $sumaOpGravada   = 0;
            $sumaOpExonerada = 0;
            $sumaIgv         = 0;
            $sumaTotalVenta  = 0;

            foreach ($items as $index => $item) {
                $loteId = $item['id'] ?? $item['lote_id'] ?? null;
                if (!$loteId) throw new Exception("Error Item #" . ($index + 1) . ": lote_id no encontrado.");

                $cantidadPresentacion = (int)($item['cantidad'] ?? 0);
                if ($cantidadPresentacion <= 0) throw new Exception("Error Item #" . ($index + 1) . ": cantidad inválida.");

                $unidadMedida = strtoupper($item['unidad_medida'] ?? 'UNIDAD');
                $precioPresentacion = (float)($item['precio_venta'] ?? $item['precio_unitario'] ?? 0);

                // Buscar Medicamento
                $medicamentoId = $item['medicamento_id'] ?? null;
                $medicamento = null;
                if ($medicamentoId) {
                    $medicamento = Medicamento::find($medicamentoId);
                } else {
                    $loteTemp = Lote::with('medicamento')->find($loteId);
                    if ($loteTemp) {
                        $medicamento = $loteTemp->medicamento;
                        $medicamentoId = $medicamento?->id;
                    }
                }

                if (!$medicamento) throw new Exception("Error Item #" . ($index + 1) . ": no se encontró el medicamento.");

                // FACTOR Y PRECIOS
                $factor = 1;
                if ($unidadMedida === 'CAJA') $factor = (int)($medicamento->unidades_por_envase ?? 0);
                elseif ($unidadMedida === 'BLISTER') $factor = (int)($medicamento->unidades_por_blister ?? 0);

                if ($factor < 1) throw new Exception("Factor no configurado para {$unidadMedida} en {$medicamento->nombre}");

                $cantidadUnidades = $cantidadPresentacion * $factor;
                $precioUnit = round($precioPresentacion / $factor, 4);
                $subtotalItem = round($precioUnit * $cantidadUnidades, 2);

                // IMPUESTOS
                $esExonerado = $esZonaAmazonia || !$medicamento->afecto_igv;
                $valorUnitario = 0;
                $igvItemTotal = 0;
                $baseItemTotal = 0;
                $codigoAfectacion = '';

                if ($esExonerado) {
                    $codigoAfectacion = '20'; // Exonerado
                    $valorUnitario = $precioUnit;
                    $baseItemTotal = $subtotalItem;
                    $igvItemTotal  = 0;
                    $sumaOpExonerada += $baseItemTotal;
                } else {
                    $codigoAfectacion = '10'; // Gravado
                    $baseItemTotal = round($subtotalItem / 1.18, 2);
                    $igvItemTotal  = round($subtotalItem - $baseItemTotal, 2);
                    $valorUnitario = round($baseItemTotal / $cantidadUnidades, 4);
                    $sumaOpGravada += $baseItemTotal;
                    $sumaIgv       += $igvItemTotal;
                }

                $sumaTotalVenta += $subtotalItem;

                $itemsProcesados[] = [
                    'lote_id'         => $loteId,
                    'medicamento_id'  => $medicamentoId,
                    'cantidad'        => $cantidadUnidades,
                    'precio_unitario' => $precioUnit,
                    'valor_unitario'  => $valorUnitario,
                    'igv'             => $igvItemTotal,
                    'subtotal_neto'   => $subtotalItem,
                    'subtotal_bruto'  => $baseItemTotal,
                    'tipo_afectacion' => $codigoAfectacion
                ];
            }

            // 3. DESCUENTOS Y TOTALES FINALES
            $descuentoDinero = isset($data['descuento_puntos']) ? (float)$data['descuento_puntos'] : 0;
            $puntosUsados    = isset($data['puntos_usados']) ? (int)$data['puntos_usados'] : 0;

            if ($descuentoDinero > $sumaTotalVenta) {
                $descuentoDinero = $sumaTotalVenta;
            }

            $totalNetoFinal = round($sumaTotalVenta - $descuentoDinero, 2);

            // 4. SERIE Y NUMERO
            $tipoComp = $data['tipo_comprobante'];
            $serie = ($tipoComp == 'FACTURA' ? $sucursal->serie_factura : $sucursal->serie_boleta) ?: 'B001';

            $ultimoCorrelativo = Venta::where('tipo_comprobante', $tipoComp)->where('serie', $serie)->max('numero');
            $nuevoNumero = $ultimoCorrelativo ? ($ultimoCorrelativo + 1) : 1;

            $montoRecibido = ($data['medio_pago'] === 'EFECTIVO' && !empty($data['paga_con']))
                ? (float)$data['paga_con']
                : $totalNetoFinal;

            // 5. CREAR VENTA (CABECERA)
            $venta = Venta::create([
                'caja_sesion_id'   => $caja->id,
                'sucursal_id'      => $sucursal->id,

                // AQUI USAMOS EL ID PROCESADO (NO EL DEL ARRAY DIRECTO)
                'cliente_id'       => $clienteId,

                'user_id'          => $user->id,
                'tipo_comprobante' => $tipoComp,
                'serie'            => $serie,
                'numero'           => $nuevoNumero,
                'fecha_emision'    => now(),
                'medio_pago'       => $data['medio_pago'],
                'monto_recibido'   => round($montoRecibido, 2),
                'referencia_pago'  => $data['referencia_pago'] ?? null,
                'estado'           => 'EMITIDA',
                'total_bruto'      => round($sumaOpGravada + $sumaOpExonerada, 2),
                'total_descuento'  => round($descuentoDinero, 2),
                'total_neto'       => $totalNetoFinal,
                'op_gravada'       => round($sumaOpGravada, 2),
                'op_exonerada'     => round($sumaOpExonerada, 2),
                'op_inafecta'      => 0,
                'total_igv'        => round($sumaIgv, 2),
                'porcentaje_igv'   => $sucursal->impuesto_porcentaje,
            ]);

            // 6. GUARDAR DETALLES
            foreach ($itemsProcesados as $item) {
                $lote = Lote::lockForUpdate()->find($item['lote_id']);

                if (!$lote) throw new Exception("Lote no encontrado.");
                if ($lote->stock_actual < $item['cantidad']) {
                    throw new Exception("Stock insuficiente en lote {$lote->id}.");
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
                    'subtotal_descuento' => 0,
                    'tipo_afectacion' => $item['tipo_afectacion'],
                ]);
            }

            // 7. PUNTOS (USAMOS $clienteId)
            // Solo procesamos puntos si el cliente NO es el genérico (1) y existe
            if ($clienteId && $clienteId != 1) {
                $cliente = Cliente::find($clienteId);

                if ($cliente) {
                    if ($puntosUsados > 0) {
                        $cliente->decrement('puntos', min($cliente->puntos, $puntosUsados));
                    }
                    $config = Configuracion::first();
                    $ratio = $config->puntos_por_moneda ?? 1;
                    $puntosGanados = intval($totalNetoFinal * $ratio);

                    if ($puntosGanados > 0) {
                        $cliente->increment('puntos', $puntosGanados);
                    }
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
        // (El código de anulación se mantiene igual, no lo toqué)
        return DB::transaction(function () use ($user, $venta, $motivo) {
            foreach ($venta->detalles as $detalle) {
                $lote = Lote::find($detalle->lote_id);
                if ($lote) {
                    $lote->increment('stock_actual', $detalle->cantidad);
                }
            }

            $sucursal = $venta->sucursal;
            if ($venta->tipo_comprobante === 'FACTURA') {
                $serieNota = $sucursal->serie_nc_factura;
            } else {
                $serieNota = $sucursal->serie_nc_boleta;
            }

            if (empty($serieNota)) throw new Exception("La sucursal no tiene configurada una serie para Notas de Crédito.");

            $ultimoCorrelativo = NotaCredito::where('serie', $serieNota)
                ->where('sucursal_id', $sucursal->id)
                ->max('numero');

            $nuevoNumero = $ultimoCorrelativo ? ($ultimoCorrelativo + 1) : 1;

            $notaCredito = NotaCredito::create([
                'venta_id'           => $venta->id,
                'sucursal_id'        => $sucursal->id,
                'serie'              => $serieNota,
                'numero'             => $nuevoNumero,
                'fecha_emision'      => now(),
                'tipo_nota'          => '07',
                'cod_motivo'         => '01',
                'descripcion_motivo' => $motivo,
                'sunat_exito'        => false,
            ]);

            $venta->estado = 'ANULADO';
            $venta->save();

            $this->sunatService->transmitirNotaCredito($notaCredito, $venta);

            return $notaCredito;
        });
    }
}
