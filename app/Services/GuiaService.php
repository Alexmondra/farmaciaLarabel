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

    public function __construct(SunatGuiaService $sunatService)
    {
        $this->sunatService = $sunatService;
    }

    public function registrarGuia(User $user, Sucursal $sucursal, array $data): GuiaRemision
    {
        $guia = DB::transaction(function () use ($user, $sucursal, $data) {

            $hoy = now()->format('Y-m-d');
            $estadoInicial = ($data['fecha_traslado'] <= $hoy) ? 'EN TRANSITO' : 'REGISTRADO';

            // --- LÓGICA DE PROTECCIÓN DE CLIENTE ---
            $clienteId = $data['cliente_id'] ?? null;

            // Si falta cliente pero hay venta (aunque venta_id llegue nulo, lo intentamos buscar en items o request)
            if (!$clienteId && !empty($data['venta_id'])) {
                $venta = \App\Models\Ventas\Venta::find($data['venta_id']);
                if ($venta) $clienteId = $venta->cliente_id;
            }

            // Si llegamos aquí sin cliente y NO es traslado interno, ERROR CLARO
            if (!$clienteId && ($data['motivo_traslado'] ?? '') !== '04') {
                // Intento final desesperado: Cliente "VARIOS" (ajusta el ID 1 según tu BD)
                // $clienteId = 1; 
                // O mejor lanzamos la excepción:
                throw new Exception("Error: Falta seleccionar el Cliente destinatario.");
            }

            // Lógica Motivos
            $descMotivo = $data['descripcion_motivo'] ?? null;
            if (empty($descMotivo)) {
                $mapa = ['01' => 'VENTA', '02' => 'COMPRA', '04' => 'TRASLADO INTERNO', '13' => 'OTROS'];
                $descMotivo = $mapa[$data['motivo_traslado']] ?? 'TRASLADO';
            }

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
                'ubigeo_partida'    => $data['ubigeo_partida'] ?? $sucursal->ubigeo,
                'direccion_partida' => $data['direccion_partida'] ?? $sucursal->direccion,
                'ubigeo_llegada'    => $data['ubigeo_llegada'],
                'direccion_llegada' => $data['direccion_llegada'],
                'doc_chofer_tipo'   => $data['doc_chofer_tipo'] ?? '1',
                'doc_chofer_numero' => $data['doc_chofer_numero'] ?? null,
                'nombre_chofer'     => $data['nombre_chofer'] ?? null,
                'licencia_conducir' => $data['licencia_conducir'] ?? null,
                'placa_vehiculo'    => $data['placa_vehiculo'] ?? null,
                'doc_transportista_numero' => $data['doc_transportista_numero'] ?? null,
                'razon_social_transportista' => $data['razon_social_transportista'] ?? null,
                'estado_traslado' => $estadoInicial,
                'sunat_exito'     => false
            ]);

            $items = json_decode($data['items'], true);
            foreach ($items as $item) {
                DetalleGuiaRemision::create([
                    'guia_remision_id' => $guia->id,
                    'medicamento_id'   => $item['medicamento_id'] ?? null,
                    'codigo_producto'  => $item['codigo'] ?? 'GEN',
                    'descripcion'      => $item['descripcion'],
                    'unidad_medida'    => 'NIU',
                    'cantidad'         => $item['cantidad']
                ]);

                // Solo descontamos si NO hay venta (si venta_id es null)
                if (empty($data['venta_id'])) {
                    $this->descontarStockLote($item);
                }
            }

            return $guia;
        });

        // ENVIAR A SUNAT
        try {
            $this->sunatService->transmitirGuia($guia);
        } catch (\Throwable $e) { // <--- CAMBIA Exception POR Throwable
            $guia->mensaje_sunat = "Error local: " . $e->getMessage();
            $guia->save();
        }

        return $guia;
    }

    private function descontarStockLote(array $item)
    {
        $loteId = $item['lote_id'] ?? null;
        if (!$loteId) throw new Exception("Producto sin lote: " . $item['descripcion']);
        $lote = Lote::lockForUpdate()->find($loteId);
        if (!$lote || $lote->stock_actual < $item['cantidad']) throw new Exception("Stock insuficiente.");
        $lote->decrement('stock_actual', $item['cantidad']);
    }
}
