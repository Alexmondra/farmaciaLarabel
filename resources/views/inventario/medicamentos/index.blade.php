@extends('adminlte::page')

@section('title', 'Inventario')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="text-dark font-weight-bold">
        <i class="fas fa-pills mr-2 text-primary"></i>Inventario
    </h1>

    {{-- PERMISO: CREAR MEDICAMENTO --}}
    @can('medicamentos.crear')
    <a href="{{ route('inventario.medicamentos.create') }}" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus mr-2"></i> Nuevo Medicamento
    </a>
    @endcan
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
</script>
@endsection