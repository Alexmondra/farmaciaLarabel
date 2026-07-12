@extends('tienda.layout')

@section('title', 'Pedido ' . $pedido->codigo)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="store-card bg-white p-4 mb-4">
            <h1 class="h3">Pedido {{ $pedido->codigo }}</h1>
            <p class="text-muted mb-0">Estado: <strong>{{ $pedido->estado }}</strong> | Pago: <strong>{{ $pedido->estado_pago }}</strong></p>
            <p class="text-muted">Sucursal: {{ $pedido->sucursal->nombre ?? '-' }}</p>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pedido->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->descripcion }}</td>
                                <td>{{ $detalle->cantidad }}</td>
                                <td>S/ {{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                                <td>S/ {{ number_format((float) $detalle->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <strong class="h4">Total: S/ {{ number_format((float) $pedido->total, 2) }}</strong>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="store-card bg-white p-4 text-center">
            <h2 class="h5">QR de recojo</h2>
            <div class="my-3">
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(180)->generate(route('tienda.pedidos.recojo', $pedido->qr_token)) !!}
            </div>
            <p class="text-muted small mb-0">Presenta este QR en la sucursal elegida para ubicar tu pedido.</p>
        </div>
    </div>
</div>
@endsection
