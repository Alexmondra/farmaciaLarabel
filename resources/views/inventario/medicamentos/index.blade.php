@extends('adminlte::page')

@section('title', 'Inventario')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="text-dark font-weight-bold">
        <i class="fas fa-pills mr-2 text-primary"></i>Inventario
    </h1>

</div>
@endsection

@section('content')

{{-- BARRA DE BÚSQUEDA Y FILTROS (CENTRADA) --}}
<div class="row justify-content-center mb-4">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3" style="background-color: #f8f9fa;">
                <form id="filterForm">
                    <div class="row align-items-center">
                        {{-- 1. BUSCADOR PRINCIPAL (Grande) --}}
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

                        {{-- 2. FILTRO DE PRECIOS (Rango) --}}
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

{{-- CONTENEDOR TABLA --}}
<div class="card shadow border-0">
    <div class="card-body p-0" id="tabla-contenedor">
        @include('inventario.medicamentos._index_tabla')
    </div>
</div>

{{-- MODAL PRECIO (IGUAL QUE ANTES) --}}
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

    // 2. BUSCADOR OPTIMIZADO (State Check)
    let timeout = null;
    let ultimaBusqueda = ""; // <--- AQUÍ GUARDAMOS EL "RSTRO" ANTERIOR

    function aplicarFiltros() {
        let q = $('#searchInput').val().trim(); // .trim() quita espacios al inicio/final
        let min = $('#minPrice').val();
        let max = $('#maxPrice').val();

        // CREAMOS LA HUELLA DIGITAL DE LA BÚSQUEDA ACTUAL
        // Esto crea un string único: "q=amox&min=10&max="
        let params = new URLSearchParams({
            q: q,
            min: min,
            max: max
        }).toString();

        // === REGLA 1: NO REPETIR BÚSQUEDA ===
        // Si lo que vas a buscar es IDÉNTICO a lo último que buscaste, DETENTE.
        // Esto evita que busque si presionas Shift, Ctrl, o flechas sin cambiar texto.
        if (params === ultimaBusqueda) {
            return;
        }

        // === REGLA 2: RESET INTELIGENTE ===
        // Si todo está vacío, volvemos a la URL base (Reset)
        // Pero si ya estábamos en la base, la Regla 1 nos detendrá antes.
        let url = "{{ route('inventario.medicamentos.index') }}";

        // Solo agregamos parámetros si existen
        if (params) {
            url += "?" + params;
        }

        // Guardamos esta búsqueda como la "última realizada"
        ultimaBusqueda = params;

        // EJECUTAMOS AJAX
        $('#tabla-contenedor').css('opacity', '0.5');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                $('#tabla-contenedor').html(data);
                $('#tabla-contenedor').css('opacity', '1');
            },
            error: function() {
                // Si falla, permitimos buscar de nuevo borrando el rastro
                ultimaBusqueda = "";
                ToastCentro.fire({
                    icon: 'error',
                    title: 'Error al filtrar.'
                });
                $('#tabla-contenedor').css('opacity', '1');
            }
        });
    }

    // LISTENER MEJORADO
    $('#searchInput, #minPrice, #maxPrice').on('keyup change', function(e) {
        // Ignorar teclas que no escriben (Shift, Ctrl, Alt, CapsLock, Flechas)
        // Códigos: 16, 17, 18, 20, 37-40
        if ([16, 17, 18, 20, 37, 38, 39, 40].includes(e.keyCode)) return;

        clearTimeout(timeout);

        // Esperamos 500ms a que termines de escribir
        timeout = setTimeout(aplicarFiltros, 500);
    });

    // PAGINACIÓN (Se mantiene igual)
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');

        // MANTENER FILTROS AL PAGINAR
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
                // Actualizamos el rastro para que la paginación no rompa la lógica
                // (Opcional, pero recomendable)
            }
        });
    });

    // 3. LÓGICA PRECIO (IGUAL)
    function abrirModalPrecio(id, nombre, precioActual) {
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
</script>
@endsection