@extends('adminlte::page')

@section('title', 'Compras')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Compras</h1>

    <a href="{{ route('compras.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nueva compra
    </a>
</div>
@endsection

@section('content')

<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead class="bg-light">
                <tr>
                    <th>Fecha</th>
                    <th>Documento</th>
                    <th>Proveedor</th>
                    <th>Sucursal</th>
                    <th>Estado</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($compras as $c)
                @php
                $total = $c->detalles->sum(fn($d) => $d->cantidad * $d->precio_compra_unitario);
                @endphp
                <tr>
                    {{-- FECHA --}}
                    <td>
                        {{ $c->fecha_recepcion
                                ? $c->fecha_recepcion->format('d/m/Y')
                                : '-' }}
                    </td>

                    {{-- DOCUMENTO --}}

                    <td>
                        {{ $c->proveedor->ruc ?? '-' }}
                    </td>



                    {{-- PROVEEDOR --}}

                    <td>{{ $c->proveedor->razon_social ?? '-' }}</td>

                    {{-- SUCURSAL --}}
                    <td>{{ $c->sucursal->nombre ?? '-' }}</td>

                    {{-- ESTADO (numérico → texto) --}}
                    <td>
                        @switch($c->estado)
                        @case('registrada')
                        <span class="badge bg-secondary">Registrada</span>
                        @break

                        @case('recibida')
                        <span class="badge bg-success">Recibida</span>
                        @break

                        @case('pendiente')
                        <span class="badge bg-warning text-dark">Pendiente</span>
                        @break

                        @case('anulada')
                        <span class="badge bg-danger">Anulada</span>
                        @break

                        @default
                        <span class="badge bg-light text-muted">Desconocido</span>
                        @endswitch
                    </td>


                    {{-- TOTAL --}}
                    <td class="text-end">
                        {{ $c->costo_total_factura ?? '-' }}
                    </td>

                    {{-- ACCIONES --}}
                    <td class="text-end">
                        <a href="{{ route('compras.show', $c->id) }}"
                            class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> Ver
                        </a>

                        <a href="{{ route('compras.edit', $c->id) }}"
                            class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Editar
                        </a>

                        @if($c->estado != 2)
                        <form method="POST"
                            action="{{ route('compras.destroy', $c->id) }}"
                            class="d-inline"
                            onsubmit="return confirm('¿Anular esta compra?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                <i class="fas fa-ban"></i> Anular
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center p-4 text-muted">
                        No hay compras registradas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($compras->hasPages())
    <div class="card-footer">
        {{ $compras->links() }}
    </div>
    @endif
</div>

@endsection