<?php

namespace App\Services;

use App\Models\Ventas\Venta;
use App\Models\Ventas\DetalleVenta;
use App\Models\Ventas\CajaSesion;
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

            // 1. CARGAR CAJA Y SUCURSAL
            // Usamos la sucursal de la CAJA (Así evitamos el error "on null" si eres Admin)
            $caja = CajaSesion::with('sucursal')
                ->where('id', $data['caja_sesion_id'])
                ->where('estado', 'ABIERTO')
                ->first();

            if (!$caja) {
                throw new Exception("La caja seleccionada está cerrada o no existe.");
            }

            if (!$caja->sucursal) {
                throw new Exception("La caja abierta (ID {$caja->id}) no tiene sucursal asignada.");
            }

            $sucursal = $caja->sucursal;

            // 2. PROCESAR ITEMS
            $items = json_decode($data['items'], true);
            $itemsProcesados = [];
            $totalNeto = 0;   // Total a Pagar (Con IGV)
            $totalBruto = 0;  // Total Base (Sin IGV)
            $totalIGV = 0;    // Total Impuestos

            foreach ($items as $index => $item) {
                // Solución al error "Undefined array key 'id'"
                $loteId = $item['id'] ?? $item['lote_id'] ?? null;

                if (!$loteId) throw new Exception("Error en Item #" . ($index + 1) . ": Falta ID del producto.");

                $precioVenta = (float) ($item['precio_venta'] ?? $item['precio_unitario'] ?? 0);
                $cantidad = (int) $item['cantidad'];

                // Cálculos por ITEM
                $valorUnitario = $precioVenta / 1.18; // Precio sin IGV
                $igvUnitario = $precioVenta - $valorUnitario;

                $subtotalNeto = $precioVenta * $cantidad; // Precio x Cantidad
                $subtotalBruto = $valorUnitario * $cantidad; // Base x Cantidad
                $igvTotal = $igvUnitario * $cantidad;

                // Acumuladores Generales
                $totalNeto += $subtotalNeto;
                $totalBruto += $subtotalBruto;
                $totalIGV += $igvTotal;

                $itemsProcesados[] = [
                    'lote_id' => $loteId,
                    'medicamento_id' => $item['medicamento_id'] ?? null,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioVenta,
                    'valor_unitario' => $valorUnitario,
                    'igv' => $igvTotal,
                    'subtotal_neto' => $subtotalNeto,
                    'subtotal_bruto' => $subtotalBruto // <--- ¡AQUÍ ESTABA EL FALTANTE!
                ];
            }

            // 3. GENERAR SERIE Y NUMERO
            $tipoComp = $data['tipo_comprobante'];
            $serie = ($tipoComp == 'FACTURA' ? $sucursal->serie_factura : $sucursal->serie_boleta)
                ?: ($tipoComp == 'FACTURA' ? 'F001' : 'B001');

            $ultimoCorrelativo = Venta::where('tipo_comprobante', $tipoComp)
                ->where('serie', $serie)
                ->max('numero');
            $nuevoNumero = $ultimoCorrelativo ? ($ultimoCorrelativo + 1) : 1;

            // 4. CREAR VENTA
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
                'estado'           => 'EMITIDA',

                'total_bruto'     => round($totalBruto, 2),
                'total_descuento' => 0,
                'total_neto'      => round($totalNeto, 2),

                'op_gravada'      => round($totalBruto, 2),
                'op_exonerada'    => 0,
                'op_inafecta'     => 0,
                'total_igv'       => round($totalIGV, 2),
                'porcentaje_igv'  => 18.00,
            ]);

            // 5. GUARDAR DETALLES
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
                    'subtotal_bruto'  => $item['subtotal_bruto'], // <--- ¡SOLUCIONADO!
                    'subtotal_descuento' => 0 // Aseguramos que no quede null
                ]);
            }

            // 6. ENVIAR A SUNAT
            if ($tipoComp == 'BOLETA' || $tipoComp == 'FACTURA') {
                $this->sunatService->transmitirAComprobante($venta);
            }

            return $venta;
        });
    }
}
