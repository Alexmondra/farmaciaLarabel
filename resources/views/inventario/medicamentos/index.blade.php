@extends('adminlte::page')

@section('title','Medicamentos')

@section('content_header')
<h1>Medicamentos</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        {{-- Filtros --}}
        <form method="GET" action="{{ route('inventario.medicamentos.index') }}" class="row g-2 mb-3">
            <div class="col-md-3">
                <label class="form-label">Sucursal</label>
                <select name="sucursal_id" class="form-control" onchange="this.form.submit()">
                    @if($esAdmin)
                    <option value="" {{ empty($sucursalFiltro) ? 'selected' : '' }}>Todas las sucursales</option>
                    @endif
                    @foreach($sucursalesDisponibles as $s)
                    <option value="{{ $s->id }}" {{ (string)$sucursalFiltro===(string)$s->id ? 'selected':'' }}>
                        {{ $s->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Buscar</label>
                <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Nombre, código, barras, laboratorio">
            </div>
            <div class="col-md-5 d-flex align-items-end">
                <button class="btn btn-primary me-2">Aplicar</button>
                <a href="{{ route('inventario.medicamentos.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                <a href="{{ route('inventario.medicamentos.create') }}" class="btn btn-success ms-auto">
                    <i class="fas fa-plus"></i> Nuevo
                </a>
            </div>
        </form>

        {{-- Mensajes --}}
        @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

        {{-- Banner admin en "todas" --}}
        @if($esAdmin && empty($sucursalFiltro))
        <div class="alert alert-info">Viendo <b>todas</b> las sucursales. Para <b>editar o eliminar</b>, selecciona una sucursal.</div>
        @endif

        {{-- Tabla --}}
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Código</th>
                        <th>Código de barras</th>
                        <th>Categoría</th>
                        @if($esAdmin && empty($sucursalFiltro))
                        <th class="text-end">Stock total</th>
                        <th>Desglose</th>
                        @else
                        <th class="text-end">Stock</th>
                        <th>Precios</th>
                        <th>Ubicación</th>
                        @endif
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicamentos as $m)
                    <tr>
                        <td>{{ $m->nombre }}</td>
                        <td>{{ $m->codigo }}</td>
                        <td>{{ $m->codigo_barras ?? '-' }}</td>
                        <td>{{ $m->categoria->nombre ?? '-' }}</td>

                        @if($esAdmin && empty($sucursalFiltro))
                        <td class="text-end">{{ $m->stock_total ?? 0 }}</td>
                        <td>
                            @php
                            $map = $m->desglose_stock ?? [];
                            $index = $sucursalesDisponibles->keyBy('id');
                            @endphp
                            <details>
                                <summary>Ver</summary>
                                <ul class="mb-0">
                                    @forelse($map as $sid => $stk)
                                    <li>{{ $index[$sid]->nombre ?? ('Suc. #'.$sid) }}: {{ $stk }}</li>
                                    @empty
                                    <li>Sin stock</li>
                                    @endforelse
                                </ul>
                            </details>
                        </td>
                        @else
                        <td class="text-end">{{ $m->stock ?? 0 }}</td>
                        <td>
                            <div class="small">
                                <div><strong>V:</strong> {{ $m->precio_v !== null ? number_format($m->precio_v, 2) : '-' }}</div>
                                <div><strong>C:</strong> {{ $m->precio_c !== null ? number_format($m->precio_c, 2) : '-' }}</div>
                            </div>
                        </td>
                        <td>{{ $m->ubicacion ?? '-' }}</td>
                        @endif

                        <td class="text-center">
                            <a href="{{ route('inventario.medicamentos.show', $m->id) }}" class="btn btn-sm btn-outline-info">Ver</a>

                            @if($esAdmin && empty($sucursalFiltro))
                            <button class="btn btn-sm btn-outline-secondary" disabled title="Selecciona sucursal">Editar</button>
                            <button class="btn btn-sm btn-outline-danger" disabled title="Selecciona sucursal">Eliminar</button>
                            @else
                            <a href="{{ route('inventario.medicamentos.editSucursal', [$m->id, $sucursalFiltro]) }}" class="btn btn-sm btn-primary">
                                Editar
                            </a>

                            <form method="POST" action="{{ route('inventario.medicamentos.detachSucursal', [$m->id, $sucursalFiltro]) }}"
                                class="d-inline" onsubmit="return confirm('¿Eliminar solo de esta sucursal?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Eliminar</button>
                            </form>
                            @endif
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Sin registros.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $medicamentos->links() }}
    </div>
</div>
@stop