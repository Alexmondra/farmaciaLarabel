@extends('adminlte::page')

@section('title', 'Monitor SUNAT')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark-mode-light">
                <i class="fas fa-satellite-dish text-orange mr-2"></i> Monitor de Envíos
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                <li class="breadcrumb-item active">Errores SUNAT</li>
            </ol>
        </div>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            {{-- TARJETA PRINCIPAL --}}
            <div class="card card-outline card-orange shadow-lg">
                <div class="card-header border-0">
                    <h3 class="card-title mt-1">
                        <i class="fas fa-exclamation-triangle text-danger mr-1"></i>
                        Cola de Rectificación
                    </h3>
                    <div class="card-tools">
                        @if($ventas->total() > 0)
                        <span class="badge badge-danger p-2 px-3" style="font-size: 0.9rem">
                            {{ $ventas->total() }} Errores Pendientes
                        </span>
                        @else
                        <span class="badge badge-success p-2 px-3">
                            Sistema Saludable
                        </span>
                        @endif
                    </div>
                </div>

                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover table-striped align-middle text-nowrap">
                        <thead>
                            <tr class="text-uppercase text-xs text-muted border-bottom">
                                <th style="width: 15%" class="pl-4">Emisión</th>
                                <th style="width: 20%">Comprobante</th>
                                <th style="width: 25%">Cliente</th>
                                <th style="width: 25%">Diagnóstico SUNAT</th>
                                <th style="width: 15%" class="text-right pr-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventas as $venta)
                            <tr>
                                {{-- 1. FECHA --}}
                                <td class="pl-4 align-middle">
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold" style="font-size: 1.1em;">
                                            {{ $venta->fecha_emision->format('d/m/Y') }}
                                        </span>
                                        <small class="text-muted">
                                            <i class="far fa-clock mr-1"></i>{{ $venta->fecha_emision->format('h:i A') }}
                                        </small>
                                    </div>
                                </td>

                                {{-- 2. COMPROBANTE --}}
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        {{-- Icono visual del tipo --}}
                                        <div class="rounded-circle d-flex justify-content-center align-items-center mr-3 shadow-sm 
                                            {{ $venta->tipo_comprobante == 'FACTURA' ? 'bg-primary' : 'bg-info' }}"
                                            style="width: 40px; height: 40px;">
                                            <i class="fas {{ $venta->tipo_comprobante == 'FACTURA' ? 'fa-building' : 'fa-user' }} text-white"></i>
                                        </div>

                                        <div>
                                            <span class="d-block font-weight-bold">{{ $venta->serie }}-{{ $venta->numero }}</span>
                                            <div class="d-flex align-items-center">
                                                <span class="badge {{ $venta->tipo_comprobante == 'FACTURA' ? 'badge-primary' : 'badge-info' }} mr-2">
                                                    {{ substr($venta->tipo_comprobante, 0, 1) }}
                                                </span>
                                                <span class="text-success font-weight-bold">
                                                    S/ {{ number_format($venta->total_neto, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- 3. CLIENTE --}}
                                <td class="align-middle">
                                    <div class="user-block">
                                        <span class="username ml-0" style="font-size: 1rem;">
                                            {{ Str::limit($venta->cliente->nombre_completo, 25) }}
                                        </span>
                                        <span class="description ml-0">
                                            <i class="fas fa-id-card text-muted mr-1"></i>
                                            {{ $venta->cliente->documento }}
                                        </span>
                                    </div>
                                </td>

                                {{-- 4. ERROR (DIAGNÓSTICO) --}}
                                <td class="align-middle">
                                    @if($venta->codigo_error_sunat)
                                    <div class="callout callout-danger py-2 px-3 m-0 shadow-sm" style="border-left-width: 4px;">
                                        <h6 class="text-danger font-weight-bold mb-1" style="font-size: 0.9rem;">
                                            <i class="fas fa-bug mr-1"></i> Error {{ $venta->codigo_error_sunat }}
                                        </h6>
                                        <p class="text-muted text-xs mb-0 text-wrap" style="line-height: 1.3; min-width: 200px;">
                                            {{ Str::limit($venta->mensaje_sunat, 80) }}
                                        </p>
                                    </div>
                                    @else
                                    <span class="badge badge-warning text-white p-2">
                                        <i class="fas fa-hourglass-half mr-1"></i> En Cola de Envío
                                    </span>
                                    @endif
                                </td>

                                {{-- 5. ACCIONES --}}
                                <td class="align-middle text-right pr-4">
                                    <div class="btn-group shadow-sm">
                                        {{-- Botón Editar (Gris) --}}
                                        <a href="{{ route('facturacion.edit', $venta->id) }}"
                                            class="btn btn-default"
                                            data-toggle="tooltip"
                                            title="Corregir datos">
                                            <i class="fas fa-pen text-dark"></i>
                                        </a>

                                        {{-- Botón Reenviar (Naranja) --}}
                                        <form action="{{ route('facturacion.reenviar', $venta->id) }}" method="POST" class="d-inline form-reenviar">
                                            @csrf
                                            <button type="submit" class="btn btn-orange font-weight-bold text-white btn-action">
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
                                        <div class="icon-container mb-3">
                                            <i class="fas fa-check-circle text-success fa-4x opacity-50"></i>
                                        </div>
                                        <h4 class="text-muted font-weight-bold">¡Todo limpio!</h4>
                                        <p class="text-muted">No hay comprobantes con errores pendientes de corrección.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($ventas->hasPages())
                <div class="card-footer border-top">
                    <div class="d-flex justify-content-end">
                        {{ $ventas->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* Soporte Dark Mode */
    .text-dark-mode-light {
        color: inherit;
    }

    /* Botón Naranja Personalizado */
    .btn-orange {
        background-color: #ff851b;
        border-color: #ff851b;
        transition: all 0.3s ease;
    }

    .btn-orange:hover {
        background-color: #e07415;
        border-color: #d06a12;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* Ajustes finos de tabla */
    .table td {
        vertical-align: middle !important;
    }

    .callout {
        background-color: transparent;
    }

    /* Modo oscuro específico para Callout */
    .dark-mode .callout-danger {
        border-left-color: #e74c3c;
        background-color: rgba(231, 76, 60, 0.1);
    }

    .dark-mode .btn-default {
        background-color: #3f474e;
        border-color: #4b545c;
        color: #fff;
    }

    .dark-mode .btn-default:hover {
        background-color: #4b545c;
    }

    .dark-mode .text-dark {
        color: #fff !important;
        /* Fuerza ícono blanco en dark mode */
    }

    /* Opacidad suave */
    .opacity-50 {
        opacity: 0.5;
    }
</style>
@stop

@section('js')
<script>
    $(function() {
        // Activar tooltips de Bootstrap
        $('[data-toggle="tooltip"]').tooltip();

        // Script para evitar doble clic y mostrar "Enviando..."
        $('.form-reenviar').on('submit', function() {
            var btn = $(this).find('.btn-action');
            var icon = btn.find('i');

            // Deshabilitar para evitar doble envío
            btn.prop('disabled', true);

            // Animación
            icon.removeClass('fa-paper-plane').addClass('fa-sync fa-spin');
            btn.html('<i class="fas fa-sync fa-spin mr-1"></i> Enviando...');

            // Permitir que el form se envíe
            return true;
        });
    });
</script>
@stop