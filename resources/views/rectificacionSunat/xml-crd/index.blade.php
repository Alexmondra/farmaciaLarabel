@extends('adminlte::page')

@section('title', 'Repositorio Digital')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark-mode-light">
                <i class="fas fa-cloud-download-alt text-teal mr-2"></i> Repositorio SUNAT
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                <li class="breadcrumb-item active">Archivos XML/CDR</li>
            </ol>
        </div>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-teal shadow-lg">

        <div class="card-header border-0">
            <form action="{{ route('facturacion.comprobantes.index') }}" method="GET" id="searchForm">
                <div class="row align-items-end">

                    <div class="col-lg-4 col-md-12 mb-2">
                        <h3 class="card-title font-weight-bold mt-2">
                            <i class="fas fa-list mr-1"></i> Comprobantes Emitidos
                        </h3>
                    </div>

                    <div class="col-lg-4 col-md-6 col-12 mb-2">
                        <label class="mb-0 text-sm text-muted">Rango de Fechas:</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-teal border-0">
                                    <i class="far fa-calendar-alt"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control float-right" id="reservation" name="rango_fechas" value="{{ $rangoFechas }}">
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 col-12 mb-2">
                        <label class="mb-0 text-sm text-muted">Búsqueda rápida (Serie, Cliente, RUC):</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control"
                                placeholder="Ej: F001-450, o Juan Perez..."
                                value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-teal">
                                    <i class="fas fa-search mr-1"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap table-striped">
                <thead>
                    <tr>
                        <th>Emisión</th>
                        <th>Comprobante</th>
                        <th>Cliente</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Descargas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $venta)
                    <tr>
                        <td class="align-middle">
                            <span class="d-block font-weight-bold">{{ $venta->fecha_emision->format('d/m/Y') }}</span>
                            <small class="text-muted">{{ $venta->fecha_emision->format('h:i A') }}</small>
                        </td>
                        <td class="align-middle">
                            <span class="font-weight-bold d-block">{{ $venta->serie }}-{{ $venta->numero }}</span>
                            <span class="badge badge-secondary" style="font-size: 0.75em;">
                                {{ $venta->tipo_comprobante == '01' ? 'FACTURA' : 'BOLETA' }}
                            </span>
                        </td>
                        <td class="align-middle">
                            {{ $venta->cliente ? $venta->cliente->nombre_completo : 'PÚBLICO GENERAL' }}
                        </td>
                        <td class="align-middle text-center">
                            @if($venta->codigo_error_sunat === '0')
                            <span class="badge badge-pill badge-success">ACEPTADO</span>
                            @elseif($venta->codigo_error_sunat)
                            <span class="badge badge-pill badge-danger">ERROR</span>
                            @else
                            <span class="badge badge-pill badge-warning text-white">PENDIENTE</span>
                            @endif
                        </td>
                        <td class="align-middle text-right">
                            <div class="btn-group">
                                {{-- 1. PDF (Rojo) --}}
                                <a href="{{ route('reportes.venta.pdf', $venta->id) }}"
                                    class="btn btn-sm btn-outline-danger font-weight-bold"
                                    target="_blank"
                                    title="Ver PDF">
                                    <i class="fas fa-file-pdf mr-1"></i> PDF
                                </a>

                                {{-- 2. XML (Azul) --}}
                                @if($venta->ruta_xml)
                                <a href="{{ route('facturacion.download.xml', $venta->id) }}"
                                    class="btn btn-sm btn-outline-info font-weight-bold"
                                    title="Descargar XML">
                                    <i class="fas fa-file-code mr-1"></i> XML
                                </a>
                                @else
                                <button class="btn btn-sm btn-default disabled" style="opacity: 0.5;">
                                    <i class="fas fa-file-code mr-1"></i> XML
                                </button>
                                @endif

                                {{-- 3. CDR (Verde) --}}
                                @if($venta->ruta_cdr)
                                <a href="{{ route('facturacion.download.cdr', $venta->id) }}"
                                    class="btn btn-sm btn-outline-success font-weight-bold"
                                    title="Descargar CDR">
                                    <i class="fas fa-check-circle mr-1"></i> CDR
                                </a>
                                @else
                                <button class="btn btn-sm btn-default disabled" style="opacity: 0.5;">
                                    <i class="fas fa-clock mr-1"></i> CDR
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">No hay resultados en este rango.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-end">
            {{ $ventas->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .text-dark-mode-light {
        color: inherit;
    }

    /* Ajustes Dark Mode para el calendario */
    .daterangepicker {
        background-color: #343a40;
        border: 1px solid #4b545c;
        color: #fff;
    }

    .daterangepicker .calendar-table {
        background-color: #343a40;
        border: 1px solid #4b545c;
    }

    .daterangepicker td.off {
        background-color: #343a40;
        color: #6c757d;
    }

    .daterangepicker td.available:hover {
        background-color: #3f474e;
    }

    .daterangepicker td.active {
        background-color: #20c997;
    }

    /* Teal */
    .daterangepicker .ranges li:hover {
        background-color: #3f474e;
    }
</style>
@stop

@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
    $(function() {
        // Configuramos el calendario
        $('#reservation').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' - ',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                firstDay: 1
            },
            ranges: {
                'Hoy': [moment(), moment()],
                'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Últimos 7 Días': [moment().subtract(6, 'days'), moment()],
                'Este Mes': [moment().startOf('month'), moment().endOf('month')],
                'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            startDate: '{{ explode(" - ", $rangoFechas)[0] }}',
            endDate: '{{ explode(" - ", $rangoFechas)[1] }}'
        });

        // EVENTO CLAVE: AL APLICAR (SELECCIONAR) FECHA -> ENVIAR FORMULARIO
        $('#reservation').on('apply.daterangepicker', function(ev, picker) {
            // Actualizamos el valor del input (por seguridad)
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            // Enviamos el formulario automáticamente
            $('#searchForm').submit();
        });

        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@stop