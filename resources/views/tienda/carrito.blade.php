@extends('tienda.layout')

@section('title', 'Carrito')

@section('content')
<h1 class="h3 mb-4">Carrito</h1>

@if ($items->isEmpty())
    <div class="alert alert-info">Tu carrito esta vacio.</div>
    <a href="{{ route('tienda.index') }}" class="btn btn-store">Ir al catalogo</a>
@else
    @if($esMultiSucursal)
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
            <span style="font-size: 1.3rem;">&#9888;</span>
            <div>
                <strong>Tienes productos de diferentes sucursales.</strong><br>
                <small>Te recomendamos elegir productos de una sola sucursal para recoger tu pedido en el menor tiempo. Si continuas asi, el tiempo de espera sera de al menos <strong>una semana</strong> mientras trasladamos todo a un solo punto de recojo.</small>
            </div>
        </div>
    @endif
    <div class="store-card bg-white p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Sucursal</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item['producto']->nombre }}</td>
                            <td>{{ $item['producto']->sucursal->nombre ?? '-' }}</td>
                             <td>S/ {{ number_format($item['precio'], 2) }}</td>
                             <td style="width: 160px;">
                                 <form method="POST" action="{{ route('tienda.carrito.update', $item['producto']) }}" class="d-flex gap-2">
                                     @csrf
                                     @method('PATCH')
                                     <input type="number" name="cantidad" value="{{ $item['cantidad'] }}" min="0" max="{{ $item['stock_disponible'] === null ? 99 : min(99, $item['stock_disponible']) }}" class="form-control form-control-sm">
                                     <button class="btn btn-sm btn-outline-secondary">OK</button>
                                 </form>
                                 <small class="text-muted">{{ $item['stock_disponible'] === null ? 'Sin limite de stock' : 'Stock: ' . $item['stock_disponible'] }}</small>
                             </td>
                            <td>S/ {{ number_format($item['subtotal'], 2) }}</td>
                            <td>
                                <form method="POST" action="{{ route('tienda.carrito.destroy', $item['producto']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Quitar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <strong class="h4 mb-0">Total: S/ {{ number_format($total, 2) }}</strong>
            <a href="{{ route('tienda.checkout.create') }}" class="btn btn-store btn-lg">Finalizar pedido</a>
        </div>
    </div>
@endif
@endsection
