@extends('adminlte::page')

@section('title', 'Detalle de medicamento')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Detalle de medicamento</h1>

    <a href="{{ route('inventario.medicamentos.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
</div>
@endsection

@section('content')

{{-- DATOS GENERALES DEL MEDICAMENTO --}}
<div class="card mb-4">
    <div class="card-header">
        <strong>{{ $medicamento->nombre }}</strong>
    </div>
    <div class="card-body">

        <div class="row mb-2">
            <div class="col-md-4">
                <label class="text-muted mb-0">Código</label><br>
                <span>{{ $medicamento->codigo ?? '—' }}</span>
            </div>

            <div class="col-md-4">
                <label class="text-muted mb-0">Código de barra</label><br>
                <span>{{ $medicamento->codigo_barra ?? '—' }}</span>
            </div>

            <div class="col-md-4">
                <label class="text-muted mb-0">Categoría</label><br>
                <span>{{ $medicamento->categoria->nombre ?? '—' }}</span>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <label class="text-muted mb-0">Laboratorio</label><br>
                <span>{{ $medicamento->laboratorio ?? '—' }}</span>
            </div>

            <div class="col-md-4">
                <label class="text-muted mb-0">Concentración</label><br>
                <span>{{ $medicamento->concentracion ?? '—' }}</span>
            </div>

            <div class="col-md-4">
                <label class="text-muted mb-0">Presentación</label><br>
                <span>{{ $medicamento->presentacion ?? '—' }}</span>
            </div>
        </div>

    </div>
</div>


{{-- DETALLE POR SUCURSAL --}}
@forelse($sucursalesDetalle as $item)
@php
/** @var \App\Models\Sucursal $sucursal */
$sucursal = $item['sucursal'];
$precio = $item['precio'];
$stock_total = $item['stock_total'];
$lotes = $item['lotes'];
@endphp

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">

        <div>
            <h5 class="mb-0">
                Sucursal: <strong>{{ $sucursal->nombre }}</strong>
            </h5>
            <small class="text-muted">
                Stock total: {{ $stock_total }} unidades
            </small>
        </div>

        <div class="text-end">
            <div class="mb-1">
                <span class="text-muted">Precio de venta:</span>
                @if(!is_null($precio))
                <strong>S/ {{ number_format($precio, 2) }}</strong>
                @else
                <span class="text-muted">No definido</span>
                @endif
            </div>

            {{-- Botón para “eliminar” (desactivar) SOLO en esta sucursal --}}
            <form method="POST"
                action="{{ route('inventario.medicamento_sucursal.destroy', [
                                'medicamento' => $medicamento->id,
                                'sucursal'    => $sucursal->id,
                          ]) }}"
                class="d-inline"
                onsubmit="return confirm('¿Desactivar este medicamento SOLO en la sucursal {{ $sucursal->nombre }}?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i>
                    Eliminar en esta sucursal
                </button>
            </form>
        </div>

    </div>

    <div class="card-body p-0">

        {{-- LOTES DE ESTA SUCURSAL --}}
        @if($lotes->isEmpty())
        <div class="p-3 text-muted">
            No hay lotes registrados para esta sucursal.
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Código lote</th>
                        <th>Stock</th>
                        <th>F. vencimiento</th>
                        <th>Ubicación</th>
                        <th>Precio compra</th>
                        <th>Precio oferta</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lotes as $lote)
                    <tr>
                        <td>{{ $lote->codigo_lote }}</td>
                        <td>{{ $lote->stock_actual }}</td>
                        <td>
                            {{ $lote->fecha_vencimiento
                                            ? \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y')
                                            : '—' }}
                        </td>
                        <td>{{ $lote->ubicacion ?? '—' }}</td>
                        <td>
                            @if(!is_null($lote->precio_compra))
                            S/ {{ number_format($lote->precio_compra, 4) }}
                            @else
                            —
                            @endif
                        </td>
                        <td>
                            @if(!is_null($lote->precio_oferta))
                            S/ {{ number_format($lote->precio_oferta, 2) }}
                            @else
                            —
                            @endif
                        </td>
                        <td>{{ $lote->observaciones ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>
</div>

@empty
<div class="alert alert-info">
    Este medicamento no está asociado a ninguna sucursal visible para tu usuario.
</div>
@endforelse

@endsection