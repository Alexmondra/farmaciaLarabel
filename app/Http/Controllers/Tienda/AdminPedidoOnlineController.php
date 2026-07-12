<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\Tienda\PedidoOnline;
use Illuminate\Http\Request;

class AdminPedidoOnlineController extends Controller
{
    public function index(Request $request)
    {
        $pedidos = PedidoOnline::with(['cliente', 'sucursal'])
            ->when($request->filled('estado'), fn($query) => $query->where('estado', $request->estado))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim($request->q);
                $query->where(function ($sub) use ($term) {
                    $sub->where('codigo', 'LIKE', "%{$term}%")
                        ->orWhere('cliente_documento', 'LIKE', "%{$term}%")
                        ->orWhere('cliente_nombre', 'LIKE', "%{$term}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('tienda.admin.pedidos.index', compact('pedidos'));
    }

    public function show(PedidoOnline $pedido)
    {
        $pedido->load(['cliente', 'sucursal', 'detalles.medicamento', 'pagos', 'venta']);

        return view('tienda.admin.pedidos.show', compact('pedido'));
    }

    public function updateEstado(Request $request, PedidoOnline $pedido)
    {
        $data = $request->validate([
            'estado' => ['required', 'in:PENDIENTE,CONFIRMADO,PREPARANDO,LISTO,ENTREGADO,CANCELADO,CONVERTIDO_A_VENTA'],
        ]);

        $pedido->estado = $data['estado'];

        if ($data['estado'] === 'CONFIRMADO' && !$pedido->confirmado_at) {
            $pedido->confirmado_at = now();
        }

        if ($data['estado'] === 'ENTREGADO' && !$pedido->entregado_at) {
            $pedido->entregado_at = now();
        }

        $pedido->save();

        return back()->with('success', 'Estado del pedido actualizado.');
    }
}
