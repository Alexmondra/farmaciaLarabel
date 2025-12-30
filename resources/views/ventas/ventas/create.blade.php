@extends('adminlte::page')

@section('title', 'Punto de Venta')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark font-weight-bold">
        <i class="fas fa-cash-register mr-2 text-success"></i>Punto de Venta
    </h1>
</div>
@stop

@section('css')
{{-- Estilos Modulares (Sin cambios, usa los que ya creaste) --}}
@include('ventas.ventas.partials.css_pos')
@stop

@section('content')

{{-- Alertas de Error Globales --}}
@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm">
    <strong><i class="fas fa-exclamation-triangle"></i> Corrija los errores:</strong>
    <ul class="mb-0 mt-1 pl-3">
        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

<form action="{{ route('ventas.store') }}" method="POST" id="form-venta">
    @csrf
    {{-- Inputs Hidden Globales --}}
    <input type="hidden" name="caja_sesion_id" value="{{ $cajaAbierta->id }}">
    <input type="hidden" id="sucursal_id" value="{{ $cajaAbierta->sucursal_id }}">
    <input type="hidden" name="items" id="input-items-json" value="[]">
    <input type="hidden" name="cliente_id" id="cliente_id_hidden">

    {{-- =======================================================
         FILA 1: BUSCADOR (Izquierda) + CLIENTE (Derecha)
         ======================================================= --}}
    <div class="row">
        {{-- 1. Buscador Medicamentos (Ancho 7) --}}
        <div class="col-md-7">
            @include('ventas.ventas.partials.html_buscador')
        </div>

        {{-- 2. Datos Cliente (Ancho 5) --}}
        <div class="col-md-5">
            @include('ventas.ventas.partials.html_cliente')
        </div>
    </div>

    {{-- =======================================================
         FILA 2: CARRITO (Izquierda) + COBRO (Derecha)
         ======================================================= --}}
    <div class="row mt-2">
        {{-- 3. Tabla Carrito (Ancho 9) --}}
        <div class="col-md-9">
            @include('ventas.ventas.partials.html_carrito')
        </div>

        {{-- 4. Panel Cobro (Ancho 3) --}}
        <div class="col-md-3">
            @include('ventas.ventas.partials.html_cobro')
        </div>
    </div>
</form>

{{-- MODALES (Se cargan fuera del flujo visual) --}}
@include('ventas.ventas.partials.modal_lotes')
@include('ventas.clientes.modal-create-edit')
@include('ventas.clientes.modal-show')
<input type="hidden" id="cliente_id_hidden" name="cliente_id">
@stop

@section('js')
{{-- Variable necesaria para las categorías --}}
<script>
    window.listadoCategorias = @json($categorias);
</script>

{{-- Tu Lógica Original Unificada --}}
@include('ventas.ventas.partials.js_pos_core')
@stop