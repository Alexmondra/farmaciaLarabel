@extends('adminlte::page')

@section('title', 'Medicamentos')

@section('content_header')
<h1 class="mb-3">Medicamentos</h1>

{{-- Buscador --}}
<form method="GET" action="{{ route('inventario.medicamentos.index') }}" class="mb-3">
    <div class="input-group">
        <input type="text" name="q" value="{{ $q }}" class="form-control"
            placeholder="Buscar por nombre, código, laboratorio...">
        <button class="btn btn-primary">
            <i class="fas fa-search"></i> Buscar
        </button>
    </div>
</form>
@endsection


@section('content')

{{-- Tabla --}}
<div class="card">
    <div class="card-body table-responsive p-0">

        <table class="table table-hover text-nowrap">
            <thead class="bg-light">
                <tr>
                    <th style="width: 30%">Nombre</th>
                    <th style="width: 15%">Categoría</th>
                    <th style="width: 20%">Stock</th>
                    <th style="width: 10%">Precio</th>
                    <th style="width: 25%" class="text-end">Acciones</th>
                </tr>
            </thead>

            <tbody>

                @forelse($medicamentos as $m)
                <tr>
                    {{-- Nombre --}}
                    <td>
                        <strong>{{ $m->nombre }}</strong><br>
                        <small class="text-muted">Código: {{ $m->codigo ?? '-' }}</small>
                    </td>

                    {{-- Categoría --}}
                    <td>{{ $m->categoria->nombre ?? '-' }}</td>

                    {{-- STOCK --}}
                    <td>
                        @if($sucursalSeleccionada)
                        {{-- 🟢 UNA sola sucursal seleccionada --}}
                        <strong>{{ $m->stock_unico }}</strong>
                        <br>
                        <small class="text-muted">
                            {{ $sucursalSeleccionada->nombre }}
                        </small>

                        @else
                        {{-- 🔵 MULTI-SUCURSAL --}}
                        @php $lista = $m->stock_por_sucursal ?? collect(); @endphp

                        @if($lista->isEmpty())
                        <span class="text-muted">Sin stock</span>
                        @else
                        @foreach($lista as $item)
                        <div>
                            <strong>{{ $item['sucursal_name'] }}</strong>:
                            {{ $item['stock'] }}
                        </div>
                        @endforeach
                        @endif
                        @endif
                    </td>

                    {{-- Precio --}}
                    <td>
                        @if($sucursalSeleccionada && $m->precio_v)
                        S/ {{ number_format($m->precio_v, 2) }}
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- Acciones --}}
                    <td class="text-end">

                        {{-- Ver detalle --}}
                        <a href="{{ route('inventario.medicamentos.show', $m->id) }}"
                            class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                            Ver
                        </a>

                        {{-- Eliminar SOLO si hay sucursal seleccionada --}}
                        @if($sucursalSeleccionada)
                        <form method="POST"
                            action="{{ route('inventario.medicamento_sucursal.destroy', [
                                             'medicamento' => $m->id,
                                             'sucursal'    => $sucursalSeleccionada->id
                                      ]) }}"
                            class="d-inline"
                            onsubmit="return confirm('¿Eliminar este medicamento SOLO de la sucursal {{ $sucursalSeleccionada->nombre }}?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                                Eliminar
                            </button>
                        </form>
                        @endif

                    </td>
                </tr>

                @empty
                <tr>
                    <td colspan="5" class="text-center p-4 text-muted">
                        No se encontraron medicamentos.
                    </td>
                </tr>
                @endforelse

            </tbody>
        </table>

    </div>

    {{-- Paginación --}}
    @if ($medicamentos->hasPages())
    <div class="card-footer">
        {{ $medicamentos->links() }}
    </div>
    @endif
</div>

@endsection