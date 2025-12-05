@extends('adminlte::page')

@section('title', 'Detalle de Caja')

@section('content')

@php
// Cálculos
$totalVentas = $cajaSesion->ventas->sum('total_neto');
$saldoTeorico = $cajaSesion->saldo_inicial + $totalVentas;
$diferencia = $cajaSesion->saldo_real - $saldoTeorico;

// Desglose
$porMetodo = $cajaSesion->ventas->groupBy('medio_pago')->map->sum('total_neto');
$isOpen = $cajaSesion->estado === 'ABIERTO';

// Separar Observaciones (Truco PHP para dividir el texto)
// El controlador guarda: "Texto Apertura | CIERRE: Texto Cierre"
$partesObs = explode(' | CIERRE: ', $cajaSesion->observaciones);
$obsApertura = $partesObs[0] ?? '';
$obsCierre = $partesObs[1] ?? null;
@endphp

<style>
    /* VARIABLES BASE */
    :root {
        --bg-light: #f4f6f9;
        --card-bg: #ffffff;
        --text-main: #343a40;
        --border-color: #e9ecef;
    }

    /* ESTILOS DE COMPONENTES */
    .compact-header,
    .mini-card,
    .audit-box {
        background: var(--card-bg);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        border-radius: 8px;
    }

    .compact-header {
        padding: 0.8rem 1rem;
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .mini-card {
        padding: 0.8rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .mini-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        opacity: 0.7;
        font-weight: 700;
        margin-bottom: 0.2rem;
    }

    .mini-value {
        font-size: 1.2rem;
        font-weight: 700;
        line-height: 1.1;
    }

    .audit-box {
        background: rgba(0, 0, 0, 0.03);
        /* Fondo sutil */
        border-style: dashed;
        text-align: center;
        padding: 0.5rem;
    }

    /* TABLA LIMPIA */
    .table-clean th {
        border-top: none;
        font-size: 0.7rem;
        text-transform: uppercase;
        opacity: 0.6;
        font-weight: 700;
        background: transparent;
    }

    .table-clean td {
        vertical-align: middle;
        font-size: 0.85rem;
        border-bottom: 1px solid var(--border-color);
        padding: 0.5rem 0.75rem;
        color: var(--text-main);
    }

    /* ============================================================
       MODO OSCURO (CORRECCIONES PROFUNDAS)
       ============================================================ */
    .dark-mode {
        --bg-light: #454d55;
        --card-bg: #343a40;
        --text-main: #ffffff;
        --border-color: #6c757d;
    }

    /* Forzar colores oscuros sobre Bootstrap utilities */
    .dark-mode .bg-white {
        background-color: #343a40 !important;
        color: #ffffff !important;
    }

    .dark-mode .bg-light {
        background-color: #3f474e !important;
        color: #ffffff !important;
    }

    .dark-mode .text-dark {
        color: #ffffff !important;
    }

    .dark-mode .text-muted {
        color: #adb5bd !important;
    }

    .dark-mode .border {
        border-color: #6c757d !important;
    }

    /* Ajuste específico para el Modal en modo oscuro */
    .dark-mode .modal-content {
        background-color: #343a40;
        color: #fff;
    }

    .dark-mode .modal-header {
        border-bottom-color: #6c757d;
    }

    .dark-mode .close {
        color: #fff;
        text-shadow: none;
        opacity: 1;
    }

    .dark-mode .table thead th {
        color: #fff;
        border-color: #6c757d;
    }
</style>
<div class="container-fluid pt-2">

    {{-- 1. HEADER CON BOTONES --}}
    <div class="compact-header shadow-sm">
        <div class="d-flex align-items-center">
            <div class="mr-3">
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; font-size: 0.9rem;">
                    {{ substr($cajaSesion->usuario->name ?? 'U', 0, 1) }}
                </div>
            </div>
            <div>
                <h6 class="font-weight-bold mb-0">{{ $cajaSesion->usuario->name ?? 'Usuario' }}</h6>
                <small style="opacity: 0.7;">{{ $cajaSesion->sucursal->nombre }} <span class="mx-1">|</span> Caja #{{ $cajaSesion->id }}</small>
            </div>
        </div>

        <div>
            {{-- BOTÓN OBSERVACIONES --}}
            <button class="btn btn-sm btn-outline-info mr-2" data-toggle="modal" data-target="#modalObservaciones">
                <i class="far fa-comment-dots mr-1"></i> Observaciones
            </button>

            @if($isOpen)
            {{-- BOTÓN CERRAR CAJA (Solo si está abierta) --}}
            <button class="btn btn-sm btn-warning font-weight-bold mr-2 shadow-sm"
                data-toggle="modal"
                data-target="#modalCerrarCaja"
                data-saldo-inicial="S/ {{ number_format($cajaSesion->saldo_inicial, 2) }}"
                data-ventas-total="S/ {{ number_format($totalVentas, 2) }}"
                data-saldo-estimado="{{ number_format($saldoTeorico, 2) }}"
                data-action-url="{{ route('cajas.update', $cajaSesion->id) }}">
                <i class="fas fa-lock mr-1"></i> CERRAR CAJA
            </button>
            @else
            <span class="badge badge-secondary mr-2 px-3 py-2">CERRADA</span>
            @endif

            <a href="{{ route('cajas.index') }}" class="btn btn-sm btn-outline-secondary" title="Volver">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </div>

    {{-- 2. DASHBOARD DE NÚMEROS --}}
    <div class="row mb-3">
        <div class="col-md-3 mb-2">
            <div class="mini-card shadow-sm">
                <span class="mini-label"><i class="far fa-calendar-alt mr-1"></i> Tiempos</span>
                <div class="d-flex justify-content-between mb-1">
                    <span class="mini-sub">Inic:</span>
                    <span class="font-weight-bold">{{ $cajaSesion->fecha_apertura->format('d/m H:i') }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="mini-sub">Fin:</span>
                    <span class="font-weight-bold">
                        {{ $cajaSesion->fecha_cierre ? $cajaSesion->fecha_cierre->format('d/m H:i') : '-- : --' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-2 mb-2">
            <div class="mini-card shadow-sm border-left-warning" style="border-left: 3px solid #ffc107;">
                <span class="mini-label text-warning">Inicial</span>
                <span class="mini-value">S/ {{ number_format($cajaSesion->saldo_inicial, 2) }}</span>
            </div>
        </div>

        <div class="col-md-2 mb-2">
            <div class="mini-card shadow-sm border-left-success" style="border-left: 3px solid #28a745;">
                <span class="mini-label text-success">Ventas</span>
                <span class="mini-value">S/ {{ number_format($totalVentas, 2) }}</span>
            </div>
        </div>

        <div class="col-md-2 mb-2">
            <div class="mini-card shadow-sm border-left-primary" style="border-left: 3px solid #007bff;">
                <span class="mini-label text-primary">Teórico</span>
                <span class="mini-value">S/ {{ number_format($saldoTeorico, 2) }}</span>
            </div>
        </div>

        {{-- CARD AUDITORÍA (CONDICIONAL) --}}
        <div class="col-md-3 mb-2">
            <div class="audit-box shadow-sm">
                @if(!$isOpen)
                {{-- SI ESTÁ CERRADA: MUESTRA DATOS REALES --}}
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="mini-label mb-0">Dinero Real</span>
                    <span class="font-weight-bold text-dark" style="font-size:1.1rem;">S/ {{ number_format($cajaSesion->saldo_real, 2) }}</span>
                </div>
                <div class="badge {{ $diferencia == 0 ? 'bg-success' : ($diferencia < 0 ? 'bg-danger' : 'bg-success') }} d-block" style="font-size: 0.85rem;">
                    {{ $diferencia > 0 ? '+' : '' }} S/ {{ number_format($diferencia, 2) }}
                    <span style="opacity:0.8; font-weight:400; font-size:0.75rem;">({{ $diferencia == 0 ? 'OK' : ($diferencia < 0 ? 'Falta' : 'Sobra') }})</span>
                </div>
                @else
                {{-- SI ESTÁ ABIERTA: MENSAJE ESPERA --}}
                <div class="text-center py-2 text-muted">
                    <i class="fas fa-hourglass-half fa-lg mb-2 opacity-50"></i>
                    <h6 class="font-weight-bold mb-0 text-info">Caja en Curso</h6>
                    <small>El arqueo se verá al cerrar.</small>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 3. MOVIMIENTOS Y PAGOS --}}
    <div class="row">
        <div class="col-md-12 mb-3">
            <div class="card shadow-sm mb-0">
                <div class="card-body p-2 d-flex align-items-center bg-light rounded">
                    <span class="mini-label mr-3 mb-0 text-muted">MEDIOS PAGO:</span>
                    @forelse($porMetodo as $metodo => $total)
                    <div class="mr-3 px-2 py-1 bg-white border rounded">
                        <small class="text-muted font-weight-bold" style="font-size: 0.7rem;">{{ $metodo }}</small>
                        <span class="font-weight-bold ml-1" style="font-size: 0.9rem;">S/ {{ number_format($total, 2) }}</span>
                    </div>
                    @empty
                    <span class="text-muted small">Sin movimientos.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent py-2 border-bottom">
            <h6 class="font-weight-bold mb-0" style="font-size: 0.9rem;"><i class="fas fa-list mr-2 opacity-50"></i>Movimientos</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-clean table-hover mb-0">
                <thead>
                    <tr>
                        <th class="pl-3">Comprobante</th>
                        <th>Hora</th>
                        <th>Cliente</th>
                        <th>Pago</th>
                        <th class="text-right">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cajaSesion->ventas as $venta)
                    <tr>
                        <td class="pl-3">
                            <span class="font-weight-bold">{{ $venta->tipo_comprobante }}</span>
                            <span class="small opacity-50 ml-1">{{ $venta->serie }}-{{ $venta->numero }}</span>
                        </td>
                        <td class="small">{{ $venta->fecha_emision->format('h:i A') }}</td>
                        <td class="small">{{ Str::limit($venta->cliente->nombre ?? 'Público', 20) }}</td>
                        <td><span class="badge badge-light border font-weight-normal">{{ $venta->medio_pago }}</span></td>
                        <td class="text-right font-weight-bold">S/ {{ number_format($venta->total_neto, 2) }}</td>
                        <td class="text-right pr-3">
                            <button class="btn btn-xs btn-default border rounded-circle shadow-sm"
                                data-toggle="modal"
                                data-target="#modalDetalleVenta"
                                data-detalles="{{ $venta->detalles->toJson() }}"
                                data-venta-info="{{ $venta->serie }}-{{ $venta->numero }}">
                                <i class="far fa-eye text-info"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 small opacity-50">Sin ventas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODALES --}}
@include('ventas.cajas._modal_cierre') {{-- Reutilizamos el modal de cierre --}}
@include('ventas.cajas._modal_observaciones', ['obsApertura' => $obsApertura, 'obsCierre' => $obsCierre])
@include('ventas.cajas._modal_detalle_venta_js') {{-- O el HTML del modal de detalle que tenías --}}

@stop

@push('js')
<script>
    $(document).ready(function() {
        // 1. LÓGICA MODAL CIERRE (Copiada del index para que funcione aquí también)
        $('#modalCerrarCaja').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var modal = $(this);
            modal.find('#formCerrarCaja').attr('action', button.data('action-url'));
            modal.find('#displaySaldoInicial').val(button.data('saldo-inicial'));
            modal.find('#displayVentasTotal').val(button.data('ventas-total'));
            modal.find('#displaySaldoEstimado').val('S/ ' + button.data('saldo-estimado'));
            modal.find('#saldo_real').val('');
        });

        // 2. LÓGICA MODAL DETALLE (La que ya tenías)
        $('#modalDetalleVenta').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var detalles = button.data('detalles');
            var tbody = $(this).find('#modalDetalleBody');
            tbody.empty();

            if (detalles) {
                detalles.forEach(function(item) {
                    // 1. Obtener nombre del producto
                    var nombre = item.medicamento ? item.medicamento.nombre : 'General';

                    // 2. Convertir valores a números para evitar errores de formato
                    var cantidad = item.cantidad;
                    var precioUnit = parseFloat(item.precio_unitario).toFixed(2);
                    var total = parseFloat(item.subtotal_neto).toFixed(2);

                    var fila = `
                <tr>
                    <td class="pl-3 align-middle">${nombre}</td>
                    <td class="text-center align-middle">${cantidad}</td>
                    <td class="text-right align-middle">${precioUnit}</td> 
                    <td class="text-right pr-3 font-weight-bold align-middle">${total}</td>
                </tr>
            `;

                    tbody.append(fila);
                });
            }
        });
    });
</script>
@endpush