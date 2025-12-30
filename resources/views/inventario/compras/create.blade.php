@extends('adminlte::page')

@section('title', 'Registrar compra')

@section('content_header')
<h1>Registrar nueva compra</h1>
@stop

@include('inventario.compras.partials.styles')

@section('content')

<form action="{{ route('compras.store') }}" method="POST" enctype="multipart/form-data" id="form-compra">
    @csrf

    {{-- 1. HEADER (Datos Proveedor) --}}
    @include('inventario.compras.partials.header')

    {{-- 2. ITEMS (Tabla) --}}
    @include('inventario.compras.partials.items')

    {{-- Espacio para que no tape la barra fija --}}
    <div style="height: 150px;"></div>

    {{-- 3. FOOTER (Botón Guardar) --}}
    @include('inventario.compras.partials.footer')

</form>
{{-- AQUÍ CIERRA EL FORMULARIO DE COMPRA --}}


{{-- ========================================================= --}}
{{-- ZONA DE MODALES --}}
{{-- ========================================================= --}}

{{-- 1. MODAL VER PROVEEDOR --}}
<div class="modal fade" id="modalVerProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white py-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-id-card mr-2"></i> Datos del Proveedor</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <h5 class="text-center font-weight-bold mb-1" id="view_razon_social">--</h5>
                <p class="text-center text-muted mb-3" id="view_ruc">RUC: --</p>
                <hr>
                <p class="mb-1"><i class="fas fa-phone-alt text-info mr-2"></i> <span id="view_telefono"></span></p>
                <p class="mb-1"><i class="fas fa-envelope text-info mr-2"></i> <span id="view_email"></span></p>
                <p class="mb-0"><i class="fas fa-map-marker-alt text-info mr-2"></i> <span id="view_direccion"></span></p>
            </div>
        </div>
    </div>
</div>

{{-- 2. MODAL CREAR PROVEEDOR --}}
<div class="modal fade" id="modalCrearProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-plus-circle mr-2"></i> Nuevo Proveedor</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formNuevoProveedor">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-2"><label class="small font-weight-bold">RUC *</label><input type="text" name="ruc" class="form-control input-enhanced" required></div>
                    <div class="form-group mb-2"><label class="small font-weight-bold">Razón Social *</label><input type="text" name="razon_social" class="form-control input-enhanced" required></div>
                    <div class="row">
                        <div class="col-6 mb-2"><label class="small font-weight-bold">Teléfono</label><input type="text" name="telefono" class="form-control input-enhanced"></div>
                        <div class="col-6 mb-2"><label class="small font-weight-bold">Email</label><input type="email" name="email" class="form-control input-enhanced"></div>
                    </div>
                    <div class="mb-0"><label class="small font-weight-bold">Dirección</label><input type="text" name="direccion" class="form-control input-enhanced"></div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm">Guardar Proveedor</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 1. MODAL CREAR MEDICAMENTO (ESTILO UNIFICADO CON COMPRAS) --}}
@include('inventario.medicamentos.general.modals')

@stop

@include('inventario.compras.partials.scripts')