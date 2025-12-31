@extends('adminlte::page')

@section('title', 'Compras')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Compras</h1>

    @can('compras.crear')
    <a href="{{ route('compras.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nueva compra
    </a>
    @endcan
</div>
@endsection

@section('content')

<div class="card">
    <div class="card-body table-responsive p-0">
        {{-- CAMBIO 1: Se remueve la clase 'text-nowrap' para permitir que el texto se ajuste en móvil --}}
        <table class="table table-hover">
            <thead class="bg-light">
                <tr>
                    <th style="width: 15%;">Fecha</th>
                    <th style="width: 15%;" class="d-none d-sm-table-cell">Documento</th> {{-- OCULTO EN MÓVIL PEQUEÑO --}}
                    <th style="width: 25%;">Proveedor</th>
                    <th style="width: 15%;" class="d-none d-md-table-cell">Sucursal</th> {{-- OCULTO EN MÓVIL/TABLET --}}
                    <th style="width: 10%;">Estado</th>
                    <th style="width: 10%;" class="text-end">Total</th>
                    <th style="width: 10%;" class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($compras as $c)

                <tr>
                    {{-- FECHA --}}
                    <td>
                        {{ $c->fecha_recepcion
                                ? $c->fecha_recepcion->format('d/m/Y')
                                : '-' }}
                    </td>

                    {{-- DOCUMENTO --}}
                    <td class="d-none d-sm-table-cell">
                        {{ $c->tipo_comprobante ?? '' }}
                        <br>
                        <small class="text-muted">{{ $c->numero_factura_proveedor ?? '-' }}</small>
                    </td>

                    {{-- PROVEEDOR --}}
                    <td>
                        {{ $c->proveedor->razon_social ?? '-' }}
                        <br>
                        <small class="text-muted">{{ $c->proveedor->ruc ?? '-' }}</small>
                    </td>

                    {{-- SUCURSAL --}}
                    <td class="d-none d-md-table-cell">
                        {{ $c->sucursal->nombre ?? '-' }}
                    </td>

                    {{-- ESTADO --}}
                    <td>
                        @if($c->estado == 'registrada')
                        <span class="badge badge-success">REGISTRADA</span>
                        @elseif($c->estado == 'anulada')
                        <span class="badge badge-danger">ANULADA</span>
                        @else
                        <span class="badge badge-secondary">{{ strtoupper($c->estado) }}</span>
                        @endif
                    </td>

                    {{-- TOTAL --}}
                    <td class="text-end font-weight-bold">
                        S/ {{ number_format($c->costo_total_factura, 2) }}
                    </td>

                    {{-- ACCIONES --}}
                    <td class="text-end">
                        {{-- VER DETALLE --}}
                        <a href="{{ route('compras.show', $c->id) }}" class="btn btn-sm btn-info" title="Ver Detalle">
                            <i class="fas fa-eye"></i>
                            {{-- CAMBIO 2: Oculta el texto "Ver" solo en móviles pequeños, dejando solo el icono --}}
                            <span class="d-none d-sm-inline">Ver</span>
                        </a>

                        {{-- PROTECCIÓN CON PERMISO --}}
                        @can('compras.eliminar')
                        @if($c->estado !== 'anulada')
                        <form method="POST"
                            action="{{ route('compras.destroy', $c->id) }}"
                            class="d-inline"
                            onsubmit="return confirm('⚠️ ¿Estás seguro? Al anular, se descontará el stock de los productos.');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" title="Anular Compra">
                                <i class="fas fa-ban"></i>
                                {{-- CAMBIO 3: Oculta el texto "Anular" solo en móviles pequeños, dejando solo el icono --}}
                                <span class="d-none d-sm-inline">Anular</span>
                            </button>
                        </form>
                        @else
                        <button class="btn btn-sm btn-secondary" disabled>
                            <i class="fas fa-ban"></i>
                        </button>
                        @endif
                        @endcan
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