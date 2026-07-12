<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\Tienda\Carrito;
use App\Models\Tienda\TiendaProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarritoController extends Controller
{
    public function index()
    {
        $this->cargarDeBD();
        [$items, $total] = $this->itemsCarrito();
        $sucursalIds = $items->pluck('producto.sucursal_id')->unique();
        $esMultiSucursal = $sucursalIds->count() > 1;

        return view('tienda.carrito', compact('items', 'total', 'esMultiSucursal'));
    }

    public function store(Request $request, TiendaProducto $producto)
    {
        abort_unless($producto->visible, 404);

        $data = $request->validate([
            'cantidad' => ['nullable', 'integer', 'min:1', 'max:99'],
            'confirmar_multi' => ['nullable'],
        ]);

        $carrito = session('tienda_carrito', []);
        $cantidad = (int) ($data['cantidad'] ?? 1);
        $nuevaCantidad = min(($carrito[$producto->id] ?? 0) + $cantidad, 99);

        if (!$producto->permiteCantidad($nuevaCantidad)) {
            return back()->with('warning', 'Stock insuficiente para agregar esa cantidad.');
        }

        if (!$request->boolean('confirmar_multi') && !empty($carrito)) {
            $itemsEnCarrito = TiendaProducto::whereIn('id', array_keys($carrito))
                ->pluck('sucursal_id')->unique();
            if ($itemsEnCarrito->isNotEmpty() && !$itemsEnCarrito->contains($producto->sucursal_id)) {
                return back()->withInput()
                    ->with('confirmar_multi_sucursal', $producto->id)
                    ->with('confirmar_multi_sucursal_nombre', $producto->sucursal->nombre ?? 'otra sucursal');
            }
        }

        $carrito[$producto->id] = $nuevaCantidad;
        session(['tienda_carrito' => $carrito]);

        $this->guardarEnBD($producto->id, $nuevaCantidad);

        [$items] = $this->itemsCarrito();
        $sucursalIds = $items->pluck('producto.sucursal_id')->unique();

        if ($sucursalIds->count() > 1) {
            return redirect()->route('tienda.carrito.index')->with('warning', 'Tienes productos de diferentes sucursales. Te recomendamos elegir productos de una sola sucursal para recoger tu pedido en el menor tiempo. Si continuas con productos de varias sucursales, el tiempo de espera sera de al menos una semana mientras trasladamos todo a un solo punto de recojo.');
        }

        return redirect()->route('tienda.carrito.index')->with('success', 'Producto agregado al carrito.');
    }

    public function update(Request $request, TiendaProducto $producto)
    {
        $data = $request->validate([
            'cantidad' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $carrito = session('tienda_carrito', []);

        if ((int) $data['cantidad'] === 0) {
            unset($carrito[$producto->id]);
            $this->eliminarDeBD($producto->id);
        } else {
            if (!$producto->permiteCantidad((int) $data['cantidad'])) {
                return back()->with('warning', 'Stock insuficiente para esa cantidad.');
            }

            $carrito[$producto->id] = (int) $data['cantidad'];
            $this->guardarEnBD($producto->id, (int) $data['cantidad']);
        }

        session(['tienda_carrito' => $carrito]);

        return redirect()->route('tienda.carrito.index')->with('success', 'Carrito actualizado.');
    }

    public function destroy(TiendaProducto $producto)
    {
        $carrito = session('tienda_carrito', []);
        unset($carrito[$producto->id]);
        session(['tienda_carrito' => $carrito]);

        $this->eliminarDeBD($producto->id);

        return redirect()->route('tienda.carrito.index')->with('success', 'Producto retirado del carrito.');
    }

    private function guardarEnBD(int $productoId, int $cantidad): void
    {
        $cliente = Auth::guard('tienda')->user();
        if (!$cliente) return;

        Carrito::updateOrCreate(
            ['cliente_id' => $cliente->id, 'tienda_producto_id' => $productoId],
            ['cantidad' => $cantidad]
        );
    }

    private function eliminarDeBD(int $productoId): void
    {
        $cliente = Auth::guard('tienda')->user();
        if (!$cliente) return;

        Carrito::where('cliente_id', $cliente->id)
            ->where('tienda_producto_id', $productoId)
            ->delete();
    }

    public function cargarDeBD(): void
    {
        $cliente = Auth::guard('tienda')->user();
        if (!$cliente) return;

        $itemsDB = Carrito::where('cliente_id', $cliente->id)->get();

        if ($itemsDB->isEmpty()) return;

        $carrito = session('tienda_carrito', []);

        foreach ($itemsDB as $item) {
            if (!isset($carrito[$item->tienda_producto_id])) {
                $carrito[$item->tienda_producto_id] = $item->cantidad;
            }
        }

        session(['tienda_carrito' => $carrito]);
    }

    public function sincronizarBD(): void
    {
        $cliente = Auth::guard('tienda')->user();
        if (!$cliente) return;

        $carrito = session('tienda_carrito', []);

        Carrito::where('cliente_id', $cliente->id)->delete();

        foreach ($carrito as $productoId => $cantidad) {
            if ($cantidad > 0) {
                Carrito::create([
                    'cliente_id' => $cliente->id,
                    'tienda_producto_id' => $productoId,
                    'cantidad' => $cantidad,
                ]);
            }
        }
    }

    private function itemsCarrito(): array
    {
        $carrito = session('tienda_carrito', []);
        $productos = TiendaProducto::with(['medicamento', 'sucursal'])
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
}
