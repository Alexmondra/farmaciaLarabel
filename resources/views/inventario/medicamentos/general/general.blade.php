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
                        <th style="width: 15%">Jerarquía</th>
                        <th style="width: 15%">Categoría</th>
                        <th style="width: 15%" class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- OJO: Usamos @forelse para poder usar @empty --}}
                    @forelse($medicamentos as $med)
                    @php
                    // JSON COMPLETO PARA EL MODAL DE EDICIÓN
                    $medJson = [
                    'id' => $med->id,
                    'codigo' => $med->codigo,
                    'codigo_digemid' => $med->codigo_digemid,

                    'codigo_barra' => $med->codigo_barra,
                    'codigo_barra_blister' => $med->codigo_barra_blister,

                    'nombre' => $med->nombre,
                    'laboratorio' => $med->laboratorio,
                    'presentacion' => $med->presentacion,
                    'concentracion' => $med->concentracion,
                    'forma_farmaceutica' => $med->forma_farmaceutica,
                    'descripcion' => $med->descripcion,
                    'registro_sanitario' => $med->registro_sanitario,

                    'categoria_id' => $med->categoria_id,
                    'unidades_por_envase' => $med->unidades_por_envase,
                    'unidades_por_blister' => $med->unidades_por_blister,

                    'afecto_igv' => $med->afecto_igv,
                    'receta_medica' => $med->receta_medica,

                    'imagen_url' => $med->imagen_path ? asset('storage/' . $med->imagen_path) : null
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
                        <td class="align-middle">
                            <span class="font-weight-bold text-secondary">{{ $med->codigo }}</span>
                            @if($med->codigo_barra)
                            <br><small class="text-muted"><i class="fas fa-barcode"></i> {{ $med->codigo_barra }}</small>
                            @endif
                        </td>
                        <td class="align-middle">
                            <span class="d-block font-weight-bold text-primary">{{ $med->nombre }}</span>
                            <small class="text-muted">{{ $med->laboratorio }} - {{ $med->presentacion }}</small>
                        </td>
                        <td class="align-middle">
                            <span class="badge badge-success">Caja x{{ $med->unidades_por_envase }}</span>
                            @if($med->unidades_por_blister)
                            <span class="badge badge-info">Blíster x{{ $med->unidades_por_blister }}</span>
                            @endif
                        </td>
                        <td class="align-middle">
                            {{ $med->categoria->nombre ?? 'S/C' }}
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
                        <td colspan="6" class="text-center py-5 text-muted">
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

@include('inventario.medicamentos.general.modals')

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {

        let sufijoAleatorio = "";

        // 1. CREAR
        $('#btnNuevoGlobal').click(function() {
            $('#formNuevoMedicamentoRapid')[0].reset();
            $('#crear_med_igv').prop('checked', true);
            sufijoAleatorio = Math.floor(Math.random() * 900) + 100;
            $('#crear_codigo').val('NEW-' + sufijoAleatorio);
            $('#modalCrearMedicamento').modal('show');
            setTimeout(() => {
                $('input[name="nombre"]').focus();
            }, 500);
        });

        // AUTO CÓDIGO
        $('#formNuevoMedicamentoRapid input[name="nombre"]').on('input', function() {
            let texto = $(this).val().toUpperCase();
            let limpio = texto.replace(/[^A-Z0-9]/g, '');
            let prefijo = limpio.substring(0, 6) || "NEW";
            $('#crear_codigo').val(prefijo + '-' + sufijoAleatorio);
        });

        // 2. EDITAR (Cargar datos)
        $('.btn-editar').click(function() {
            let info = $(this).data('info');

            $('#edit_med_id').val(info.id);
            $('#edit_med_nombre').val(info.nombre);
            $('#edit_med_codigo').val(info.codigo);
            $('#edit_med_digemid').val(info.codigo_digemid);
            $('#edit_med_lab').val(info.laboratorio);
            $('#edit_med_cat').val(info.categoria_id);
            $('#edit_med_pres').val(info.presentacion);
            $('#edit_med_conc').val(info.concentracion);
            $('#edit_med_forma').val(info.forma_farmaceutica);
            $('#edit_med_desc').val(info.descripcion);

            $('#edit_med_barra').val(info.codigo_barra);
            $('#edit_med_barra_blister').val(info.codigo_barra_blister);
            $('#edit_med_unidades').val(info.unidades_por_envase);
            $('#edit_med_unidades_blister').val(info.unidades_por_blister);

            $('#edit_med_igv').prop('checked', info.afecto_igv == 1 || info.afecto_igv === true);
            $('#edit_med_receta').prop('checked', info.receta_medica == 1 || info.receta_medica === true);

            if (info.imagen_url) {
                $('#img_med_foto_edit').attr('src', info.imagen_url).show();
                $('#div_med_placeholder_edit').hide();
            } else {
                $('#img_med_foto_edit').hide();
                $('#div_med_placeholder_edit').show();
            }

            $('#modalVerMedicamento').modal('show');
        });

        // GUARDAR NUEVO
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
                            timer: 1000,
                            showConfirmButton: false
                        })
                        .then(() => location.reload());
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'Error.';
                    if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    Swal.fire('Error', msg, 'error');
                },
                complete: () => btn.prop('disabled', false).text('Guardar Producto')
            });
        });

        // GUARDAR EDICIÓN
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
                            timer: 1000,
                            showConfirmButton: false
                        })
                        .then(() => location.reload());
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'Error.';
                    if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    Swal.fire('Error', msg, 'error');
                },
                complete: () => btn.prop('disabled', false).text('Actualizar Cambios')
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