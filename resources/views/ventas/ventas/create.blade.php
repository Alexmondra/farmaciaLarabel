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
@include('ventas.ventas.partials.buscador_clientes_css')

<style>
    /* --- ESTILOS POS --- */
    .search-container,
    .search-container-cat {
        position: relative;
    }

    #resultados-medicamentos {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 9999;
        max-height: 350px;
        overflow-y: auto;
        background: white;
        border: 1px solid #ced4da;
        border-top: none;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.19);
        border-radius: 0 0 5px 5px;
        display: none;
    }

    #resultados-medicamentos.active {
        display: block;
    }

    /* CLASE NUEVA: Item seleccionado con teclado */
    /* --- ESTILO SELECCIÓN TECLADO (Medicamentos Y Categorías) --- */
    .resultado-medicamento.active-key,
    .item-categoria.active-key {
        background-color: #28a745 !important;
        /* Fondo Verde */
        color: white !important;
        /* Texto Blanco */
        border-color: #28a745;
        font-weight: bold;
    }

    /* Asegurar que el texto pequeño también se vuelva blanco */
    .resultado-medicamento.active-key small,
    .resultado-medicamento.active-key .text-muted,
    .item-categoria.active-key small {
        color: #e9ecef !important;
    }

    /* Asegurar que el texto interno también cambie a blanco */
    .resultado-medicamento.active-key small,
    .resultado-medicamento.active-key .text-muted {
        color: #e9ecef !important;
    }

    .table-carrito th {
        background-color: #f4f6f9;
        border-top: 0;
        font-size: 0.9rem;
    }

    .table-carrito td {
        vertical-align: middle;
        font-size: 0.95rem;
    }

    .total-display {
        font-size: 2.2rem;
        font-weight: bold;
        color: #28a745;
        line-height: 1.2;
    }

    /* Estilos Calculadora Vuelto */
    .input-pago {
        font-size: 1.2rem;
        font-weight: bold;
        text-align: center;
    }

    .vuelto-display {
        font-size: 1.5rem;
        font-weight: 800;
        color: #dc3545;
    }

    /* Rojo para llamar atención */
</style>

@include('ventas.ventas.partials.buscador_clientes_css')

<style>
    /* ESTILOS ESPECÍFICOS DEL POS */

    /* Resultados de medicamentos flotantes */
    .search-container,
    .search-container-cat {
        position: relative;
    }

    #resultados-medicamentos {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 9999;
        max-height: 350px;
        overflow-y: auto;
        background: white;
        border: 1px solid #ced4da;
        border-top: none;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.19);
        border-radius: 0 0 5px 5px;
        display: none;
    }

    #resultados-medicamentos.active {
        display: block;
    }

    /* Tabla del Carrito */
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

{{-- ALERTA DE ERRORES GLOBALES --}}
@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <strong><i class="fas fa-exclamation-triangle"></i> Por favor corrija los siguientes errores:</strong>
    <ul class="mb-0 mt-1 pl-3">
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

    {{-- INPUTS OCULTOS --}}
    <input type="hidden" name="caja_sesion_id" value="{{ $cajaAbierta->id }}">
    <input type="hidden" id="sucursal_id" value="{{ $cajaAbierta->sucursal_id }}">
    <input type="hidden" name="items" id="input-items-json" value="[]">
    <input type="hidden" name="cliente_id" id="cliente_id_hidden">

    {{-- FILA 1: BUSCADORES --}}
    <div class="row">

        {{-- 1. BUSCADOR DE MEDICAMENTOS --}}
        <div class="col-md-7">
            @include('ventas.ventas.partials.buscador_medicamentos')
        </div>

        {{-- 2. DATOS DEL CLIENTE (DISEÑO FINAL) --}}
        <div class="col-md-5">
            <div class="card card-primary card-outline card-cliente-pos">
                <div class="card-header py-2">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-user-tag text-primary mr-1"></i> Datos del Cliente
                    </h3>
                </div>

                <div class="card-body py-2">
                    <div class="form-row align-items-end">
                        {{-- TIPO --}}
                        <div class="col-4">
                            <div class="form-group mb-2">
                                <label class="mb-1 text-muted small font-weight-bold">TIPO</label>
                                <select name="tipo_comprobante" id="tipo_comprobante" class="form-control form-control-sm font-weight-bold">
                                    <option value="BOLETA">DNI / BOL</option>
                                    <option value="FACTURA">RUC / FACT</option>
                                    <option value="TICKET">TICKET</option>
                                </select>
                            </div>
                        </div>

                        {{-- INPUT BUSCADOR --}}
                        <div class="col-8">
                            <div class="form-group mb-2">
                                <label class="mb-1 text-muted small font-weight-bold" id="label-documento">NÚMERO</label>
                                <div class="input-group input-group-sm">
                                    <input type="text"
                                        id="busqueda_cliente"
                                        class="form-control input-cliente-pos"
                                        placeholder="Ingrese 8 dígitos"
                                        autocomplete="off">

                                    <div class="input-group-append">
                                        {{-- Loader --}}
                                        <span class="input-group-text loader-input d-none" id="loader-cliente">
                                            <i class="fas fa-circle-notch fa-spin"></i>
                                        </span>
                                        {{-- Botones Acciones --}}
                                        <button class="btn btn-success d-none" type="button" id="btn-crear-cliente" title="Nuevo">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <button class="btn btn-primary d-none" type="button" id="btn-ver-cliente" title="Ver Datos">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- DISPLAY NOMBRE --}}
                    <div class="form-group mb-0 mt-1">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                            </div>
                            <input type="text" id="nombre_cliente_display" class="form-control display-nombre-cliente" readonly placeholder="--- Cliente General ---">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILA 2: DETALLE Y PAGO --}}
    <div class="row mt-2">

        {{-- 3. CARRITO --}}
        <div class="col-md-9">
            <div class="card card-outline card-secondary" style="height: 100%; min-height: 400px;">
                <div class="card-header py-2 bg-light border-bottom-0">
                    <h3 class="card-title text-muted font-weight-bold">
                        <i class="fas fa-shopping-cart mr-1"></i> Detalle de la Venta
                    </h3>
                </div>
                <div class="card-body table-responsive p-0" style="height: 350px;">
                    <table class="table table-head-fixed text-nowrap table-carrito mb-0">
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
                                <td colspan="6" class="text-center text-muted py-5 align-middle">
                                    <div class="opacity-50">
                                        <i class="fas fa-shopping-basket fa-3x mb-3"></i><br>
                                        <span class="font-weight-bold">El carrito está vacío.</span><br>
                                        <small>Busque productos arriba para comenzar.</small>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 4. COBRO --}}
        <div class="col-md-3">
            <div class="card card-success shadow-sm" style="height: 100%; min-height: 400px;">
                <div class="card-header text-center py-3">
                    <h3 class="card-title float-none font-weight-bold">
                        <i class="fas fa-wallet mr-1"></i> COBRO
                    </h3>
                </div>
                <div class="card-body text-center d-flex flex-column justify-content-between p-4">

                    <div>
                        <h6 class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 0.8rem;">Total a Pagar</h6>
                        <div class="total-display mb-3">S/ <span id="total-venta">0.00</span></div>

                        {{-- MEDIO DE PAGO --}}
                        <div class="form-group text-left mb-3">
                            <label class="small font-weight-bold text-uppercase text-muted">Medio de Pago</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white"><i class="fas fa-money-bill-wave text-success"></i></span>
                                </div>
                                <select name="medio_pago" id="medio_pago" class="form-control form-control-lg font-weight-bold">
                                    <option value="EFECTIVO">EFECTIVO</option>
                                    <option value="TARJETA">TARJETA</option>
                                    <option value="YAPE">YAPE</option>
                                    <option value="PLIN">PLIN</option>
                                </select>
                            </div>
                        </div>

                        {{-- CALCULADORA DE VUELTO (Solo visible en Efectivo) --}}
                        <div id="bloque-calculadora">
                            <div class="form-group text-left mb-2">
                                <label class="small font-weight-bold text-uppercase text-muted">Paga con (S/)</label>
                                <input type="number" id="input-paga-con" class="form-control input-pago" placeholder="0.00" step="0.10" min="0">
                            </div>

                            <div class="mt-2 p-2 bg-light rounded border border-light">
                                <small class="text-muted text-uppercase font-weight-bold">Vuelto</small><br>
                                <span class="vuelto-display">S/ <span id="txt-vuelto">0.00</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-light btn-block btn-lg text-success font-weight-bold shadow-sm py-3 mb-3">
                            <i class="fas fa-check-circle mr-2"></i> CONFIRMAR VENTA
                        </button>

                        <a href="{{ route('ventas.index') }}" class="btn btn-outline-light btn-block text-white border-white btn-sm" style="opacity: 0.8;">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- MODALES MODULARES (Sin basura HTML) --}}
@include('ventas.clientes.modal-create-edit')
@include('ventas.clientes.modal-show')

@stop

@section('js')
{{-- 1. DATOS PHP A JS --}}
<script>
    window.listadoCategorias = @json($categorias);
</script>

<script>
    $(document).ready(function() {

        /* ==========================================
           LOGICA DE COBRO Y VUELTO
           ========================================== */

        // 1. Mostrar/Ocultar Calculadora según Medio de Pago
        $('#medio_pago').change(function() {
            let metodo = $(this).val();
            if (metodo === 'EFECTIVO') {
                $('#bloque-calculadora').slideDown();
                $('#input-paga-con').focus();
            } else {
                $('#bloque-calculadora').slideUp();
                $('#input-paga-con').val(''); // Limpiar
                $('#txt-vuelto').text('0.00');
            }
        });

        // 2. Cálculo en tiempo real
        $('#input-paga-con').on('input', function() {
            // Obtenemos el total actual del texto (quitando basura si hubiera)
            let totalTexto = $('#total-venta').text();
            let total = parseFloat(totalTexto) || 0;

            calcularVuelto(total);
        });

        // Función Global (para poder llamarla desde el renderCarrito)
        window.calcularVuelto = function(totalVenta) {
            // Si no estamos en efectivo, no hacemos nada visual
            if ($('#medio_pago').val() !== 'EFECTIVO') return;

            let pagaCon = parseFloat($('#input-paga-con').val()) || 0;
            let vuelto = pagaCon - totalVenta;

            // Validación visual
            let elVuelto = $('#txt-vuelto');

            if (vuelto < 0) {
                elVuelto.text('Falta dinero');
                elVuelto.parent().removeClass('text-success').addClass('text-danger');
            } else {
                elVuelto.text(vuelto.toFixed(2));
                elVuelto.parent().removeClass('text-danger').addClass('text-success');
            }
        };

        // VALIDACIÓN AL ENVIAR VENTA
        $('#form-venta').on('submit', function(e) {
            // ... (tus validaciones de carrito y cliente) ...
            let items = $('#input-items-json').val();
            if (items === '[]' || items === '') {
                e.preventDefault();
                toastr.error('Carrito vacío.');
                return;
            }

            let tipo = $('#tipo_comprobante').val();
            let clienteId = $('#cliente_id_hidden').val();
            if (tipo === 'FACTURA' && !clienteId) {
                e.preventDefault();
                toastr.error('Falta RUC para Factura.');
                return;
            }

            // VALIDACIÓN EXTRA: PAGO EFECTIVO
            if ($('#medio_pago').val() === 'EFECTIVO') {
                let total = parseFloat($('#total-venta').text());
                let pagaCon = parseFloat($('#input-paga-con').val()) || 0;

                // Si ingresó monto, validamos que alcance. Si lo dejó vacío, asumimos pago exacto.
                if ($('#input-paga-con').val().length > 0 && pagaCon < total) {
                    e.preventDefault();
                    toastr.error('El monto de pago es insuficiente.');
                    $('#input-paga-con').focus().addClass('is-invalid');
                    return;
                }
            }
        });

        // ... (Resto de tu lógica de modales que ya tenías) ...
        // (Asegúrate de copiar el bloque de 'window.openCreateModal', etc. que te pasé en la respuesta anterior)

        // --- PEGA AQUÍ LA LÓGICA DE MODALES (openCreateModal, verifyDocument, etc.) ---
        window.openCreateModal = function() {
            $('#formCliente')[0].reset();
            resetFormState();
            $('.input-future').removeClass('is-invalid bg-light').prop('readonly', false);
            $('#cliente_id').val('');
            $('#modalTitulo').html('<span style="color: #00d2d3;">●</span> Nuevo Cliente');
            toggleDetailsPanel(false);
            $('#modalCliente').modal('show');
        }
        // ... (resto de funciones del modal) ...
        // Para ahorrar espacio, asumo que tienes el bloque de la respuesta anterior
        // que define verifyDocument, handleDuplicate, etc.
        const resetFormState = () => {
            $('#documento').removeClass('is-invalid');
            $('#doc-error').remove();
            $('#btnGuardar').prop('disabled', false).removeClass('btn-secondary').addClass('btn-info').html('<i class="fas fa-save mr-1"></i> GUARDAR');
        };
        const toggleDetailsPanel = (show) => {
            const fields = $('#extra-fields');
            if (show) {
                fields.slideDown();
                $('#toggleText').text('Ocultar Detalles');
                $('#toggleIcon').addClass('rotate-icon');
            } else {
                fields.slideUp();
                $('#toggleText').text('Ver Completo (Contacto)');
                $('#toggleIcon').removeClass('rotate-icon');
            }
        };
        // ... etc ...
    });
</script>

{{-- 2. LÓGICA DE BUSCADORES (INCLUDES) --}}
@include('ventas.ventas.partials.buscador_medicamentos_js')
@include('ventas.ventas.partials.buscador_clientes_js')

{{-- 3. LÓGICA DE LA PÁGINA CREATE (MODALES + SUBMIT) --}}
<script>
    $(document).ready(function() {

        /* =======================================================
           A. VALIDACIÓN AL GUARDAR LA VENTA
           ======================================================= */
        $('#form-venta').on('submit', function(e) {
            // 1. Validar Carrito
            let items = $('#input-items-json').val();
            if (items === '[]' || items === '') {
                e.preventDefault();
                if (typeof toastr !== 'undefined') toastr.error('El carrito está vacío.');
                else alert('El carrito está vacío.');
                return;
            }

            // 2. Validar Cliente para FACTURA
            let tipo = $('#tipo_comprobante').val();
            let clienteId = $('#cliente_id_hidden').val();

            if (tipo === 'FACTURA' && !clienteId) {
                e.preventDefault();
                if (typeof toastr !== 'undefined') toastr.error('Para emitir FACTURA debe seleccionar una Empresa (RUC).');
                else alert('Falta Cliente RUC para Factura.');
                $('#busqueda_cliente').focus();
                return;
            }
        });

        /* =======================================================
           B. DEFINICIÓN DE MODALES (USADOS POR EL BUSCADOR DE CLIENTES)
           ======================================================= */

        // --- 1. MODAL CREAR CLIENTE ---
        window.openCreateModal = function() {
            $('#formCliente')[0].reset();
            resetFormState();
            $('.input-future').removeClass('is-invalid bg-light').prop('readonly', false);
            $('#cliente_id').val('');

            $('#modalTitulo').html('<span style="color: #00d2d3;">●</span> Nuevo Cliente');
            toggleDetailsPanel(false);
            $('#modalCliente').modal('show');
        }

        // Función para verificar duplicados DESDE EL MODAL
        window.verifyDocument = function(doc) {
            const tipo = $('#tipo_documento').val();
            const requiredLen = (tipo === 'RUC') ? 11 : 8;

            if (doc.length === requiredLen) {
                $('#documento').addClass('is-loading');
                $.get("{{ route('clientes.check') }}", {
                        doc: doc
                    })
                    .done(res => res.exists ? handleDuplicate(res.data) : handleFree())
                    .always(() => $('#documento').removeClass('is-loading'));
            } else {
                resetFormState();
            }
        };

        // Helpers Visuales del Modal
        const handleDuplicate = (data) => {
            const isRUC = data.tipo_documento === 'RUC';
            const nombre = isRUC ? data.razon_social : `${data.nombre} ${data.apellidos}`;

            $('#documento').addClass('is-invalid');
            let msg = `<div id="doc-error" class="text-danger small font-weight-bold mt-1"><i class="fas fa-exclamation-circle"></i> Registrado como: ${nombre}</div>`;
            $('#doc-error').length ? $('#doc-error').html(msg) : $('#documento').parent().after(msg);

            if (isRUC) $('#razon_social').val(data.razon_social);
            else {
                $('#nombre').val(data.nombre);
                $('#apellidos').val(data.apellidos);
            }
            $('#email').val(data.email);
            $('#telefono').val(data.telefono);
            $('#direccion').val(data.direccion);

            $('#btnGuardar').prop('disabled', true).addClass('btn-secondary').removeClass('btn-info').html('<i class="fas fa-ban"></i> YA REGISTRADO');
            $('.input-future').not('#documento, #tipo_documento').prop('readonly', true).addClass('bg-light');
            toggleDetailsPanel(data.email || data.telefono || data.direccion);
        };

        const handleFree = () => {
            resetFormState();
            if ($('#nombre').prop('readonly') || $('#razon_social').prop('readonly')) {
                $('.input-future').not('#documento, #tipo_documento').val('').prop('readonly', false).removeClass('bg-light');
            }
        };

        const resetFormState = () => {
            $('#documento').removeClass('is-invalid');
            $('#doc-error').remove();
            $('#btnGuardar').prop('disabled', false).removeClass('btn-secondary').addClass('btn-info').html('<i class="fas fa-save mr-1"></i> GUARDAR');
        };

        const toggleDetailsPanel = (show) => {
            const fields = $('#extra-fields');
            if (show) {
                fields.slideDown();
                $('#toggleText').text('Ocultar Detalles');
                $('#toggleIcon').addClass('rotate-icon');
            } else {
                fields.slideUp();
                $('#toggleText').text('Ver Completo (Contacto)');
                $('#toggleIcon').removeClass('rotate-icon');
            }
        };

        // Listeners internos del Modal Create
        $('#tipo_documento').change(function() {
            $('#documento').val('');
            let isRUC = $(this).val() === 'RUC';
            $('#documento').attr({
                maxlength: isRUC ? 11 : 8,
                minlength: isRUC ? 11 : 8,
                placeholder: isRUC ? 'RUC (11)' : 'DNI (8)'
            });
            $('.bloque-dni').toggleClass('d-none', isRUC);
            $('.bloque-ruc').toggleClass('d-none', !isRUC);
            resetFormState();
        });

        $('#documento').on('input', function() {
            this.value = this.value.replace(/\D/g, '');
            verifyDocument(this.value);
        });

        $('.toggle-details').click(() => toggleDetailsPanel($('#extra-fields').is(':hidden')));

        // SUBMIT DEL MODAL (GUARDAR NUEVO CLIENTE)
        $('#formCliente').submit(function(e) {
            e.preventDefault();
            const btn = $('#btnGuardar');
            if (btn.prop('disabled')) return;

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

            $.ajax({
                url: '/clientes',
                method: 'POST',
                data: $(this).serialize(),
                success: (res) => {
                    $('#modalCliente').modal('hide');
                    if (typeof toastr !== 'undefined') toastr.success(res.message);

                    // AUTO-SELECCIÓN EN POS
                    let docNuevo = $('#documento').val();
                    if (docNuevo && window.reloadTable) window.reloadTable();
                },
                error: (xhr) => {
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, (k, v) => $(`[name="${k}"]`).addClass('is-invalid'));
                        if (typeof toastr !== 'undefined') toastr.error('Revise los campos.');
                    } else if (typeof toastr !== 'undefined') toastr.error('Error servidor.');
                },
                complete: () => {
                    if (!$('#documento').hasClass('is-invalid'))
                        btn.prop('disabled', false).html('<i class="fas fa-save"></i> GUARDAR');
                }
            });
        });

        // --- 2. MODAL VER (SHOW) ---
        window.openShowModal = function(id) {
            $('#show_avatar').html('<i class="fas fa-spinner fa-spin"></i>');
            $('#show_nombre').text('Cargando...');

            $.get(`/clientes/${id}`, function(res) {
                if (!res.success) return;
                const c = res.data;
                const isRUC = (c.tipo_documento === 'RUC');

                const nombre = isRUC ? c.razon_social : `${c.nombre} ${c.apellidos}`;
                const inicial = (nombre || '?').charAt(0).toUpperCase();

                if (isRUC) {
                    $('#show_avatar').css({
                        background: '#fff3e0',
                        color: '#ff9800'
                    }).html('<i class="fas fa-building"></i>');
                    $('#block-sexo').addClass('d-none');
                } else {
                    $('#show_avatar').css({
                        background: '#e0f7fa',
                        color: '#00bcd4'
                    }).text(inicial);
                    $('#block-sexo').removeClass('d-none');
                    $('#show_sexo').text(c.sexo === 'M' ? 'Masculino' : 'Femenino');
                }

                $('#show_nombre').text(nombre || 'SIN DATOS');
                $('#show_tipo_doc').text(`${c.tipo_documento}: ${c.documento}`);
                $('#show_puntos').text(c.puntos || 0);
                $('#show_registro').text(new Date(c.created_at).toLocaleDateString('es-PE'));

                const showIf = (sel, val) => {
                    const row = $(sel).closest('div[class^="col"]');
                    (val && val !== '--') ? $(sel).text(val) && row.show(): row.hide();
                };
                showIf('#show_email', c.email);
                showIf('#show_telefono', c.telefono);
                showIf('#show_direccion', c.direccion);

                const rows = (c.ventas || []).map(v => `
                        <tr><td>${new Date(v.created_at).toLocaleDateString('es-PE')}</td>
                        <td class="font-weight-bold">S/ ${parseFloat(v.total).toFixed(2)}</td>
                        <td><span class="badge badge-success">Completo</span></td></tr>`).join('');

                const tableHtml = rows ?
                    `<div class="table-responsive"><table class="table table-hover table-sm text-center mb-0"><thead class="bg-light text-muted"><tr><th>FECHA</th><th>TOTAL</th><th>ESTADO</th></tr></thead><tbody>${rows}</tbody></table></div>` :
                    `<div class="empty-state text-center py-5"><i class="fas fa-shopping-basket fa-3x text-muted mb-3 opacity-25"></i><p class="text-muted font-weight-bold">Sin historial reciente</p></div>`;

                $('#history-container').html(tableHtml);
                $('#modalShowCliente').modal('show');
            });
        }

        // Fix modal close
        $('.close, [data-dismiss="modal"]').on('click', () => $('.modal').modal('hide'));
    });
</script>
@stop