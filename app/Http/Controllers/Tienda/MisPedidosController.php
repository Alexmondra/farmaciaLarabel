<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\Tienda\PedidoOnline;
use App\Models\Ventas\Venta;
use Illuminate\Support\Facades\URL;

class MisPedidosController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $cliente = auth('tienda')->user();

        // Si es una petición AJAX para "Cargar más"
        if ($request->ajax() && $request->has('type')) {
            $type = $request->input('type');
            $offset = (int) $request->input('offset', 5);
            
            if ($type === 'pedidos') {
                $pedidos = PedidoOnline::with(['detalles', 'sucursal'])
                    ->where('cliente_id', $cliente->id)
                    ->latest()
                    ->skip($offset)
                    ->take(15)
                    ->get();

                $total = PedidoOnline::where('cliente_id', $cliente->id)->count();
                $hasMore = ($offset + $pedidos->count()) < $total;

                $html = '';
                foreach ($pedidos as $pedido) {
                    $html .= view('tienda.partials.pedido-row', compact('pedido'))->render();
                }

                return response()->json([
                    'html' => $html,
                    'hasMore' => $hasMore
                ]);
            }

            if ($type === 'ventas') {
                $ventas = Venta::with(['sucursal'])
                    ->where('cliente_id', $cliente->id)
                    ->whereDoesntHave('pedidoOnline')
                    ->latest('fecha_emision')
                    ->skip($offset)
                    ->take(15)
                    ->get();

                $total = Venta::where('cliente_id', $cliente->id)
                    ->whereDoesntHave('pedidoOnline')
                    ->count();
                $hasMore = ($offset + $ventas->count()) < $total;

                $html = '';
                foreach ($ventas as $venta) {
                    $html .= view('tienda.partials.venta-row', compact('venta'))->render();
                }

                return response()->json([
                    'html' => $html,
                    'hasMore' => $hasMore
                ]);
            }
        }

        // Carga inicial (primeros 5 elementos)
        $pedidos = PedidoOnline::with(['detalles', 'sucursal'])
            ->where('cliente_id', $cliente->id)
            ->latest()
            ->paginate(5, ['*'], 'pedidos_page');

        $ventas = Venta::with(['sucursal'])
            ->where('cliente_id', $cliente->id)
            ->whereDoesntHave('pedidoOnline')
            ->latest('fecha_emision')
            ->paginate(5, ['*'], 'ventas_page');

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
            'url_pdf' => URL::temporarySignedRoute('publico.descargar', now()->addHours(24), ['id' => $venta->id])
        ]);
    }
}
