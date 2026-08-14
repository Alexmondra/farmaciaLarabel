@extends('adminlte::page')

@section('title', 'Publicar producto')

@section('content_header')
<h1>Publicar producto en tienda</h1>
@endsection

@section('content')
@include('tienda.partials.alerts')
<form method="POST" action="{{ route('tienda.admin.productos.store') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="medicamento_sucursal" id="medicamento_sucursal" value="{{ old('medicamento_sucursal') }}">
    <input type="hidden" id="medicamento_id_seleccionado" value="">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label>Buscar medicamento</label>
                        <input type="search" id="buscar_medicamento" class="form-control" placeholder="Nombre, codigo o codigo de barras" autocomplete="off">
                        <small class="text-muted">Escribe al menos 2 caracteres. Solo apareceran hasta 5 medicamentos con sucursales activas no publicadas.</small>
                    </div>

                    <div id="resultados_medicamentos" class="mb-3"></div>
                    <div class="d-flex align-items-start gap-2 mb-3">
                        <div id="seleccion_producto" class="alert alert-success d-none mb-0 flex-grow-1"></div>
                        <button type="button" id="btn_editar_med" class="btn btn-warning d-none" title="Editar medicamento">
                            <i class="fas fa-edit"></i> Editar medicamento
                        </button>
                    </div>

                    <div class="form-group">
                        <label>Nombre para tienda</label>
                        <input name="nombre_web" value="{{ old('nombre_web') }}" class="form-control" placeholder="Opcional, si se deja vacio usa el nombre del medicamento">
                    </div>

                    <div class="form-group">
                        <label>Descripcion web</label>
                        <textarea name="descripcion_web" class="form-control" rows="4">{{ old('descripcion_web') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Imagenes</label>
                        <label class="upload-box d-flex flex-column align-items-center justify-content-center text-center p-4 border rounded" for="imagenes">
                            <strong>Subir imagenes</strong>
                            <span class="text-muted">La primera sera principal si el medicamento no tiene imagen. Las demas iran a la galeria.</span>
                        </label>
                        <input type="file" id="imagenes" name="imagenes[]" class="d-none" accept="image/*" multiple>
                        <div id="preview_imagenes" class="row mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label>Precio web</label>
                        <input type="number" step="0.01" min="0" name="precio_web" value="{{ old('precio_web') }}" class="form-control" placeholder="Opcional">
                        <small id="precio_sucursal" class="text-muted">Si se deja vacio usa el precio de sucursal.</small>
                    </div>
                    <div class="form-group">
                        <label>Modo de stock</label>
                        <select name="stock_modo" id="stock_modo" class="form-control" required>
                            <option value="stock_sucursal" @selected(old('stock_modo', 'stock_sucursal') === 'stock_sucursal')>Usar stock real de sucursal</option>
                            <option value="stock_manual" @selected(old('stock_modo') === 'stock_manual')>Stock web manual</option>
                            <option value="sin_control" @selected(old('stock_modo') === 'sin_control')>Sin control de stock</option>
                        </select>
                    </div>
                    <div class="form-group" id="stock_web_group">
                        <label>Stock web</label>
                        <input type="number" min="0" name="stock_web" value="{{ old('stock_web') }}" class="form-control">
                    </div>
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" name="visible" value="1" class="custom-control-input" id="visible" @checked(old('visible', true))>
                        <label class="custom-control-label" for="visible">Visible en tienda</label>
                    </div>
                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" name="destacado" value="1" class="custom-control-input" id="destacado" @checked(old('destacado'))>
                        <label class="custom-control-label" for="destacado">Destacado</label>
                    </div>
                    <button class="btn btn-primary btn-block">Publicar</button>
                    <a href="{{ route('tienda.admin.productos.index') }}" class="btn btn-secondary btn-block">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</form>
@include('inventario.medicamentos.general.modals')
@endsection

@push('css')
<style>
    .upload-box { background: #f8fafc; border-style: dashed !important; cursor: pointer; min-height: 150px; }
    .med-card { cursor: pointer; transition: background .15s ease; }
    .med-card:hover { background: #f8fafc; }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('buscar_medicamento');
    const resultados = document.getElementById('resultados_medicamentos');
    const seleccionado = document.getElementById('seleccion_producto');
    const hidden = document.getElementById('medicamento_sucursal');
    const medIdHidden = document.getElementById('medicamento_id_seleccionado');
    const btnEditarMed = document.getElementById('btn_editar_med');
    const precioSucursal = document.getElementById('precio_sucursal');
    const stockModo = document.getElementById('stock_modo');
    const stockWebGroup = document.getElementById('stock_web_group');
    let timer;

    function toggleStockWeb() {
        stockWebGroup.style.display = stockModo.value === 'stock_manual' ? 'block' : 'none';
    }

    stockModo.addEventListener('change', toggleStockWeb);
    toggleStockWeb();

    input.addEventListener('input', () => {
        clearTimeout(timer);
        const q = input.value.trim();
        hidden.value = '';
        medIdHidden.value = '';
        seleccionado.classList.add('d-none');
        seleccionado.innerHTML = '';
        btnEditarMed.classList.add('d-none');

        if (q.length < 2) {
            resultados.innerHTML = '';
            return;
        }

        timer = setTimeout(async () => {
            const response = await fetch(`{{ route('tienda.admin.productos.buscar_medicamentos') }}?q=${encodeURIComponent(q)}`);
            const data = await response.json();
            resultados.innerHTML = data.length ? data.map(renderMedicamento).join('') : '<div class="alert alert-light border">Sin resultados disponibles.</div>';
        }, 250);
    });

    resultados.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-med-sucursal]');
        if (!btn) return;

        hidden.value = btn.dataset.medSucursal;
        medIdHidden.value = btn.dataset.medId;
        seleccionado.classList.remove('d-none');
        seleccionado.innerHTML = `<strong>${btn.dataset.nombre}</strong><br>Sucursal: ${btn.dataset.sucursal} - Precio actual: S/ ${Number(btn.dataset.precio).toFixed(2)} - Stock real: ${btn.dataset.stock}`;
        precioSucursal.textContent = `Precio actual de sucursal: S/ ${Number(btn.dataset.precio).toFixed(2)}`;
        btnEditarMed.classList.remove('d-none');
        input.value = '';
        resultados.innerHTML = '';
    });

    function renderMedicamento(med) {
        const sucursales = med.sucursales.length
            ? med.sucursales.map(sucursal => `<button type="button" class="btn btn-sm btn-outline-primary mr-1 mb-1" data-med-sucursal="${med.id}:${sucursal.id}" data-med-id="${med.id}" data-nombre="${escapeHtml(med.nombre)}" data-sucursal="${escapeHtml(sucursal.nombre)}" data-precio="${sucursal.precio}" data-stock="${sucursal.stock}">${escapeHtml(sucursal.nombre)} - S/ ${Number(sucursal.precio).toFixed(2)}</button>`).join('')
            : '<span class="badge badge-secondary">Sin sucursales disponibles</span>';

        return `<div class="border rounded p-2 mb-2 med-card">
            <div class="d-flex gap-2">
                <div class="flex-grow-1">
                    <strong>${escapeHtml(med.nombre)}</strong><br>
                    <small class="text-muted">Codigo: ${escapeHtml(med.codigo || '-')} | Lab: ${escapeHtml(med.laboratorio || '-')} | Barra: ${escapeHtml(med.codigo_barra || '-')} | Blister: ${escapeHtml(med.codigo_barra_blister || '-')}</small>
                    <div class="mt-2">${sucursales}</div>
                </div>
            </div>
        </div>`;
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>'"]/g, char => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'}[char]));
    }

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

    document.getElementById('imagenes').addEventListener('change', (event) => {
        const preview = document.getElementById('preview_imagenes');
        preview.innerHTML = '';
        Array.from(event.target.files).forEach((file, index) => {
            const url = URL.createObjectURL(file);
            preview.insertAdjacentHTML('beforeend', `<div class="col-md-3 mb-2"><div class="border rounded p-1 text-center"><img src="${url}" class="img-fluid" style="height: 90px; object-fit: contain;"><small class="d-block text-muted">${index === 0 ? 'Principal si falta' : 'Galeria'}</small></div></div>`);
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
