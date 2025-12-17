@extends('adminlte::page')

@section('title', 'Detalle de Compra')

@section('content_header')
{{-- Nueva cabecera moderna y responsiva --}}
<div class="d-flex justify-content-between align-items-center header-modern">
    <h1 class="text-dark font-weight-bold" style="font-size: 1.8rem;">
        <i class="fas fa-file-invoice-dollar mr-2 text-info"></i> Compra #{{ $compra->id }}
    </h1>
    {{-- Botón Volver compacto en móvil --}}
    <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary shadow-sm btn-action-compact">
        <i class="fa fa-arrow-left"></i> <span class="d-none d-sm-inline">Volver al listado</span>
    </a>
</div>
@endsection

@section('content')

{{-- FILA SUPERIOR: PROVEEDOR / COMPRA --}}
<div class="row mb-4">

    {{-- PROVEEDOR (Card moderna con borde a la izquierda) --}}
    <div class="col-md-6 mb-3">
        <div class="card card-modern shadow-sm border-left-info h-100">
            <div class="card-header bg-transparent py-2">
                <h6 class="mb-0 font-weight-bold text-info"><i class="fas fa-truck-moving mr-2"></i> Datos del Proveedor</h6>
            </div>
            <div class="card-body py-3 px-4 data-list">
                <div class="data-item"><span class="data-label">Razón Social:</span> <span class="data-value font-weight-bold">{{ $compra->proveedor->razon_social ?? '-' }}</span></div>
                <div class="data-item"><span class="data-label">RUC:</span> <span class="data-value">{{ $compra->proveedor->ruc ?? '-' }}</span></div>
                <div class="data-item d-none d-sm-flex"><span class="data-label">Teléfono:</span> <span class="data-value">{{ $compra->proveedor->telefono ?? '-' }}</span></div>
                <div class="data-item d-none d-md-flex"><span class="data-label">Dirección:</span> <span class="data-value small">{{ $compra->proveedor->direccion ?? '-' }}</span></div>
            </div>
        </div>
    </div>

    {{-- COMPRA (Card moderna con borde a la izquierda) --}}
    @php
    $totalFinal = $compra->costo_total_factura ?? 0;
    $estadoClass = match($compra->estado) {
    'registrada' => 'badge-secondary',
    'recibida' => 'badge-success',
    'pendiente' => 'badge-warning text-dark',
    'anulada' => 'badge-danger',
    default => 'badge-light text-muted',
    };
    @endphp

    <div class="col-md-6 mb-3">
        <div class="card card-modern shadow-sm border-left-primary h-100">
            <div class="card-header bg-transparent py-2">
                <h6 class="mb-0 font-weight-bold text-primary"><i class="fas fa-receipt mr-2"></i> Datos de la Compra</h6>
            </div>
            <div class="card-body py-3 px-4 data-list">
                <div class="data-item"><span class="data-label">Fecha:</span> <span class="data-value">{{ optional($compra->fecha_recepcion)->format('d/m/Y') }}</span></div>
                <div class="data-item"><span class="data-label">Documento:</span> <span class="data-value font-weight-bold">{{ trim(($compra->tipo_comprobante ?? '').' '.($compra->numero_factura_proveedor ?? '')) ?: '-' }}</span></div>
                <div class="data-item d-none d-sm-flex"><span class="data-label">Sucursal:</span> <span class="data-value">{{ $compra->sucursal->nombre ?? '-' }}</span></div>
                <div class="data-item"><span class="data-label">Estado:</span> <span class="data-value"><span class="badge {{ $estadoClass }}">{{ strtoupper($compra->estado) }}</span></span></div>
                <div class="data-item total-display-box border-top mt-2 pt-2"><span class="data-label h5 mb-0">TOTAL:</span> <span class="data-value h5 mb-0 text-success">S/ {{ number_format($totalFinal, 2) }}</span></div>
            </div>
        </div>
    </div>
</div>

{{-- DETALLES DE LA COMPRA (TABLA MODERNA) --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-boxes mr-2"></i> Detalle de Ítems Comprados</h6>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0 table-items-detail">
                <thead>
                    <tr>
                        <th style="width: 5%" class="pl-3">#</th>
                        <th style="width: 30%">Medicamento</th>
                        <th style="width: 15%" class="text-center d-none d-sm-table-cell">Lote</th>
                        <th style="width: 15%" class="text-center d-none d-md-table-cell">Vencimiento</th>
                        <th style="width: 10%" class="text-end">Cant.</th>
                        <th style="width: 10%" class="text-end d-none d-lg-table-cell">P. Unit.</th>
                        <th style="width: 15%" class="text-end pr-3">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($compra->detalles as $i => $det)
                    @php
                    $lote = $det->lote;
                    $medicamento = optional($lote)->medicamento;
                    $subtotal = $det->cantidad_recibida * $det->precio_compra_unitario;
                    @endphp
                    <tr>
                        <td class="pl-3 align-middle">{{ $i + 1 }}</td>

                        {{-- Medicamento --}}
                        <td class="align-middle">
                            <span class="font-weight-bold text-dark">{{ $medicamento->nombre ?? '-' }}</span>
                            <br>
                            {{-- Lote visible solo en móvil --}}
                            <small class="text-muted d-sm-none">Lote: {{ $lote->codigo_lote ?? '-' }}</small>
                        </td>

                        {{-- Lote (visible desde sm) --}}
                        <td class="text-center align-middle d-none d-sm-table-cell small">{{ $lote->codigo_lote ?? '-' }}</td>

                        {{-- Fecha de vencimiento (visible desde md) --}}
                        <td class="text-center align-middle d-none d-md-table-cell small">
                            @if(optional($lote->fecha_vencimiento)->isPast())
                            <span class="text-danger font-weight-bold">{{ optional($lote->fecha_vencimiento)->format('d/m/Y') ?? '-' }}</span>
                            @else
                            <span>{{ optional($lote->fecha_vencimiento)->format('d/m/Y') ?? '-' }}</span>
                            @endif
                        </td>

                        {{-- Cantidad --}}
                        <td class="text-end align-middle font-weight-bold">{{ $det->cantidad_recibida }}</td>

                        {{-- Precio Unitario (visible desde lg) --}}
                        <td class="text-end align-middle d-none d-lg-table-cell small">S/ {{ number_format($det->precio_compra_unitario, 2) }}</td>

                        {{-- Subtotal --}}
                        <td class="text-end align-middle font-weight-bold text-primary pr-3">
                            S/ {{ number_format($subtotal, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No hay detalles registrados para esta compra.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- TARJETA DE OBSERVACIONES (Separada y destacada) --}}
@if($compra->observaciones)
<div class="card card-modern shadow-sm mt-4 border-left-warning">
    <div class="card-body py-3 px-4">
        <h6 class="mb-2 font-weight-bold text-warning"><i class="fas fa-sticky-note mr-2"></i> Observaciones de la Compra</h6>
        <p class="text-muted mb-0 small">{{ $compra->observaciones }}</p>
    </div>
</div>
@endif

@endsection

@section('css')
<style>
    /* ============================================================
       ESTILOS DE DETALLE DE COMPRA (show.blade.php)
       ============================================================ */

    /* VARIABLES BASE */
    :root {
        --bg-default: #ffffff;
        --text-main: #343a40;
        --border-color: #e9ecef;
        --color-primary: #007bff;
        --color-info: #17a2b8;
        --color-warning: #ffc107;
        --color-success: #28a745;
    }

    /* DARK MODE OVERRIDES */
    body.dark-mode {
        --bg-default: #343a40;
        --text-main: #f8f9fa;
        --border-color: #4b6584;
    }

    .text-dark {
        color: var(--text-main) !important;
    }

    .text-muted {
        color: #adb5bd !important;
    }

    /* ESTILOS GENERALES DE LA VISTA */
    .header-modern h1 {
        font-size: 1.8rem;
    }

    .btn-action-compact {
        font-size: 0.85rem;
    }

    /* CARD MODERNA (Proveedor / Compra) */
    .card-modern {
        border-radius: 12px;
        border: 1px solid var(--border-color);
        background-color: var(--bg-default);
    }

    .border-left-info {
        border-left: 5px solid var(--color-info) !important;
    }

    .border-left-primary {
        border-left: 5px solid var(--color-primary) !important;
    }

    .border-left-warning {
        border-left: 5px solid var(--color-warning) !important;
    }

    .card-modern .card-header {
        border-bottom: 1px solid var(--border-color);
    }

    /* Dark Mode para Cards */
    .dark-mode .card-modern {
        border-color: #4b545c;
    }

    .dark-mode .card-modern .card-header {
        background-color: #3f474e !important;
        border-bottom-color: #4b545c;
    }


    /* LISTADO DE DATOS DENTRO DE LA CARD (GRID) */
    .data-list {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 8px;
    }

    .data-item {
        padding: 4px 0;
        border-bottom: 1px dashed var(--border-color);
        display: flex;
        /* Usamos flex para distribuir contenido en el grid */
        justify-content: space-between;
        align-items: center;
        grid-column: 1 / -1;
    }

    .data-item:last-of-type {
        border-bottom: none;
    }

    .data-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.85rem;
    }

    .dark-mode .data-label {
        color: #adb5bd;
    }

    .data-value {
        text-align: right;
        font-size: 0.9rem;
    }

    .total-display-box {
        grid-column: 1 / -1;
        /* Ocupa todo el ancho */
        display: flex;
        justify-content: space-between;
    }


    /* TABLA DE ITEMS */
    .table-items-detail th {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #6c757d;
        font-weight: 700;
        padding: 0.75rem 0.5rem;
    }

    .dark-mode .table-items-detail th {
        color: #adb5bd;
    }

    .table-items-detail td {
        font-size: 0.85rem;
    }

    /* Columna de Medicamento */
    .table-items-detail td:nth-child(2) {
        font-weight: 600;
    }


    /* ============================================================
       RESPONSIVIDAD MÓVIL (Menor a 768px)
       ============================================================ */
    @media (max-width: 767.98px) {

        .header-modern h1 {
            font-size: 1.4rem !important;
        }

        /* 1. LAYOUT DE DATOS (Proveedor/Compra) */
        .data-list {
            /* En móvil, volvemos a una lista simple */
            grid-template-columns: 1fr;
            gap: 0;
        }

        .data-item {
            padding: 8px 0;
        }

        .data-label,
        .data-value {
            font-size: 0.8rem;
        }

        .total-display-box .data-label,
        .total-display-box .data-value {
            font-size: 1.1rem !important;
        }

        /* 2. TABLA DE ITEMS */
        .table-items-detail th,
        .table-items-detail td {
            padding: 0.5rem !important;
            font-size: 0.75rem;
        }

        /* Ocultar texto en el botón Volver */
        .btn-action-compact .d-sm-inline {
            display: none !important;
        }

        /* Ocultar el texto en el header de la tabla para ahorrar espacio */
        .card-header h6 {
            font-size: 0.9rem !important;
        }
    }
</style>
@endsection