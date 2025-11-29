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
    // 1. CONFIGURACIÓN TOAST CENTRADO
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

    // 2. LÓGICA DE BÚSQUEDA MULTI-FILTRO
    let timeout = null;

    function aplicarFiltros() {
        let q = $('#searchInput').val();
        let min = $('#minPrice').val();
        let max = $('#maxPrice').val();

        // Construimos la URL con todos los parámetros
        let url = "{{ route('inventario.medicamentos.index') }}";
        // URLSearchParams facilita armar el string ?q=...&min=...
        let params = new URLSearchParams({
            q: q,
            min: min,
            max: max
        });

        $('#tabla-contenedor').css('opacity', '0.5');

        $.ajax({
            url: url + "?" + params.toString(),
            type: 'GET',
            success: function(data) {
                $('#tabla-contenedor').html(data);
                $('#tabla-contenedor').css('opacity', '1');
            },
            error: function() {
                ToastCentro.fire({
                    icon: 'error',
                    title: 'Error al filtrar.'
                });
                $('#tabla-contenedor').css('opacity', '1');
            }
        });
    }

    // Escuchamos eventos en los 3 inputs
    // Usamos 'keyup' para escribir y 'change' para las flechitas del input number
    $('#searchInput, #minPrice, #maxPrice').on('keyup change', function() {
        clearTimeout(timeout);
        timeout = setTimeout(aplicarFiltros, 500); // Espera 500ms
    });

    // Paginación
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        // Al paginar, debemos mantener los filtros actuales
        let url = $(this).attr('href');

        // Truco: Agregamos los filtros actuales a la URL de paginación si no los tiene
        let q = $('#searchInput').val();
        let min = $('#minPrice').val();
        let max = $('#maxPrice').val();

        if (url.indexOf('q=') === -1) url += "&q=" + q;
        if (url.indexOf('min=') === -1) url += "&min=" + min;
        if (url.indexOf('max=') === -1) url += "&max=" + max;

        // Reutilizamos la lógica AJAX directa (sin llamar a aplicarFiltros para no duplicar params)
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

    // 3. LÓGICA PRECIO (IGUAL QUE ANTES)
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