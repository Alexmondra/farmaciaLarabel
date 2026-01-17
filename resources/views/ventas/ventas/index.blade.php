@extends('adminlte::page')

@section('title', 'Historial de Ventas')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="text-dark font-weight-bold">Historial de Ventas</h1>
    <div class="d-flex">
        @if($cajaAbierta)
        <a href="{{ route('ventas.create') }}" class="btn btn-success shadow-sm">
            <i class="fas fa-plus-circle mr-1"></i> Nueva Venta
        </a>
        @endif
    </div>
</div>
@stop

@section('content')
<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-body py-2">
        <form id="form-filtros">
            <div class="row align-items-end">
                <div class="col-md-4 mb-2">
                    <label class="small font-weight-bold">Buscar Ticket / Cliente</label>
                    <input type="text" name="search_q" class="form-control" placeholder="Escribe para buscar..." autocomplete="off">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold">Desde</label>
                    <input type="date" name="fecha_desde" class="form-control" value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold">Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control" value="{{ now()->format('Y-m-d') }}">
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Aquí se cargará la tabla --}}
<div class="card shadow-sm" id="tabla-container">
    @include('ventas.ventas._table')
</div>

@include('ventas.cajas._modal_apertura', ['sucursalesParaApertura' => $sucursalesParaApertura])
@stop

@push('css')
<style>
    /* Soporte Modo Oscuro AdminLTE 4 */
    .dark-mode .thead-dark-adaptive {
        background-color: #343a40;
        color: #fff;
    }

    .dark-mode .venta-anulada {
        background-color: rgba(255, 0, 0, 0.1) !important;
        opacity: 0.8;
    }

    .venta-anulada {
        background-color: rgba(220, 53, 69, 0.05) !important;
        border-left: 4px solid #dc3545;
    }

    /* Hover dinámico */
    .fila-venta:hover {
        background-color: rgba(0, 123, 255, 0.1) !important;
        transition: background-color 0.2s ease;
    }

    /* Ajustes para móviles */
    @media (max-width: 576px) {
        .card-body.py-2 {
            padding: 0.5rem;
        }

        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }

        h1 {
            font-size: 1.5rem;
        }

        .pagination {
            font-size: 0.8rem;
        }
    }

    .pagination {
        margin-bottom: 0;
    }
</style>
@endpush

@push('js')
<script>
    // Definimos la función fuera para que sea accesible globalmente
    function fetchVentas(url = "{{ route('ventas.index') }}") {
        const $container = $('#tabla-container');
        $container.css('opacity', '0.5');

        $.ajax({
            url: url,
            method: 'GET',
            data: $('#form-filtros').serialize(),
            success: function(html) {
                $container.html(html).css('opacity', '1');
                // Scroll suave arriba de la tabla
                $('html, body').animate({
                    scrollTop: $container.offset().top - 100
                }, 300);
            },
            error: function(xhr) {
                $container.css('opacity', '1');
                console.error(xhr.responseText);
            }
        });
    }

    $(document).ready(function() {
        // Búsqueda en tiempo real
        let searchTimer;
        $('#form-filtros').on('keyup', 'input[name="search_q"]', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(fetchVentas, 400);
        });

        // Cambio de fechas
        $('#form-filtros').on('change', 'input[type="date"]', function() {
            fetchVentas();
        });

        // Capturar clics en la paginación (Delegación de eventos)
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');
            if (url) fetchVentas(url);
        });

        // Doble clic para abrir detalle
        $(document).on('dblclick', '.fila-venta', function() {
            window.location.href = $(this).data('url');
        });
    });

    // Función para la alerta de anulación "bonita"
    function confirmarAnulacion(id, correlativo) {
        Swal.fire({
            title: '¿Confirmar Anulación?',
            html: `Vas a anular la venta <b>${correlativo}</b>.<br><small class="text-danger">Se devolverá el stock y se enviará a SUNAT.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sí, Anular',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                document.getElementById('form-anular-' + id).submit();
            }
        });
    }
</script>
@endpush