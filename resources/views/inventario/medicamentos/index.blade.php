@extends('adminlte::page')

@section('title', 'Medicamentos')

@section('content_header')
<h1 class="text-primary">Listado de Medicamentos</h1>
@stop

@section('content')

{{-- BUSCADOR --}}
<form method="GET" class="mb-3">
    <div class="input-group" style="max-width: 380px;">
        <input type="text" class="form-control" name="q" value="{{ $q }}" placeholder="Buscar medicamento...">
        <button class="btn btn-primary" type="submit">Buscar</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">

        <table class="table table-striped table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th style="width: 28%">Medicamento</th>
                    <th style="width: 20%">Categoría</th>
                    <th style="width: 22%">Stock</th>
                    <th style="width: 12%" class="text-end">Precio</th>
                    <th style="width: 10%" class="text-end"></th>
                </tr>
            </thead>

            <tbody>
                @forelse($medicamentos as $m)
                <tr>
                    {{-- Medicamento --}}
                    <td>
                        <strong>{{ $m->nombre }}</strong><br>
                        <small class="text-muted">
                            Cod: {{ $m->codigo }}<br>
                            Barra: {{ $m->codigo_barra ?? '---' }}
                        </small>
                    </td>

                    {{-- Categoría --}}
                    <td>
                        {{ $m->categoria->nombre ?? '---' }}
                    </td>

                    {{-- STOCK --}}
                    <td>
                        @if($sucursalSeleccionada)
                        {{-- Solo una sucursal: mostrar 1 número --}}
                        <strong>{{ $m->stock_unico ?? 0 }}</strong>
                        <br>
                        <small class="text-muted">
                            {{ $sucursalSeleccionada->nombre }}
                        </small>
                        @else
                        {{-- Varias sucursales: desglose --}}
                        @php
                        $lista = $m->stock_por_sucursal ?? collect();
                        @endphp

                        @if($lista->isEmpty())
                        <span class="text-muted">Sin stock</span>
                        @else
                        @foreach($lista as $item)
                        <div>
                            <strong>{{ $item['sucursal_name'] }}</strong>
                            = {{ $item['stock'] }}
                        </div>
                        @endforeach
                        @endif
                        @endif
                    </td>

                    {{-- PRECIO --}}
                    <td class="text-end">
                        @if($m->precio_v)
                        <strong>S/. {{ number_format($m->precio_v, 2) }}</strong>
                        @else
                        <span class="text-muted">---</span>
                        @endif
                    </td>

                    {{-- ACCIONES --}}
                    <td class="text-end">

                        {{-- Botón VER (con icono) --}}
                        <a href="{{ route('inventario.medicamentos.show', $m->id) }}"
                            class="btn btn-sm btn-info mb-1"
                            title="Ver Detalle">
                            <i class="fas fa-eye"></i> {{-- Icono de "ver" --}}
                        </a>

                        {{-- Botón ELIMINAR (con icono) SOLO SI HAY sucursal seleccionada --}}
                        @if($sucursalSeleccionada)
                        <form method="POST"
                            action="{{ route('inventario.medicamento_sucursal.destroy', [
        'medicamento' => $m->id,
        'sucursal'    => $sucursalSeleccionada->id,
    ]) }}"
                            class="d-inline"
                            onsubmit="return confirm('¿Eliminar este medicamento SOLO de la sucursal {{ $sucursalSeleccionada->nombre }}?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger mb-1"
                                title="Eliminar en esta sucursal">
                                <i class="fas fa-trash"></i> {{-- Icono de "eliminar" --}}
                            </button>
                        </form>
                        @endif

                    </td>


                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center p-3 text-muted">
                        No se encontraron medicamentos.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    <div class="card-footer">
        {{ $medicamentos->links() }}
    </div>
</div>

@stop