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

    /**
     * Genera el siguiente número de comprobante de forma ATÓMICA.
     * Usa INSERT ... ON DUPLICATE KEY UPDATE con LAST_INSERT_ID()
     * para evitar gap locks en la tabla ventas.
     */
    private function siguienteNumeroComprobante(int $sucursalId, string $tipoComp, string $serie): int
    {
        // INSERT ... ON DUPLICATE KEY UPDATE es atómico en MySQL/InnoDB
        // sin necesidad de transacción explícita ni gap locks
        DB::statement("
            INSERT INTO secuencias_ventas (sucursal_id, tipo_comprobante, serie, ultimo_numero, created_at, updated_at)
            VALUES (?, ?, ?, LAST_INSERT_ID(1), NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                ultimo_numero = LAST_INSERT_ID(ultimo_numero + 1),
                updated_at = NOW()
        ", [$sucursalId, $tipoComp, $serie]);

        return (int) DB::getPdo()->lastInsertId();
    }

    public function registrarVenta(User $user, array $data): Venta
    {
        // ============================================================
        // FASE 1: CARGAS Y CÁLCULOS (fuera de transacción, solo lectura)
        // ============================================================

        // 1. CARGAR CAJA Y SUCURSAL
        $caja = CajaSesion::with('sucursal')
            ->where('id', $data['caja_sesion_id'])
            ->where('estado', 'ABIERTO')
            ->first();

        if (!$caja) throw new Exception("La caja cerrada o no existe.");

        $sucursal = $caja->sucursal;
        $esZonaAmazonia = ($sucursal->impuesto_porcentaje == 0);

        // 2. PARSEAR ITEMS
        $items = json_decode($data['items'], true);
        if (!is_array($items) || empty($items)) {
            throw new Exception("El carrito está vacío o es inválido.");
        }

        // 3. BATCH LOAD: todos los medicamentos en UNA sola query
        $medicamentoIds = [];
        $loteIds = [];
        foreach ($items as $item) {
            $mid = $item['medicamento_id'] ?? null;
            $lid = $item['id'] ?? $item['lote_id'] ?? null;
            if ($mid) $medicamentoIds[] = $mid;
            if ($lid) $loteIds[] = $lid;
        }
        $medicamentoIds = array_unique($medicamentoIds);
        $loteIds = array_unique($loteIds);

        $medicamentos = Medicamento::whereIn('id', $medicamentoIds)->get()->keyBy('id');
        $lotesPreload = Lote::with('medicamento')->whereIn('id', $loteIds)->get()->keyBy('id');

        // 4. PROCESAR ITEMS (cálculos, SIN queries adicionales)
        $itemsProcesados = [];
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

            // Obtener medicamento del batch pre-cargado
            $medicamentoId = $item['medicamento_id'] ?? null;
            $medicamento = null;

            if ($medicamentoId && isset($medicamentos[$medicamentoId])) {
                $medicamento = $medicamentos[$medicamentoId];
            } else {
                // Fallback: buscar desde el lote pre-cargado
                $lotePre = $lotesPreload[$loteId] ?? null;
                if ($lotePre && $lotePre->medicamento) {
                    $medicamento = $lotePre->medicamento;
                    $medicamentoId = $medicamento->id;
                }
            }

            if (!$medicamento) throw new Exception("Error Item #" . ($index + 1) . ": no se encontró el medicamento.");

            // FACTOR Y PRECIOS REALES
            $factor = 1;
            if ($unidadMedida === 'CAJA') $factor = (int)($medicamento->unidades_por_envase ?? 0);
            elseif ($unidadMedida === 'BLISTER') $factor = (int)($medicamento->unidades_por_blister ?? 0);

            if ($factor < 1) throw new Exception("Factor no configurado para {$unidadMedida} en {$medicamento->nombre}");

            $cantidadUnidades = $cantidadPresentacion * $factor;
            $precioUnit = round($precioPresentacion / $factor, 4);
            $subtotalItem = round($precioUnit * $cantidadUnidades, 2);

            // --- CALCULO DE IMPUESTOS ---
            $esExonerado = $esZonaAmazonia || !$medicamento->afecto_igv;
            $valorUnitario = 0;
            $igvItemTotal = 0;
            $baseItemTotal = 0;
            $codigoAfectacion = '';

            if ($esExonerado) {
                $codigoAfectacion = '20';
                $valorUnitario = $precioUnit;
                $baseItemTotal = $subtotalItem;
                $igvItemTotal  = 0;
                $sumaOpExonerada += $baseItemTotal;
            } else {
                $codigoAfectacion = '10';
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
                'tipo_afectacion' => $codigoAfectacion,
            ];
        }

        // 5. DESCUENTOS Y TOTALES
        $descuentoDinero = isset($data['descuento_puntos']) ? (float)$data['descuento_puntos'] : 0;
        $puntosUsados    = isset($data['puntos_usados']) ? (int)$data['puntos_usados'] : 0;

        if ($descuentoDinero > $sumaTotalVenta) {
            $descuentoDinero = $sumaTotalVenta;
        }

        $totalNetoFinal = round($sumaTotalVenta - $descuentoDinero, 2);

        // 6. SERIE Y NÚMERO (ATÓMICO, sin gap locks)
        $tipoComp = $data['tipo_comprobante'];
        $serie = ($tipoComp == 'FACTURA' ? $sucursal->serie_factura : $sucursal->serie_boleta) ?: 'B001';
        $nuevoNumero = $this->siguienteNumeroComprobante($sucursal->id, $tipoComp, $serie);

        $montoRecibido = ($data['medio_pago'] === 'EFECTIVO' && !empty($data['paga_con']))
            ? (float)$data['paga_con']
            : $totalNetoFinal;

        // 7. CARGAR CONFIGURACIÓN FUERA DE TRANSACCIÓN (evita cache DB dentro del lock)
        $config = $this->getConfigCacheada();
        $ratioPuntos = $config->puntos_por_moneda ?? 1;

        // ============================================================
        // FASE 2: TRANSACCIÓN ATÓMICA (solo escrituras mínimas)
        // ============================================================
        $venta = DB::transaction(function () use (
            $user, $caja, $sucursal, $data, $itemsProcesados, $loteIds,
            $sumaOpGravada, $sumaOpExonerada, $sumaIgv, $sumaTotalVenta,
            $totalNetoFinal, $descuentoDinero, $puntosUsados, $ratioPuntos,
            $tipoComp, $serie, $nuevoNumero, $montoRecibido
        ) {
            // 1. CREAR VENTA (CABECERA)
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
                'total_bruto'      => round($sumaOpGravada + $sumaOpExonerada, 2),
                'total_descuento'  => round($descuentoDinero, 2),
                'total_neto'       => $totalNetoFinal,
                'op_gravada'       => round($sumaOpGravada, 2),
                'op_exonerada'     => round($sumaOpExonerada, 2),
                'op_inafecta'      => 0,
                'total_igv'        => round($sumaIgv, 2),
                'porcentaje_igv'   => $sucursal->impuesto_porcentaje,
            ]);

            // 2. BULK INSERT DETALLES (un solo INSERT para todos los items)
            $detallesData = [];
            foreach ($itemsProcesados as $item) {
                $detallesData[] = [
                    'venta_id'          => $venta->id,
                    'lote_id'           => $item['lote_id'],
                    'medicamento_id'    => $item['medicamento_id'],
                    'cantidad'          => $item['cantidad'],
                    'precio_unitario'   => $item['precio_unitario'],
                    'valor_unitario'    => $item['valor_unitario'],
                    'igv'               => $item['igv'],
                    'subtotal_neto'     => $item['subtotal_neto'],
                    'subtotal_bruto'    => $item['subtotal_bruto'],
                    'subtotal_descuento' => 0,
                    'tipo_afectacion'   => $item['tipo_afectacion'],
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }
            DB::table('detalle_ventas')->insert($detallesData);

            // 3. BATCH DECREMENT STOCK (un solo lockForUpdate para todos los lotes)
            $lotesBloqueados = Lote::whereIn('id', $loteIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($itemsProcesados as $item) {
                $lote = $lotesBloqueados[$item['lote_id']] ?? null;
                if (!$lote) throw new Exception("Lote ID {$item['lote_id']} no encontrado.");

                if ($lote->stock_actual >= $item['cantidad']) {
                    $lote->decrement('stock_actual', $item['cantidad']);
                } else {
                    $lote->update(['stock_actual' => 0]);
                }
            }

            // 4. ACTUALIZAR PUNTOS DEL CLIENTE
            if ($data['cliente_id'] && $data['cliente_id'] != 1) {
                $cliente = Cliente::find($data['cliente_id']);
                if ($cliente) {
                    if ($puntosUsados > 0) {
                        $cliente->decrement('puntos', min($cliente->puntos, $puntosUsados));
                    }
                    $puntosGanados = intval($totalNetoFinal * $ratioPuntos);
                    if ($puntosGanados > 0) {
                        $cliente->increment('puntos', $puntosGanados);
                    }
                }
            }

            return $venta;
        });

        // ============================================================
        // FASE 3: ENCOLAR SUNAT (después del commit, fuera de transacción)
        // ============================================================
        if ($tipoComp == 'BOLETA' || $tipoComp == 'FACTURA') {
            \App\Jobs\ProcesarVentaSunat::dispatch($venta);
        }

        return $venta;
    }

    /**
     * Anular una venta: restaura stock, crea nota de crédito, encola anulación SUNAT.
     */
    public function anularVenta(User $user, Venta $venta, string $motivo): NotaCredito
    {
        // Cargar detalles ANTES de la transacción
        $venta->load('detalles', 'sucursal');

        $sucursal = $venta->sucursal;

        // Determinar serie de NC
        if ($venta->tipo_comprobante === 'FACTURA') {
            $serieNota = $sucursal->serie_nc_factura;
        } else {
            $serieNota = $sucursal->serie_nc_boleta;
        }

        if (empty($serieNota)) {
            throw new Exception("La sucursal no tiene configurada una serie para Notas de Crédito.");
        }

        // Generar número de NC de forma atómica (sin gap locks)
        $nuevoNumero = $this->siguienteNumeroComprobante($sucursal->id, 'NC_' . $venta->tipo_comprobante, $serieNota);

        // Transacción mínima para la anulación
        $notaCredito = DB::transaction(function () use ($user, $venta, $motivo, $sucursal, $serieNota, $nuevoNumero) {
            // 1. Restaurar stock (batch)
            $loteIds = $venta->detalles->pluck('lote_id')->unique()->toArray();
            $lotes = Lote::whereIn('id', $loteIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($venta->detalles as $detalle) {
                $lote = $lotes[$detalle->lote_id] ?? null;
                if ($lote) {
                    $lote->increment('stock_actual', $detalle->cantidad);
                }
            }

            // 2. Crear Nota de Crédito
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

            // 3. Marcar venta como ANULADO
            $venta->estado = 'ANULADO';
            $venta->save();

            return $notaCredito;
        });

        // Encolar SUNAT después del commit
        \App\Jobs\ProcesarAnulacionSunat::dispatch($notaCredito, $venta);

        return $notaCredito;
    }

    /**
     * Obtiene la configuración cacheada para evitar consultas repetitivas.
     */
    private function getConfigCacheada(): Configuracion
    {
        return cache()->remember('configuracion_global', 1440, function () {
            return Configuracion::first() ?? new Configuracion();
        });
    }
}
