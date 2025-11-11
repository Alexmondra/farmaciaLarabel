@extends('adminlte::page')

@section('title','Detalle medicamento')

@section('content_header')
<h1>{{ $medicamento->nombre }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                @if($medicamento->imagen_path)
                <img src="{{ asset('storage/'.$medicamento->imagen_path) }}" class="img-fluid rounded" alt="Imagen">
                @else
                <div class="text-muted">Sin imagen</div>
                @endif
                <hr>
                <div><b>Código:</b> {{ $medicamento->codigo }}</div>
                <div><b>Código barras:</b> {{ $medicamento->codigo_barras ?? '-' }}</div>
                <div><b>Laboratorio:</b> {{ $medicamento->laboratorio ?? '-' }}</div>
                <div><b>Categoría:</b> {{ $medicamento->categoria->nombre ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                @if($medicamento->sucursales->isEmpty())
                <div class="alert alert-warning">Este medicamento aún no está asociado a ninguna sucursal.</div>
                @else
                <ul class="nav nav-tabs" role="tablist">
                    @foreach($medicamento->sucursales as $i => $s)
                    <li class="nav-item">
                        <a class="nav-link {{ $i===0?'active':'' }}" data-bs-toggle="tab" href="#s{{ $s->id }}" role="tab">
                            {{ $s->nombre }}
                        </a>
                    </li>
                    @endforeach
                </ul>

                <div class="tab-content mt-3">
                    @foreach($medicamento->sucursales as $i => $s)
                    @php
                    $info = $bySucursal[$s->id] ?? ['stock'=>0,'lotes'=>collect()];
                    $pivot = $s->pivot;
                    @endphp
                    <div class="tab-pane fade {{ $i===0?'show active':'' }}" id="s{{ $s->id }}" role="tabpanel">
                        <div class="row mb-3">
                            <div class="col-md-4"><b>Precio:</b> {{ $pivot?->precio!==null ? number_format($pivot->precio,2) : '-' }}</div>
                            <div class="col-md-4"><b>Ubicación:</b> {{ $pivot?->ubicacion ?? '-' }}</div>
                            <div class="col-md-4"><b>Stock:</b> {{ $info['stock'] ?? 0 }}</div>
                        </div>

                        <h6>Lotes</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Código</th>
                                        <th>Cantidad</th>
                                        <th>Vencimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($info['lotes'] as $l)
                                    <tr>
                                        <td>{{ $l->id }}</td>
                                        <td>{{ $l->codigo ?? '-' }}</td>
                                        <td>{{ $l->cantidad_actual }}</td>
                                        <td>{{ $l->fecha_vencimiento ?? '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-muted">Sin lotes</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <a href="{{ route('inventario.medicamentos.editSucursal', [$medicamento->id, $s->id]) }}" class="btn btn-primary btn-sm">
                            Editar en {{ $s->nombre }}
                        </a>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop