<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\Tienda\PedidoOnline;

class PedidoOnlineController extends Controller
{
    public function show(string $codigo)
    {
        $pedido = PedidoOnline::with(['detalles.medicamento', 'sucursal', 'pagos', 'cliente'])
            ->where('codigo', $codigo)
            ->firstOrFail();

        $cliente = auth('tienda')->user();
        if ($cliente && $pedido->cliente_id !== $cliente->id) {
            abort(404);
        }

        return view('tienda.pedido', compact('pedido'));
    }

    public function recojo(string $token)
    {
        $pedido = PedidoOnline::with(['detalles', 'sucursal'])
            ->where('qr_token', $token)
            ->firstOrFail();

        return view('tienda.recojo', compact('pedido'));
    }
}
