@extends('adminlte::page')

@section('title', 'Nueva Venta')

@section('content_header')
<h1>Nueva Venta</h1>
@stop

@section('css')
<style>
    /* Estilos para el buscador flotante */
    .search-container {
        position: relative;
    }

    #resultados-medicamentos {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        /* Mayor que el resto para flotar */
        max-height: 300px;
        overflow-y: auto;
        background: white;
        border: 1px solid #ced4da;
        border-radius: 0 0 .25rem .25rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        display: none;
        /* Oculto por defecto */
    }

    /* Mostrar cuando tenga contenido y clase activa */
    #resultados-medicamentos.active {
        display: block;
    }
</style>
@stop

@section('content')
<form action="{{ route('ventas.store') }}" method="POST" id="form-venta">
    @csrf
    <input type="hidden" name="caja_sesion_id" value="{{ $cajaAbierta->id }}">
    <input type="hidden" id="sucursal_id" value="{{ $cajaAbierta->sucursal_id }}">
    <input type="hidden" name="items" id="input-items-json" value="[]">

    {{-- Input oculto para el ID del cliente seleccionado --}}
    <input type="hidden" name="cliente_id" id="cliente_id_hidden" required>

    <div class="row">

        {{-- ================= IZQUIERDA: BUSCADOR DE MEDICAMENTOS ================= --}}
        <div class="col-md-5">
            {{-- Incluimos el parcial del buscador (modificado abajo) --}}
            @include('ventas.ventas.partials.buscador_medicamentos')

            {{-- Aquí podrías poner una lista de Categorías rápidas o productos comunes si quisieras llenar el espacio --}}
            <div class="mt-4 text-center text-muted">
                <i class="fas fa-pills fa-3x"></i>
                <p class="mt-2">Busque medicamentos para agregar al carrito</p>
            </div>
        </div>

        {{-- ================= DERECHA: DATOS VENTA + CARRITO + PAGO ================= --}}
        <div class="col-md-7">

            {{-- 1. Datos del Comprobante y Cliente --}}
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-tag"></i> Datos de Venta</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Tipo Comprobante --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Comprobante</label>
                                <select name="tipo_comprobante" id="tipo_comprobante" class="form-control">
                                    <option value="BOLETA">BOLETA</option>
                                    <option value="FACTURA">FACTURA</option>
                                    <option value="TICKET">TICKET</option>
                                </select>
                            </div>
                        </div>

                        {{-- Buscador de Cliente (DNI/RUC) --}}
                        <div class="col-md-8">
                            <div class="form-group">
                                <label id="label-documento">DNI Cliente</label>
                                <div class="input-group">
                                    <input type="text" id="busqueda_cliente" class="form-control" placeholder="Ingrese número">
                                    <div class="input-group-append">
                                        <button class="btn btn-info" type="button" id="btn-buscar-cliente">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <button class="btn btn-success" type="button" id="btn-nuevo-cliente" data-toggle="modal" data-target="#modalNuevoCliente">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Resultado del Cliente Seleccionado --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Cliente Seleccionado:</label>
                                <input type="text" id="nombre_cliente_display" class="form-control bg-light" readonly placeholder="Busque un cliente...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Carrito (Tabla) --}}
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Detalle (Carrito)</h3>
                </div>
                <div class="card-body table-responsive p-0" style="max-height: 300px;">
                    <table class="table table-sm table-head-fixed text-nowrap" id="tabla-carrito">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th width="15%">Lote</th>
                                <th width="10%">Cant.</th>
                                <th width="15%">Precio</th>
                                <th width="15%">Total</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody id="carrito-tbody">
                            <tr id="carrito-vacio">
                                <td colspan="6" class="text-center text-muted py-4">
                                    El carrito está vacío
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5>Total a Pagar:</h5>
                        <h3 class="text-success font-weight-bold">S/ <span id="total-venta">0.00</span></h3>
                    </div>
                </div>
            </div>

            {{-- 3. Pago y Acciones --}}
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label>Medio de Pago</label>
                        <select name="medio_pago" id="medio_pago" class="form-control">
                            <option value="EFECTIVO">EFECTIVO</option>
                            <option value="TARJETA">TARJETA</option>
                            <option value="YAPE">YAPE</option>
                            <option value="PLIN">PLIN</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('ventas.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success btn-lg btn-block ml-2">
                            <i class="fas fa-check-circle"></i> PROCESAR VENTA
                        </button>
                    </div>
                </div>
            </div>

        </div> {{-- Fin Columna Derecha --}}
    </div>
</form>

{{-- MODAL NUEVO CLIENTE (Placeholder) --}}
<div class="modal fade" id="modalNuevoCliente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Nuevo Cliente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Aquí va tu formulario para crear cliente (AJAX)</p>
                {{-- Aquí implementas tu formulario de create cliente --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

@stop

@push('js')
@include('ventas.ventas.partials.buscador_medicamentos_js')

{{-- Scripts específicos para la lógica del cliente --}}
<script>
    $(document).ready(function() {

        // 1. Cambiar etiqueta DNI/RUC según Comprobante
        $('#tipo_comprobante').on('change', function() {
            let tipo = $(this).val();
            let label = $('#label-documento');
            let input = $('#busqueda_cliente');

            if (tipo === 'FACTURA') {
                label.text('RUC Cliente');
                input.attr('placeholder', 'Ingrese RUC de 11 dígitos');
            } else {
                label.text('DNI Cliente');
                input.attr('placeholder', 'Ingrese DNI de 8 dígitos');
            }
            // Limpiar campos al cambiar
            $('#cliente_id_hidden').val('');
            $('#nombre_cliente_display').val('');
            $('#busqueda_cliente').val('');
        });

        // 2. Simulación Búsqueda Cliente (AJAX real pendiente de tu backend)
        $('#btn-buscar-cliente').on('click', function() {
            let doc = $('#busqueda_cliente').val().trim();
            let tipo = $('#tipo_comprobante').val();

            if (doc.length < 8) {
                alert("Ingrese un documento válido");
                return;
            }

            // AQUÍ DEBES HACER TU AJAX REAL PARA BUSCAR AL CLIENTE
            // Ejemplo Simulado:
            // $.get('/ventas/buscar-cliente', { doc: doc }, function(cliente) { ... });

            // Lógica de "Si no encuentra, sugerir crear":
            console.log("Buscando cliente con documento: " + doc);

            // SIMULACION DE EXITO (BORRAR ESTO CUANDO TENGAS EL BACKEND):
            /*
            $('#cliente_id_hidden').val(1); // ID ficticio
            $('#nombre_cliente_display').val('JUAN PEREZ (Simulado)');
            */

            // SIMULACION DE ERROR (NO ENCONTRADO):
            /*
            alert('Cliente no encontrado. Por favor regístrelo.');
            $('#modalNuevoCliente').modal('show'); 
            */
        });
    });
</script>
@endpush