@extends('adminlte::page')

@section('title', $medicamento->nombre)

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
<style>
    /* Si AdminLTE NO está en modo oscuro, forzamos que las tarjetas "dark" se vean blancas */
    body:not(.dark-mode) .bg-dark {
        background-color: #ffffff !important;
        color: #1f2d3d !important;
    }

    body:not(.dark-mode) .bg-secondary {
        background-color: #f4f6f9 !important;
        color: #1f2d3d !important;
        border: 1px solid #dee2e6 !important;
    }

    /* Ajuste de inputs para que no se pierdan en modo claro */
    body:not(.dark-mode) .form-control.bg-secondary {
        background-color: #fff !important;
        color: #495057 !important;
        border: 1px solid #ced4da !important;
    }

    /* --- MEJORAS RESPONSIVE --- */
    @media (max-width: 768px) {

        /* Encabezados de sucursal se apilan en móvil */
        .card-header.d-flex {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .card-header .btn-group,
        .card-header .btn {
            width: 100%;
            margin-top: 10px;
            margin-right: 0 !important;
        }

        /* Tabla con scroll para no romper el diseño */
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Ajuste de contenedores de imagen */
        #preview_container {
            margin: 0 auto 15px auto;
        }
    }

    /* --- ESTILO DE FORMULARIOS --- */
    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .custom-switch .custom-control-label {
        cursor: pointer;
    }

    /* Mantener coherencia en el modo oscuro nativo */
    .dark-mode .bg-secondary {
        background-color: #4b545c !important;
        color: #fff !important;
    }

    /* para medicantos en si categorias*/
    .select2-container--bootstrap4 .select2-selection--single {
        height: 31px !important;
        border-radius: 4px !important;
        transition: background-color 0.3s, color 0.3s;
    }

    /* --- MODO CLARO (Cuando NO existe .dark-mode) --- */
    body:not(.dark-mode) .select2-container--bootstrap4 .select2-selection--single {
        background-color: #fff !important;
        border: 1px solid #ced4da !important;
    }

    body:not(.dark-mode) .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        color: #495057 !important;
        line-height: 31px !important;
    }

    body:not(.dark-mode) .select2-dropdown {
        background-color: #ffffff !important;
        color: #495057 !important;
        border: 1px solid #ced4da !important;
    }

    body:not(.dark-mode) .select2-results__option {
        background-color: #ffffff !important;
        color: #495057 !important;
    }

    /* --- MODO OSCURO (Cuando existe .dark-mode) --- */
    body.dark-mode .select2-container--bootstrap4 .select2-selection--single {
        background-color: #6c757d !important;
        /* El gris que tenías */
        border: none !important;
    }

    body.dark-mode .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        color: #ffffff !important;
        line-height: 31px !important;
    }

    body.dark-mode .select2-dropdown {
        background-color: #343a40 !important;
        color: #ffffff !important;
        border: 1px solid #6c757d !important;
    }

    body.dark-mode .select2-search--dropdown .select2-search__field {
        background-color: #3f474e !important;
        color: #ffffff !important;
        border: 1px solid #6c757d !important;
    }

    /* --- COMUNES (Z-index y Highlight) --- */
    .select2-dropdown {
        z-index: 9999 !important;
    }

    .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
        background-color: #007bff !important;
        color: #ffffff !important;
    }
</style>
@stop


@section('content')
<div class="row pt-3">
    <div class="col-lg-4 col-md-5">
        @include('inventario.medicamentos.partials._edit_medicamento')
    </div>
    <div class="col-lg-8 col-md-7">
        <input type="hidden" id="edit_sucursal_id">
        @include('inventario.medicamentos.partials._listado_lotes')
    </div>
</div>
@include('inventario.medicamentos.partials._modales_lotes')
@endsection

@section('js')
<script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
@include('inventario.medicamentos.partials._scripts_js')
@stop