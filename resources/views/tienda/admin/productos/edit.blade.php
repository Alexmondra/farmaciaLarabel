@extends('adminlte::page')

@section('title', 'Editar producto tienda')

@section('content_header')
<h1>Editar producto tienda</h1>
@endsection

@section('content')
@include('tienda.partials.alerts')
<form method="POST" action="{{ route('tienda.admin.productos.update', $producto) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <input type="hidden" id="medicamento_id_seleccionado" value="{{ $producto->medicamento_id }}">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-2 mb-3">
                        <p class="text-muted mb-0 flex-grow-1">
                            Medicamento: <strong>{{ $producto->medicamento->nombre ?? '-' }}</strong><br>
                            Sucursal: <strong>{{ $producto->sucursal->nombre ?? '-' }}</strong>
                        </p>
                        <button type="button" id="btn_editar_med" class="btn btn-warning" title="Editar medicamento">
                            <i class="fas fa-edit"></i> Editar medicamento
                        </button>
                    </div>
                    <div class="form-group">
                        <label>Nombre para tienda</label>
                        <input name="nombre_web" value="{{ old('nombre_web', $producto->nombre_web) }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Descripcion web</label>
                        <textarea name="descripcion_web" class="form-control" rows="4">{{ old('descripcion_web', $producto->descripcion_web) }}</textarea>
                    </div>
                    <div class="alert alert-info">
                        La imagen principal se toma de la ficha del medicamento. Si el medicamento no tiene imagen, la primera que subas aqui quedara como principal; las demas iran a la galeria.
                    </div>
                    @if($producto->medicamento?->imagen_path)
                        <div class="mb-3">
                            <label class="d-block">Imagen principal del medicamento</label>
                            <img src="{{ asset('storage/' . $producto->medicamento->imagen_path) }}" alt="{{ $producto->nombre }}" class="img-thumbnail" style="height: 140px; object-fit: contain;">
                        </div>
                    @endif
                    <div class="form-group">
                        <label>Agregar imagenes de tienda</label>
                        <label class="upload-box d-flex flex-column align-items-center justify-content-center text-center p-4 border rounded" for="imagenes">
                            <strong>Subir imagenes</strong>
                            <span class="text-muted">Se convertiran a WebP.</span>
                        </label>
                        <input type="file" id="imagenes" name="imagenes[]" class="d-none" accept="image/*" multiple>
                        <small class="text-muted">Puedes seleccionar varias imagenes. Se guardaran en formato WebP.</small>
                        <div id="preview_imagenes" class="row mt-3"></div>
                    </div>
                    @if($producto->imagenes->isNotEmpty())
                        <hr>
                        <h5>Imagenes adicionales</h5>
                        <div class="row">
                            @foreach($producto->imagenes as $imagen)
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-2 h-100">
                                        <img src="{{ $imagen->url }}" alt="{{ $imagen->alt ?: $producto->nombre }}" class="img-fluid mb-2" style="height: 120px; width: 100%; object-fit: contain;">
                                        <div class="form-group mb-2">
                                            <label class="small mb-1">Texto alt</label>
                                            <input name="imagen_alt[{{ $imagen->id }}]" value="{{ old('imagen_alt.' . $imagen->id, $imagen->alt) }}" class="form-control form-control-sm">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="small mb-1">Orden</label>
                                            <input type="number" min="0" name="imagen_orden[{{ $imagen->id }}]" value="{{ old('imagen_orden.' . $imagen->id, $imagen->orden) }}" class="form-control form-control-sm">
                                        </div>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" name="imagen_visible[{{ $imagen->id }}]" value="1" class="custom-control-input" id="imagen_visible_{{ $imagen->id }}" @checked(old('imagen_visible.' . $imagen->id, $imagen->visible))>
                                            <label class="custom-control-label" for="imagen_visible_{{ $imagen->id }}">Visible</label>
                                        </div>
                                        <button type="submit" form="eliminar-imagen-{{ $imagen->id }}" class="btn btn-sm btn-outline-danger mt-2" onclick="return confirm('Eliminar esta imagen?')">Eliminar</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label>Precio web</label>
                        <input type="number" step="0.01" min="0" name="precio_web" value="{{ old('precio_web', $producto->precio_web) }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Modo de stock</label>
                        <select name="stock_modo" id="stock_modo" class="form-control" required>
                            <option value="stock_sucursal" @selected(old('stock_modo', $producto->stock_modo) === 'stock_sucursal')>Usar stock real de sucursal</option>
                            <option value="stock_manual" @selected(old('stock_modo', $producto->stock_modo) === 'stock_manual')>Stock web manual</option>
                            <option value="sin_control" @selected(old('stock_modo', $producto->stock_modo) === 'sin_control')>Sin control de stock</option>
                        </select>
                    </div>
                    <div class="form-group" id="stock_web_group">
                        <label>Stock web</label>
                        <input type="number" min="0" name="stock_web" value="{{ old('stock_web', $producto->stock_web) }}" class="form-control">
                    </div>
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" name="visible" value="1" class="custom-control-input" id="visible" @checked(old('visible', $producto->visible))>
                        <label class="custom-control-label" for="visible">Visible en tienda</label>
                    </div>
                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" name="destacado" value="1" class="custom-control-input" id="destacado" @checked(old('destacado', $producto->destacado))>
                        <label class="custom-control-label" for="destacado">Destacado</label>
                    </div>
                    <button class="btn btn-primary btn-block">Guardar</button>
                    <a href="{{ route('tienda.admin.productos.index') }}" class="btn btn-secondary btn-block">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</form>
@if($producto->imagenes->isNotEmpty())
    <div class="d-none">
        @foreach($producto->imagenes as $imagen)
            <form id="eliminar-imagen-{{ $imagen->id }}" method="POST" action="{{ route('tienda.admin.productos.imagenes.destroy', [$producto, $imagen]) }}">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    </div>
@endif
@include('inventario.medicamentos.general.modals')
@endsection

@push('css')
<style>
    .upload-box { background: #f8fafc; border-style: dashed !important; cursor: pointer; min-height: 130px; }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const stockModo = document.getElementById('stock_modo');
    const stockWebGroup = document.getElementById('stock_web_group');

    function toggleStockWeb() {
        stockWebGroup.style.display = stockModo.value === 'stock_manual' ? 'block' : 'none';
    }

    stockModo.addEventListener('change', toggleStockWeb);
    toggleStockWeb();

    document.getElementById('imagenes').addEventListener('change', (event) => {
        const preview = document.getElementById('preview_imagenes');
        preview.innerHTML = '';
        Array.from(event.target.files).forEach((file, index) => {
            const url = URL.createObjectURL(file);
            preview.insertAdjacentHTML('beforeend', `<div class="col-md-3 mb-2"><div class="border rounded p-1 text-center"><img src="${url}" class="img-fluid" style="height: 90px; object-fit: contain;"><small class="d-block text-muted">${index === 0 ? 'Primera subida' : 'Galeria'}</small></div></div>`);
        });
    });

    const btnEditarMed = document.getElementById('btn_editar_med');
    const medIdHidden = document.getElementById('medicamento_id_seleccionado');

    btnEditarMed.addEventListener('click', async () => {
        const medId = medIdHidden.value;
        if (!medId) return;

        btnEditarMed.disabled = true;
        btnEditarMed.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        try {
            const response = await fetch(`/inventario/medicamentos/${medId}/detalle-json`);
            const info = await response.json();

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
            $('#edit_med_reg').val(info.registro_sanitario);
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
        } catch (err) {
            Swal.fire('Error', 'No se pudo cargar la informacion del medicamento.', 'error');
        } finally {
            btnEditarMed.disabled = false;
            btnEditarMed.innerHTML = '<i class="fas fa-edit"></i> Editar medicamento';
        }
    });

    $('#formEditarMedicamento').on('submit', function(e) {
        e.preventDefault();
        const id = $('#edit_med_id').val();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).text('Actualizando...');

        const formData = new FormData(this);
        formData.append('_method', 'PUT');

        $.ajax({
            url: `/inventario/medicamentos/${id}/update-rapido`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function() {
                $('#modalVerMedicamento').modal('hide');
                Swal.fire({ icon: 'success', title: 'Actualizado', timer: 1000, showConfirmButton: false });
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Error.';
                if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                Swal.fire('Error', msg, 'error');
            },
            complete: () => btn.prop('disabled', false).text('Actualizar Cambios')
        });
    });
});
</script>
@endpush
