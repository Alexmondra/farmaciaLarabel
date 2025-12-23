@extends('adminlte::page')

@section('title', 'Monitor SUNAT')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-satellite-dish text-orange mr-2"></i> Monitor de Envíos
        <small class="text-muted ml-2" style="font-size: 1rem">Gestión de errores y reintentos</small>
    </h1>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-12">

        {{-- TARJETA PRINCIPAL --}}
        <div class="card card-outline card-orange shadow-lg">
            <div class="card-header border-0">
                <h3 class="card-title text-bold text-dark">
                    <i class="fas fa-exclamation-circle text-danger mr-1"></i> Comprobantes Pendientes de Rectificación
                </h3>
                <div class="card-tools">
                    <span class="badge badge-danger px-3 py-2" style="font-size: 0.9rem">
                        {{ $ventas->total() }} Errores encontrados
                    </span>
                </div>
            </div>

            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 15%" class="text-secondary text-xs uppercase pl-4">EMISIÓN</th>
                            <th style="width: 20%" class="text-secondary text-xs uppercase">COMPROBANTE</th>
                            <th style="width: 25%" class="text-secondary text-xs uppercase">CLIENTE</th>
                            <th style="width: 25%" class="text-secondary text-xs uppercase">DIAGNÓSTICO SUNAT</th>
                            <th style="width: 15%" class="text-secondary text-xs uppercase text-right pr-4">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ventas as $venta)
                        <tr>
                            {{-- 1. FECHA --}}
                            <td class="pl-4">
                                <span class="d-block font-weight-bold text-dark">
                                    {{ $venta->fecha_emision->format('d/m/Y') }}
                                </span>
                                <small class="text-muted">
                                    <i class="far fa-clock mr-1"></i>{{ $venta->fecha_emision->format('H:i A') }}
                                </small>
                            </td>

                            {{-- 2. COMPROBANTE --}}
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="icon-box mr-3 text-center rounded p-2 {{ $venta->tipo_comprobante == 'FACTURA' ? 'bg-primary' : 'bg-info' }}" style="width: 40px; opacity: 0.9">
                                        <i class="fas {{ $venta->tipo_comprobante == 'FACTURA' ? 'fa-building' : 'fa-user' }} text-white"></i>
                                    </div>
                                    <div>
                                        <span class="d-block font-weight-bold text-dark">{{ $venta->serie }}-{{ $venta->numero }}</span>
                                        <span class="badge {{ $venta->tipo_comprobante == 'FACTURA' ? 'badge-primary' : 'badge-info' }} badge-pill" style="font-size: 0.7rem">
                                            {{ $venta->tipo_comprobante }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-1 text-bold text-success">
                                    S/ {{ number_format($venta->total_neto, 2) }}
                                </div>
                            </td>

                            {{-- 3. CLIENTE --}}
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="font-weight-bold text-truncate" style="max-width: 200px;" title="{{ $venta->cliente->nombre_completo }}">
                                        {{ $venta->cliente->nombre_completo }}
                                    </span>
                                    <small class="text-muted">
                                        <i class="fas fa-id-card mr-1"></i> {{ $venta->cliente->documento }}
                                    </small>
                                </div>
                            </td>

                            {{-- 4. ERROR (DIAGNÓSTICO) --}}
                            <td>
                                @if($venta->codigo_error_sunat)
                                <div class="alert alert-light border-danger p-2 mb-0 rounded" style="border-left: 4px solid #dc3545;">
                                    <small class="d-block text-danger font-weight-bold">
                                        <i class="fas fa-bug mr-1"></i> Error {{ $venta->codigo_error_sunat }}
                                    </small>
                                    <span class="d-block text-muted text-xs" style="line-height: 1.2;">
                                        {{ Str::limit($venta->mensaje_sunat, 90) }}
                                    </span>
                                </div>
                                @else
                                <span class="badge badge-warning p-2">
                                    <i class="fas fa-clock mr-1"></i> Pendiente de envío
                                </span>
                                @endif
                            </td>

                            {{-- 5. ACCIONES --}}
                            <td class="text-right pr-4">
                                <div class="d-flex justify-content-end">


                                    {{-- Botón Reenviar con Loader --}}
                                    <form action="{{ route('facturacion.reenviar', $venta->id) }}" method="POST" class="form-reenviar">
                                        @csrf
                                        <button type="submit" class="btn btn-orange btn-sm shadow-sm font-weight-bold text-white btn-action">
                                            <i class="fas fa-paper-plane mr-1"></i> Reenviar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="empty-state">
                                    <img src="https://cdn-icons-png.flaticon.com/512/190/190411.png" alt="Sin errores" style="width: 80px; opacity: 0.5">
                                    <h4 class="mt-3 text-muted">¡Todo en orden!</h4>
                                    <p class="text-muted">No tienes comprobantes pendientes de regularizar ante SUNAT.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($ventas->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $ventas->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- CSS Adicional para darle el toque "Moderno" --}}
<style>
    .btn-orange {
        background-color: #ff851b;
        border-color: #ff851b;
    }

    .btn-orange:hover {
        background-color: #e07415;
        border-color: #d06a12;
    }

    .table td {
        vertical-align: middle !important;
    }

    .text-xs {
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
</style>
@stop

@section('js')
<script>
    // Script para evitar doble clic y mostrar "Enviando..."
    $('.form-reenviar').on('submit', function() {
        var btn = $(this).find('.btn-action');
        var icon = btn.find('i');

        btn.prop('disabled', true);
        btn.addClass('disabled');

        // Cambiar texto e icono
        icon.removeClass('fa-paper-plane').addClass('fa-sync fa-spin');
        btn.html('<i class="fas fa-sync fa-spin mr-1"></i> Enviando...');
    });
</script>
@stop