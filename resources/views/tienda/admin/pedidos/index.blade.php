@extends('adminlte::page')

@section('title', 'Pedidos online')

@section('content_header')
<h1>Pedidos online</h1>
@endsection

@section('content')
@include('tienda.partials.alerts')
<div class="card">
    <div class="card-body">
        <form method="GET" class="row mb-3">
            <div class="col-md-5">
                <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Codigo, documento o cliente">
            </div>
            <div class="col-md-4">
                <select name="estado" class="form-control">
                    <option value="">Todos los estados</option>
                    @foreach (['PENDIENTE','CONFIRMADO','PREPARANDO','LISTO','ENTREGADO','CANCELADO','CONVERTIDO_A_VENTA'] as $estado)
                        <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-block">Filtrar</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Cliente</th>
                        <th>Sucursal</th>
                        <th>Estado</th>
                        <th>Pago</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pedidos as $pedido)
                        <tr>
                            <td>{{ $pedido->codigo }}</td>
                            <td>{{ $pedido->cliente_nombre }}</td>
                            <td>{{ $pedido->sucursal->nombre ?? '-' }}</td>
                            <td><span class="badge badge-info">{{ $pedido->estado }}</span></td>
                            <td>{{ $pedido->estado_pago }}</td>
                            <td>S/ {{ number_format((float) $pedido->total, 2) }}</td>
                            <td><a href="{{ route('tienda.admin.pedidos.show', $pedido) }}" class="btn btn-sm btn-primary">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No hay pedidos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $pedidos->links() }}
    </div>
</div>
@endsection
