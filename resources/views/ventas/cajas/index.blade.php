@extends('adminlte::page')

{{-- ... (content_header, alertas, etc. - Sin cambios) ... --}}
@section('title', 'Sesiones de Caja')

@section('content_header')
<div class="d-flex justify-content-between">
    <h1>Sesiones de Caja</h1>
    <button class="btn btn-primary" data-toggle="modal" data-target="#modalAbrirCaja">
        <i class="fas fa-plus"></i> Abrir Nueva Caja
    </button>
</div>

@stop

@section('content')
<div class="card">
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    {{-- ... (tus <thead>) ... --}}
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Sucursal</th>
                    <th>Estado</th>
                    <th>Apertura</th>
                    <th>Cierre</th>
                    <th>Saldo Inicial</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cajas as $caja)
                <tr>
                    {{-- ... (tus <td>) ... --}}
                    <td>{{ $caja->id }}</td>
                    <td>{{ $caja->usuario->name ?? 'N/A' }}</td>
                    <td>{{ $caja->sucursal->nombre ?? 'N/A' }}</td>
                    <td>
                        @if($caja->estado === 'ABIERTO')
                        <span class="badge badge-success">Abierta</span>
                        @else
                        <span class="badge badge-secondary">Cerrada</span>
                        @endif
                    </td>
                    <td>{{ $caja->fecha_apertura->format('d/m/Y H:i') }}</td>
                    <td>{{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i') : '-' }}</td>
                    <td>S/ {{ number_format($caja->saldo_inicial, 2) }}</td>
                    <td>
                        <a href="{{ route('cajas.show', $caja->id) }}" class="btn btn-sm btn-info" title="Ver Detalle">
                            <i class="fas fa-eye"></i>
                        </a>

                        {{-- --- BOTÓN DE CIERRE MODIFICADO --- --}}
                        @if($caja->estado === 'ABIERTO')
                        <button class="btn btn-sm btn-warning btn-abrir-modal-cierre"
                            data-toggle="modal"
                            data-target="#modalCerrarCaja"
                            data-saldo-inicial="S/ {{ number_format($caja->saldo_inicial, 2) }}"
                            data-action-url="{{ route('cajas.update', $caja->id) }}"
                            title="Cerrar Caja">
                            <i class="fas fa-lock"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">
                        No se encontraron sesiones de caja.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        {{ $cajas->links() }}
    </div>
</div>

{{-- --- AQUÍ INCLUIMOS AMBOS MODALES --- --}}

{{-- 1. Modal de Apertura (que ya tenías) --}}
@include('ventas.cajas._modal_apertura', [
'sucursalesParaApertura' => $sucursalesParaApertura
])

{{-- 2. Modal de Cierre (el nuevo) --}}
@include('ventas.cajas._modal_cierre')


{{-- El 'div' para la lógica de JS (a prueba de formateadores) --}}
<div id="js-page-data" data-abrir-modal-apertura="{{ $errors->has('saldo_inicial') || $errors->has('sucursal_id') ? 'true' : 'false' }}"
    data-abrir-modal-cierre="{{ $errors->has('saldo_real') || $errors->has('general_cierre') ? 'true' : 'false' }}"></div>

@stop

@push('js')
<script>
    $(document).ready(function() {

        // --- Lógica para el MODAL DE APERTURA (para reabrir si falla) ---
        var abrirApertura = $('#js-page-data').data('abrir-modal-apertura');
        if (abrirApertura === 'true' || abrirApertura === true) {
            $('#modalAbrirCaja').modal('show');
        }

        var abrirCierre = $('#js-page-data').data('abrir-modal-cierre');
        if (abrirCierre === 'true' || abrirCierre === true) {}

        $('#modalCerrarCaja').on('show.bs.modal', function(event) {

            // 1. Obtener el botón que disparó el modal
            var button = $(event.relatedTarget);

            // 2. Extraer la información de los atributos 'data-*'
            var actionUrl = button.data('action-url');
            var saldoInicial = button.data('saldo-inicial');

            // 3. Obtener referencias a elementos del modal
            var modal = $(this);

            // 4. Actualizar el 'action' del formulario
            modal.find('#formCerrarCaja').attr('action', actionUrl);

            // 5. Actualizar el campo de texto de saldo inicial
            modal.find('#displaySaldoInicial').val(saldoInicial);

            // 6. Limpiar el campo de saldo real
            modal.find('#saldo_real').val('');
        });

    });
</script>
@endpush