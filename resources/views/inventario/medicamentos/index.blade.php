@extends('adminlte::page')

@section('title','Medicamentos')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-pills"></i> Medicamentos</h1>
    <a href="{{ route('inventario.medicamentos.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Medicamento
    </a>
</div>
@stop

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif

<!-- Filtros y Búsqueda -->
<div class="card mb-4">
    <div class="card-header">
        <h5><i class="fas fa-filter"></i> Filtros y Búsqueda</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-4">
                <label>Buscar medicamento</label>
                <div class="input-group">
                    <input type="text" name="q" class="form-control" value="{{ $q }}" placeholder="Nombre, código o laboratorio">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            @if($sucursalesDisponibles->count() > 1)
            <div class="col-md-4">
                <label>Filtrar por sucursal</label>
                <select name="sucursal_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Todas las sucursales</option>
                    @foreach($sucursalesDisponibles as $sucursal)
                    <option value="{{ $sucursal->id }}" {{ $sucursalFiltro == $sucursal->id ? 'selected' : '' }}>
                        {{ $sucursal->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif
            
            <div class="col-md-4 d-flex align-items-end">
                <a href="{{ route('inventario.medicamentos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Limpiar filtros
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Medicamentos -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-list"></i> Lista de Medicamentos ({{ $medicamentos->total() }} resultados)</h5>
    </div>
    <div class="card-body p-0">
        @if($medicamentos->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="80">Imagen</th>
                        <th width="120">Código</th>
                        <th>Medicamento</th>
                        <th width="120">Categoría</th>
                        <th width="120">Laboratorio</th>
                        <th width="100">Stock</th>
                        <th width="100">Precio</th>
                        <th width="80">Estado</th>
                        <th width="120">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($medicamentos as $m)
                    @php
                        // Usar datos ya calculados en el controlador
                        $pivot = $m->pivot_data;
                        $stockReal = $m->stock_real ?? 0;
                        $precioVenta = $pivot?->precio_venta ?? 0;
                        $stockMinimo = $pivot?->stock_minimo ?? 0;
                        $isStockBajo = $stockReal <= $stockMinimo;
                    @endphp
                    <tr class="{{ $isStockBajo ? 'table-warning' : '' }}">
                        <td>
                            @if($m->imagen_path)
                            <img src="{{ asset('storage/'.$m->imagen_path) }}" 
                                 width="60" height="60" 
                                 class="rounded shadow-sm" 
                                 style="object-fit: cover;"
                                 alt="{{ $m->nombre }}">
                            @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center shadow-sm" 
                                 style="width:60px;height:60px;">
                                <i class="fas fa-pills text-muted fa-lg"></i>
                            </div>
                            @endif
                        </td>
                        <td>
                            <code class="text-primary">{{ $m->codigo }}</code>
                            @if($m->codigo_barra)
                            <br><small class="text-muted">{{ $m->codigo_barra }}</small>
                            @endif
                        </td>
                        <td>
                            <div>
                                <strong class="text-dark">{{ $m->nombre }}</strong>
                                @if($m->concentracion)
                                <br><small class="text-info">{{ $m->concentracion }}</small>
                                @endif
                                @if($m->presentacion)
                                <br><small class="text-muted">{{ $m->presentacion }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $m->categoria->nombre ?? 'Sin categoría' }}</span>
                        </td>
                        <td>
                            <small class="text-muted">{{ $m->laboratorio ?? '—' }}</small>
                        </td>
                        <td>
                            <div class="text-center">
                                <span class="badge badge-{{ $isStockBajo ? 'warning' : 'success' }} badge-lg">
                                    {{ $stockReal }}
                                </span>
                                @if($isStockBajo)
                                <br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Stock bajo</small>
                                @endif
                                @if($pivot?->ubicacion)
                                <br><small class="text-muted"><i class="fas fa-map-marker-alt"></i> {{ $pivot->ubicacion }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <strong class="text-success">S/ {{ number_format($precioVenta, 2) }}</strong>
                        </td>
                        <td>
                            <span class="badge badge-{{ $m->activo ? 'success' : 'danger' }}">
                                {{ $m->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group-vertical btn-group-sm" role="group">
                                <a href="{{ route('inventario.medicamentos.show',$m) }}" 
                                   class="btn btn-info btn-sm" 
                                   title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('inventario.medicamentos.edit',$m) }}" 
                                   class="btn btn-warning btn-sm" 
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('inventario.medicamentos.destroy',$m) }}" 
                                      method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirm('¿Eliminar este medicamento?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No se encontraron medicamentos</h5>
            <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
            <a href="{{ route('inventario.medicamentos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Crear primer medicamento
            </a>
        </div>
        @endif
    </div>
    
    @if($medicamentos->hasPages())
    <div class="card-footer">
        {{ $medicamentos->links() }}
    </div>
    @endif
</div>
@stop

@section('css')
<style>
.badge-lg {
    font-size: 0.9em;
    padding: 0.5em 0.75em;
}
.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,0.1);
}
</style>
@stop