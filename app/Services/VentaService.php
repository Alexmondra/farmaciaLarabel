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

            // 2. PROCESAR ITEMS
            $items = json_decode($data['items'], true);
            if (!is_array($items) || empty($items)) {
                throw new Exception("El carrito está vacío o es inválido.");
            }

            $itemsProcesados = [];

            // Acumuladores de VALORES REALES (Sin descontar nada aún)
            $sumaOpGravada   = 0;
            $sumaOpExonerada = 0;
            $sumaIgv         = 0;
            $sumaTotalVenta  = 0; // La suma de todos los "Totales" de linea

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

                // FACTOR Y PRECIOS REALES
                $factor = 1;
                if ($unidadMedida === 'CAJA') $factor = (int)($medicamento->unidades_por_envase ?? 0);
                elseif ($unidadMedida === 'BLISTER') $factor = (int)($medicamento->unidades_por_blister ?? 0);

                if ($factor < 1) throw new Exception("Factor no configurado para {$unidadMedida} en {$medicamento->nombre}");

                $cantidadUnidades = $cantidadPresentacion * $factor;

                // Precio Unitario Real (Por pastilla/unidad)
                $precioUnit = round($precioPresentacion / $factor, 4);

                // Subtotal de la línea (Precio x Cantidad)
                // AQUÍ ESTÁ EL CAMBIO: Calculamos el total PURO. 8 * 3.00 = 24.00
                $subtotalItem = round($precioUnit * $cantidadUnidades, 2);

                // --- CALCULO DE IMPUESTOS DIRECTO ---
                $esExonerado = $esZonaAmazonia || !$medicamento->afecto_igv;

                $valorUnitario = 0;
                $igvItemTotal = 0;
                $baseItemTotal = 0;
                $codigoAfectacion = '';

                if ($esExonerado) {
                    // CÓDIGO 20: EXONERADO
                    $codigoAfectacion = '20';
                    $valorUnitario = $precioUnit;

                    $baseItemTotal = $subtotalItem; // Todo es base
                    $igvItemTotal  = 0;

                    $sumaOpExonerada += $baseItemTotal;
                } else {
                    // CÓDIGO 10: GRAVADO
                    $codigoAfectacion = '10';
                    $baseItemTotal = round($subtotalItem / 1.18, 2);
                    $igvItemTotal  = round($subtotalItem - $baseItemTotal, 2);
                    $valorUnitario = round($baseItemTotal / $cantidadUnidades, 4);

                    $sumaOpGravada += $baseItemTotal;
                    $sumaIgv       += $igvItemTotal;
                }

                $sumaTotalVenta += $subtotalItem;

                // Guardamos los datos LIMPIOS para el detalle (SIN APLICAR DESCUENTO AQUÍ)
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
            // Aquí aplicamos el descuento SOLO al final
            $descuentoDinero = isset($data['descuento_puntos']) ? (float)$data['descuento_puntos'] : 0;
            $puntosUsados    = isset($data['puntos_usados']) ? (int)$data['puntos_usados'] : 0;

            if ($descuentoDinero > $sumaTotalVenta) {
                $descuentoDinero = $sumaTotalVenta;
            }

            // Total Neto = (Suma de los productos) - (Descuento Global)
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

                // TOTAL BRUTO: Suma de las Operaciones
                'total_bruto'      => round($sumaOpGravada + $sumaOpExonerada, 2),

                // TOTAL DESCUENTO: El valor global del descuento se guarda AQUÍ
                'total_descuento'  => round($descuentoDinero, 2),

                // TOTAL NETO: Lo que paga el cliente realmente
                'total_neto'       => $totalNetoFinal,

                // BASES IMPONIBLES (Sin tocar, sumas reales de los productos)
                'op_gravada'       => round($sumaOpGravada, 2),
                'op_exonerada'     => round($sumaOpExonerada, 2),
                'op_inafecta'      => 0,
                'total_igv'        => round($sumaIgv, 2),
                'porcentaje_igv'   => $sucursal->impuesto_porcentaje,
            ]);

            foreach ($itemsProcesados as $item) {
                $lote = Lote::lockForUpdate()->find($item['lote_id']);

                if (!$lote) throw new Exception("Lote no encontrado.");
                if ($lote->stock_actual >= $item['cantidad']) {
                    $lote->decrement('stock_actual', $item['cantidad']);
                } else {
                    $lote->update(['stock_actual' => 0]);
                }

                DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'lote_id'         => $item['lote_id'],
                    'medicamento_id'  => $item['medicamento_id'] ?? $lote->medicamento_id,

                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],

                    'valor_unitario'  => $item['valor_unitario'],
                    'igv'             => $item['igv'],

                    'subtotal_neto'      => $item['subtotal_neto'],
                    'subtotal_bruto'     => $item['subtotal_bruto'],
                    'subtotal_descuento' => 0, // 0 porque el descuento es GLOBAL en la cabecera
                    'tipo_afectacion'    => $item['tipo_afectacion'],
                ]);
            }

            // 7. PUNTOS
            if ($data['cliente_id']) {
                $cliente = Cliente::find($data['cliente_id']);

                // VERIFICACIÓN DE SEGURIDAD:
                if ($cliente && $cliente->id != 1) {
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
