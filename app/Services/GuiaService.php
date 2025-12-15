<?php

namespace App\Services;

use App\Models\Guias\GuiaRemision;
use App\Models\Guias\DetalleGuiaRemision;
use App\Models\Inventario\Lote;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\Sunat\SunatGuiaService;
use Exception;

class GuiaService
{
    protected $sunatService;

    // Mapa de descripciones para motivos de traslado
    private const MOTIVO_MAPA = [
        '01' => 'VENTA',
        '02' => 'COMPRA',
        '04' => 'TRASLADO INTERNO',
        '13' => 'OTROS'
    ];

    public function __construct(SunatGuiaService $sunatService)
    {
        $this->sunatService = $sunatService;
    }

    public function registrarGuia(User $user, Sucursal $sucursal, array $data): GuiaRemision
    {
        $guia = DB::transaction(function () use ($user, $sucursal, $data) {

            $estadoInicial = (strtotime($data['fecha_traslado']) <= strtotime(now()->format('Y-m-d'))) ? 'EN TRANSITO' : 'REGISTRADO';

            $descMotivo = $data['descripcion_motivo'] ?? (self::MOTIVO_MAPA[$data['motivo_traslado']] ?? 'TRASLADO');

            $esTrasladoInterno = $data['motivo_traslado'] === '04';

            $clienteId = $this->resolveClienteId($data);

            if (!$clienteId && !$esTrasladoInterno) {
                throw new Exception("Error: Falta seleccionar el Cliente destinatario (Motivo: {$data['motivo_traslado']}).");
            }
            $codigoEstablecimientoLlegada = $esTrasladoInterno
                ? ($data['codigo_establecimiento_llegada'] ?? null)
                : null; // Null si no es traslado interno.

            $guia = GuiaRemision::create([
                'sucursal_id'        => $sucursal->id,
                'user_id'            => $user->id,
                'venta_id'           => $data['venta_id'] ?? null,
                'cliente_id'         => $clienteId,
                'serie'              => $data['serie'],
                'numero'             => $data['numero'],
                'fecha_emision'      => now(),
                'fecha_traslado'     => $data['fecha_traslado'],
                'motivo_traslado'    => $data['motivo_traslado'],
                'descripcion_motivo' => strtoupper($descMotivo),
                'modalidad_traslado' => $data['modalidad_traslado'],
                'peso_bruto'         => $data['peso_bruto'],
                'numero_bultos'      => $data['numero_bultos'] ?? 1,

                // Direcciones
                'ubigeo_partida'    => $data['ubigeo_partida'] ?? $sucursal->ubigeo,
                'direccion_partida' => $data['direccion_partida'] ?? $sucursal->direccion,
                'codigo_establecimiento_partida' => $data['codigo_establecimiento_partida'] ?? $sucursal->codigo,
                'ubigeo_llegada'    => $data['ubigeo_llegada'],
                'direccion_llegada' => $data['direccion_llegada'],
                'codigo_establecimiento_llegada' => $codigoEstablecimientoLlegada, // Corregido para NULL en Venta

                // Transporte (Usando el Null Coalescing Operator para valores por defecto)
                'doc_chofer_tipo'   => $data['doc_chofer_tipo'] ?? '1',
                'doc_chofer_numero' => $data['doc_chofer_numero'] ?? null,
                'nombre_chofer'     => $data['nombre_chofer'] ?? null,
                'licencia_conducir' => $data['licencia_conducir'] ?? null,
                'placa_vehiculo'    => $data['placa_vehiculo'] ?? null,
                'doc_transportista_numero' => $data['doc_transportista_numero'] ?? null,
                'razon_social_transportista' => $data['razon_social_transportista'] ?? null,

                // Estado
                'estado_traslado' => $estadoInicial,
                'sunat_exito'     => false
            ]);

            // 5. REGISTRAR DETALLES Y DESCONTAR STOCK
            $this->registerDetailsAndDecrementStock($guia, $data);

            return $guia;
        });

        try {
            $this->sunatService->transmitirGuia($guia);
        } catch (\Throwable $e) {
            $guia->mensaje_sunat = "Error local: " . $e->getMessage();
            $guia->save();
        }

        return $guia;
    }

    private function resolveClienteId(array $data): ?int
    {
        $clienteId = $data['cliente_id'] ?? null;

        if (!$clienteId && !empty($data['venta_id'])) {
            $venta = \App\Models\Ventas\Venta::find($data['venta_id']);
            return $venta ? $venta->cliente_id : null;
        }

        return $clienteId;
    }

    private function registerDetailsAndDecrementStock(GuiaRemision $guia, array $data): void
    {
        $items = json_decode($data['items'], true);

        foreach ($items as $item) {
            DetalleGuiaRemision::create([
                'guia_remision_id' => $guia->id,
                'medicamento_id'   => $item['medicamento_id'] ?? null,
                'lote_id'          => $item['lote_id'] ?? null,
                'codigo_producto'  => $item['codigo'] ?? 'GEN',
                'descripcion'      => $item['descripcion'],
                'unidad_medida'    => 'NIU',
                'cantidad'         => $item['cantidad']
            ]);

            if (empty($data['venta_id'])) {
                $this->descontarStockLote($item);
            }
        }
    }

    /**
     * Descuenta el stock del lote de forma segura dentro de la transacción.
     */
    private function descontarStockLote(array $item): void
    {
        $loteId = $item['lote_id'] ?? null;
        if (!$loteId) {
            throw new Exception("Producto sin lote: " . ($item['descripcion'] ?? 'desconocido'));
        }

        // Uso de lockForUpdate() dentro de la transacción es CRÍTICO
        $lote = Lote::lockForUpdate()->find($loteId);

        if (!$lote || $lote->stock_actual < $item['cantidad']) {
            throw new Exception("Stock insuficiente para el lote ID: {$loteId} (Solicitado: {$item['cantidad']}).");
        }

        $lote->decrement('stock_actual', $item['cantidad']);
    }
}
