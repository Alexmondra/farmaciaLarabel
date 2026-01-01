@extends('adminlte::page')

@section('title', 'Editar Proveedor')

@section('content_header')
<h1><i class="fas fa-edit mr-2"></i>Editar Proveedor</h1>
@stop

@section('content')
@include('inventario.proveedores._form', [
'route' => route('inventario.proveedores.update', $proveedor),
'method' => 'PUT',
'submitText' => 'Actualizar',
'proveedor' => $proveedor
])
@stop

@section('css')
<style>
    body.dark-mode .card.shadow-sm {
        background-color: #343a40 !important;
        border-color: #495057 !important;
        color: #d1d9e0 !important;
    }

    body.dark-mode .card-footer.bg-white {
        background-color: #3e444a !important;
        border-top-color: #495057 !important;
    }

    body.dark-mode .form-control {
        background-color: #2b3035;
        color: #d1d9e0;
        border-color: #5d6874;
    }

    body.dark-mode .form-control:focus {
        background-color: #2b3035;
        color: #d1d9e0;
        border-color: #6c757d;
        box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
    }

    body.dark-mode .custom-control-label {
        color: #d1d9e0 !important;
    }

    body.dark-mode .text-muted {
        color: #a0aec0 !important;
    }
</style>
@stop