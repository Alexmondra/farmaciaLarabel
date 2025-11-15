@extends('adminlte::page')

@section('title', 'Detalle de medicamento')

@section('content_header')
<h1>Detalle de medicamento</h1>
<p class="text-muted mb-0">
    <strong>{{ $medicamento->nombre }}</strong>
    @if($sucursalSeleccionada)
    <span class="ml-2 badge bg-primary">
        Sucursal actual: {{ $sucursalSeleccionada->nombre }}
    </span>
    @endif
</p>
@stop

@section('content')

{{-- INFO GENERAL DEL MEDICAMENTO --}}
<div class="card mb-3">
    <div class="card-header bg-primary text-white">
        Información general
    </div>
    <div class="card-body">
        <div class="row">

            <div class="col-md-6">
                <p><strong>Código interno:</strong> {{ $medicamento->codigo }}</p>
                <p><strong>Código de barra:</strong> {{ $medicamento->codigo_barra ?? '---' }}</p>
                <p><strong>Laboratorio:</strong> {{ $medicamento->laboratorio ?? '---' }}</p>
            </div>

            <div class="col-md-6">
                <p><strong>Categoría:</strong> {{ $medicamento->categoria->nombre ?? '---' }}</p>
                {{-- Aquí podrías mostrar más campos generales si los tienes --}}
            </div>

        </div>
    </div>
</div>

{{-- DETALLE POR SUCURSAL --}}
@forelse($sucursalesDetalle as $detalle)
@php
/** @var \App\Models\Sucursal $sucursal */
$sucursal = $detalle['sucursal'];
$stockTotal = $detalle['stock_total'];
$precio = $detalle['precio'];
$lotes = $detalle['lotes'];
@endphp

<div class="card mb-4">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <div>
            <strong>Sucursal:</strong> {{ $sucursal->nombre }}
        </div>
        <div>
            @if(!is_null($precio))
            <strong>Precio venta:</strong> S/. {{ number_format($precio, 2) }}
            @else
            <span class="text-light">Precio venta: ---</span>
            @endif
            <span class="ml-3">
                <strong>Stock total:</strong> {{ $stockTotal }}
            </span>
        </div>
    </div>

    <div class="card-body p-0">
        @if($lotes->isEmpty())
        <p class="p-3 mb-0 text-muted">
            No hay lotes registrados para esta sucursal.
        </p>
        @else
        <table class="table table-sm table-striped mb-0">
            <thead>
                <tr>
                    <th style="width: 15%">Código lote</th>
                    <th style="width: 20%">Ubicación</th>
                    <th style="width: 20%">Fecha vencimiento</th>
                    <th style="width: 15%" class="text-end">Stock</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lotes as $lote)
                <tr>
                    <td>{{ $lote->codigo_lote }}</td>
                    <td>{{ $lote->ubicacion ?? '---' }}</td>
                    <td>{{ $lote->fecha_vencimiento ?? '---' }}</td>
                    <td class="text-end">{{ $lote->stock_actual }}</td>
                    <td>{{ $lote->observaciones ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

@empty
<div class="alert alert-warning">
    Este medicamento no está disponible en ninguna de las sucursales que puedes ver.
</div>
@endforelse

<a href="{{ route('inventario.medicamentos.index') }}" class="btn btn-secondary">
    Volver al listado
</a>

@stop