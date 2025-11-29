@extends('adminlte::page')

@section('title', 'Punto de Venta')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark" style="font-size: 1.5rem;">Punto de Venta</h1>
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
        <li class="breadcrumb-item active">Nueva Venta</li>
    </ol>
</div>
@stop

@section('css')
<style>
    /* ==========================================
       ESTILOS PARA QUE EL BUSCADOR FLOTE
       ========================================== */

    /* Hacemos que los resultados floten sobre el carrito y no empujen el contenido */
    .search-container,
    .search-container-cat {
        position: relative;
        /* Referencia para el absolute hijo */
    }

    #resultados-medicamentos {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 9999;
        /* ENCIMA de todo */
        max-height: 350px;
        overflow-y: auto;
        background: white;
        border: 1px solid #ced4da;
        border-top: none;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.19), 0 6px 6px rgba(0, 0, 0, 0.23);
        border-radius: 0 0 5px 5px;
        display: none;
        /* Oculto por defecto */
    }

    /* Clase para mostrarlo */
    #resultados-medicamentos.active {
        display: block;
    }

    /* Ajuste visual para la tabla del carrito */
    .table-carrito th {
        background-color: #f4f6f9;
        border-top: 0;
        font-size: 0.9rem;
    }

    .table-carrito td {
        vertical-align: middle;
        font-size: 0.95rem;
    }

    /* Total Grande */
    .total-display {
        font-size: 2.2rem;
        font-weight: bold;
        color: #28a745;
        line-height: 1.2;
    }
</style>
@stop

@section('content')

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>¡Ups! Algo salió mal:</strong>
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<form action="{{ route('ventas.store') }}" method="POST" id="form-venta">
    @csrf
    {{-- Datos ocultos necesarios --}}
    <input type="hidden" name="caja_sesion_id" value="{{ $cajaAbierta->id }}">
    <input type="hidden" id="sucursal_id" value="{{ $cajaAbierta->sucursal_id }}">
    <input type="hidden" name="items" id="input-items-json" value="[]">
    <input type="hidden" name="cliente_id" id="cliente_id_hidden">
    {{-- ============================================================== 
       FILA SUPERIOR: BUSCADOR (Izquierda) | CLIENTE (Derecha)
       ============================================================== --}}
    <div class="row">

        {{-- 1. SECCIÓN BUSCADOR (Incluye el partial que subiste) --}}
        <div class="col-md-7">
            {{-- Este include trae el buscador Y el modal de lotes que está dentro --}}
            @include('ventas.ventas.partials.buscador_medicamentos')
        </div>

        {{-- 2. SECCIÓN CLIENTE (Compacta) --}}
        <div class="col-md-5">
            <div class="card card-primary card-outline" style="height: 100%;">
                <div class="card-header py-2">
                    <h3 class="card-title" style="font-size: 1.1rem;"><i class="fas fa-user-tag"></i> Datos del Cliente</h3>
                </div>
                <div class="card-body py-2">
                    <div class="form-row">
                        {{-- Tipo Comprobante --}}
                        <div class="col-4">
                            <div class="form-group mb-2">
                                <label class="mb-1 small text-muted">Tipo</label>
                                <select name="tipo_comprobante" id="tipo_comprobante" class="form-control form-control-sm">
                                    <option value="BOLETA">BOLETA</option>
                                    <option value="FACTURA">FACTURA</option>
                                    <option value="TICKET">TICKET</option>
                                </select>
                            </div>
                        </div>
                        {{-- Buscador DNI/RUC --}}
                        <div class="col-8">
                            <div class="form-group mb-2">
                                <label class="mb-1 small text-muted" id="label-documento">DNI Cliente</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" id="busqueda_cliente" class="form-control" placeholder="Ingrese número">
                                    <div class="input-group-append">
                                        <button class="btn btn-info" type="button" id="btn-buscar-cliente">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <button class="btn btn-secondary" type="button" data-toggle="modal" data-target="#modalNuevoCliente">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Nombre del Cliente Seleccionado --}}
                    <div class="form-group mb-0">
                        <input type="text" id="nombre_cliente_display" class="form-control form-control-sm bg-light font-weight-bold text-primary" readonly placeholder="--- Cliente General ---">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================== 
       FILA INFERIOR: CARRITO (Izquierda ancha) | PAGO (Derecha angosta)
       ============================================================== --}}
    <div class="row mt-2">

        {{-- 3. CARRITO DE COMPRAS --}}
        <div class="col-md-9">
            <div class="card card-outline card-secondary" style="min-height: 400px;">
                <div class="card-header py-2 bg-light">
                    <h3 class="card-title text-muted" style="font-size: 1rem;"><i class="fas fa-shopping-cart"></i> Detalle de la Venta</h3>
                </div>
                {{-- Tabla con scroll si hay muchos productos --}}
                <div class="card-body table-responsive p-0" style="height: 350px;">
                    <table class="table table-head-fixed text-nowrap table-carrito">
                        <thead>
                            <tr>
                                <th style="width: 40%">Producto</th>
                                <th style="width: 10%" class="text-center">Cant.</th>
                                <th style="width: 15%" class="text-right">P. Unit</th>
                                <th style="width: 15%" class="text-right">Total</th>
                                <th style="width: 5%"></th>
                            </tr>
                        </thead>
                        <tbody id="carrito-tbody">
                            <tr id="carrito-vacio">
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-shopping-basket fa-3x mb-3 text-gray-300"></i><br>
                                    El carrito está vacío.<br>
                                    <small>Busque productos arriba para comenzar.</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 4. ZONA DE PAGO --}}
        <div class="col-md-3">
            <div class="card card-success shadow-sm" style="min-height: 400px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cash-register"></i> Cobro</h3>
                </div>
                <div class="card-body text-center d-flex flex-column justify-content-between">

                    <div>
                        <h6 class="text-muted text-uppercase mb-1">Total a Pagar</h6>
                        <div class="total-display mb-3">S/ <span id="total-venta">0.00</span></div>

                        <div class="form-group text-left">
                            <label class="small">Medio de Pago</label>
                            <select name="medio_pago" id="medio_pago" class="form-control form-control-lg">
                                <option value="EFECTIVO">EFECTIVO</option>
                                <option value="TARJETA">TARJETA</option>
                                <option value="YAPE">YAPE</option>
                                <option value="PLIN">PLIN</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-light btn-block btn-lg text-success font-weight-bold shadow-sm mb-2">
                            <i class="fas fa-check-circle"></i> CONFIRMAR
                        </button>

                        <a href="{{ route('ventas.index') }}" class="btn btn-outline-light btn-block text-white border-white mt-2" style="opacity: 0.8;">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- MODAL NUEVO CLIENTE (Este sí va aparte o aquí mismo si es simple) --}}
<div class="modal fade" id="modalNuevoCliente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary py-2">
                <h5 class="modal-title text-white">Nuevo Cliente</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="text-center text-muted">Formulario de registro rápido...</p>
                {{-- Aquí iría tu form de cliente --}}
            </div>
        </div>
    </div>
</div>

@stop


@section('js')
{{-- 1. PASAMOS LOS DATOS DE PHP A UNA VARIABLE GLOBAL JS --}}
<script>
    window.listadoCategorias = @json($categorias);
</script>

{{-- 2. Incluimos SOLO el JS del buscador/modal --}}
@include('ventas.ventas.partials.buscador_medicamentos_js')
@stop