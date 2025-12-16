@extends('adminlte::page')

@section('title', 'Historial de Ventas')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="text-dark">Historial de Ventas</h1>
    </div>

    <div class="d-flex">
        @if($cajaAbierta)
        {{-- CASO 1: CAJA ESTÁ ABIERTA--}}

        {{-- Botón A: Ir a la gestión de Caja (Para Cerrarla)--}}
        <a href="{{ route('cajas.show', $cajaAbierta->id) }}" class="btn btn-outline-danger mr-2 shadow-sm" title="Ir a arqueo y cierre">
            <i class="fas fa-cash-register mr-1"></i> Cerrar Caja
        </a>

        {{-- Botón B: Nueva Venta (Acción Principal Rápida)--}}
        <a href="{{ route('ventas.create') }}" class="btn btn-success shadow-sm">
            <i class="fas fa-plus-circle mr-1"></i> Nueva Venta
        </a>

        @else
        {{-- CASO 2: CAJA ESTÁ CERRADA--}}

        {{-- Botón único: Abrir Caja--}}
        <button class="btn btn-primary shadow-sm font-weight-bold" data-toggle="modal" data-target="#modalAbrirCaja">
            <i class="fas fa-unlock-alt mr-1"></i> ABRIR CAJA
        </button>
        @endif
    </div>
</div>
@stop

@section('content')

{{-- BARRA DE BÚSQUEDA Y FILTROS--}}
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h3 class="card-title text-muted"><i class="fas fa-filter mr-1"></i> Filtros de Búsqueda</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
        </div>
    </div>
    <div class="card-body py-2">
        <form action="{{ route('ventas.index') }}" method="GET">
            <div class="row align-items-end">
                {{-- 1. Buscador Texto (Prioridad)--}}
                <div class="col-md-4 mb-2">
                    <label class="small font-weight-bold">Buscar Ticket / Cliente</label>
                    <div class="input-group">
                        <input type="text" name="search_q" class="form-control"
                            placeholder="N° Boleta o Nombre..."
                            value="{{ request('search_q') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted">Si escribes aquí, se ignoran las fechas.</small>
                </div>

                {{-- 2. Fechas (Secundario)--}}
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold">Desde</label>
                    <input type="date" name="fecha_desde" class="form-control"
                        value="{{ request('fecha_desde', now()->format('Y-m-d')) }}">
                </div>

                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold">Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control"
                        value="{{ request('fecha_hasta', now()->format('Y-m-d')) }}">
                </div>

                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-default btn-block border">
                        <i class="fas fa-sync-alt mr-1"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- TABLA DE RESULTADOS --}}
<div class="card shadow-sm">
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    {{-- MOVER ACCIONES AL PRINCIPIO PARA MOBILE --}}
                    <th>Comprobante</th>
                    <th>Cliente</th>
                    <th class="d-none d-sm-table-cell">Emisión</th>
                    <th class="d-none d-md-table-cell">Vendedor</th>
                    <th class="d-none d-md-table-cell">Pago</th>
                    <th class="text-right">Total</th>
                    <th class="d-none d-sm-table-cell text-center">Estado</th>
                    <th class="text-center pr-3" style="width: 100px;">Acciones</th>

                </tr>
            </thead>
            <tbody>
                @forelse($ventas as $venta)
                <tr>
                    {{-- COMPROBANTE --}}
                    <td class="align-middle">
                        <span class="font-weight-bold text-primary">
                            {{ $venta->tipo_comprobante }}
                        </span>
                        <br>
                        <small class="text-muted">{{ $venta->serie }}-{{ $venta->numero }}</small>
                    </td>

                    {{-- CLIENTE (Permitir salto de línea) --}}
                    <td class="align-middle text-wrap small">
                        {{ Str::limit($venta->cliente->nombre ?? 'Público', 25) }}
                    </td>

                    {{-- EMISIÓN (Ocultar en Móviles pequeños) --}}
                    <td class="d-none d-sm-table-cell align-middle small">{{ $venta->fecha_emision->format('d/m/Y H:i') }}</td>

                    {{-- VENDEDOR y PAGO (Ocultar en Móviles/Tablet) --}}
                    <td class="d-none d-md-table-cell align-middle small">{{ $venta->usuario->name ?? 'N/A' }}</td>
                    <td class="d-none d-md-table-cell align-middle"><span class="badge badge-light border">{{ $venta->medio_pago }}</span></td>

                    {{-- TOTAL --}}
                    <td class="align-middle text-right font-weight-bold">
                        S/ {{ number_format($venta->total_neto, 2) }}
                    </td>

                    {{-- ESTADO (Ocultar en Móviles pequeños) --}}
                    <td class="d-none d-sm-table-cell align-middle text-center">
                        @if($venta->estado == 'ANULADO')
                        <span class="badge badge-danger">ANULADO</span>
                        @else
                        <span class="badge badge-success">EMITIDA</span>
                        @endif
                    </td>

                    {{-- ACCIONES --}}
                    <td class="align-middle text-center pr-3">
                        <a href="{{ route('ventas.show', $venta->id) }}" class="btn btn-sm btn-info shadow-sm py-0 px-1" title="Ver Detalle">
                            <i class="fas fa-eye"></i>
                        </a>

                        @if($venta->estado !== 'ANULADO')
                        <form action="{{ route('ventas.anular', $venta->id) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('¿Estás seguro de anular esta venta? Se emitirá una Nota de Crédito a SUNAT y se devolverá el stock.');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger shadow-sm py-0 px-1" title="Anular Venta">
                                <i class="fas fa-ban"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i><br>
                        No se encontraron ventas con los filtros seleccionados.<br>
                        <small>Intenta cambiar las fechas o borrar la búsqueda.</small>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($ventas->hasPages())
    <div class="card-footer bg-white py-3">
        {{ $ventas->appends(request()->input())->links() }}
    </div>
    @endif
</div>

{{-- MODAL DE APERTURA (Requerido si la caja está cerrada)--}}
@include('ventas.cajas._modal_apertura', ['sucursalesParaApertura' => $sucursalesParaApertura])

@stop

@push('css')
<style>
    .pagination {
        justify-content: center;
        margin-bottom: 0;
    }

    /* Ajuste para que la tabla sea más compacta en móvil */
    @media (max-width: 767.98px) {

        .table td,
        .table th {
            padding: 0.4rem !important;
            vertical-align: middle;
        }

        /* Oculta campos menos críticos en móviles muy pequeños (< 576px) */
        /* Ya usamos d-none/d-sm-table-cell, esto es solo por si acaso */
        /* Se usa white-space: normal para evitar el table-responsive de ocultar texto */
        .table-responsive .table {
            white-space: normal;
        }

        /* Fuerza la compresión de los botones de acción */
        .table-responsive .btn-group,
        .table-responsive .d-inline {
            display: flex;
            gap: 2px;
        }
    }
</style>
@endpush