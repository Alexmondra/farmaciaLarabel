@extends('adminlte::page')

@section('title', 'Productos tienda')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Productos tienda</h1>
    <a href="{{ route('tienda.admin.productos.create') }}" class="btn btn-primary">Publicar producto</a>
</div>
@endsection

@section('content')
@include('tienda.partials.alerts')
<div class="card">
    <div class="card-body">
        <form method="GET" class="row mb-3">
            <div class="col-md-10">
                <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar producto por nombre o codigo">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-block">Buscar</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Sucursal</th>
                        <th>Precio web</th>
                        <th>Stock</th>
                        <th>Visible</th>
                        <th>Destacado</th>
                        <th>Slug</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productos as $producto)
                        <tr>
                            <td>
                                <strong>{{ $producto->nombre }}</strong><br>
                                <small class="text-muted">{{ $producto->medicamento->codigo ?? '' }}</small>
                            </td>
                            <td>{{ $producto->sucursal->nombre ?? '-' }}</td>
                            <td>{{ $producto->precio_web ? 'S/ ' . number_format((float) $producto->precio_web, 2) : 'Precio de sucursal' }}</td>
                            <td>
                                @if($producto->stock_modo === 'sin_control')
                                    Sin control
                                @elseif($producto->stock_modo === 'stock_manual')
                                    Manual: {{ $producto->stock_web ?? 0 }}
                                @else
                                    Sucursal: {{ $producto->stockDisponible() }}
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $producto->visible ? 'success' : 'secondary' }}">
                                    {{ $producto->visible ? 'Si' : 'No' }}
                                </span>
                            </td>
                            <td>{{ $producto->destacado ? 'Si' : 'No' }}</td>
                            <td><code>{{ $producto->slug }}</code></td>
                            <td class="text-right">
                                <a href="{{ route('tienda.productos.show', $producto->slug) }}" target="_blank" class="btn btn-sm btn-outline-info">Ver</a>
                                <a href="{{ route('tienda.admin.productos.edit', $producto) }}" class="btn btn-sm btn-primary">Editar</a>
                                <form method="POST" action="{{ route('tienda.admin.productos.destroy', $producto) }}" class="d-inline" onsubmit="return confirm('Retirar producto de la tienda?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Retirar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">No hay productos publicados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $productos->links() }}
    </div>
</div>
@endsection
