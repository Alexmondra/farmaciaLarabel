@extends('tienda.layout')

@section('title', 'Validar recojo')

@section('content')
<div class="store-card bg-white p-4">
    <h1 class="h3">Pedido {{ $pedido->codigo }}</h1>
    <p class="mb-1">Cliente: <strong>{{ $pedido->cliente_nombre }}</strong></p>
    <p class="mb-1">Sucursal: <strong>{{ $pedido->sucursal->nombre ?? '-' }}</strong></p>
    <p class="mb-1">Estado: <strong>{{ $pedido->estado }}</strong></p>
    <p class="mb-0">Total: <strong>S/ {{ number_format((float) $pedido->total, 2) }}</strong></p>
</div>
@endsection
