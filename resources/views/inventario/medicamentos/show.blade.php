@extends('adminlte::page')

@section('title', 'Detalles del Medicamento')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-pills"></i> {{ $medicamento->nombre }}</h1>
    <div>
        <a href="{{ route('inventario.medicamentos.edit', $medicamento) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="{{ route('inventario.medicamentos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>
@stop

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif

<!-- Pestañas principales -->
<div class="card">
    <div class="card-header p-0">
        <ul class="nav nav-tabs" id="medicamentoTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab">
                    <i class="fas fa-info-circle"></i> Información
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="sucursales-tab" data-toggle="tab" href="#sucursales" role="tab">
                    <i class="fas fa-store"></i> Sucursales
                    <span class="badge badge-primary ml-1">{{ $sucursalesMedicamento->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="lotes-tab" data-toggle="tab" href="#lotes" role="tab">
                    <i class="fas fa-boxes"></i> Lotes
                    <span class="badge badge-info ml-1">{{ $lotes->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="historial-tab" data-toggle="tab" href="#historial" role="tab">
                    <i class="fas fa-history"></i> Historial
                </a>
            </li>
        </ul>
    </div>
    
    <div class="card-body">
        <div class="tab-content" id="medicamentoTabsContent">
            <!-- Pestaña Información -->
            <div class="tab-pane fade show active" id="info" role="tabpanel">
                @include('inventario.medicamentos.partials.tab-informacion')
            </div>
            
            <!-- Pestaña Sucursales -->
            <div class="tab-pane fade" id="sucursales" role="tabpanel">
                @include('inventario.medicamentos.partials.tab-sucursales')
            </div>
            
            <!-- Pestaña Lotes -->
            <div class="tab-pane fade" id="lotes" role="tabpanel">
                @include('inventario.medicamentos.partials.tab-lotes')
            </div>
            
            <!-- Pestaña Historial -->
            <div class="tab-pane fade" id="historial" role="tabpanel">
                @include('inventario.medicamentos.partials.tab-historial')
            </div>
        </div>
    </div>
</div>

<!-- Modales -->
@include('inventario.medicamentos.partials.modal-agregar-sucursal')
@include('inventario.medicamentos.partials.modal-agregar-lote')
@include('inventario.medicamentos.partials.modal-editar-sucursal')
@stop

@section('css')
<style>
.nav-tabs .nav-link {
    border-radius: 0;
}
.nav-tabs .nav-link.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}
.badge {
    font-size: 0.7em;
}
</style>
@stop

@section('js')
<script>
// Activar pestañas con hash en URL
$(document).ready(function() {
    const hash = window.location.hash;
    if (hash) {
        $('.nav-tabs a[href="' + hash + '"]').tab('show');
    }
    
    // Actualizar hash cuando cambie de pestaña
    $('.nav-tabs a').on('click', function() {
        window.location.hash = $(this).attr('href');
    });
});

// Auto-hide alerts después de 5 segundos
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 5000);
</script>
@stop