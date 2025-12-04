@extends('adminlte::page')

@section('title', 'Sesiones de Caja')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="font-weight-bold text-dark">Sesiones de Caja</h1>
    @if($tieneCajaAbierta)
    <button class="btn btn-secondary shadow-sm" disabled><i class="fas fa-lock mr-2"></i> Caja en Uso</button>
    @else
    <button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalAbrirCaja"><i class="fas fa-plus mr-2"></i> Abrir Caja</button>
    @endif
</div>
@stop

@section('content')

<style>
    /* --- ESTILOS LIGHT MODE (Por defecto) --- */
    .row-faltante {
        background-color: #ffebee !important;
    }

    /* Rojo pastel */
    .row-sobrante {
        background-color: #e3f2fd !important;
    }

    /* Azul pastel */
    .text-diff-neg {
        color: #c0392b;
        font-weight: 800;
    }

    .text-diff-pos {
        color: #2980b9;
        font-weight: 800;
    }

    /* --- ESTILOS DARK MODE (AdminLTE) --- */
    .dark-mode .row-faltante {
        background-color: rgba(231, 76, 60, 0.2) !important;
    }

    /* Rojo oscuro transp. */
    .dark-mode .row-sobrante {
        background-color: rgba(52, 152, 219, 0.2) !important;
    }

    /* Azul oscuro transp. */
    .dark-mode .text-diff-neg {
        color: #ff6b6b;
    }

    /* Rojo brillante */
    .dark-mode .text-diff-pos {
        color: #54a0ff;
    }

    /* Azul brillante */

    /* Ajustes generales Dark Mode */
    .dark-mode .card {
        background-color: #343a40;
        color: white;
    }

    .dark-mode .table-hover tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }
</style>

{{-- FILTROS --}}
<div class="card mb-3 shadow-sm border-0">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('cajas.index') }}" class="form-row align-items-end">
            <div class="col-md-3">
                <label class="small font-weight-bold mb-1">Usuario</label>
                <input type="text" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="Nombre...">
            </div>
            <div class="col-md-3">
                <label class="small font-weight-bold mb-1">Fecha</label>
                <input type="date" name="filtro_fecha" class="form-control form-control-sm" value="{{ request('filtro_fecha') }}">
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold mb-1">Resultado</label>
                <select name="filtro_cuadre" class="form-control form-control-sm">
                    <option value="">-- Todos --</option>
                    <option value="faltante" {{ request('filtro_cuadre') == 'faltante' ? 'selected' : '' }}>Faltante (Rojo)</option>
                    <option value="sobrante" {{ request('filtro_cuadre') == 'sobrante' ? 'selected' : '' }}>Sobrante (Azul)</option>
                    <option value="exacto" {{ request('filtro_cuadre') == 'exacto' ? 'selected' : '' }}>Exacto</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-info btn-block"><i class="fas fa-filter"></i> Filtrar</button>
            </div>
            @if(request()->anyFilled(['q', 'filtro_fecha', 'filtro_cuadre']))
            <div class="col-md-2">
                <a href="{{ route('cajas.index') }}" class="btn btn-sm btn-outline-secondary btn-block">Limpiar</a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- TABLA --}}
<div class="card shadow border-0">
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover mb-0 align-middle text-nowrap">
            <thead class="bg-dark">
                <tr>
                    <th class="text-center" style="width:50px">#</th>
                    <th>Usuario</th>
                    <th>Sucursal</th>
                    <th class="text-center">Estado</th>
                    <th>Apertura / Cierre</th>
                    <th>Inicial</th>
                    <th>Resultado</th>
                    <th class="text-right pr-4">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cajas as $caja)
                @php
                $clase = '';
                if ($caja->estado === 'CERRADO') {
                if ($caja->diferencia < 0) $clase='row-faltante' ;
                    elseif ($caja->diferencia > 0) $clase = 'row-sobrante';
                    }
                    @endphp

                    <tr class="fila-caja {{ $clase }}" data-href="{{ route('cajas.show', $caja->id) }}" style="cursor: pointer;">

                        <td class="text-center text-muted font-weight-bold">{{ $cajas->firstItem() + $loop->index }}</td>
                        <td class="font-weight-bold">{{ $caja->usuario->name ?? 'N/A' }}</td>
                        <td><small class="text-muted">{{ $caja->sucursal->nombre ?? 'N/A' }}</small></td>

                        <td class="text-center">
                            <span class="badge {{ $caja->estado === 'ABIERTO' ? 'badge-success' : 'badge-secondary' }} px-2">
                                {{ $caja->estado }}
                            </span>
                        </td>

                        <td>
                            <div class="small" style="line-height: 1.2;">
                                <div class="text-success"><i class="fas fa-play fa-xs mr-1"></i> {{ $caja->fecha_apertura->format('d/m H:i') }}</div>
                                @if($caja->fecha_cierre)
                                <div class="text-danger"><i class="fas fa-stop fa-xs mr-1"></i> {{ $caja->fecha_cierre->format('d/m H:i') }}</div>
                                @endif
                            </div>
                        </td>

                        <td>S/ {{ number_format($caja->saldo_inicial, 2) }}</td>

                        {{-- RESULTADO DEL CUADRE --}}
                        <td>
                            @if($caja->estado === 'CERRADO')
                            @if($caja->diferencia == 0)
                            <span class="badge badge-light border"><i class="fas fa-check"></i> OK</span>
                            @else
                            <span class="{{ $caja->diferencia < 0 ? 'text-diff-neg' : 'text-diff-pos' }}">
                                {{ $caja->diferencia > 0 ? '+' : '' }} S/ {{ number_format($caja->diferencia, 2) }}
                            </span>
                            @endif
                            @else
                            <span class="text-muted small font-italic">...</span>
                            @endif
                        </td>

                        <td class="text-right pr-3">
                            <div class="btn-group">
                                <a href="{{ route('cajas.show', $caja->id) }}" class="btn btn-sm btn-info shadow-sm" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if($caja->estado === 'ABIERTO')
                                @php
                                $ventas = $caja->ventas_sum_total_neto ?? 0;
                                $estimado = $caja->saldo_inicial + $ventas;
                                @endphp
                                <button type="button"
                                    class="btn btn-sm btn-warning shadow-sm font-weight-bold text-dark btn-trigger-cerrar"
                                    data-action-url="{{ route('cajas.update', $caja->id) }}"
                                    data-saldo-inicial="S/ {{ number_format($caja->saldo_inicial, 2) }}"
                                    data-ventas-total="S/ {{ number_format($ventas, 2) }}"
                                    data-saldo-estimado="S/ {{ number_format($estimado, 2) }}"
                                    title="Cerrar">
                                    <i class="fas fa-lock"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">Sin registros.</td>
                    </tr>
                    @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white py-2">
        {{ $cajas->withQueryString()->links() }}
    </div>
</div>

@include('ventas.cajas._modal_apertura')
@include('ventas.cajas._modal_cierre')

@stop

@push('js')
<script>
    $(document).ready(function() {
        // 1. Navegación Fila
        $('.fila-caja').on('dblclick', function(e) {
            if ($(e.target).closest('.btn-trigger-cerrar, a, input').length) return;
            window.location.href = $(this).data('href');
        });

        // 2. Botón Cerrar (Nuclear)
        $(document).on('click', '.btn-trigger-cerrar', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            let btn = $(this);
            let modal = $('#modalCerrarCaja');

            modal.find('#formCerrarCaja').attr('action', btn.data('action-url'));
            modal.find('#displaySaldoInicial').val(btn.data('saldo-inicial'));
            modal.find('#displayVentasTotal').val(btn.data('ventas-total'));
            modal.find('#displaySaldoEstimado').val(btn.data('saldo-estimado'));
            modal.find('#saldo_real, #observaciones_cierre').val('');

            modal.modal('show');
        });
    });
</script>
@endpush