@extends('adminlte::page')

@section('title', 'Alertas de Stock')

@section('content_header')
<div class="row mb-2 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0 text-dark font-weight-bold" style="font-family: 'Segoe UI', sans-serif;">
            📉 Alertas de Abastecimiento
            @if(!$sucursalId)
            <span class="badge badge-info text-sm ml-2 shadow-sm" style="vertical-align: middle;">Vista Global</span>
            @endif
        </h1>
    </div>
    <div class="col-sm-6 text-right d-none d-sm-block">
        <small class="text-muted">Gestión optimizada de inventario</small>
    </div>
</div>
@stop

@section('content')

{{-- 1. BARRA DE HERRAMIENTAS --}}
<div class="card shadow-sm border-0 mb-4" style="border-radius: 15px; overflow: hidden;">
    <div class="card-body p-3 bg-white">
        <form action="{{ route('reportes.stock_bajo') }}" method="GET">
            <div class="row align-items-center">

                {{-- Buscador --}}
                <div class="col-12 col-md-5 mb-3 mb-md-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-0 rounded-left text-muted pl-3">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                        <input type="text" name="search" class="form-control bg-light border-0 rounded-right"
                            placeholder="Buscar medicamento..."
                            value="{{ $search ?? '' }}" style="height: 40px;">
                    </div>
                </div>

                {{-- Filtros Funcionales --}}
                <div class="col-12 col-md-7 text-center text-md-right">
                    <div class="btn-group shadow-sm d-flex flex-wrap" role="group">
                        {{-- Botón TODO --}}
                        <button type="submit" name="filtro" value="todos"
                            class="btn btn-light border flex-fill {{ (!$filtro || $filtro=='todos') ? 'active font-weight-bold bg-secondary text-white' : '' }}">
                            Todo
                        </button>

                        {{-- Botón BAJOS (Aún queda) --}}
                        <button type="submit" name="filtro" value="bajos"
                            class="btn btn-light border text-warning flex-fill {{ ($filtro=='bajos') ? 'active font-weight-bold bg-warning-light' : '' }}">
                            ⚠️ Aún queda
                        </button>

                        {{-- Botón AGOTADOS --}}
                        <button type="submit" name="filtro" value="agotados"
                            class="btn btn-light border text-danger flex-fill {{ ($filtro=='agotados') ? 'active font-weight-bold bg-danger-light' : '' }}">
                            ⛔ Agotado
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- 2. LISTADO DE PRODUCTOS --}}
<div class="card card-outline card-warning shadow-lg border-0" style="border-radius: 10px;">
    <div class="card-body p-0">
        @if($stocks->isEmpty())
        <div class="text-center p-5">
            <div class="mb-3">
                <i class="fas fa-check-circle fa-4x text-success opacity-50"></i>
            </div>
            <h4 class="text-muted font-weight-bold">No hay resultados</h4>
            <p class="text-muted">
                @if($search || $filtro)
                No se encontraron productos con los filtros actuales.
                @else
                ¡Excelente! Tu inventario está saludable.
                @endif
            </p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 600px;">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th class="pl-4 py-3 text-uppercase text-secondary text-xs font-weight-bolder opacity-7" style="width: 45%;">Producto</th>
                        <th class="text-center py-3 text-uppercase text-secondary text-xs font-weight-bolder opacity-7" style="width: 20%;">Estado</th>
                        <th class="py-3 text-uppercase text-secondary text-xs font-weight-bolder opacity-7 pr-4" style="width: 35%;">Nivel de Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stocks as $item)
                    @php
                    // Usamos el dato calculado directamente por SQL (gracias al Scope)
                    $stockReal = $item->stock_computado ?? 0;
                    $minimo = $item->stock_minimo;

                    // Evitamos división por cero
                    $porcentaje = ($minimo > 0) ? ($stockReal / $minimo) * 100 : 100;
                    if($porcentaje > 100) $porcentaje = 100;

                    // Lógica visual
                    if ($stockReal == 0) {
                    $rowClass = 'bg-red-soft';
                    $iconBg = '#ffecec';
                    $iconColor = 'text-danger';
                    $statusBadge = '<span class="badge badge-danger px-2 py-1 shadow-sm">AGOTADO</span>';
                    $barColor = 'bg-danger';
                    $textColor = 'text-danger';
                    } else {
                    $rowClass = '';
                    $iconBg = '#fff3cd';
                    $iconColor = 'text-warning';
                    // Muestra cuánto queda explícitamente
                    $statusBadge = '<span class="badge badge-warning text-dark px-2 py-1 shadow-sm">QUEDAN: '.$stockReal.'</span>';
                    $barColor = 'bg-warning';
                    $textColor = 'text-dark';
                    }
                    @endphp

                    <tr class="{{ $rowClass }}" style="border-bottom: 1px solid #f0f2f5;">

                        {{-- COLUMNA 1: PRODUCTO --}}
                        <td class="pl-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px mr-3">
                                    <div class="d-flex align-items-center justify-content-center rounded shadow-sm"
                                        style="width:40px; height:40px; background-color: {{ $iconBg }};">
                                        <i class="fas fa-pills {{ $iconColor }}"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-dark font-weight-bold" style="font-size: 1rem;">
                                        {{ $item->medicamento->nombre }}
                                    </span>
                                    <span class="text-muted text-xs">
                                        {{ $item->medicamento->laboratorio ?? 'Genérico' }}
                                        <span class="d-none d-md-inline">| {{ $item->medicamento->presentacion ?? '' }}</span>
                                    </span>
                                    @if(!$sucursalId)
                                    <span class="text-info text-xs mt-1">
                                        <i class="fas fa-store-alt mr-1"></i> {{ $item->sucursal->nombre }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- COLUMNA 2: ESTADO --}}
                        <td class="text-center align-middle">
                            {!! $statusBadge !!}
                        </td>

                        {{-- COLUMNA 3: BARRA DE PROGRESO --}}
                        <td class="align-middle pr-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-xs font-weight-bold {{ $textColor }}">
                                    Actual: {{ $stockReal }}
                                </span>
                                <span class="text-xs text-muted">
                                    Meta: {{ $minimo }}
                                </span>
                            </div>
                            <div class="progress rounded-pill shadow-inner" style="height: 8px; background-color: #e9ecef;">
                                <div class="progress-bar {{ $barColor }} progress-bar-striped" role="progressbar"
                                    style="width: {{ $porcentaje }}%">
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- 3. PAGINACIÓN --}}
        <div class="card-footer bg-white d-flex justify-content-end py-3">
            {{-- Esto genera los enlaces << 1 2 3 4 >> automáticamente --}}
            {!! $stocks->links() !!}
        </div>

        @endif
    </div>
</div>
@stop

@section('css')
<style>
    /* Estilos pulidos */
    .bg-red-soft {
        background-color: #fff5f5 !important;
    }

    .bg-warning-light {
        background-color: #fff3cd !important;
        color: #856404 !important;
    }

    .bg-danger-light {
        background-color: #f8d7da !important;
        color: #721c24 !important;
    }

    .shadow-inner {
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
    }

    .table-hover tbody tr:hover {
        background-color: #f4f6f9;
        transition: background-color 0.2s;
    }

    .active {
        box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
    }

    /* Paginación pequeña y bonita */
    .pagination {
        margin-bottom: 0;
    }

    .page-link {
        border-radius: 5px;
        margin: 0 2px;
        color: #6c757d;
        border: none;
        background: #f8f9fa;
    }

    .page-item.active .page-link {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #212529;
        font-weight: bold;
    }

    /* Dark Mode */
    .dark-mode .bg-red-soft {
        background-color: #381216 !important;
    }

    .dark-mode .bg-white {
        background-color: #343a40 !important;
    }

    .dark-mode .page-link {
        background-color: #3f474e;
        color: #fff;
    }

    .dark-mode .page-item.active .page-link {
        background-color: #ffc107;
        color: black;
    }
</style>
@stop