@extends('adminlte::page')

@section('title', 'Monitor DIGEMID')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0 text-dark fw-bold">
            <i class="fas fa-server text-primary mr-2"></i>Monitor DIGEMID
        </h1>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid h-100 pb-3">

    {{-- 1. VALIDACIÓN DE SUCURSAL --}}
    @if(isset($sinSucursal) && $sinSucursal)
    <div class="alert alert-danger shadow-sm">
        <h5><i class="icon fas fa-ban"></i> ¡Atención!</h5>
        No has seleccionado una sucursal activa. Por favor, selecciona una sucursal en el menú superior para ver el reporte.
    </div>
    @else

    {{-- ✅ Modal confirmación PDF grande --}}
    @if(session('pdf_confirm'))
    @php($p = session('pdf_confirm'))
    <div class="modal fade" id="pdfConfirmModal" tabindex="-1" role="dialog" aria-labelledby="pdfConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 520px;">
            <div class="modal-content" style="border-radius: 14px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="pdfConfirmModalLabel" style="font-weight: 700;">
                        Confirmar exportación
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body pt-2">
                    <div class="d-flex align-items-start">
                        <div class="mr-3" style="font-size: 28px; line-height: 1;">⚠️</div>

                        <div>
                            <div style="font-weight: 700; font-size: 14px;">
                                El reporte tiene {{ $p['total'] }} filas
                            </div>

                            <div class="text-muted" style="font-size: 13px;">
                                Se recomienda <b>Excel</b> (más rápido y liviano).
                                Si continúas con <b>PDF</b> puede demorar o fallar según la carga del servidor.
                            </div>

                            <div class="mt-2 text-muted" style="font-size: 12px;">
                                ¿Qué deseas hacer?
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-dismiss="modal">
                        Cancelar
                    </button>

                    <a href="{{ $p['excel_url'] }}" class="btn btn-success">
                        📗 Exportar Excel
                    </a>

                    <a href="{{ $p['pdf_url'] }}" class="btn btn-outline-danger">
                        📄 Continuar PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif





    {{-- 2. SI HAY SUCURSAL, MOSTRAMOS TODO --}}
    <form id="filterForm" class="h-100">

        <div class="card shadow-sm border-0 d-flex flex-column" style="height: 80vh;">

            <div class="card-header bg-white py-3 flex-shrink-0">
                <div class="row g-2 align-items-end">

                    <div class="col-lg-3 col-md-4">
                        <label class="small fw-bold text-muted mb-1 text-uppercase">Búsqueda</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" id="inputSearch" class="form-control bg-light border"
                                placeholder="Nombre, código..." autocomplete="off">
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-2">
                        <label class="small fw-bold text-muted mb-1 text-uppercase">Estado</label>
                        <select name="estado_filtro" class="form-select bg-light border" onchange="cargarTabla()">
                            <option value="activos" selected>Solo Activos</option>
                            <option value="todos">Todos</option>
                            <option value="inactivos">Inactivos</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-2">
                        <label class="small fw-bold text-muted mb-1 text-uppercase">Disponibilidad</label>
                        <select name="stock_filtro" class="form-select bg-light border" onchange="cargarTabla()">
                            <option value="todos" selected>Todos</option>
                            <option value="con_stock">Con Stock (>0)</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-2">
                        <label class="d-block mb-1 text-white">.</label>
                        <div class="dropdown w-100">
                            <button class="btn btn-outline-secondary w-100 text-start dropdown-toggle text-truncate" type="button" id="dropdownCols" data-toggle="dropdown">
                                <i class="fas fa-table mr-1"></i> Columnas
                            </button>
                            <div class="dropdown-menu p-3 shadow-lg stop-propagation-click" style="width: 280px;">
                                <h6 class="dropdown-header font-weight-bold text-primary px-0 mb-2">Columnas Visibles</h6>
                                <div style="max-height: 250px; overflow-y: auto;">
                                    @foreach($columnasDisponibles as $key => $label)
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input column-check" type="checkbox" name="cols[]" value="{{ $key }}" id="sw_{{ $key }}"
                                            {{ in_array($key, $colsSeleccionadas) ? 'checked' : '' }}>
                                        <label class="form-check-label small cursor-pointer" for="sw_{{ $key }}">{{ $label }}</label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-2 text-end">
                        <label class="d-block mb-1 text-white">.</label>

                        <div class="btn-group w-100">
                            <button type="button" id="btnExportarExcel" class="btn btn-success fw-bold shadow-sm">
                                <i class="fas fa-file-excel mr-2"></i> Excel
                            </button>
                            <button type="button" id="btnExportarPdf" class="btn btn-danger fw-bold shadow-sm">
                                <i class="fas fa-file-pdf mr-2"></i> PDF
                            </button>
                        </div>
                    </div>


                </div>
            </div>

            <div class="card-body p-0 position-relative flex-grow-1 overflow-hidden bg-light">
                <div id="loader" class="loader-overlay" style="display:none;">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2 small fw-bold text-muted">Cargando...</div>
                    </div>
                </div>
                <div id="tablaContainer" class="h-100 w-100"></div>
            </div>

        </div>
    </form>
    @endif
</div>
@endsection

@section('css')
<style>
    .loader-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(var(--bs-body-bg-rgb), 0.85);
        z-index: 50;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(2px);
    }

    .cursor-pointer {
        cursor: pointer;
    }

    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: transparent;
    }

    ::-webkit-scrollbar-thumb {
        background: #adb5bd;
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #6c757d;
    }
</style>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        @if(!isset($sinSucursal) || !$sinSucursal)
        cargarTabla();
        @endif

        $('.stop-propagation-click').on('click', function(e) {
            e.stopPropagation();
        });
        $(document).on('change', '.column-check', function() {
            cargarTabla();
        });

        let timeout = null;
        $('#inputSearch').on('keyup', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => cargarTabla(), 400);
        });

        $(document).on('click', '#pagination-links a', function(e) {
            e.preventDefault();
            cargarTabla($(this).attr('href'));
        });

        $('#btnExportarExcel').on('click', function() {
            let url = "{{ route('digemid.exportar') }}";
            let params = $('#filterForm').serialize();
            window.location.href = url + "?" + params;
        });

        $(document).on('change', '#selectAll', function() {
            $('.row-checkbox').prop('checked', $(this).is(':checked'));
        });

        @if(session('pdf_confirm'))
        $('#pdfConfirmModal').modal({
            backdrop: true, // ✅ clic fuera cierra
            keyboard: true, // ✅ ESC cierra
            show: true
        });
        @endif

    });

    function cargarTabla(url = null) {
        let targetUrl = url || "{{ route('digemid.index') }}";
        $('#loader').fadeIn(100);
        $.ajax({
            url: targetUrl,
            type: "GET",
            data: $('#filterForm').serialize(),
            success: function(resp) {
                $('#tablaContainer').html(resp);
                $('#loader').fadeOut(100);
            },
            error: function() {
                $('#loader').fadeOut();
            }
        });
    }

    $('#btnExportarExcel').on('click', function() {
        let url = "{{ route('digemid.exportar') }}";
        let params = $('#filterForm').serialize();
        window.location.href = url + "?format=excel&" + params;
    });

    $('#btnExportarPdf').on('click', function() {
        let url = "{{ route('digemid.exportar') }}";
        let params = $('#filterForm').serialize();
        window.location.href = url + "?format=pdf&" + params;
    });
</script>
@endsection