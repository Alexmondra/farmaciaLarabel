@extends('adminlte::page')

@section('title', 'Inventario')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="text-dark font-weight-bold">
        <i class="fas fa-pills mr-2 text-primary"></i>Inventario
    </h1>

    <div class="btn-group shadow-sm">
        @can('stock.ajustar')
        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-dolly-flatbed mr-2"></i> Operaciones
        </button>
        @endcan

        <div class="dropdown-menu dropdown-menu-right">
            @can('compras.crear ')
            <a class="dropdown-item" href="{{ route('compras.create') }}"> {{-- Pon aquí tu ruta real --}}
                <i class="fas fa-cart-plus text-success mr-2"></i> Registrar Compra (Ingreso)
            </a>
            @endcan

            <div class="dropdown-divider"></div>

            @can('stock.ajustar')
            <a class="dropdown-item" href="#" onclick="abrirModalSalida()">
                <i class="fas fa-trash-alt text-danger mr-2"></i> Dar de Baja / Ajuste (Salida)
            </a>
            @endcan

            @can('guias.crear')
            <a class="dropdown-item" href="{{ route('guias.create') }}"> {{-- Pon aquí tu ruta real --}}
                <i class="fas fa-truck-loading text-info mr-2"></i> Generar Guía (Traslado)
            </a>
            @endcan

            @can('stock.ajustar')
            <a class="dropdown-item" href="#" onclick="abrirModalIngreso()">
                <i class="fas fa-cart-plus text-success mr-2"></i> Registrar Ingreso / Ajuste (+)
            </a>
            @endcan
        </div>


    </div>
</div>
@endsection

@section('content')

{{-- BARRA DE BÚSQUEDA Y FILTROS --}}
<div class="row justify-content-center mb-4">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3" style="background-color: #f8f9fa;">
                <form id="filterForm">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0">
                                        <i class="fas fa-search text-primary"></i>
                                    </span>
                                </div>
                                <input type="text" id="searchInput" class="form-control border-left-0"
                                    placeholder="Buscar medicamento, código, laboratorio..."
                                    autofocus autocomplete="off">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-end">
                                <span class="text-muted mr-2 font-weight-bold small text-uppercase">Precio:</span>
                                <div class="input-group input-group-sm mr-2" style="width: 100px;">
                                    <div class="input-group-prepend"><span class="input-group-text">Min</span></div>
                                    <input type="number" id="minPrice" class="form-control" placeholder="0">
                                </div>
                                <span class="text-muted mr-2">-</span>
                                <div class="input-group input-group-sm" style="width: 100px;">
                                    <div class="input-group-prepend"><span class="input-group-text">Max</span></div>
                                    <input type="number" id="maxPrice" class="form-control" placeholder="Inf">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- CONTENEDOR TABLA (CARGA VÍA AJAX) --}}
<div class="card shadow border-0">
    <div class="card-body p-0" id="tabla-contenedor">
        @include('inventario.medicamentos._index_tabla')
    </div>
</div>

{{-- MODAL PRECIO --}}
@can('medicamentos.editar')
<div class="modal fade" id="modalPrecio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"> {{-- Quitamos modal-sm para que quepa todo --}}
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title font-weight-bold">
                    <i class="fas fa-tags mr-1"></i> Configurar Precios
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formUpdatePrecio" onsubmit="guardarPrecio(event)">
                <div class="modal-body bg-light">
                    <p id="lblNombreMedicamento" class="font-weight-bold text-center text-dark mb-3" style="font-size: 1.1em;"></p>
                    <input type="hidden" id="medIdHidden">

                    <div class="row">
                        {{-- 1. PRECIO UNITARIO (Siempre visible) --}}
                        <div class="col-12 mb-3">
                            <label class="small font-weight-bold text-primary mb-1">PRECIO UNITARIO (S/)</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text bg-white border-primary fw-bold">S/</span></div>
                                <input type="number" step="0.01" min="0"
                                    class="form-control font-weight-bold text-primary form-control-lg"
                                    id="inputPrecioUnidad" required placeholder="0.00">
                            </div>
                            <small class="text-muted">Precio por pastilla o unidad mínima.</small>
                        </div>

                        {{-- 2. PRECIO BLISTER (Opcional) --}}
                        <div class="col-6 mb-2" id="divPrecioBlister">
                            <label class="small font-weight-bold text-info mb-1">P. BLÍSTER (S/)</label>
                            <input type="number" step="0.01" min="0"
                                class="form-control font-weight-bold border-info text-info"
                                id="inputPrecioBlister" placeholder="0.00">
                            <small class="text-info" id="lblInfoBlister">x 10 un.</small>
                        </div>

                        {{-- 3. PRECIO CAJA (Opcional) --}}
                        <div class="col-6 mb-2" id="divPrecioCaja">
                            <label class="small font-weight-bold text-success mb-1">P. CAJA (S/)</label>
                            <input type="number" step="0.01" min="0"
                                class="form-control font-weight-bold border-success text-success"
                                id="inputPrecioCaja" placeholder="0.00">
                            <small class="text-success" id="lblInfoCaja">x 100 un.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-2 justify-content-center bg-white">
                    <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-2"></i> GUARDAR PRECIOS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@can('medicamentos.editar')
<div class="modal fade" id="modalStockMin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light py-2">
                <h6 class="modal-title font-weight-bold">
                    <i class="fas fa-sliders-h mr-1 text-muted"></i> Stock Mínimo
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formUpdateStockMin" onsubmit="guardarStockMin(event)">
                <div class="modal-body">
                    <p id="lblNombreMedStock" class="small text-muted mb-2 text-center"></p>
                    <input type="hidden" id="medIdStockHidden">

                    <div class="form-group mb-0">
                        <label class="small text-muted">Avisar cuando quede menos de:</label>
                        <div class="input-group">
                            <input type="number" step="1" min="0"
                                class="form-control text-center font-weight-bold"
                                id="inputNuevoStockMin" required>
                            <div class="input-group-append">
                                <span class="input-group-text">Unid.</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-1 justify-content-center">
                    <button type="submit" class="btn btn-primary btn-sm btn-block">Guardar Configuración</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL BAJAR STOK --}}

<div class="modal fade" id="modalSalidaStock" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white py-2">
                <h6 class="modal-title font-weight-bold">
                    <i class="fas fa-trash-alt mr-2"></i> Registrar Baja de Inventario
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body bg-light">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body p-3">
                        <label class="small font-weight-bold text-muted text-uppercase">1. Buscar Medicamento</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="txtBuscarSalida"
                                placeholder="Escribe el nombre (Ej. Ibuprofeno)..." autocomplete="off">
                            <div class="input-group-append">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-danger"></i></span>
                            </div>
                        </div>
                        <div id="listaResultadosSalida" class="list-group shadow"
                            style="position: absolute; width: 90%; z-index: 1050; max-height: 250px; overflow-y: auto; display: none; margin-top: 5px;">
                        </div>
                    </div>
                </div>

                <div id="panelLotes" style="display: none;">
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-white border-bottom-0 py-2">
                            <h6 class="mb-0 text-primary font-weight-bold" id="lblProductoSeleccionado"></h6>
                            <small class="text-muted">Selecciona el lote del cual vas a descontar:</small>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="pl-3">Lote</th>
                                        <th>Vencimiento</th>
                                        <th class="text-center">Stock</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyLotesSalida"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <form id="formSalidaStock" style="display: none;" class="card shadow-sm border-0">
                    @csrf
                    <div class="card-body">
                        <input type="hidden" name="lote_id" id="hiddenLoteId">

                        <div class="alert alert-warning py-2 mb-3">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Descontando del Lote: <strong id="lblLoteCode"></strong>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Cantidad</label>
                                    <input type="number" name="cantidad" id="inputCantidadSalida"
                                        class="form-control font-weight-bold text-center form-control-lg" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Motivo</label>
                                    <select class="form-control form-control-lg" name="motivo" required>
                                        <option value="VENCIMIENTO">Vencimiento</option>
                                        <option value="MERMA">Merma / Rotura</option>
                                        <option value="PERDIDA">Pérdida / Robo</option>
                                        <option value="AJUSTE">Ajuste de Inventario</option>
                                        <option value="USO_INTERNO">Uso Interno</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Observación</label>
                            <input type="text" class="form-control" name="observacion" placeholder="Detalles opcionales...">
                        </div>

                        <button type="submit" class="btn btn-danger btn-block font-weight-bold">
                            CONFIRMAR BAJA
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


{{-- SUBIR STOK--}}

<div class="modal fade" id="modalIngresoStock" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-success">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title font-weight-bold">
                    <i class="fas fa-plus-circle mr-2"></i> Registrar Ingreso / Ajuste (+)
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body bg-light">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body p-3">
                        <label class="small font-weight-bold text-muted text-uppercase">1. Buscar Medicamento</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="txtBuscarIngreso"
                                placeholder="Busca por nombre o código de barras..." autocomplete="off">
                            <div class="input-group-append">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-success"></i></span>
                            </div>
                        </div>
                        <div id="listaResultadosIngreso" class="list-group shadow"
                            style="position: absolute; width: 90%; z-index: 1050; max-height: 250px; overflow-y: auto; display: none; margin-top: 5px;">
                        </div>
                    </div>
                </div>

                <form id="formIngresoStock" style="display: none;">
                    @csrf
                    <input type="hidden" name="medicamento_id" id="hiddenIngresoMedId">

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom-0">
                            <h6 class="text-success font-weight-bold mb-0" id="lblIngresoProducto"></h6>
                            <small class="text-muted">Ingresa los datos del stock a añadir:</small>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Lote <small class="text-danger">*</small></label>
                                        <input type="text" name="codigo_lote" class="form-control font-weight-bold text-uppercase"
                                            placeholder="Ej. L-2025" required id="inputIngresoLote">
                                        <small class="form-text text-muted">Si existe, suma. Si no, crea.</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Vencimiento</label>
                                        <input type="date" name="vencimiento" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Cantidad (+)</label>
                                        <input type="number" name="cantidad" class="form-control font-weight-bold text-success border-success"
                                            min="1" value="1" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Motivo</label>
                                <select class="form-control" name="motivo" required>
                                    <option value="AJUSTE">Ajuste de Inventario (Sobrante)</option>
                                    <option value="COMPRA_RAPIDA">Compra Rápida / Reposición</option>
                                    <option value="DEVOLUCION">Devolución de Cliente</option>
                                    <option value="REGALO">Bonificación / Muestra Médica</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Observación</label>
                                <textarea name="observacion" rows="1" class="form-control" placeholder="Opcional..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-success btn-block font-weight-bold shadow-sm">
                                <i class="fas fa-save mr-2"></i> GUARDAR INGRESO
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endcan

{{-- PREPARACIÓN DE DATOS PARA JS --}}
@php
$permisosJS = [
'canEdit' => auth()->user()->can('medicamentos.editar'),
'canDelete' => auth()->user()->can('medicamentos.eliminar'),
];
@endphp

@endsection

@section('js')
<script>
    // 1. CONFIGURACIÓN TOAST
    const ToastCentro = Swal.mixin({
        toast: true,
        position: 'center',
        iconColor: 'white',
        customClass: {
            popup: 'colored-toast'
        },
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // 2. PERMISOS (Sin errores visuales en editor)
    const userPermissions = JSON.parse('@json($permisosJS)');

    // 3. BUSCADOR OPTIMIZADO
    let timeout = null;
    let ultimaBusqueda = "";

    function aplicarFiltros() {
        let q = $('#searchInput').val().trim();
        let min = $('#minPrice').val();
        let max = $('#maxPrice').val();

        let params = new URLSearchParams({
            q: q,
            min: min,
            max: max
        }).toString();

        if (params === ultimaBusqueda) return;

        let url = "{{ route('inventario.medicamentos.index') }}";
        if (params) url += "?" + params;

        ultimaBusqueda = params;

        $('#tabla-contenedor').css('opacity', '0.5');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                $('#tabla-contenedor').html(data);
                $('#tabla-contenedor').css('opacity', '1');
            },
            error: function() {
                ultimaBusqueda = "";
                ToastCentro.fire({
                    icon: 'error',
                    title: 'Error al filtrar.'
                });
                $('#tabla-contenedor').css('opacity', '1');
            }
        });
    }

    $('#searchInput, #minPrice, #maxPrice').on('keyup change', function(e) {
        if ([16, 17, 18, 20, 37, 38, 39, 40].includes(e.keyCode)) return;
        clearTimeout(timeout);
        timeout = setTimeout(aplicarFiltros, 500);
    });

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        let q = $('#searchInput').val().trim();
        let min = $('#minPrice').val();
        let max = $('#maxPrice').val();

        if (url.indexOf('q=') === -1 && q) url += "&q=" + q;
        if (url.indexOf('min=') === -1 && min) url += "&min=" + min;
        if (url.indexOf('max=') === -1 && max) url += "&max=" + max;

        $('#tabla-contenedor').css('opacity', '0.5');
        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                $('#tabla-contenedor').html(data);
                $('#tabla-contenedor').css('opacity', '1');
            }
        });
    });

    // 4. LÓGICA PRECIO CON PERMISOS
    function abrirModalPrecio(id, nombre, pUnit, pBlister, pCaja, uBlister, uCaja) {
        if (!userPermissions.canEdit) {
            ToastCentro.fire({
                icon: 'error',
                title: 'Sin permisos.'
            });
            return;
        }

        $('#medIdHidden').val(id);
        $('#lblNombreMedicamento').text(nombre);

        // 1. Cargar valores actuales
        $('#inputPrecioUnidad').val(pUnit > 0 ? pUnit : '');
        $('#inputPrecioBlister').val(pBlister > 0 ? pBlister : '');
        $('#inputPrecioCaja').val(pCaja > 0 ? pCaja : '');

        // 2. Lógica Visual: ¿Mostramos input de Blíster?
        if (uBlister && uBlister > 1) {
            $('#divPrecioBlister').show();
            $('#lblInfoBlister').text('x ' + uBlister + ' un.');
        } else {
            $('#divPrecioBlister').hide();
            $('#inputPrecioBlister').val(''); // Limpiar para no enviar basura
        }

        // 3. Lógica Visual: ¿Mostramos input de Caja?
        if (uCaja && uCaja > 1) {
            $('#divPrecioCaja').show();
            $('#lblInfoCaja').text('x ' + uCaja + ' un.');
        } else {
            $('#divPrecioCaja').hide(); // Si es jarabe (uCaja=1), el precio unitario es el de la caja
            $('#inputPrecioCaja').val('');
        }

        $('#modalPrecio').modal('show');
        setTimeout(() => {
            $('#inputPrecioUnidad').select();
        }, 500);
    }

    // LÓGICA PRECIO CON PERMISOS
    function abrirModalPrecio(id, nombre, pUnit, pBlister, pCaja, uBlister, uCaja) {
        if (!userPermissions.canEdit) {
            ToastCentro.fire({
                icon: 'error',
                title: 'Sin permisos.'
            });
            return;
        }

        $('#medIdHidden').val(id);
        $('#lblNombreMedicamento').text(nombre);

        // 1. Cargar valores actuales
        $('#inputPrecioUnidad').val(pUnit > 0 ? pUnit : '');
        $('#inputPrecioBlister').val(pBlister > 0 ? pBlister : '');
        $('#inputPrecioCaja').val(pCaja > 0 ? pCaja : '');

        // 2. Lógica Visual: ¿Mostramos input de Blíster?
        if (uBlister && uBlister > 1) {
            $('#divPrecioBlister').show();
            $('#lblInfoBlister').text('x ' + uBlister + ' un.');
        } else {
            $('#divPrecioBlister').hide();
            $('#inputPrecioBlister').val(''); // Limpiar para no enviar basura
        }

        // 3. Lógica Visual: ¿Mostramos input de Caja?
        if (uCaja && uCaja > 1) {
            $('#divPrecioCaja').show();
            $('#lblInfoCaja').text('x ' + uCaja + ' un.');
        } else {
            $('#divPrecioCaja').hide(); // Si es jarabe (uCaja=1), el precio unitario es el de la caja
            $('#inputPrecioCaja').val('');
        }

        $('#modalPrecio').modal('show');
        setTimeout(() => {
            $('#inputPrecioUnidad').select();
        }, 500);
    }

    function guardarPrecio(e) {
        e.preventDefault();
        if (!userPermissions.canEdit) return;

        let medId = $('#medIdHidden').val();
        let sucursalId = "{{ $sucursalSeleccionada ? $sucursalSeleccionada->id : '' }}";

        // Obtenemos los 3 valores
        let pUnit = $('#inputPrecioUnidad').val();
        let pBlis = $('#inputPrecioBlister').val();
        let pCaja = $('#inputPrecioCaja').val();

        $.ajax({
            url: "/inventario/medicamentos/" + medId + "/sucursales/" + sucursalId,
            type: "PUT",
            data: {
                _token: "{{ csrf_token() }}",
                precio: pUnit, // Mapeado a precio_venta
                precio_blister: pBlis, // Nuevo
                precio_caja: pCaja // Nuevo
            },
            success: function(response) {
                $('#modalPrecio').modal('hide');

                // Actualizar visualmente SOLO el precio unitario en la tabla rápida
                // (Para ver los otros, el usuario recargará o entrará al detalle)
                let visualPrice = parseFloat(pUnit).toFixed(2);
                $('#price-display-' + medId).html('S/ ' + visualPrice + '<br><small class="text-muted" style="font-size:0.7em">Actualizado</small>');

                ToastCentro.fire({
                    icon: 'success',
                    title: 'Precios actualizados'
                });
            },
            error: function(xhr) {
                ToastCentro.fire({
                    icon: 'error',
                    title: 'Error al guardar.'
                });
            }
        });
    }
    // ==========================================
    // LÓGICA STOCK MÍNIMO
    // ==========================================

    function abrirModalStockMin(id, nombre, stockActual) {
        if (!userPermissions.canEdit) return;

        $('#medIdStockHidden').val(id);
        $('#lblNombreMedStock').text(nombre);
        $('#inputNuevoStockMin').val(stockActual);

        $('#modalStockMin').modal('show');

        // Enfocar input automáticamente
        setTimeout(() => {
            $('#inputNuevoStockMin').select();
        }, 500);
    }

    function guardarStockMin(e) {
        e.preventDefault();

        if (!userPermissions.canEdit) return;

        let medId = $('#medIdStockHidden').val();
        let nuevoStock = $('#inputNuevoStockMin').val();
        let sucursalId = "{{ $sucursalSeleccionada ? $sucursalSeleccionada->id : '' }}";

        if (!sucursalId) {
            ToastCentro.fire({
                icon: 'error',
                title: 'Error de sucursal.'
            });
            return;
        }

        // Reutilizamos la MISMA ruta que usas para el precio
        $.ajax({
            url: "/inventario/medicamentos/" + medId + "/sucursales/" + sucursalId,
            type: "PUT",
            data: {
                _token: "{{ csrf_token() }}",
                stock_minimo: nuevoStock // <--- Aquí enviamos la clave que espera el Controller
            },
            success: function(response) {
                $('#modalStockMin').modal('hide');

                // Actualizar valor visualmente en la tabla
                $('#min-display-' + medId).text(nuevoStock);

                ToastCentro.fire({
                    icon: 'success',
                    title: 'Stock mínimo actualizado'
                });
            },
            error: function(xhr) {
                let msj = xhr.responseJSON ? xhr.responseJSON.error : 'Error al guardar.';
                ToastCentro.fire({
                    icon: 'error',
                    title: msj
                });
            }
        });
    }

    // Función para abrir el modal
    // =========================================================
    // LÓGICA DE BÚSQUEDA PARA EL MODAL DE SALIDA (Estilo Ventas)
    // =========================================================

    const RUTA_BUSCAR = "{{ route('ventas.lookup_medicamentos') }}";
    const RUTA_LOTES = "{{ route('ventas.lookup_lotes') }}";
    const SUCURSAL_ID = "{{ $sucursalSeleccionada ? $sucursalSeleccionada->id : '' }}";

    let timeoutSalida = null;

    // VARIABLES PARA NAVEGACIÓN CON TECLADO
    let selectedIndex = -1;
    let resultCount = 0;

    // 1. ABRIR MODAL
    function abrirModalSalida() {
        if (!SUCURSAL_ID) {
            ToastCentro.fire({
                icon: 'error',
                title: 'Selecciona una sucursal primero'
            });
            return;
        }
        resetearModal();
        $('#modalSalidaStock').modal('show');
        // Enfocar y seleccionar texto al abrir
        setTimeout(() => $('#txtBuscarSalida').focus().select(), 500);
    }

    // 2. BUSCADOR EN TIEMPO REAL
    $('#txtBuscarSalida').on('input', function() { // Usamos 'input' en vez de 'keyup' para mejor respuesta
        let q = $(this).val().trim();
        let lista = $('#listaResultadosSalida');

        if (q.length < 1) {
            lista.hide();
            resultCount = 0;
            return;
        }

        clearTimeout(timeoutSalida);
        timeoutSalida = setTimeout(() => {
            $.ajax({
                url: RUTA_BUSCAR,
                method: 'GET',
                data: {
                    sucursal_id: SUCURSAL_ID,
                    q: q,
                    categoria_id: ''
                },
                success: function(data) {
                    let html = '';
                    resultCount = data.length;
                    selectedIndex = -1;

                    if (resultCount === 0) {
                        html = '<div class="list-group-item text-muted">No encontrado</div>';
                    } else {
                        data.forEach((m, index) => {
                            // Agregamos clase 'item-resultado' y un ID único para el teclado
                            html += `
                                <a href="#" class="list-group-item list-group-item-action py-2 item-resultado" 
                                   id="res-item-${index}"
                                   onclick="cargarLotesParaBaja(${m.medicamento_id}, '${m.nombre}', '${m.presentacion || ''}'); return false;">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>${m.nombre}</strong> <br>
                                            <small class="text-muted">${m.presentacion || ''}</small>
                                        </div>
                                        <span class="badge badge-light border">S/ ${parseFloat(m.precio_venta).toFixed(2)}</span>
                                    </div>
                                </a>`;
                        });
                    }
                    lista.html(html).show();
                }
            });
        }, 300);
    });

    // 3. LÓGICA DE TECLADO (FLECHAS Y ENTER)
    $('#txtBuscarSalida').on('keydown', function(e) {
        let lista = $('#listaResultadosSalida');
        if (!lista.is(':visible') || resultCount === 0) return;

        // FLECHA ABAJO (40)
        if (e.which === 40) {
            e.preventDefault();
            selectedIndex++;
            if (selectedIndex >= resultCount) selectedIndex = 0; // Vuelve al inicio
            highlightItem();
        }
        // FLECHA ARRIBA (38)
        else if (e.which === 38) {
            e.preventDefault();
            selectedIndex--;
            if (selectedIndex < 0) selectedIndex = resultCount - 1; // Va al final
            highlightItem();
        }
        // ENTER (13)
        else if (e.which === 13) {
            e.preventDefault();
            if (selectedIndex > -1) {
                // Simulamos clic en el elemento seleccionado
                $('#res-item-' + selectedIndex).click();
            }
        }
    });

    // Función para resaltar visualmente (Pintar de azul)
    function highlightItem() {
        let items = $('.item-resultado');
        items.removeClass('active'); // Quitar azul a todos

        if (selectedIndex > -1) {
            let actual = $('#res-item-' + selectedIndex);
            actual.addClass('active'); // Poner azul al seleccionado

            // Hacer scroll automático si la lista es larga
            actual[0].scrollIntoView({
                block: 'nearest'
            });
        }
    }

    // 4. CARGAR LOTES (Al dar Enter o Clic)
    window.cargarLotesParaBaja = function(id, nombre, pres) {
        $('#txtBuscarSalida').val('');
        $('#listaResultadosSalida').hide();

        $('#lblProductoSeleccionado').text(`${nombre} - ${pres}`);
        $('#panelLotes').show();
        $('#formSalidaStock').hide();

        $('#tbodyLotesSalida').html('<tr><td colspan="4" class="text-center">Cargando lotes...</td></tr>');

        $.ajax({
            url: RUTA_LOTES,
            method: 'GET',
            data: {
                medicamento_id: id,
                sucursal_id: SUCURSAL_ID
            },
            success: function(lotes) {
                let tbody = $('#tbodyLotesSalida').empty();

                if (lotes.length === 0) {
                    tbody.html('<tr><td colspan="4" class="text-center text-danger font-weight-bold">AGOTADO / SIN STOCK</td></tr>');
                    return;
                }

                lotes.forEach(l => {
                    let btn = l.stock_actual > 0 ?
                        `<button type="button" class="btn btn-sm btn-danger" 
                            onclick="prepararFormulario(${l.id}, '${l.codigo_lote}', ${l.stock_actual})">
                            <i class="fas fa-arrow-down"></i> Bajar
                           </button>` :
                        '<span class="badge badge-secondary">Cero</span>';

                    tbody.append(`
                        <tr>
                            <td class="pl-3 align-middle font-weight-bold">${l.codigo_lote}</td>
                            <td class="align-middle">${l.fecha_vencimiento || '-'}</td>
                            <td class="align-middle text-center text-primary font-weight-bold" style="font-size:1.1em">${l.stock_actual}</td>
                            <td class="align-middle">${btn}</td>
                        </tr>
                    `);
                });
            }
        });
    };

    // 5. MOSTRAR FORMULARIO FINAL
    window.prepararFormulario = function(loteId, codigo, stockMax) {
        $('#hiddenLoteId').val(loteId);
        $('#lblLoteCode').text(codigo + ' (Disp: ' + stockMax + ')');
        $('#inputCantidadSalida').val(1).attr('max', stockMax).focus().select(); // Enfoca la cantidad directo
        $('#formSalidaStock').slideDown();
    };

    // 6. GUARDAR
    $('#formSalidaStock').on('submit', function(e) {
        e.preventDefault();
        let btn = $(this).find('button[type="submit"]');
        let txt = btn.html();
        btn.prop('disabled', true).text('Guardando...');

        $.ajax({
            url: "{{ route('inventario.movimientos.store_salida') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $('#modalSalidaStock').modal('hide');
                ToastCentro.fire({
                    icon: 'success',
                    title: 'Baja registrada correctamente'
                });
                aplicarFiltros();
            },
            error: function(xhr) {
                let msg = xhr.responseJSON ? xhr.responseJSON.error : 'Error al guardar';
                ToastCentro.fire({
                    icon: 'error',
                    title: msg
                });
                btn.prop('disabled', false).html(txt);
            }
        });
    });

    function resetearModal() {
        $('#txtBuscarSalida').val('');
        $('#listaResultadosSalida').hide();
        $('#panelLotes').hide();
        $('#formSalidaStock').hide();
        $('#tbodyLotesSalida').empty();
        selectedIndex = -1;
    }


    // =========================================================
    // LÓGICA DE INGRESO / SUBIR STOCK (+)
    // =========================================================

    let timeoutIngreso = null;
    let idxIngreso = -1;
    let countIngreso = 0;

    // 1. Abrir Modal
    function abrirModalIngreso() {
        if (!SUCURSAL_ID) {
            ToastCentro.fire({
                icon: 'error',
                title: 'Falta sucursal'
            });
            return;
        }

        // Resetear
        $('#txtBuscarIngreso').val('');
        $('#listaResultadosIngreso').hide();
        $('#formIngresoStock').hide();
        $('#formIngresoStock')[0].reset();

        $('#modalIngresoStock').modal('show');
        setTimeout(() => $('#txtBuscarIngreso').focus(), 500);
    }

    // 2. Buscador (Copia optimizada del de Salida)
    $('#txtBuscarIngreso').on('input', function() {
        let q = $(this).val().trim();
        let lista = $('#listaResultadosIngreso');

        if (q.length < 2) {
            lista.hide();
            return;
        }

        clearTimeout(timeoutIngreso);
        timeoutIngreso = setTimeout(() => {
            $.ajax({
                url: RUTA_BUSCAR, // Reusamos la ruta de Ventas que ya funciona
                method: 'GET',
                data: {
                    sucursal_id: SUCURSAL_ID,
                    q: q
                },
                success: function(data) {
                    let html = '';
                    countIngreso = data.length;
                    idxIngreso = -1;

                    if (countIngreso === 0) {
                        html = '<div class="list-group-item text-muted">No encontrado</div>';
                    } else {
                        data.forEach((m, i) => {
                            html += `
                                <a href="#" class="list-group-item list-group-item-action py-2 item-ingreso" 
                                   id="ing-item-${i}"
                                   onclick="seleccionarMedIngreso(${m.medicamento_id}, '${m.nombre}', '${m.presentacion || ''}'); return false;">
                                    <strong>${m.nombre}</strong> <small class="text-muted">(${m.presentacion || ''})</small>
                                </a>`;
                        });
                    }
                    lista.html(html).show();
                }
            });
        }, 300);
    });

    // 3. Navegación Teclado (Flechas y Enter)
    $('#txtBuscarIngreso').on('keydown', function(e) {
        let lista = $('#listaResultadosIngreso');
        if (!lista.is(':visible') || countIngreso === 0) return;

        if (e.which === 40) { // Abajo
            e.preventDefault();
            idxIngreso++;
            if (idxIngreso >= countIngreso) idxIngreso = 0;
            highlightIngreso();
        } else if (e.which === 38) { // Arriba
            e.preventDefault();
            idxIngreso--;
            if (idxIngreso < 0) idxIngreso = countIngreso - 1;
            highlightIngreso();
        } else if (e.which === 13) { // Enter
            e.preventDefault();
            if (idxIngreso > -1) $('#ing-item-' + idxIngreso).click();
        }
    });

    function highlightIngreso() {
        $('.item-ingreso').removeClass('active');
        let act = $('#ing-item-' + idxIngreso);
        act.addClass('active');
        act[0].scrollIntoView({
            block: 'nearest'
        });
    }

    // 4. Seleccionar Producto -> Mostrar Formulario
    window.seleccionarMedIngreso = function(id, nombre, pres) {
        $('#txtBuscarIngreso').val('');
        $('#listaResultadosIngreso').hide();

        $('#hiddenIngresoMedId').val(id);
        $('#lblIngresoProducto').text(nombre + ' ' + pres);

        $('#formIngresoStock').fadeIn();
        // Enfocar campo de Lote automáticamente
        setTimeout(() => $('#inputIngresoLote').focus(), 200);
    };

    // 5. Guardar (AJAX)
    $('#formIngresoStock').on('submit', function(e) {
        e.preventDefault();
        let btn = $(this).find('button[type="submit"]');
        let txt = btn.html();
        btn.prop('disabled', true).text('Guardando...');

        $.ajax({
            url: "{{ route('inventario.movimientos.store_ingreso') }}", // La ruta nueva
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $('#modalIngresoStock').modal('hide');
                ToastCentro.fire({
                    icon: 'success',
                    title: 'Ingreso registrado correctamente'
                });
                aplicarFiltros(); // Recargar tabla
            },
            error: function(xhr) {
                let msg = xhr.responseJSON ? xhr.responseJSON.error : 'Error al guardar';
                ToastCentro.fire({
                    icon: 'error',
                    title: msg
                });
                btn.prop('disabled', false).html(txt);
            }
        });
    });
</script>
@endsection


<style>
    /* Estilos específicos de index */
    .colored-toast.swal2-icon-success {
        background-color: #a5dc86 !important;
    }

    /* ====================================================================
       🔥 SOLUCIÓN DEFINITIVA: LISTAS DE INGRESO Y SALIDA 🔥
       (Usamos los IDs específicos para que NADA lo sobrescriba)
       ==================================================================== */

    /* 1. APUNTAR DIRECTO A TUS LISTAS (Ingreso y Salida) */
    /* Cubre: Seleccionado con teclado (.active) y Mouse (:hover) */
    #listaResultadosSalida .list-group-item.active,
    #listaResultadosSalida .list-group-item:hover,
    #listaResultadosIngreso .list-group-item.active,
    #listaResultadosIngreso .list-group-item:hover {
        background-color: #007bff !important;
        /* AZUL FUERTE */
        background-image: none !important;
        /* Quita degradados raros */
        border-color: #007bff !important;
        color: #ffffff !important;
    }

    /* 2. FORZAR TEXTO BLANCO (Nombre, precio, info) */
    #listaResultadosSalida .list-group-item.active *,
    #listaResultadosSalida .list-group-item:hover *,
    #listaResultadosIngreso .list-group-item.active *,
    #listaResultadosIngreso .list-group-item:hover * {
        color: #ffffff !important;
    }

    /* 3. ARREGLAR ETIQUETAS DE PRECIO (Badges) */
    /* Se ponen blancas con letras azules para resaltar */
    #listaResultadosSalida .list-group-item.active .badge,
    #listaResultadosSalida .list-group-item:hover .badge,
    #listaResultadosIngreso .list-group-item.active .badge,
    #listaResultadosIngreso .list-group-item:hover .badge {
        background-color: #ffffff !important;
        color: #007bff !important;
        border: none !important;
    }

    /* 4. FONDO OSCURO CUANDO NO ESTÁ SELECCIONADO (Modo Oscuro) */
    body.dark-mode #listaResultadosSalida .list-group-item,
    body.dark-mode #listaResultadosIngreso .list-group-item {
        background-color: #343a40;
        /* Gris oscuro base */
        border-color: #4b545c;
        color: #ffffff;
    }
</style>