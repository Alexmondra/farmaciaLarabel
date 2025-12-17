@extends('adminlte::page')

@section('title', 'Vencimientos')

@section('content_header')
<div class="row mb-2 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0 text-dark font-weight-bold" style="font-family: 'Segoe UI', sans-serif;">
            ⏳ Control de Caducidad
            @if(!$sucursalId)
            <span class="badge badge-info text-sm ml-2 shadow-sm" style="vertical-align: middle;">Vista Global</span>
            @endif
        </h1>
    </div>
    <div class="col-sm-6 text-right d-none d-sm-block">
        <small class="text-muted">Monitoreo FEFO (First Expired, First Out)</small>
    </div>
</div>
@stop

@section('content')

{{-- 1. BARRA DE BÚSQUEDA --}}
<div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
    <div class="card-body p-3 bg-white">
        <form action="{{ route('reportes.vencimientos') }}" method="GET">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-0 rounded-left pl-3">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                </div>
                <input type="text" name="search" class="form-control bg-light border-0 rounded-right"
                    placeholder="Buscar lote, producto..."
                    value="{{ $search ?? '' }}" style="height: 45px;">
                @if($search)
                <div class="input-group-append ml-2">
                    <a href="{{ route('reportes.vencimientos') }}" class="btn btn-outline-secondary rounded">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- CONTENIDO PRINCIPAL --}}
@if($lotes->isEmpty())
{{-- MENSAJE DE VACÍO --}}
<div class="card card-outline card-success shadow-sm">
    <div class="card-body text-center p-5">
        <i class="fas fa-check-circle fa-4x text-success mb-3 opacity-50"></i>
        <h4 class="text-muted font-weight-bold">¡Todo en orden!</h4>
        <p class="text-muted">No tienes productos próximos a vencer.</p>
    </div>
</div>
@else

{{-- ================================================================================== --}}
{{-- VISTA DE ESCRITORIO (TABLA) - SE OCULTA EN MÓVIL (d-none d-md-block)              --}}
{{-- ================================================================================== --}}
<div class="d-none d-md-block card card-outline card-danger shadow-lg border-0" style="border-radius: 10px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="pl-4 py-3 text-uppercase text-secondary text-xs font-weight-bolder">Producto</th>
                        <th class="py-3 text-uppercase text-secondary text-xs font-weight-bolder">Lote / Ubicación</th>
                        <th class="text-center py-3 text-uppercase text-secondary text-xs font-weight-bolder">Stock</th>
                        <th class="text-right pr-4 py-3 text-uppercase text-secondary text-xs font-weight-bolder">Vencimiento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lotes as $lote)
                    @php
                    $dias = \Carbon\Carbon::now()->diffInDays($lote->fecha_vencimiento, false);
                    $dias = intval($dias);

                    $rowClass = '';
                    $badgeTime = 'badge-info';
                    $textStatusClass = 'text-muted';

                    if($dias < 0) {
                        $rowClass='bg-red-soft' ;
                        $badgeTime='badge-danger' ;
                        $textStatusClass='text-danger font-weight-bold' ;
                        } elseif($dias==0) {
                        $rowClass='bg-red-soft' ;
                        $badgeTime='badge-danger' ;
                        $textStatusClass='text-danger font-weight-bold' ;
                        } elseif($dias <=10) {
                        $badgeTime='badge-warning text-dark' ;
                        $textStatusClass='text-dark font-weight-bold' ;
                        }
                        @endphp

                        <tr class="{{ $rowClass }}">
                        <td class="pl-4">
                            <span class="font-weight-bold text-dark">{{ $lote->medicamento->nombre }}</span><br>
                            <small class="text-muted">
                                {{ $lote->medicamento->laboratorio }}
                                {{-- SUCURSAL EN ESCRITORIO --}}
                                @if(!$sucursalId)
                                <span class="text-info ml-1">
                                    <i class="fas fa-store-alt text-xs"></i> {{ $lote->sucursal->nombre }}
                                </span>
                                @endif
                            </small>
                        </td>
                        <td>
                            <span class="text-monospace text-dark">{{ $lote->codigo_lote }}</span><br>
                            <small class="text-muted"><i class="fas fa-map-marker-alt text-xs"></i> {{ $lote->ubicacion ?? 'General' }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-light border">{{ $lote->stock_actual }}</span>
                        </td>
                        <td class="text-right pr-4">
                            <span class="badge {{ $badgeTime }}">{{ $lote->fecha_vencimiento->format('d/m/Y') }}</span><br>
                            <small class="{{ $textStatusClass }}">
                                {{ $dias < 0 ? 'Vencido hace '.abs($dias).' días' : ($dias==0 ? '¡VENCE HOY!' : $dias.' días restantes') }}
                            </small>
                        </td>
                        </tr>
                        @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ================================================================================== --}}
{{-- VISTA MÓVIL (TARJETAS) - SOLO VISIBLE EN MÓVIL (d-block d-md-none)                 --}}
{{-- ================================================================================== --}}
<div class="d-block d-md-none">
    @foreach($lotes as $lote)
    @php
    $dias = \Carbon\Carbon::now()->diffInDays($lote->fecha_vencimiento, false);
    $dias = intval($dias);

    if($dias < 0) {
        $borderClass='border-danger' ; // Borde Rojo
        $dateColor='text-danger' ;
        } elseif($dias <=10) {
        $borderClass='border-warning' ; // Borde Amarillo
        $dateColor='text-dark' ;
        } else {
        $borderClass='border-secondary' ; // Borde Gris
        $dateColor='text-success' ;
        }
        @endphp

        {{-- TARJETA INDIVIDUAL --}}
        <div class="card shadow-sm mb-3 bg-white" style="border-left: 5px solid transparent;" class="{{ $borderClass }}">
        {{-- Borde de color dinámico --}}
        <div style="border-left: 5px solid {{ $dias<0 ? '#dc3545' : ($dias<=10 ? '#ffc107' : '#6c757d') }}; height: 100%; border-radius: 5px;">

            <div class="card-body p-3">
                {{-- CABECERA --}}
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div style="max-width: 70%;">
                        <h5 class="font-weight-bold text-dark mb-0" style="font-size: 1rem;">
                            {{ $lote->medicamento->nombre }}
                        </h5>
                        <small class="text-muted">{{ $lote->medicamento->laboratorio ?? 'Genérico' }}</small>

                        {{-- AQUI ESTÁ EL CAMBIO: MOSTRAR SUCURSAL EN MÓVIL --}}
                        @if(!$sucursalId)
                        <div class="mt-1">
                            <span class="badge badge-light border text-info shadow-sm">
                                <i class="fas fa-store-alt mr-1"></i> {{ $lote->sucursal->nombre }}
                            </span>
                        </div>
                        @endif
                    </div>

                    {{-- Badge de días --}}
                    <span class="badge {{ $dias<=10 ? 'badge-danger' : 'badge-light border' }}">
                        {{ $dias < 0 ? abs($dias).'d vencido' : $dias.' días' }}
                    </span>
                </div>

                <hr class="my-2" style="border-top: 1px dashed #e9ecef;">

                {{-- DETALLES --}}
                <div class="row mb-2">
                    <div class="col-6">
                        <span class="text-xs text-uppercase text-muted font-weight-bold">Lote</span><br>
                        <span class="text-dark font-weight-bold font-monospace">{{ $lote->codigo_lote }}</span>
                    </div>
                    <div class="col-6 text-right">
                        <span class="text-xs text-uppercase text-muted font-weight-bold">Ubicación</span><br>
                        <span class="text-dark">{{ $lote->ubicacion ?? '-' }}</span>
                    </div>
                </div>

                {{-- FOOTER GRIS DE LA TARJETA --}}
                <div class="p-2 rounded d-flex justify-content-between align-items-center mobile-footer-bg">
                    <div>
                        <small class="text-muted d-block" style="line-height: 1;">Stock</small>
                        <span class="font-weight-bold text-dark" style="font-size: 1.2rem;">
                            {{ $lote->stock_actual }}
                        </span>
                    </div>
                    <div class="text-right">
                        <small class="text-muted d-block" style="line-height: 1;">Vence el</small>
                        <span class="font-weight-bold {{ $dateColor }}" style="font-size: 1.1rem;">
                            {{ $lote->fecha_vencimiento->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
</div>
@endforeach
</div>

{{-- PAGINACIÓN --}}
<div class="d-flex justify-content-center mt-3">
    {!! $lotes->links() !!}
</div>

@endif

@stop

@section('css')
<style>
    /* Estilos base */
    .bg-red-soft {
        background-color: #fff5f5 !important;
    }

    .mobile-footer-bg {
        background-color: #f8f9fa;
    }

    .font-monospace {
        font-family: monospace;
    }

    /* MODO OSCURO */
    .dark-mode .bg-white {
        background-color: #343a40 !important;
    }

    .dark-mode .bg-red-soft {
        background-color: #4a181c !important;
    }

    .dark-mode .mobile-footer-bg {
        background-color: #3f474e !important;
    }

    .dark-mode .text-dark {
        color: #fff !important;
    }

    .dark-mode .text-muted {
        color: #adb5bd !important;
    }

    .dark-mode .text-info {
        color: #17a2b8 !important;
    }

    /* Asegura visibilidad del azul */
    .dark-mode .input-group-text {
        background-color: #3f474e !important;
        border: none;
    }

    .dark-mode .form-control {
        background-color: #3f474e !important;
        color: white;
    }

    .dark-mode .bg-light {
        background-color: #454d55 !important;
        color: white;
    }
</style>
@stop