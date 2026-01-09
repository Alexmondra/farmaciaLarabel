@extends('adminlte::page')

@section('title', $medicamento->nombre)

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
<style>
    /* ============================================================
       1. COLORES ADAPTATIVOS GENERALES
       ============================================================ */

    /* --- MODO CLARO --- */
    body:not(.dark-mode) {
        background-color: #f4f6f9 !important;
        color: #1f2d3d !important;
    }

    body:not(.dark-mode) .bg-dark,
    body:not(.dark-mode) .card.bg-dark {
        background-color: #ffffff !important;
        color: #1f2d3d !important;
        border: 1px solid #dee2e6 !important;
    }

    body:not(.dark-mode) label,
    body:not(.dark-mode) .card-title,
    body:not(.dark-mode) h3,
    body:not(.dark-mode) .text-white,
    body:not(.dark-mode) th,
    body:not(.dark-mode) td {
        color: #1f2d3d !important;
    }

    body:not(.dark-mode) .form-control.bg-secondary,
    body:not(.dark-mode) textarea.bg-secondary {
        background-color: #ffffff !important;
        color: #495057 !important;
        border: 1px solid #ced4da !important;
    }

    /* ============================================================
       2. CORRECCIÓN ESPECÍFICA PARA SELECT2 (MODO CLARO)
       ============================================================ */

    /* Fondo del buscador y lista de resultados */
    body:not(.dark-mode) .select2-dropdown {
        background-color: #ffffff !important;
        border: 1px solid #ced4da !important;
        color: #1f2d3d !important;
    }

    /* Input de búsqueda dentro del Select2 */
    body:not(.dark-mode) .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
        background-color: #ffffff !important;
        color: #495057 !important;
        border: 1px solid #ced4da !important;
    }

    /* Texto de las opciones en la lista */
    body:not(.dark-mode) .select2-results__option {
        color: #1f2d3d !important;
    }

    /* Opción resaltada/seleccionada */
    body:not(.dark-mode) .select2-results__option--highlighted[aria-selected] {
        background-color: #007bff !important;
        color: #ffffff !important;
    }

    /* El cuadro principal del Select2 (cerrado) */
    body:not(.dark-mode) .select2-container--bootstrap4 .select2-selection--single {
        background-color: #ffffff !important;
        border: 1px solid #ced4da !important;
        color: #495057 !important;
    }

    body:not(.dark-mode) .select2-selection__rendered {
        color: #495057 !important;
    }

    /* ============================================================
       3. RESPONSIVE Y MEJORAS UI
       ============================================================ */
    @media (max-width: 768px) {
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
        }

        .btn-regresar span {
            display: none;
        }
    }

    .select2-container--bootstrap4 .select2-selection--single {
        height: 31px !important;
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