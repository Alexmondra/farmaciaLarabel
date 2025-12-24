@extends('adminlte::page')

@section('title', 'Rectificar Venta')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-bold">
                <i class="fas fa-magic text-purple mr-2"></i> Rectificar Venta
                <small class="text-muted d-block mt-1" style="font-size: 1rem; font-weight: normal;">Corrección fiscal y reenvío a SUNAT</small>
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('facturacion.pendientes') }}">Monitor SUNAT</a></li>
                <li class="breadcrumb-item active">Rectificar</li>
            </ol>
        </div>
    </div>
</div>
@stop

@section('content')

<div class="container-fluid">

    {{-- ALERTA DE ERROR (Diseño Callout) --}}
    <div class="row">
        <div class="col-12">
            <div class="callout callout-danger shadow-sm">
                <h5 class="text-danger font-weight-bold">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Motivo del Rechazo SUNAT
                </h5>
                <p class="mb-0 text-muted">{{ $venta->mensaje_sunat }}</p>
            </div>
        </div>
    </div>

    <form action="{{ route('facturacion.rectificar', $venta->id) }}" method="POST" id="formRectificar">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- COLUMNA IZQUIERDA: FORMULARIO --}}
            <div class="col-lg-8 col-md-12">
                <div class="card card-purple card-outline shadow-sm" style="transition: all 0.3s ease;">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-sliders-h mr-1"></i> Ajuste de Datos
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            {{-- COMPROBANTE (READONLY) --}}
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-normal text-secondary">Documento Afectado</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-file-invoice"></i></span>
                                    </div>
                                    <input type="text" class="form-control font-weight-bold"
                                        value="{{ $venta->tipo_comprobante }} {{ $venta->serie }}-{{ $venta->numero }}" readonly>
                                </div>
                                <small class="text-muted"><i class="fas fa-lock mr-1"></i> El correlativo no cambiará.</small>
                            </div>

                            {{-- FECHA --}}
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-normal text-secondary">Nueva Fecha de Emisión</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                    </div>
                                    <input type="date" name="fecha_emision" class="form-control"
                                        value="{{ $venta->fecha_emision->format('Y-m-d') }}" required>
                                </div>
                                <small class="text-muted"></i> solo cambiar si ha pasado mas de 3 dias desde que se emitio el comprobante</small>

                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- BUSCADOR DE CLIENTE CON AJAX --}}
                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-primary">
                                <i class="fas fa-user-edit mr-1"></i> Cliente (Titular del Comprobante)
                            </label>

                            {{-- El select empieza solo con el cliente actual --}}
                            <select name="cliente_id" id="cliente_ajax" class="form-control" required>
                                @if($venta->cliente)
                                <option value="{{ $venta->cliente_id }}" selected>
                                    {{ $venta->cliente->documento }} - {{ $venta->cliente->nombre_completo }}
                                </option>
                                @endif
                            </select>

                            <small class="form-text text-muted">
                                <i class="fas fa-search mr-1"></i> Escribe el <b>DNI, RUC o Nombre</b> para buscar en la base de datos.
                            </small>
                        </div>

                        <div class="alert alert-info border-0 shadow-none" style="background-color: rgba(23, 162, 184, 0.1); color: #17a2b8;">
                            <div class="d-flex">
                                <div class="mr-3"><i class="fas fa-info-circle fa-2x"></i></div>
                                <div>
                                    <h6 class="text-bold mb-1">Recálculo Automático</h6>
                                    <span style="font-size: 0.9rem;">Al guardar, el sistema recalculará los impuestos (IGV) y totales matemáticamente para asegurar la consistencia del XML.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-right bg-transparent border-top-0 pb-4">
                        <a href="{{ route('facturacion.pendientes') }}" class="btn btn-default mr-2">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-purple btn-lg shadow-sm btn-guardar px-4">
                            <i class="fas fa-paper-plane mr-2"></i> Rectificar y Enviar
                        </button>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: TICKET --}}
            <div class="col-lg-4 col-md-12">
                <div class="card card-outline card-secondary shadow-sm">
                    <div class="card-header border-0 pb-0">
                        <h3 class="card-title text-muted">Resumen de Venta</h3>
                    </div>
                    <div class="card-body pt-2">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge badge-success px-3 py-2" style="font-size: 0.9rem">Total</span>
                            <h3 class="font-weight-bold text-success mb-0">S/ {{ number_format($venta->total_neto, 2) }}</h3>
                        </div>

                        <div class="dropdown-divider"></div>

                        <div class="item-list mt-3 pr-2" style="max-height: 300px; overflow-y: auto;">
                            @foreach($venta->detalles as $detalle)
                            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom border-light">
                                <div class="d-flex align-items-start">
                                    <span class="badge badge-secondary mr-2">{{ $detalle->cantidad }}</span>
                                    <span class="text-sm" style="line-height: 1.2;">
                                        {{ Str::limit($detalle->medicamento->nombre, 25) }}
                                    </span>
                                </div>
                                <span class="font-weight-bold text-sm">S/ {{ number_format($detalle->subtotal_neto, 2) }}</span>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-4 p-3 rounded" style="background-color: rgba(0,0,0,0.03);">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-secondary text-sm">Op. Gravada</span>
                                <span class="text-sm font-weight-bold">S/ {{ number_format($venta->op_gravada, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-secondary text-sm">Op. Exonerada</span>
                                <span class="text-sm font-weight-bold">S/ {{ number_format($venta->op_exonerada, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-secondary text-sm">IGV (18%)</span>
                                <span class="text-sm font-weight-bold">S/ {{ number_format($venta->total_igv, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@stop

@section('css')
{{-- CDN del Tema Bootstrap 4 para Select2 (Versión correcta) --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

<style>
    /* Estilo botón morado */
    .btn-purple {
        background-color: #6f42c1;
        border-color: #6f42c1;
        color: white;
    }

    .btn-purple:hover {
        background-color: #5a32a3;
        color: white;
    }

    /* --- MODO OSCURO: CORRECCIONES VISUALES --- */

    body.dark-mode .select2-container--bootstrap4 .select2-selection--single {
        background-color: #343a40 !important;
        border-color: #6c757d !important;
        color: #fff !important;
        height: calc(2.25rem + 2px) !important;
    }

    body.dark-mode .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        color: #fff !important;
        line-height: 2.25rem;
    }

    body.dark-mode .select2-container--bootstrap4 .select2-dropdown {
        background-color: #343a40 !important;
        border-color: #6c757d;
    }

    body.dark-mode .select2-search__field {
        background-color: #3f474e !important;
        color: #fff !important;
        border-color: #6c757d !important;
    }

    body.dark-mode .select2-results__option {
        color: #fff !important;
    }

    body.dark-mode .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
        background-color: #6f42c1 !important;
        color: #ffffff !important;
        font-weight: bold;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {

        // Inicializamos Select2 con AJAX
        $('#cliente_ajax').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: '🔍 Escribe DNI o Nombre...',
            allowClear: false,
            minimumInputLength: 2, // Empieza a buscar tras 2 letras
            language: {
                inputTooShort: function() {
                    return "Escribe al menos 2 caracteres...";
                },
                searching: function() {
                    return "Buscando...";
                },
                noResults: function() {
                    return "No se encontró el cliente";
                },
                errorLoading: function() {
                    return "Error de conexión.";
                }
            },
            ajax: {
                url: '{{ route("facturacion.clientes.buscar") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        // Feedback al guardar
        $('#formRectificar').on('submit', function() {
            var btn = $('.btn-guardar');
            btn.prop('disabled', true);
            btn.html('<i class="fas fa-circle-notch fa-spin mr-2"></i> Enviando...');
        });
    });
</script>
@stop