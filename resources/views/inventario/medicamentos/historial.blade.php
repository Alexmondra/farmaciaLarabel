@extends('adminlte::page')

@section('title', 'Historial de Lotes')

@section('content_header')
<div class="d-flex justify-content-between align-items-center mb-3 historial-title-box">
    <div>
        <h1 class="text-dark font-weight-bold" style="font-size: 1.8rem;">Historial de Lotes</h1>
        <p class="text-muted mb-0">
            Medicamento: <strong class="text-dark">{{ $medicamento->nombre }}</strong>
            <span class="mx-2">|</span>
            Sucursal: <strong>{{ $sucursal->nombre }}</strong>
        </p>
    </div>
    <a href="{{ route('inventario.medicamentos.index') }}" class="btn btn-outline-secondary shadow-sm">
        <i class="fas fa-arrow-left mr-2"></i> Volver al listado
    </a>
</div>
@endsection

@section('content')

{{-- FILTROS (Acordeón Moderno) --}}
<div class="card shadow-none border mb-4" style="background-color: #f8f9fa;">
    <div class="card-header border-0 bg-transparent py-3" id="headingFilters">
        <h5 class="mb-0">
            <button class="btn btn-link text-dark font-weight-bold p-0 text-decoration-none"
                type="button" data-toggle="collapse" data-target="#collapseFilters" aria-expanded="true">
                <i class="fas fa-filter text-primary mr-2"></i> Filtros de Búsqueda
            </button>
        </h5>
    </div>

    <div id="collapseFilters" class="collapse {{ request()->hasAny(['estado', 'q_lote', 'fecha_inicio']) ? 'show' : '' }}">
        <div class="card-body pt-0">
            <form method="GET">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="small text-muted font-weight-bold text-uppercase">Estado</label>
                        <select name="estado" class="form-control shadow-sm border-0">
                            <option value="">Todos los estados</option>
                            <option value="vigentes" {{ request('estado') == 'vigentes' ? 'selected' : '' }}>✅ Vigentes</option>
                            <option value="agotados" {{ request('estado') == 'agotados' ? 'selected' : '' }}>🚫 Agotados (Stock 0)</option>
                            <option value="vencidos" {{ request('estado') == 'vencidos' ? 'selected' : '' }}>⚠️ Vencidos</option>
                            <option value="por_vencer" {{ request('estado') == 'por_vencer' ? 'selected' : '' }}>⏳ Por Vencer</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="small text-muted font-weight-bold text-uppercase">Código Lote</label>
                        <div class="input-group shadow-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text border-0 bg-white"><i class="fas fa-barcode text-muted"></i></span>
                            </div>
                            <input type="text" name="q_lote" class="form-control border-0" value="{{ request('q_lote') }}" placeholder="Ej. L-2023...">
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="small text-muted font-weight-bold text-uppercase">Vence Desde</label>
                        <input type="date" name="fecha_inicio" class="form-control shadow-sm border-0" value="{{ request('fecha_inicio') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="small text-muted font-weight-bold text-uppercase">Vence Hasta</label>
                        <input type="date" name="fecha_fin" class="form-control shadow-sm border-0" value="{{ request('fecha_fin') }}">
                    </div>
                </div>
                <div class="text-right border-top pt-3 mt-2">
                    <a href="{{ route('inventario.medicamento_sucursal.historial', ['medicamento' => $medicamento->id, 'sucursal' => $sucursal->id]) }}"
                        class="btn btn-link text-muted mr-2">Limpiar filtros</a>
                    <button type="submit" class="btn btn-primary shadow-sm px-4">
                        <i class="fas fa-search mr-1"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- TABLA DE RESULTADOS --}}
<div class="card shadow-sm border-0">
    {{-- Agregamos 'table-responsive' para evitar desbordes y 'table-sm' para compactar --}}
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="bg-light">
                <tr class="text-uppercase small text-muted">
                    <th class="pl-3 py-3 border-top-0">Lote / Vencimiento</th>
                    <th class="py-3 border-top-0">Estado</th>

                    {{-- Ocultamos estas columnas en móvil (d-none) y las mostramos en MD para arriba (d-md-table-cell) --}}
                    <th class="py-3 border-top-0 d-none d-sm-table-cell">Vencimiento</th>
                    <th class="text-center py-3 border-top-0 d-none d-md-table-cell">Inicial</th>

                    <th class="text-center py-3 border-top-0">Stock</th>
                    <th class="text-right pr-3 py-3 border-top-0 d-none d-md-table-cell">Registro</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lotes as $lote)
                @php
                $vence = $lote->fecha_vencimiento ? \Carbon\Carbon::parse($lote->fecha_vencimiento) : null;
                $esVencido = $vence && $vence->isPast();
                $esAgotado = $lote->stock_actual <= 0;
                    $stockInicial=$lote->detalleCompra->cantidad_recibida ?? null;
                    @endphp
                    <tr>
                        {{-- COLUMNA 1: CÓDIGO + VENCIMIENTO (SOLO MÓVIL) --}}
                        <td class="pl-3">
                            {{-- 'text-nowrap' evita que el código se parta --}}
                            <div class="font-weight-bold text-dark text-nowrap">
                                {{ $lote->codigo_lote }}
                            </div>

                            {{-- ESTO SOLO SE VE EN MÓVIL (d-md-none): Ponemos la fecha aquí abajo para ahorrar espacio --}}
                            <div class="d-block d-md-none mt-1">
                                @if($vence)
                                <small class="{{ $esVencido ? 'text-danger' : 'text-muted' }}">
                                    <i class="far fa-calendar-alt mr-1"></i>{{ $vence->format('d/m/y') }}
                                </small>
                                @else
                                <small class="text-muted">No vence</small>
                                @endif
                            </div>
                        </td>

                        {{-- COLUMNA 2: ESTADO --}}
                        <td>
                            @if($esVencido)
                            {{-- En móvil usamos iconos o texto corto, en PC el badge completo --}}
                            <span class="badge badge-danger">Vencido</span>
                            @elseif($esAgotado)
                            <span class="badge badge-secondary">Agotado</span>
                            @else
                            <span class="badge badge-success">Vigente</span>
                            @endif
                        </td>

                        {{-- COLUMNA 3: VENCIMIENTO (Solo Tablet/PC) --}}
                        <td class="d-none d-sm-table-cell">
                            @if($vence)
                            <span class="font-weight-bold {{ $esVencido ? 'text-danger' : 'text-dark' }}">
                                {{ $vence->format('d/m/Y') }}
                            </span>
                            <br><small class="text-muted d-none d-md-block">{{ $vence->diffForHumans() }}</small>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- COLUMNA 4: INICIAL (SOLO PC) --}}
                        <td class="text-center d-none d-md-table-cell">
                            <span class="text-muted">{{ $stockInicial ?? 'kardex' }}</span>
                        </td>

                        {{-- COLUMNA 5: STOCK ACTUAL (Visible siempre) --}}
                        <td class="text-center">
                            <span class="font-weight-bold {{ $esAgotado ? 'text-muted' : 'text-primary' }}" style="font-size: 1.1rem;">
                                {{ $lote->stock_actual }}
                            </span>
                        </td>

                        {{-- COLUMNA 6: REGISTRO (SOLO PC) --}}
                        <td class="text-right pr-3 d-none d-md-table-cell text-muted small">
                            {{ $lote->created_at->format('d/m/Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Sin resultados.
                        </td>
                    </tr>
                    @endforelse
            </tbody>
        </table>
    </div>

    @if($lotes->hasPages())
    <div class="card-footer bg-white border-top-0 d-flex justify-content-center justify-content-md-end">
        {{ $lotes->appends(request()->query())->links() }}
    </div>
    @endif
</div>

@endsection

@section('css')
@include('inventario.medicamentos.css')
<style>
    /* Estilos específicos de historial.blade.php */
    .opacity-50 {
        opacity: 0.5;
    }

    .card-header .btn-link:hover {
        text-decoration: none;
        color: #007bff !important;
    }
</style>
@endsection