{{-- 1. MODAL CREAR MEDICAMENTO (CORREGIDO: Con Registro Sanitario) --}}
<div class="modal fade" id="modalCrearMedicamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-teal text-white py-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-plus-circle mr-2"></i> Nuevo Medicamento</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formNuevoMedicamentoRapid" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    {{-- FILA 1: CÓDIGOS PRINCIPALES --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-muted">CÓD. INTERNO</label>
                            <input type="text" name="codigo" id="crear_codigo" class="form-control font-weight-bold text-center bg-light" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-primary">COD. DIGEMID</label>
                            <input type="text" name="codigo_digemid" class="form-control border-primary" placeholder="Ej: 12345">
                        </div>
                        <div class="col-md-6">
                            <label class="small font-weight-bold">NOMBRE COMERCIAL *</label>
                            <input type="text" name="nombre" class="form-control font-weight-bold" required placeholder="Nombre del producto...">
                        </div>
                    </div>

                    {{-- FILA 2: DATOS DE SCANNER (BARRAS) --}}
                    <div class="row mb-3 bg-light p-2 rounded mx-0">
                        <div class="col-md-6">
                            <label class="small font-weight-bold"><i class="fas fa-box mr-1"></i> CODIGO DE BARRA CAJA (Envase)</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-barcode"></i></span></div>
                                <input type="text" name="codigo_barra" class="form-control" placeholder="Escanear caja...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="small font-weight-bold"><i class="fas fa-tablets mr-1"></i> CODIGO DE BARRA BLÍSTER (Opcional)</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-barcode"></i></span></div>
                                <input type="text" name="codigo_barra_blister" class="form-control" placeholder="Escanear blíster...">
                            </div>
                        </div>
                    </div>

                    {{-- FILA 3: UNIDADES Y JERARQUÍA --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-success">UNIDADES POR CAJA *</label>
                            <input type="number" name="unidades_por_envase" class="form-control text-center fw-bold border-success" value="1" min="1" required>
                            <small class="text-muted d-block text-center" style="font-size: 10px;">Total pastillas</small>
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-info">UNIDADES POR BLÍSTER</label>
                            <input type="number" name="unidades_por_blister" class="form-control text-center fw-bold border-info" placeholder="Ej: 10">
                            <small class="text-muted d-block text-center" style="font-size: 10px;">Pastillas por tira</small>
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold">CATEGORÍA</label>
                            <select name="categoria_id" class="form-control">
                                <option value="">-- Sin Categoría --</option>
                                @foreach(\App\Models\Inventario\Categoria::all() as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- FILA 4: DETALLES FARMACÉUTICOS --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="small font-weight-bold">FORMA FARM.</label>
                            <input type="text" name="forma_farmaceutica" class="form-control form-control-sm" placeholder="Ej: Tableta">
                        </div>
                        <div class="col-md-3">
                            <label class="small">LABORATORIO</label>
                            <input type="text" name="laboratorio" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="small">PRESENTACIÓN</label>
                            <input type="text" name="presentacion" class="form-control form-control-sm" placeholder="Caja x 100">
                        </div>
                        <div class="col-md-3">
                            <label class="small">CONCENTRACIÓN</label>
                            <input type="text" name="concentracion" class="form-control form-control-sm" placeholder="500mg">
                        </div>
                    </div>

                    {{-- FILA 5: REGISTRO SANITARIO Y DESCRIPCIÓN (AQUÍ ESTÁ EL CAMBIO) --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-primary">REG. SANITARIO</label>
                            <input type="text" name="registro_sanitario" class="form-control form-control-sm border-primary" placeholder="Ej: EN-12345">
                        </div>
                        <div class="col-md-8">
                            <label class="small">DESCRIPCIÓN / NOTAS</label>
                            <textarea name="descripcion" class="form-control form-control-sm" rows="1" placeholder="Detalles adicionales..."></textarea>
                        </div>
                    </div>

                    {{-- FILA 6: EXTRAS Y FOTO --}}
                    <div class="row align-items-center border-top pt-2">
                        <div class="col-md-6 mb-2">
                            <div class="d-flex justify-content-around">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="crear_med_igv" name="afecto_igv" value="1" checked>
                                    <label class="custom-control-label font-weight-bold" for="crear_med_igv">Con IGV</label>
                                </div>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="crear_med_receta" name="receta_medica" value="1">
                                    <label class="custom-control-label font-weight-bold text-danger" for="crear_med_receta">Pide Receta</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2"><label class="small">FOTO</label><input type="file" name="imagen" class="form-control form-control-sm" accept="image/*"></div>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="submit" class="btn btn-success shadow-sm font-weight-bold">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. MODAL EDITAR MEDICAMENTO (YA TIENE REGISTRO SANITARIO) --}}
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

                        {{-- === COLUMNA IZQUIERDA (IDENTIDAD) === --}}
                        <div class="col-md-3 border-right">
                            {{-- FOTO --}}
                            <div class="text-center mb-2">
                                <img id="img_med_foto_edit" src="" class="img-fluid rounded shadow-sm border w-100 mb-2" style="height: 150px; object-fit: cover; display: none;">
                                <div id="div_med_placeholder_edit" class="img-placeholder-box mb-2 p-4 bg-light border">
                                    <i class="fas fa-camera fa-3x mb-2 text-secondary opacity-50"></i>
                                </div>
                                <label class="btn btn-outline-dark btn-sm btn-block shadow-sm">
                                    <i class="fas fa-camera"></i> Cambiar
                                    <input type="file" name="imagen" class="d-none" accept="image/*" onchange="previewImage(this, '#img_med_foto_edit', '#div_med_placeholder_edit')">
                                </label>
                            </div>

                            {{-- CÓDIGO INTERNO --}}
                            <div class="text-center mb-3">
                                <label class="small font-weight-bold text-muted mb-0">SKU / INTERNO</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="codigo" id="edit_med_codigo" class="form-control text-center font-weight-bold border-0 bg-light text-secondary" readonly style="font-size: 1.1em;">
                                </div>
                            </div>
                            <hr>
                            {{-- CATEGORÍA --}}
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted">CATEGORÍA</label>
                                <select name="categoria_id" id="edit_med_cat" class="form-control form-control-sm border-secondary">
                                    <option value="">-- Sin Categoría --</option>
                                    @foreach(\App\Models\Inventario\Categoria::all() as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- SWITCHES --}}
                            <div class="bg-light p-2 rounded border">
                                <div class="custom-control custom-switch mb-2">
                                    <input type="checkbox" class="custom-control-input" id="edit_med_igv" name="afecto_igv" value="1">
                                    <label class="custom-control-label small font-weight-bold" for="edit_med_igv">Afecto IGV</label>
                                </div>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="edit_med_receta" name="receta_medica" value="1">
                                    <label class="custom-control-label small font-weight-bold text-danger" for="edit_med_receta">Pide Receta</label>
                                </div>
                            </div>
                        </div>

                        {{-- === COLUMNA DERECHA (DATOS EDITABLES) === --}}
                        <div class="col-md-9 pl-md-4">

                            {{-- NOMBRE --}}
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">NOMBRE COMERCIAL</label>
                                <input type="text" name="nombre" id="edit_med_nombre" class="form-control font-weight-bold bg-white input-lg" required style="font-size: 1.2rem;">
                            </div>

                            {{-- DIGEMID Y LABORATORIO --}}
                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <label class="small text-primary fw-bold">CÓDIGO DIGEMID</label>
                                    <input type="text" name="codigo_digemid" id="edit_med_digemid" class="form-control form-control-sm border-primary font-weight-bold">
                                </div>
                                <div class="col-8">
                                    <label class="small">LABORATORIO</label>
                                    <input type="text" name="laboratorio" id="edit_med_lab" class="form-control form-control-sm">
                                </div>
                            </div>

                            {{-- CÓDIGOS DE BARRA --}}
                            <div class="row g-2 mb-2 bg-light p-2 rounded mx-0 border-left border-warning">
                                <div class="col-6">
                                    <label class="small font-weight-bold"><i class="fas fa-barcode"></i> CODIGO BARRA CAJA</label>
                                    <input type="text" name="codigo_barra" id="edit_med_barra" class="form-control form-control-sm">
                                </div>
                                <div class="col-6">
                                    <label class="small font-weight-bold"><i class="fas fa-barcode"></i> CODIGO BARRA BLÍSTER</label>
                                    <input type="text" name="codigo_barra_blister" id="edit_med_barra_blister" class="form-control form-control-sm">
                                </div>
                            </div>

                            {{-- UNIDADES --}}
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="small text-success fw-bold">UNIDADES POR CAJA</label>
                                    <input type="number" name="unidades_por_envase" id="edit_med_unidades" class="form-control form-control-sm border-success fw-bold text-center" required>
                                </div>
                                <div class="col-6">
                                    <label class="small text-info fw-bold">UNIDADES POR BLÍSTER</label>
                                    <input type="number" name="unidades_por_blister" id="edit_med_unidades_blister" class="form-control form-control-sm border-info fw-bold text-center">
                                </div>
                            </div>

                            {{-- DETALLES FARMACÉUTICOS --}}
                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <label class="small font-weight-bold">FORMA FARM.</label>
                                    <input type="text" name="forma_farmaceutica" id="edit_med_forma" class="form-control form-control-sm" placeholder="Ej: Tableta">
                                </div>
                                <div class="col-4">
                                    <label class="small">PRESENTACIÓN</label>
                                    <input type="text" name="presentacion" id="edit_med_pres" class="form-control form-control-sm">
                                </div>
                                <div class="col-4">
                                    <label class="small">CONCENTRACIÓN</label>
                                    <input type="text" name="concentracion" id="edit_med_conc" class="form-control form-control-sm">
                                </div>
                            </div>

                            {{-- REGISTRO SANITARIO Y DESCRIPCIÓN --}}
                            <div class="row g-2">
                                <div class="col-4">
                                    <label class="small font-weight-bold text-primary">REG. SANITARIO</label>
                                    <input type="text" name="registro_sanitario" id="edit_med_reg" class="form-control form-control-sm border-primary">
                                </div>
                                <div class="col-8">
                                    <label class="small">DESCRIPCIÓN / NOTAS</label>
                                    <textarea name="descripcion" id="edit_med_desc" class="form-control form-control-sm" rows="1"></textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning btn-sm shadow-sm font-weight-bold">Actualizar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>