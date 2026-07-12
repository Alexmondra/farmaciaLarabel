@extends('adminlte::page')

@section('title', 'Pedido ' . $pedido->codigo)

@section('content_header')
<h1>Pedido {{ $pedido->codigo }}</h1>
@endsection

@section('content')
@include('tienda.partials.alerts')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Detalle</div>
            <div class="card-body table-responsive">
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
                <strong>Total: S/ {{ number_format((float) $pedido->total, 2) }}</strong>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Cliente y estado</div>
            <div class="card-body">
                <p><strong>Cliente:</strong> {{ $pedido->cliente_nombre }}</p>
                <p><strong>Documento:</strong> {{ $pedido->cliente_documento }}</p>
                <p><strong>Telefono:</strong> {{ $pedido->cliente_telefono ?: '-' }}</p>
                <p><strong>Email:</strong> {{ $pedido->cliente_email ?: '-' }}</p>
                <p><strong>Sucursal:</strong> {{ $pedido->sucursal->nombre ?? '-' }}</p>
                <p><strong>Metodo pago:</strong> {{ $pedido->metodo_pago }}</p>
                <form method="POST" action="{{ route('tienda.admin.pedidos.estado', $pedido) }}">
                    @csrf
                    @method('PATCH')
                    <label>Estado</label>
                    <select name="estado" class="form-control mb-3">
                        @foreach (['PENDIENTE','CONFIRMADO','PREPARANDO','LISTO','ENTREGADO','CANCELADO','CONVERTIDO_A_VENTA'] as $estado)
                            <option value="{{ $estado }}" @selected($pedido->estado === $estado)>{{ $estado }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary btn-block">Actualizar</button>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header">QR de recojo</div>
            <div class="card-body text-center">
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(160)->generate(route('tienda.pedidos.recojo', $pedido->qr_token)) !!}
            </div>
        </div>
    </div>
</div>
@endsection
