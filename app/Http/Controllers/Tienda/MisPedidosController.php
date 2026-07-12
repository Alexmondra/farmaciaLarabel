<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\Tienda\PedidoOnline;
use App\Models\Ventas\Venta;

class MisPedidosController extends Controller
{
    public function index()
    {
        $cliente = auth('tienda')->user();

        $pedidos = PedidoOnline::with(['detalles', 'sucursal'])
            ->where('cliente_id', $cliente->id)
            ->latest()
            ->take(5)->get();

        $ventas = Venta::with(['sucursal'])
            ->where('cliente_id', $cliente->id)
            ->latest('fecha_emision')
            ->take(5)->get();

        return view('tienda.mis-pedidos', compact('pedidos', 'ventas'));
    }

    public function detalleVenta(Venta $venta)
    {
        $cliente = auth('tienda')->user();

        if ($venta->cliente_id !== $cliente->id) {
            abort(404);
        }

        $venta->load(['sucursal', 'detalle_ventas.medicamento']);

        $detalles = $venta->detalle_ventas->map(function ($detalle) {
            return [
                'producto' => $detalle->medicamento->nombre ?? $detalle->descripcion ?? '-',
                'cantidad' => (int) $detalle->cantidad,
                'precio' => number_format((float) $detalle->precio_unitario, 2),
                'subtotal' => number_format((float) $detalle->subtotal_neto, 2),
            ];
        });

        return response()->json([
            'tipo_comprobante' => $venta->tipo_comprobante,
            'serie' => $venta->serie,
            'numero' => $venta->numero,
            'fecha' => $venta->fecha_emision ? $venta->fecha_emision->format('d/m/Y') : '-',
            'sucursal' => $venta->sucursal->nombre ?? '-',
            'total' => number_format((float) $venta->total_neto, 2),
            'detalles' => $detalles,
        ]);
    }
}
