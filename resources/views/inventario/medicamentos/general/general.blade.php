@extends('adminlte::page')

@section('title', 'Catálogo General')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark-mode-light">
                <i class="fas fa-globe-americas text-primary mr-2"></i> Catálogo Maestro
            </h1>
        </div>
        <div class="col-sm-6 text-right">
            {{-- BOTÓN NUEVO --}}
            <button class="btn btn-success shadow-sm" id="btnNuevoGlobal">
                <i class="fas fa-plus-circle mr-1"></i> Nuevo Medicamento
            </button>
        </div>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">

    {{-- BARRA DE BÚSQUEDA --}}
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('inventario.medicamentos.general') }}" method="GET">
                <div class="input-group input-group-lg">
                    <input type="search" name="q" class="form-control"
                        placeholder="Buscar por Nombre, Código, Laboratorio..."
                        value="{{ $q ?? '' }}" autofocus>
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLA DE RESULTADOS --}}
    <div class="card shadow-lg">
        <div class="card-header border-0">
            <h3 class="card-title mt-1">Listado de Productos</h3>
            <div class="card-tools">
                <span class="badge badge-info">{{ $medicamentos->total() }} Registros</span>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped align-middle text-nowrap">
                <thead class="bg-light text-uppercase text-xs text-muted">
                    <tr>
                        <th style="width: 5%">Foto</th>
                        <th style="width: 10%">Código</th>
                        <th style="width: 30%">Medicamento</th>
                        <th style="width: 15%">Laboratorio</th>
                        <th style="width: 15%">Categoría</th>
                        <th style="width: 10%" class="text-center">C.Sanitario</th>
                        <th style="width: 15%" class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicamentos as $med)
                    @php
                    // Preparamos datos para el modal sin recargar
                    $medJson = [
                    'id' => $med->id,
                    'codigo' => $med->codigo,
                    'codigo_digemid' => $med->codigo_digemid,
                    'codigo_barra' => $med->codigo_barra,
                    'registro_sanitario' => $med->registro_sanitario,
                    'nombre' => $med->nombre,
                    'laboratorio' => $med->laboratorio,
                    'categoria_id' => $med->categoria_id,
                    'presentacion' => $med->presentacion,
                    'concentracion' => $med->concentracion,
                    'unidades_por_envase' => $med->unidades_por_envase,
                    'afecto_igv' => $med->afecto_igv,
                    'descripcion' => $med->descripcion,
                    'imagen_url' => $med->imagen_path ? asset('storage/' . $med->imagen_path) : null,
                    'precio_venta' => $med->sucursales->first()->pivot->precio_venta ?? 0
                    ];
                    @endphp

                    <tr>
                        <td class="align-middle text-center">
                            @if($med->imagen_path)
                            <img src="{{ asset('storage/' . $med->imagen_path) }}" class="rounded shadow-sm" style="width: 40px; height: 40px; object-fit: cover;">
                            @else
                            <i class="fas fa-camera text-gray opacity-50 fa-2x"></i>
                            @endif
                        </td>
                        <td class="align-middle font-weight-bold text-secondary">{{ $med->codigo_barra }}</td>
                        <td class="align-middle">
                            <span class="d-block font-weight-bold text-primary">{{ $med->nombre }}</span>
                            <small class="text-muted">{{ $med->presentacion }} {{ $med->concentracion }}</small>
                        </td>
                        <td class="align-middle">{{ $med->laboratorio ?? '-' }}</td>
                        <td class="align-middle">
                            @if($med->categoria)
                            <span class="badge badge-light border">{{ $med->categoria->nombre }}</span>
                            @else
                            <span class="text-muted text-xs">S/C</span>
                            @endif
                        </td>
                        <td class="align-middle text-center">{{ $med->registro_sanitario }}</td>

                        </td>
                        <td class="align-middle text-right">
                            <button class="btn btn-sm btn-outline-warning btn-editar shadow-sm"
                                data-info="{{ json_encode($medJson) }}"
                                title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-search fa-3x mb-3 opacity-50"></i>
                            <h5>Sin resultados</h5>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-end">
            {{ $medicamentos->appends(['q' => $q ?? ''])->links() }}
        </div>
    </div>
</div>

{{-- AQUÍ INCLUIMOS LOS MODALES QUE ESTÁN EN EL OTRO ARCHIVO --}}
@include('inventario.medicamentos.general.modals')

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {

        let sufijoAleatorio = "";

        // 1. ABRIR MODAL CREAR (CON EFECTO MAGIA)
        $('#btnNuevoGlobal').click(function() {
            $('#formNuevoMedicamentoRapid')[0].reset();
            $('#crear_med_igv').prop('checked', true);

            // Generamos sufijo único al abrir (Ej: 842)
            sufijoAleatorio = Math.floor(Math.random() * 900) + 100;
            $('#crear_codigo').val('NEW-' + sufijoAleatorio); // Estado inicial

            $('#modalCrearMedicamento').modal('show');

            // Foco al nombre para escribir rápido
            setTimeout(() => {
                $('input[name="nombre"]').focus();
            }, 500);
        });

        // 2. MAGIA: CÓDIGO DINÁMICO AL ESCRIBIR NOMBRE
        $('#formNuevoMedicamentoRapid input[name="nombre"]').on('input', function() {
            let texto = $(this).val().toUpperCase();
            let limpio = texto.replace(/[^A-Z0-9]/g, ''); // Solo letras y números
            let prefijo = limpio.substring(0, 6); // Max 6 letras

            if (prefijo.length === 0) prefijo = "NEW";

            $('#crear_codigo').val(prefijo + '-' + sufijoAleatorio);
        });

        // 3. ABRIR MODAL EDITAR
        $('.btn-editar').click(function() {
            let info = $(this).data('info');

            $('#edit_med_id').val(info.id);
            $('#edit_med_nombre').val(info.nombre);
            $('#edit_med_codigo').val(info.codigo);
            $('#edit_med_digemid').val(info.codigo_digemid);
            $('#edit_med_barra').val(info.codigo_barra);
            $('#edit_med_reg').val(info.registro_sanitario);
            $('#edit_med_lab').val(info.laboratorio);
            $('#edit_med_pres').val(info.presentacion);
            $('#edit_med_conc').val(info.concentracion);
            $('#edit_med_unidades').val(info.unidades_por_envase);
            $('#edit_med_desc').val(info.descripcion);
            $('#edit_med_cat').val(info.categoria_id);

            let isAfecto = (info.afecto_igv == 1 || info.afecto_igv === true);
            $('#edit_med_igv').prop('checked', isAfecto);

            if (info.imagen_url) {
                $('#img_med_foto_edit').attr('src', info.imagen_url).show();
                $('#div_med_placeholder_edit').hide();
            } else {
                $('#img_med_foto_edit').hide();
                $('#div_med_placeholder_edit').show();
            }

            $('#modalVerMedicamento').modal('show');
        });

        // 4. GUARDAR NUEVO
        $('#formNuevoMedicamentoRapid').on('submit', function(e) {
            e.preventDefault();
            let btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).text('Guardando...');

            $.ajax({
                url: "{{ route('inventario.medicamentos.storeRapido') }}",
                method: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#modalCrearMedicamento').modal('hide');
                    Swal.fire({
                            icon: 'success',
                            title: 'Creado',
                            text: 'Producto registrado.',
                            timer: 1500,
                            showConfirmButton: false
                        })
                        .then(() => location.reload());
                },
                error: function(xhr) {
                    let msg = 'Error al guardar.';
                    if (xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    Swal.fire('Error', msg, 'error');
                },
                complete: () => btn.prop('disabled', false).text('Guardar')
            });
        });

        // 5. GUARDAR EDICIÓN
        $('#formEditarMedicamento').on('submit', function(e) {
            e.preventDefault();
            let id = $('#edit_med_id').val();
            let btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).text('Actualizando...');

            let formData = new FormData(this);
            formData.append('_method', 'PUT');

            $.ajax({
                url: "/inventario/medicamentos/" + id + "/update-rapido",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#modalVerMedicamento').modal('hide');
                    Swal.fire({
                            icon: 'success',
                            title: 'Actualizado',
                            text: 'Cambios guardados.',
                            timer: 1500,
                            showConfirmButton: false
                        })
                        .then(() => location.reload());
                },
                error: function(xhr) {
                    let msg = 'Error al actualizar.';
                    if (xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    Swal.fire('Error', msg, 'error');
                },
                complete: () => btn.prop('disabled', false).text('Actualizar')
            });
        });

        window.previewImage = function(input, imgSelector, placeholderSelector) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $(imgSelector).attr('src', e.target.result).show();
                    $(placeholderSelector).hide();
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    });
</script>
@stop