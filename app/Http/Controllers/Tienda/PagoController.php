<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\Tienda\PedidoOnline;
use App\Services\CulqiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PagoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:tienda')->except('webhook');
    }

    public function show(string $codigo)
    {
        $pedido = PedidoOnline::where('codigo', $codigo)
            ->where('cliente_id', auth('tienda')->id())
            ->with('pagos')
            ->firstOrFail();

        if ($pedido->metodo_pago !== 'PAGO_ONLINE') {
            return redirect()->route('tienda.pedidos.show', $pedido->codigo)
                ->with('warning', 'Este pedido no requiere pago online.');
        }

        if ($pedido->estado_pago !== 'PENDIENTE') {
            return redirect()->route('tienda.pedidos.show', $pedido->codigo)
                ->with('warning', 'Este pedido ya fue pagado o cancelado.');
        }

        return view('tienda.pago', [
            'pedido' => $pedido,
            'publicKey' => config('services.culqi.public_key'),
            'rsaId' => config('services.culqi.rsa_id'),
            'rsaPublicKey' => config('services.culqi.rsa_public_key'),
        ]);
    }

    public function crearOrdenAjax(string $codigo)
    {
        $pedido = PedidoOnline::where('codigo', $codigo)
            ->where('cliente_id', auth('tienda')->id())
            ->with('pagos')
            ->firstOrFail();

        if ($pedido->metodo_pago !== 'PAGO_ONLINE' || $pedido->estado_pago !== 'PENDIENTE') {
            return response()->json(['success' => false, 'error' => 'No se puede pagar en este momento.'], 422);
        }

        $pagoPendiente = $pedido->pagos->where('estado', 'PENDIENTE')->first();
        $orderId = $pagoPendiente?->referencia_externa;

        $necesitaNuevaOrden = true;

        if ($orderId) {
            $culqi = new CulqiService();
            $resultado = $culqi->obtenerOrden($orderId);

            if ($resultado['success']) {
                $estado = $resultado['data']['state'] ?? '';

                if ($estado === 'paid') {
                    DB::transaction(function () use ($pedido, $pagoPendiente, $resultado) {
                        if ($pagoPendiente) {
                            $pagoPendiente->update([
                                'estado' => 'COMPLETADO',
                                'payload' => $resultado['data'],
                                'pagado_at' => now(),
                            ]);
                        }
                        $pedido->update(['estado_pago' => 'COMPLETADO']);
                    });

                    return response()->json([
                        'success' => true,
                        'redirect' => route('tienda.pedidos.show', $pedido->codigo),
                    ]);
                }

                // Solo reutilizamos la orden si sigue en estado inicial 'created'
                if ($estado === 'created') {
                    $necesitaNuevaOrden = false;
                }
            }
        }

        if (!$necesitaNuevaOrden && $orderId) {
            return response()->json(['success' => true, 'order_id' => $orderId]);
        }

        $telefono = $pedido->cliente_telefono;

        if (!$telefono || strlen(trim($telefono)) < 6) {
            return response()->json([
                'success' => false,
                'error' => 'Debes tener un numero de telefono registrado para pagar online. Actualiza tus datos en tu perfil.',
                'fallback' => true,
            ], 422);
        }

        $culqi = new CulqiService();
        $email = $pedido->cliente_email ?: 'cliente@farmacia.com';
        $nombre = $pedido->cliente_nombre ?: 'Cliente';
        $orderNumber = $pedido->codigo . '-' . now()->timestamp;

        $resultado = $culqi->crearOrden($pedido->total, 'PEN', $email, $orderNumber, $nombre, $telefono);

        if ($resultado['success']) {
            $orderId = $resultado['data']['id'];

            if ($pagoPendiente) {
                $pagoPendiente->update(['referencia_externa' => $orderId]);
            } else {
                $pedido->pagos()->create([
                    'proveedor' => 'CULQI',
                    'referencia_externa' => $orderId,
                    'estado' => 'PENDIENTE',
                    'monto' => $pedido->total,
                    'moneda' => 'PEN',
                ]);
            }

            return response()->json(['success' => true, 'order_id' => $orderId]);
        }

        Log::warning('Culqi crearOrden fallo en crearOrdenAjax', [
            'pedido' => $pedido->codigo,
            'error' => $resultado['error'],
            'order_number' => $orderNumber,
        ]);

        return response()->json([
            'success' => false,
            'error' => $resultado['error'],
            'fallback' => true,
        ]);
    }

    public function procesar(Request $request, string $codigo)
    {
        $pedido = PedidoOnline::where('codigo', $codigo)
            ->where('cliente_id', auth('tienda')->id())
            ->firstOrFail();

        if ($pedido->metodo_pago !== 'PAGO_ONLINE' || $pedido->estado_pago !== 'PENDIENTE') {
            return response()->json(['success' => false, 'message' => 'No se puede pagar en este momento.'], 422);
        }

        $request->validate([
            'token_id' => ['nullable', 'string', 'max:120'],
            'order_id' => ['nullable', 'string', 'max:50'],
        ]);

        $culqi = new CulqiService();

        if ($request->filled('token_id')) {
            $email = $pedido->cliente_email ?: 'cliente@farmacia.com';
            $resultado = $culqi->crearCargo($pedido->total, 'PEN', $email, $request->input('token_id'));

            if (!$resultado['success']) {
                return response()->json(['success' => false, 'message' => $resultado['error']], 422);
            }

            $cargo = $resultado['data'];

            DB::transaction(function () use ($pedido, $cargo) {
                $pedido->pagos()->create([
                    'proveedor' => 'CULQI',
                    'referencia_externa' => $cargo['id'],
                    'estado' => 'COMPLETADO',
                    'monto' => $pedido->total,
                    'moneda' => 'PEN',
                    'payload' => $cargo,
                    'pagado_at' => now(),
                ]);

                $pedido->update(['estado_pago' => 'COMPLETADO']);
            });

            return response()->json([
                'success' => true,
                'redirect' => route('tienda.pedidos.show', $pedido->codigo),
            ]);
        }

        if ($request->filled('order_id')) {
            $resultado = $culqi->obtenerOrden($request->input('order_id'));

            if ($resultado['success'] && ($resultado['data']['state'] ?? '') === 'paid') {
                DB::transaction(function () use ($pedido, $resultado) {
                    $pago = $pedido->pagos()->where('referencia_externa', $resultado['data']['id'])->first();

                    if ($pago) {
                        $pago->update([
                            'estado' => 'COMPLETADO',
                            'payload' => $resultado['data'],
                            'pagado_at' => now(),
                        ]);
                    }

                    $pedido->update(['estado_pago' => 'COMPLETADO']);
                });

                return response()->json([
                    'success' => true,
                    'redirect' => route('tienda.pedidos.show', $pedido->codigo),
                ]);
            }

            $estadoMsg = $resultado['data']['state'] ?? 'pendiente';
            return response()->json([
                'success' => false,
                'message' => 'Tu pago esta en estado ' . $estadoMsg . '. Te notificaremos cuando se confirme.',
            ], 422);
        }

        return response()->json(['success' => false, 'message' => 'No se recibio informacion de pago.'], 422);
    }

    public function webhook(Request $request)
    {
        $payload = $request->all();
        Log::info('Culqi webhook recibido', $payload);

        $type = $payload['type'] ?? '';
        $data = $payload['data'] ?? [];

        if ($type === 'order.status.changed' && ($data['state'] ?? '') === 'paid') {
            $orderId = $data['id'] ?? '';

            $pago = \App\Models\Tienda\PagoOnline::where('referencia_externa', $orderId)
                ->where('proveedor', 'CULQI')
                ->first();

            if ($pago) {
                DB::transaction(function () use ($pago, $data) {
                    $pago->update([
                        'estado' => 'COMPLETADO',
                        'payload' => $data,
                        'pagado_at' => now(),
                    ]);

                    $pago->pedido->update(['estado_pago' => 'COMPLETADO']);
                });
            }
        }

        return response()->json(['ok' => true]);
    }
}
