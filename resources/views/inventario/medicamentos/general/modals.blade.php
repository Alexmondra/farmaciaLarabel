{{-- 1. MODAL CREAR MEDICAMENTO (NUEVO) --}}
<div class="modal fade" id="modalCrearMedicamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-plus-circle mr-2"></i> Nuevo Medicamento</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formNuevoMedicamentoRapid" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    {{-- FILA 1: CÓDIGO AUTOMÁTICO VISIBLE --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-primary">
                                <i class="fas fa-barcode mr-1"></i> CÓDIGO (AUTO)
                            </label>
                            <input type="text" name="codigo" id="crear_codigo"
                                class="form-control font-weight-bold text-center"
                                style="background-color: #e9ecef; color: #495057; letter-spacing: 1px;"
                                readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold">CÓDIGO BARRAS</label>
                            <div class="input-group">
                                <input type="text" name="codigo_barra" class="form-control" placeholder="Escanear...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold">REG. SANITARIO</label>
                            <input type="text" name="registro_sanitario" class="form-control">
                        </div>
                    </div>

                    {{-- FILA 2 --}}
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="small font-weight-bold">NOMBRE COMERCIAL *</label>
                            <input type="text" name="nombre" class="form-control" required placeholder="Escriba el nombre aquí...">
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold">LABORATORIO</label>
                            <input type="text" name="laboratorio" class="form-control">
                        </div>
                    </div>

                    {{-- FILA 3 --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="small font-weight-bold">CATEGORÍA</label>
                            <select name="categoria_id" class="form-control">
                                <option value="">-- Sin Categoría --</option>
                                @foreach(\App\Models\Inventario\Categoria::all() as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3"><label class="small font-weight-bold">PRESENTACIÓN</label><input type="text" name="presentacion" class="form-control"></div>
                        <div class="col-md-3"><label class="small font-weight-bold">CONCENTRACIÓN</label><input type="text" name="concentracion" class="form-control"></div>
                        <div class="col-md-3"><label class="small font-weight-bold text-primary">CONTENIDO *</label><input type="number" name="unidades_por_envase" class="form-control text-center fw-bold" value="1" min="1" required></div>
                    </div>

                    {{-- FILA 4 --}}
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="crear_med_igv" name="afecto_igv" value="1" checked>
                                <label class="custom-control-label font-weight-bold" for="crear_med_igv">¿Afecto a IGV (18%)?</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2"><label class="small font-weight-bold">DESCRIPCIÓN</label><textarea name="descripcion" class="form-control" rows="2"></textarea></div>
                        <div class="col-md-6 mb-2"><label class="small font-weight-bold">FOTO</label><input type="file" name="imagen" class="form-control" accept="image/*"></div>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm shadow-sm font-weight-bold">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. MODAL EDITAR MEDICAMENTO --}}
<div class="modal fade" id="modalVerMedicamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form id="formEditarMedicamento" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="edit_med_id">
                <div class="modal-header bg-warning py-2">
                    <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-edit mr-2"></i> Editar Producto</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img id="img_med_foto_edit" src="" class="img-fluid rounded shadow-sm border w-100 mb-2" style="height: 180px; object-fit: cover; display: none;">
                            <div id="div_med_placeholder_edit" class="img-placeholder-box mb-2 p-4 bg-light border">
                                <i class="fas fa-camera fa-3x mb-2 text-secondary opacity-50"></i>
                            </div>
                            <label class="btn btn-outline-dark btn-sm btn-block">
                                <i class="fas fa-camera"></i> Cambiar Imagen
                                <input type="file" name="imagen" class="d-none" accept="image/*" onchange="previewImage(this, '#img_med_foto_edit', '#div_med_placeholder_edit')">
                            </label>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group mb-2"><label class="small font-weight-bold">NOMBRE</label><input type="text" name="nombre" id="edit_med_nombre" class="form-control form-control-sm font-weight-bold" required></div>
                            <div class="row g-2 mb-2">
                                <div class="col-4"><label class="small">CÓDIGO</label><input type="text" name="codigo" id="edit_med_codigo" class="form-control form-control-sm" required></div>
                                <div class="col-4"><label class="small">BARRAS</label><input type="text" name="codigo_barra" id="edit_med_barra" class="form-control form-control-sm"></div>
                                <div class="col-4"><label class="small">REGISTRO</label><input type="text" name="registro_sanitario" id="edit_med_reg" class="form-control form-control-sm"></div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="small">CATEGORÍA</label>
                                    <select name="categoria_id" id="edit_med_cat" class="form-control form-control-sm">
                                        <option value="">-- Sin Categoría --</option>
                                        @foreach(\App\Models\Inventario\Categoria::all() as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6"><label class="small">LABORATORIO</label><input type="text" name="laboratorio" id="edit_med_lab" class="form-control form-control-sm"></div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-4"><label class="small">PRES.</label><input type="text" name="presentacion" id="edit_med_pres" class="form-control form-control-sm"></div>
                                <div class="col-4"><label class="small">CONC.</label><input type="text" name="concentracion" id="edit_med_conc" class="form-control form-control-sm"></div>
                                <div class="col-4"><label class="small">UNID.</label><input type="number" name="unidades_por_envase" id="edit_med_unidades" class="form-control form-control-sm fw-bold" required></div>
                            </div>
                            <div class="custom-control custom-switch pt-2">
                                <input type="checkbox" class="custom-control-input" id="edit_med_igv" name="afecto_igv" value="1">
                                <label class="custom-control-label font-weight-bold text-dark" for="edit_med_igv">¿Afecto a IGV?</label>
                            </div>
                            <textarea name="descripcion" id="edit_med_desc" class="form-control form-control-sm mt-2" rows="2" placeholder="Descripción..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning btn-sm shadow-sm font-weight-bold">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>