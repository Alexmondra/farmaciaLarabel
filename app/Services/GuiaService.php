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
use Barryvdh\DomPDF\Facade\Pdf;

class GuiaService
{
    protected $sunatService;

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
        // El resto del método registrarGuia permanece igual (emisión y egreso)
        $guia = DB::transaction(function () use ($user, $sucursal, $data) {

            // 1. DETERMINAR ESTADO Y DESCRIPCIÓN
            $estadoInicial = (strtotime($data['fecha_traslado']) <= strtotime(now()->format('Y-m-d'))) ? 'EN TRANSITO' : 'REGISTRADO';
            $descMotivo = $data['descripcion_motivo'] ?? (self::MOTIVO_MAPA[$data['motivo_traslado']] ?? 'TRASLADO');
            $esTrasladoInterno = $data['motivo_traslado'] === '04';

            // 2. RESOLVER CLIENTE Y VENTA
            $clienteId = $this->resolveClienteId($data);
            if (!$clienteId && !$esTrasladoInterno) {
                throw new Exception("Error: Falta seleccionar el Cliente destinatario (Motivo: {$data['motivo_traslado']}).");
            }

            // 3. CORREGIR CÓDIGO DE ESTABLECIMIENTO DE LLEGADA
            $codigoEstablecimientoLlegada = $esTrasladoInterno
                ? ($data['codigo_establecimiento_llegada'] ?? null)
                : null;

            // 4. CREAR LA GUÍA
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
                'codigo_establecimiento_partida' => $data['codigo_establecimiento_partida'] ?? $sucursal->codigo,
                'ubigeo_llegada'    => $data['ubigeo_llegada'],
                'direccion_llegada' => $data['direccion_llegada'],
                'codigo_establecimiento_llegada' => $codigoEstablecimientoLlegada,
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

            // 5. REGISTRAR DETALLES Y DESCONTAR STOCK (EGRESO)
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
                    // Descuento de stock
                    $this->descontarStockLote($item);

                    // Registro de Movimiento de EGRESO (si lo deseas mantener para la sucursal de ORIGEN)
                    // $this->registrarMovimiento(..., 'EGRESO', 'TRASLADO_SALIDA', ...)
                }
            }

            return $guia;
        });

        // 6. ENVIAR A SUNAT
        try {
            $this->sunatService->transmitirGuia($guia);
        } catch (\Throwable $e) {
            $guia->mensaje_sunat = "Error local: " . $e->getMessage();
            $guia->save();
        }

        return $guia;
    }

    /**
     * Proceso de Recepción de Guía (El ingreso de stock en la sucursal de destino)
     */
    public function recepcionarGuia(int $guiaId, array $data, User $receptor): GuiaRemision
    {
        return DB::transaction(function () use ($guiaId, $data, $receptor) {

            // 1. Cargar la Guía con todas las relaciones necesarias para el INGRESO
            // Hacemos eager loading: Sucursal (Origen) + Detalles + Lote (con su Medicamento)
            $guia = GuiaRemision::with(['detalles.lote.medicamento', 'sucursal'])->findOrFail($guiaId);

            // --- VALIDACIONES DE ESTADO y DATA ---
            if ($guia->estado_traslado !== 'EN TRANSITO') {
                throw new Exception("La Guía debe estar en estado 'EN TRANSITO' para ser recibida.");
            }

            // Validación CRÍTICA: La guía debe tener detalles.
            if ($guia->detalles->isEmpty()) {
                throw new Exception("La Guía N° {$guia->serie}-{$guia->numero} no tiene productos para recibir.");
            }

            // Encontrar Sucursal Destino (ID de la sucursal que está recibiendo)
            $sucursalDestinoId = $receptor->sucursal_id; // Asumimos que el usuario receptor pertenece a la sucursal destino

            // 2. Procesar Detalles (Incrementar Lote y Registrar INGRESO)
            // 2. Obtener Sucursal de Destino (Receptor)
            $sucursalDestino = $this->getSucursalByCodEstablecimiento($guia->codigo_establecimiento_llegada);

            if (!$sucursalDestino) {
                // Si no se encuentra, lanzamos la excepción CLARA
                throw new Exception("No se encontró la sucursal de destino con el código de establecimiento {$guia->codigo_establecimiento_llegada}.");
            }

            // 🔥 CORRECCIÓN: Ahora podemos obtener el ID de forma segura
            $sucursalDestinoId = $sucursalDestino->id;

            // **LA VARIABLE $detalle SOLO EXISTE DENTRO DE ESTE BUCLE**
            foreach ($guia->detalles as $detalle) {

                // A. Verificación de Lote de Origen
                // Si falta el lote_id en el detalle, o el lote no existe, fallamos con mensaje claro.
                $loteOrigen = optional($detalle->lote);

                if (!$loteOrigen || !$loteOrigen->medicamento) {
                    // Si esta excepción se lanza, el mensaje genérico será más claro.
                    throw new Exception("Falta información del lote original o el medicamento asociado para el detalle ID: {$detalle->id}. Por favor, verifique el detalle de la guía.");
                }

                // B. Incrementar/Crear Lote en la Sucursal Destino
                $loteDestino = $this->incrementarOCrearLote(
                    $detalle,
                    $loteOrigen,
                    $sucursalDestinoId
                );

                // C. Registrar Movimiento de INGRESO
                $this->registrarMovimiento(
                    $detalle->medicamento_id,
                    $loteDestino, // Usamos el nuevo lote de destino (o el incrementado)
                    $sucursalDestinoId,
                    $detalle->cantidad,
                    'INGRESO',
                    'TRASLADO_LLEGADA',
                    // Usamos optional() para proteger el acceso a $guia->sucursal->nombre
                    "Guía N° {$guia->serie}-{$guia->numero} (Origen: " . optional($guia->sucursal)->nombre . ")",
                    $receptor->id
                );
            } // <-- La variable $detalle deja de existir aquí.

            // 3. Actualizar Estado de la Guía (Línea CRÍTICA)
            // **¡Asegúrate que NO uses la variable $detalle aquí!**
            $guia->update([
                'estado_traslado' => 'ENTREGADO',
                'fecha_recepcion' => now(),
                'sucursal_destino_id' => $sucursalDestinoId,
                'receptor_id' => $receptor->id,
            ]);

            return $guia;
        });
    }

    /**
     * Registra un movimiento de inventario (INGRESO o EGRESO).
     */
    private function registrarMovimiento(int $medicamentoId, \App\Models\Inventario\Lote $lote, int $sucursalId, $cantidad, string $tipo, string $motivo, string $referencia, int $userId): void
    {
        $stockFinal = $lote->stock_actual;

        \App\Models\Inventario\MovimientoInventario::create([
            'tipo' => strtolower($tipo) === 'ingreso' ? 'entrada' : strtolower($tipo),
            'medicamento_id' => $medicamentoId,
            'sucursal_id' => $sucursalId,
            'lote_id' => $lote->id,
            'cantidad' => $cantidad,
            'motivo' => $motivo,
            'referencia' => $referencia,
            'user_id' => $userId,
            'stock_final' => $stockFinal
        ]);
    }

    public function anularGuia(int $guiaId, string $motivoAnulacion, User $user): GuiaRemision
    {
        return DB::transaction(function () use ($guiaId, $motivoAnulacion, $user) {

            $guia = GuiaRemision::with('detalles.lote')->findOrFail($guiaId);

            // 1. VALIDACIONES DE ESTADO
            if (in_array($guia->estado_traslado, ['RECEPCIONADO', 'ENTREGADO', 'ANULADO'])) {
                throw new Exception("Esta guía ya está en estado final ({$guia->estado_traslado}) y no puede ser recibida.");
            }
            if ($guia->motivo_traslado !== '04') {
                throw new Exception("Solo se puede recepcionar guías de Traslado Interno (Motivo 04).");
            }

            // 2. REVERTIR EL EGRESO (Devolver stock a la sucursal de origen)
            $sucursalOrigenId = $guia->sucursal_id;

            foreach ($guia->detalles as $detalle) {
                $lote = $detalle->lote; // Lote original de donde salió el stock

                if (!$lote) {
                    // Esto podría pasar si el detalle no guardó el lote_id (debería ser corregido)
                    throw new Exception("Fallo de datos: Lote no encontrado para el producto {$detalle->descripcion}.");
                }

                // A. Devolver el stock al lote original
                // Usamos increment para sumar la cantidad que se restó en el registro.
                $lote->increment('stock_actual', $detalle->cantidad);

                // B. Registrar Movimiento de INGRESO por ANULACIÓN
                $this->registrarMovimiento(
                    $detalle->medicamento_id,
                    $lote,
                    $sucursalOrigenId,
                    $detalle->cantidad,
                    'entrada', // Usar 'entrada' (el valor correcto para el ENUM)
                    'ANULACION_GUIA',
                    "Reversión por anulación de Guía N° {$guia->serie}-{$guia->numero}. Motivo: {$motivoAnulacion}",
                    $user->id
                );
            }

            // 3. MARCAR COMO ANULADO
            $guia->update([
                'estado_traslado' => 'ANULADO',
                'motivo_anulacion' => $motivoAnulacion, // Asumiendo que agregaste este campo a tu tabla guias_remision
                'anulado_por_user_id' => $user->id, // Asumiendo que agregaste este campo
            ]);

            // 4. (OPCIONAL) Enviar solicitud de anulación a SUNAT si aplica.
            // Aquí iría la lógica para enviar el ticket de baja a SUNAT, si lo estás implementando.

            return $guia;
        });
    }
    /**
     * Resuelve el ID del cliente, buscando en la venta si aplica.
     */
    private function resolveClienteId(array $data): ?int
    {
        $clienteId = $data['cliente_id'] ?? null;

        if (!$clienteId && !empty($data['venta_id'])) {
            $venta = \App\Models\Ventas\Venta::find($data['venta_id']);
            return $venta ? $venta->cliente_id : null;
        }

        return $clienteId;
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

        $lote = Lote::lockForUpdate()->find($loteId);

        if (!$lote || $lote->stock_actual < $item['cantidad']) {
            throw new Exception("Stock insuficiente para el lote ID: {$loteId} (Solicitado: {$item['cantidad']}).");
        }

        $lote->decrement('stock_actual', $item['cantidad']);
    }

    /**
     * Incrementa stock en un lote existente o crea uno nuevo en la sucursal destino.
     */
    private function incrementarOCrearLote($detalle, $loteOrigen, int $sucursalDestinoId): Lote
    {
        // 1. Intentar encontrar el lote en la sucursal destino (mismo código y vencimiento)
        $loteDestino = Lote::where('medicamento_id', $detalle->medicamento_id)
            ->where('sucursal_id', $sucursalDestinoId)
            ->where('codigo_lote', $loteOrigen->codigo_lote)
            ->where('fecha_vencimiento', $loteOrigen->fecha_vencimiento)
            ->lockForUpdate()
            ->first();

        if ($loteDestino) {
            // 2. Si el lote YA EXISTE, solo incrementamos el stock.
            $loteDestino->increment('stock_actual', $detalle->cantidad);
        } else {
            // 3. Si el lote NO EXISTE en la sucursal destino, lo CREAMOS.
            $loteDestino = Lote::create([
                'medicamento_id'    => $detalle->medicamento_id,
                'sucursal_id'       => $sucursalDestinoId,
                'codigo_lote'       => $loteOrigen->codigo_lote,
                'stock_actual'      => $detalle->cantidad,
                'fecha_vencimiento' => $loteOrigen->fecha_vencimiento,
                'ubicacion'         => $loteOrigen->ubicacion,
                'precio_compra'     => $loteOrigen->precio_compra,
            ]);
        }

        return $loteDestino;
    }

    private function getSucursalByCodEstablecimiento(string $codigoEstablecimiento)
    {
        return \App\Models\Sucursal::where('codigo', $codigoEstablecimiento)->first();
    }




    ///pdf

    public function generarPdf(GuiaRemision $guia): string
    {
        // 1. Cargamos relaciones necesarias para el PDF
        $guia->load(['detalles.medicamento', 'cliente', 'sucursal']);

        // 2. Definir los datos que se enviarán a la vista
        $data = [
            'guia' => $guia,
            'empresa' => \App\Models\Configuracion::first(), // Asume que tienes un modelo de configuración
            'fecha_impresion' => now()->format('d/m/Y H:i:s'),
        ];

        // 3. Renderizar la vista Blade del PDF
        $pdf = PDF::loadView('pdf.guias.guia_remision_sunat', $data);

        // 4. Definir el nombre del archivo y ruta de almacenamiento
        $nombreArchivo = $this->getNombreArchivoPdf($guia);
        $ruta = 'sunat/pdf/guias/' . $nombreArchivo;

        // 5. Guardar el archivo en Storage
        Storage::put($ruta, $pdf->output());

        // 6. Actualizar la ruta en el modelo de la Guía
        $guia->ruta_pdf = $ruta;
        $guia->save();

        return $ruta;
    }

    /**
     * Genera el nombre del archivo PDF
     */
    private function getNombreArchivoPdf(GuiaRemision $guia): string
    {
        // Formato RUC-TIPO-SERIE-NUMERO.pdf (Ej: 20555444333-09-T001-000001.pdf)
        $ruc = \App\Models\Configuracion::first()->empresa_ruc;
        $tipo = '09'; // Código SUNAT para Guía de Remisión
        return "{$ruc}-{$tipo}-{$guia->serie}-{$guia->numero}.pdf";
    }
}
