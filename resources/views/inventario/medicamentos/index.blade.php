@extends('adminlte::page')

@section('title', 'Inventario')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="text-dark font-weight-bold">
        <i class="fas fa-pills mr-2 text-primary"></i>Inventario
    </h1>

    <div class="btn-group shadow-sm">
        {{-- BOTÓN 1: ACCIONES DE STOCK (Lo que necesitas ahora) --}}
        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-dolly-flatbed mr-2"></i> Operaciones
        </button>
        <div class="dropdown-menu dropdown-menu-right">
            {{-- Opción A: Ingreso por Compra (Lo más común) --}}
            <a class="dropdown-item" href="{{ route('compras.create') }}"> {{-- Pon aquí tu ruta real --}}
                <i class="fas fa-cart-plus text-success mr-2"></i> Registrar Compra (Ingreso)
            </a>

            <div class="dropdown-divider"></div>

            {{-- Opción B: Salidas / Mermas --}}
            <a class="dropdown-item" href="#" onclick="abrirModalSalida()">
                <i class="fas fa-trash-alt text-danger mr-2"></i> Dar de Baja / Ajuste (Salida)
            </a>

            {{-- Opción C: Traslados --}}
            <a class="dropdown-item" href="{{ route('guias.create') }}"> {{-- Pon aquí tu ruta real --}}
                <i class="fas fa-truck-loading text-info mr-2"></i> Generar Guía (Traslado)
            </a>
        </div>

        {{-- BOTÓN 2: CREAR FICHA (Solo si el producto es nuevo en el sistema) --}}
        @can('medicamentos.crear')
        <a href="{{ route('inventario.medicamentos.create') }}" class="btn btn-primary border-left">
            <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">Nuevo Ítem</span>
        </a>
        @endcan
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
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light py-2">
                <h6 class="modal-title font-weight-bold">Actualizar Precio</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formUpdatePrecio" onsubmit="guardarPrecio(event)">
                <div class="modal-body">
                    <p id="lblNombreMedicamento" class="small text-muted mb-2 text-center"></p>
                    <input type="hidden" id="medIdHidden">
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">S/</span></div>
                        <input type="number" step="0.01" min="0" class="form-control text-center font-weight-bold" id="inputNuevoPrecio" required>
                    </div>
                </div>
                <div class="modal-footer p-1 justify-content-center">
                    <button type="submit" class="btn btn-primary btn-sm btn-block">Guardar</button>
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
    function abrirModalPrecio(id, nombre, precioActual) {
        // Bloqueo de seguridad JS
        if (!userPermissions.canEdit) {
            ToastCentro.fire({
                icon: 'error',
                title: 'No tienes permiso para editar precios.'
            });
            return;
        }

        $('#medIdHidden').val(id);
        $('#lblNombreMedicamento').text(nombre);
        $('#inputNuevoPrecio').val(precioActual);
        $('#modalPrecio').modal('show');
        setTimeout(() => {
            $('#inputNuevoPrecio').select();
        }, 500);
    }

    function guardarPrecio(e) {
        e.preventDefault();

        // Doble verificación
        if (!userPermissions.canEdit) return;

        let medId = $('#medIdHidden').val();
        let nuevoPrecio = $('#inputNuevoPrecio').val();
        let sucursalId = "{{ $sucursalSeleccionada ? $sucursalSeleccionada->id : '' }}";

        if (!sucursalId) {
            ToastCentro.fire({
                icon: 'error',
                title: 'No hay sucursal seleccionada.'
            });
            return;
        }

        $.ajax({
            url: "/inventario/medicamentos/" + medId + "/sucursales/" + sucursalId,
            type: "PUT",
            data: {
                _token: "{{ csrf_token() }}",
                precio: nuevoPrecio
            },
            success: function(response) {
                $('#modalPrecio').modal('hide');
                $('#price-display-' + medId).text('S/ ' + parseFloat(nuevoPrecio).toFixed(2));
                ToastCentro.fire({
                    icon: 'success',
                    title: '¡Precio actualizado!'
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

    // Abrir Modal
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
        setTimeout(() => $('#txtBuscarSalida').focus(), 500);
    }

    // BUSCADOR EN TIEMPO REAL
    $('#txtBuscarSalida').on('keyup', function() {
        let q = $(this).val().trim();
        let lista = $('#listaResultadosSalida');

        if (q.length < 2) {
            lista.hide();
            return;
        }

        clearTimeout(timeoutSalida);
        timeoutSalida = setTimeout(() => {
            $.ajax({
                url: RUTA_BUSCAR, // Usamos la ruta de ventas
                method: 'GET',
                data: {
                    sucursal_id: SUCURSAL_ID,
                    q: q,
                    categoria_id: '' // Enviamos vacío para buscar en todo
                },
                success: function(data) {
                    let html = '';
                    if (data.length === 0) {
                        html = '<div class="list-group-item text-muted">No encontrado</div>';
                    } else {
                        data.forEach(m => {
                            // Renderizamos igual que en ventas
                            html += `
                                <a href="#" class="list-group-item list-group-item-action py-2" 
                                   onclick="cargarLotesParaBaja(${m.medicamento_id}, '${m.nombre}', '${m.presentacion || ''}')">
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
                },
                error: function() {
                    console.error("Error buscando en ruta ventas");
                }
            });
        }, 300);
    });

    // CARGAR LOTES (Igual que en Ventas)
    window.cargarLotesParaBaja = function(id, nombre, pres) {
        // Limpiamos UI
        $('#txtBuscarSalida').val('');
        $('#listaResultadosSalida').hide();

        $('#lblProductoSeleccionado').text(`${nombre} - ${pres}`);
        $('#panelLotes').show();
        $('#formSalidaStock').hide();

        $('#tbodyLotesSalida').html('<tr><td colspan="4" class="text-center">Cargando lotes...</td></tr>');

        // Llamamos a la ruta de lotes de ventas
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
                    let btn = '';
                    if (l.stock_actual > 0) {
                        btn = `<button type="button" class="btn btn-sm btn-danger" 
                                onclick="prepararFormulario(${l.id}, '${l.codigo_lote}', ${l.stock_actual})">
                                <i class="fas fa-arrow-down"></i> Bajar
                               </button>`;
                    } else {
                        btn = '<span class="badge badge-secondary">Cero</span>';
                    }

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

    // MOSTRAR FORMULARIO FINAL
    window.prepararFormulario = function(loteId, codigo, stockMax) {
        $('#hiddenLoteId').val(loteId);
        $('#lblLoteCode').text(codigo + ' (Disp: ' + stockMax + ')');
        $('#inputCantidadSalida').val(1).attr('max', stockMax).focus();
        $('#formSalidaStock').slideDown();
    };

    // GUARDAR (AJAX)
    $('#formSalidaStock').on('submit', function(e) {
        e.preventDefault();

        let btn = $(this).find('button[type="submit"]');
        let txt = btn.html();
        btn.prop('disabled', true).text('Guardando...');

        $.ajax({
            url: "{{ route('inventario.movimientos.store_salida') }}", // La ruta corregida del paso 1
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $('#modalSalidaStock').modal('hide');
                ToastCentro.fire({
                    icon: 'success',
                    title: 'Stock actualizado correctamente'
                });
                aplicarFiltros(); // Recargar tabla principal (tu función existente)
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
    }
</script>
@endsection