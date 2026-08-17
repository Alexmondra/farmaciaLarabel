<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\Tienda\Carrito;
use App\Models\Tienda\PedidoOnline;
use App\Models\Tienda\TiendaProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function create()
    {
        [$items, $total] = $this->itemsCarrito();

        if ($items->isEmpty()) {
            return redirect()->route('tienda.index')->with('warning', 'Agrega productos antes de finalizar el pedido.');
        }

        $cliente = auth('tienda')->user();
        $sucursalIds = $items->pluck('producto.sucursal_id')->unique();
        $esMultiSucursal = $sucursalIds->count() > 1;

        if ($esMultiSucursal) {
            $fechaRecojoDefault = now()->addWeek()->setTime(14, 0)->format('Y-m-d\TH:i');
            $fechaRecojoMin = now()->addWeek()->setTime(0, 0)->format('Y-m-d\TH:i');
        } else {
            $fechaRecojoDefault = now()->addDay()->setTime(14, 0)->format('Y-m-d\TH:i');
            $fechaRecojoMin = now()->format('Y-m-d\TH:i');
        }

        $sucursales = \App\Models\Sucursal::whereIn('id', $sucursalIds)->get();
        $sucursalesJson = $sucursales->map(function($s) {
            return [
                'id' => $s->id,
                'nombre' => $s->nombre,
                'direccion' => $s->direccion,
                'distrito' => $s->distrito,
                'lat' => $s->latitud ? (float)$s->latitud : null,
                'lng' => $s->longitud ? (float)$s->longitud : null,
            ];
        });

        $pagosOnlinePendientes = PedidoOnline::where('cliente_id', $cliente->id)
            ->where('metodo_pago', 'PAGO_ONLINE')
            ->where('estado_pago', 'PENDIENTE')
            ->whereIn('estado', ['PENDIENTE', 'CONFIRMADO'])
            ->count();

        $limiteOnlineAlcanzado = $pagosOnlinePendientes >= 3;
        $montoInsuficienteOnline = $total < 15.00;

        return view('tienda.checkout', compact('items', 'total', 'cliente', 'fechaRecojoDefault', 'fechaRecojoMin', 'esMultiSucursal', 'sucursales', 'sucursalesJson', 'limiteOnlineAlcanzado', 'montoInsuficienteOnline'));
    }

    public function store(Request $request)
    {
        [$items, $total] = $this->itemsCarrito();

        if ($items->isEmpty()) {
            return redirect()->route('tienda.index')->with('warning', 'Agrega productos antes de finalizar el pedido.');
        }

        $sucursalIds = $items->pluck('producto.sucursal_id')->unique();
        $esMultiSucursal = $sucursalIds->count() > 1;

        $fechaRecojoMin = $esMultiSucursal ? now()->addWeek()->startOfDay() : now();

        $metodoPagoEsOnline = $request->input('metodo_pago') === 'PAGO_ONLINE';

        $reglasTelefono = $metodoPagoEsOnline
            ? ['required', 'string', 'max:30', 'min:6']
            : ['nullable', 'string', 'max:30'];

        $data = $request->validate([
            'cliente_telefono' => $reglasTelefono,
            'cliente_email' => ['nullable', 'email', 'max:120'],
            'tipo_entrega' => ['required', 'in:RECOJO_SUCURSAL'],
            'metodo_pago' => ['required', 'in:PAGO_AL_RECOGER,PAGO_ONLINE'],
            'fecha_recojo' => ['required', 'date', 'after:' . $fechaRecojoMin->toDateTimeString()],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'sucursal_recojo_id' => $esMultiSucursal ? ['required', 'integer', 'in:' . $sucursalIds->join(',')] : ['nullable'],
            'confirmar_datos' => ['nullable'],
        ]);

        $cliente = auth('tienda')->user();

        $telefonoNuevo = $data['cliente_telefono'] ?? null;
        $emailNuevo = $data['cliente_email'] ?? null;

        $telefonoCambio = $telefonoNuevo !== null && $cliente->telefono !== null && $telefonoNuevo !== $cliente->telefono;
        $emailCambio = $emailNuevo !== null && $cliente->email !== null && $emailNuevo !== $cliente->email;

        if (!$request->boolean('confirmar_datos') && ($telefonoCambio || $emailCambio)) {
            $mensajes = [];
            if ($telefonoCambio) {
                $mensajes[] = 'telefono de ' . $cliente->telefono . ' a ' . $telefonoNuevo;
            }
            if ($emailCambio) {
                $mensajes[] = 'email de ' . $cliente->email . ' a ' . $emailNuevo;
            }

            return back()->withInput()
                ->with('confirmar_cambio_datos', implode(' y ', $mensajes));
        }

        foreach ($items as $item) {
            if (!$item['producto']->permiteCantidad($item['cantidad'])) {
                return back()->withInput()->withErrors([
                    'stock' => 'Stock insuficiente para ' . $item['producto']->nombre . '.',
                ]);
            }
        }

        if ($data['metodo_pago'] === 'PAGO_AL_RECOGER') {
            $pedidosActivos = PedidoOnline::where('cliente_id', $cliente->id)
                ->whereIn('estado', ['PENDIENTE', 'CONFIRMADO'])
                ->count();

            if ($pedidosActivos >= 3) {
                return back()->withInput()->withErrors([
                    'metodo_pago' => 'Has alcanzado el limite de 3 pedidos activos con pago al recoger. Usa pago online o espera a que se completen tus pedidos actuales.',
                ]);
            }
        }

        if ($data['metodo_pago'] === 'PAGO_ONLINE') {
            if ($total < 15.00) {
                return back()->withInput()->withErrors([
                    'metodo_pago' => 'El pago online solo esta disponible para compras de S/ 15.00 o mas. Por favor, selecciona Pago al Recoger.',
                ]);
            }

            $pagosOnlinePendientes = PedidoOnline::where('cliente_id', $cliente->id)
                ->where('metodo_pago', 'PAGO_ONLINE')
                ->where('estado_pago', 'PENDIENTE')
                ->whereIn('estado', ['PENDIENTE', 'CONFIRMADO'])
                ->count();

            if ($pagosOnlinePendientes >= 3) {
                return back()->withInput()->withErrors([
                    'metodo_pago' => 'Tienes 3 pedidos pendientes de pago online. Paga tus pedidos anteriores para habilitar esta opcion o selecciona Pago al Recoger.',
                ]);
            }
        }

        $pedido = DB::transaction(function () use ($data, $items, $total, $cliente, $esMultiSucursal) {
            $cliente->update([
                'telefono' => $data['cliente_telefono'] ?? $cliente->telefono,
                'email' => $data['cliente_email'] ?? $cliente->email,
            ]);

            $sucursalId = $esMultiSucursal
                ? (int) $data['sucursal_recojo_id']
                : $items->first()['producto']->sucursal_id;

            $pedido = PedidoOnline::create([
                'codigo' => $this->generarCodigoPedido(),
                'qr_token' => Str::random(60),
                'cliente_id' => $cliente->id,
                'sucursal_id' => $sucursalId,
                'cliente_tipo_documento' => $cliente->tipo_documento,
                'cliente_documento' => $cliente->documento,
                'cliente_nombre' => $cliente->nombre_completo,
                'cliente_telefono' => $data['cliente_telefono'] ?? $cliente->telefono,
                'cliente_email' => $data['cliente_email'] ?? $cliente->email,
                'tipo_entrega' => $data['tipo_entrega'],
                'metodo_pago' => $data['metodo_pago'],
                'fecha_recojo' => $data['fecha_recojo'],
                'estado_pago' => 'PENDIENTE',
                'estado' => 'PENDIENTE',
                'subtotal' => $total,
                'costo_envio' => 0,
                'total' => $total,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            foreach ($items as $item) {
                $pedido->detalles()->create([
                    'medicamento_id' => $item['producto']->medicamento_id,
                    'descripcion' => $item['producto']->nombre,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            if ($data['metodo_pago'] === 'PAGO_ONLINE') {
                $pedido->pagos()->create([
                    'proveedor' => 'CULQI',
                    'estado' => 'PENDIENTE',
                    'monto' => $pedido->total,
                    'moneda' => 'PEN',
                ]);
            }

            return $pedido;
        });

        session()->forget('tienda_carrito');
        Carrito::where('cliente_id', $cliente->id)->delete();

        if ($data['metodo_pago'] === 'PAGO_ONLINE') {
            return redirect()->route('tienda.pago.show', $pedido->codigo)
                ->with('info', 'Completa el pago para confirmar tu pedido.');
        }

        return redirect()->route('tienda.pedidos.show', $pedido->codigo)
            ->with('success', 'Pedido registrado correctamente.');
    }

    private function itemsCarrito(): array
    {
        $carrito = session('tienda_carrito', []);
        $productos = TiendaProducto::with(['medicamento.categoria', 'sucursal'])
            ->where('visible', true)
            ->whereIn('id', array_keys($carrito))
            ->get();

        $items = $productos->map(function (TiendaProducto $producto) use ($carrito) {
            $cantidad = (int) ($carrito[$producto->id] ?? 0);
            $precio = $producto->precioVenta();

            return [
                'producto' => $producto,
                'cantidad' => $cantidad,
                'precio' => $precio,
                'subtotal' => $precio * $cantidad,
                'stock_disponible' => $producto->stockDisponible(),
            ];
        })->filter(fn($item) => $item['cantidad'] > 0)->values();

        return [$items, $items->sum('subtotal')];
    }

    private function generarCodigoPedido(): string
    {
        do {
            $codigo = 'WEB-' . now()->format('ymd') . '-' . Str::upper(Str::random(6));
        } while (PedidoOnline::where('codigo', $codigo)->exists());

        return $codigo;
    }
}
