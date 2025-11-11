@extends('adminlte::page')

@section('title', 'Editar por sucursal')

@section('content_header')
<h1>Editar en {{ $sucursal->nombre }} — {{ $m->nombre }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        {{-- Mensajes --}}
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Datos generales --}}
        <div class="row g-3 mb-3 small text-muted">
            <div class="col-md-3"><b>Código:</b> {{ $m->codigo }}</div>
            <div class="col-md-3"><b>Código barras:</b> {{ $m->codigo_barras ?? '-' }}</div>
            <div class="col-md-3"><b>Categoría:</b> {{ $m->categoria->nombre ?? '-' }}</div>
            <div class="col-md-3"><b>Stock actual:</b> {{ $pivot->stock_actual ?? 0 }}</div>
        </div>

        {{-- Formulario --}}
        <form method="POST" action="{{ route('inventario.medicamentos.updateSucursal', [$m->id, $sucursal->id]) }}" class="row g-3">
            @csrf @method('PUT')

            <div class="col-md-4">
                <label class="form-label">Precio venta (V)</label>
                <input type="number" step="0.01" name="precio_v" class="form-control"
                    value="{{ old('precio_v', $pivot->precio_venta ?? '') }}" placeholder="0.00">
            </div>

            <div class="col-md-4">
                <label class="form-label">Precio compra (C)</label>
                <input type="number" step="0.01" name="precio_c" class="form-control"
                    value="{{ old('precio_c', $pivot->precio_compra ?? '') }}" placeholder="0.00">
            </div>

            <div class="col-md-4">
                <label class="form-label">Stock mínimo</label>
                <input type="number" name="stock_minimo" class="form-control"
                    value="{{ old('stock_minimo', $pivot->stock_minimo ?? '') }}" placeholder="0">
            </div>

            <div class="col-md-6">
                <label class="form-label">Ubicación</label>
                <input type="text" name="ubicacion" class="form-control"
                    value="{{ old('ubicacion', $pivot->ubicacion ?? '') }}" placeholder="Ej. Pasillo A - Estante 3">
            </div>

            <div class="col-md-6 d-flex align-items-end justify-content-end">
                <a href="{{ route('inventario.medicamentos.index', ['sucursal_id' => $sucursal->id]) }}"
                    class="btn btn-outline-secondary me-2">Volver</a>
                <button class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>

        <hr>

        {{-- Lotes --}}
        <h5 class="mb-3">Lotes en {{ $sucursal->nombre }}</h5>
        @php $lotes = $m->lotes ?? collect(); @endphp

        @if($lotes->isEmpty())
        <div class="text-muted">Sin lotes registrados para este medicamento.</div>
        @else
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th class="text-end">Cantidad</th>
                        <th>Vencimiento</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lotes as $l)
                    @php
                    $porVencer = $l->fecha_vencimiento ? \Carbon\Carbon::parse($l->fecha_vencimiento)->isBefore(now()->addMonths(1)) : false;
                    $vencido = $l->fecha_vencimiento ? \Carbon\Carbon::parse($l->fecha_vencimiento)->isBefore(today()) : false;
                    @endphp
                    <tr>
                        <td>{{ $l->id }}</td>
                        <td>{{ $l->codigo ?? '-' }}</td>
                        <td class="text-end">{{ $l->cantidad_actual }}</td>
                        <td>{{ $l->fecha_vencimiento ? \Carbon\Carbon::parse($l->fecha_vencimiento)->format('d/m/Y') : '-' }}</td>
                        <td>
                            @if($vencido)
                            <span class="badge bg-danger">Vencido</span>
                            @elseif($porVencer)
                            <span class="badge bg-warning text-dark">Por vencer</span>
                            @else
                            <span class="badge bg-success">Vigente</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>
</div>
@stop