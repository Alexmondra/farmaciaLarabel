{{-- 1. MODAL CREAR MEDICAMENTO (NUEVO) --}}
<div class="modal fade" id="modalCrearMedicamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-teal text-white py-2"> {{-- Cambio a teal para consistencia --}}
                <h6 class="modal-title fw-bold"><i class="fas fa-plus-circle mr-2"></i> Nuevo Medicamento</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formNuevoMedicamentoRapid" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    {{-- FILA 1: CÓDIGOS (El corazón del inventario) --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-muted">CÓDIGO INTERNO (AUTO)</label>
                            <input type="text" name="codigo" id="crear_codigo"
                                class="form-control font-weight-bold text-center"
                                style="background-color: #e9ecef; color: #495057;" readonly>
                        </div>
                        {{-- === AQUÍ ESTÁ EL NUEVO CAMPO === --}}
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-primary">COD. DIGEMID</label>
                            <input type="text" name="codigo_digemid" class="form-control font-weight-bold border-primary" placeholder="Ej: 12345">
                            <small class="text-muted" style="font-size: 10px;">Requerido para Observatorio</small>
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold">CÓDIGO BARRAS</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                </div>
                                <input type="text" name="codigo_barra" class="form-control" placeholder="Escanear...">
                            </div>
                        </div>
                    </div>

                    {{-- FILA 2: DATOS PRINCIPALES --}}
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="small font-weight-bold">NOMBRE COMERCIAL *</label>
                            <input type="text" name="nombre" class="form-control" required placeholder="Escriba el nombre aquí...">
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold">REG. SANITARIO</label>
                            <input type="text" name="registro_sanitario" class="form-control" placeholder="Ej: EN-1234">
                        </div>
                    </div>

                    {{-- FILA 3: DETALLES FARMACÉUTICOS --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="small font-weight-bold">LABORATORIO</label>
                            <input type="text" name="laboratorio" class="form-control" placeholder="Ej: Bayer">
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
                        {{-- Unidades por envase es CRÍTICO para el precio unitario --}}
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-danger">UNIDADES (CAJA/FRASCO) *</label>
                            <input type="number" name="unidades_por_envase" class="form-control text-center fw-bold" value="1" min="1" required>
                            <small class="text-muted">Si es caja de 100, pon 100.</small>
                        </div>
                    </div>

                    {{-- FILA 4: PRESENTACIÓN --}}
                    <div class="row mb-3">
                        <div class="col-md-6"><label class="small font-weight-bold">PRESENTACIÓN</label><input type="text" name="presentacion" class="form-control" placeholder="Ej: Caja, Frasco, Tubo"></div>
                        <div class="col-md-6"><label class="small font-weight-bold">CONCENTRACIÓN</label><input type="text" name="concentracion" class="form-control" placeholder="Ej: 500mg"></div>
                    </div>

                    {{-- FILA 5: EXTRAS --}}
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
                        {{-- FOTO --}}
                        <div class="col-md-3 text-center">
                            <img id="img_med_foto_edit" src="" class="img-fluid rounded shadow-sm border w-100 mb-2" style="height: 150px; object-fit: cover; display: none;">
                            <div id="div_med_placeholder_edit" class="img-placeholder-box mb-2 p-4 bg-light border">
                                <i class="fas fa-camera fa-3x mb-2 text-secondary opacity-50"></i>
                            </div>
                            <label class="btn btn-outline-dark btn-sm btn-block">
                                <i class="fas fa-camera"></i> Cambiar
                                <input type="file" name="imagen" class="d-none" accept="image/*" onchange="previewImage(this, '#img_med_foto_edit', '#div_med_placeholder_edit')">
                            </label>
                        </div>

                        {{-- DATOS --}}
                        <div class="col-md-9">
                            <div class="form-group mb-2"><label class="small font-weight-bold">NOMBRE COMERCIAL *</label><input type="text" name="nombre" id="edit_med_nombre" class="form-control form-control-sm font-weight-bold" required></div>

                            {{-- FILA CÓDIGOS --}}
                            <div class="row g-2 mb-2">
                                <div class="col-4"><label class="small text-muted">CÓDIGO INTERNO</label><input type="text" name="codigo" id="edit_med_codigo" class="form-control form-control-sm bg-light" readonly></div>
                                {{-- NUEVO CAMPO DIGEMID --}}
                                <div class="col-4"><label class="small text-primary font-weight-bold">COD. DIGEMID</label><input type="text" name="codigo_digemid" id="edit_med_digemid" class="form-control form-control-sm border-primary font-weight-bold"></div>
                                <div class="col-4"><label class="small">CÓDIGO BARRAS</label><input type="text" name="codigo_barra" id="edit_med_barra" class="form-control form-control-sm"></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-6"><label class="small">REGISTRO SANITARIO</label><input type="text" name="registro_sanitario" id="edit_med_reg" class="form-control form-control-sm"></div>
                                <div class="col-6"><label class="small">LABORATORIO</label><input type="text" name="laboratorio" id="edit_med_lab" class="form-control form-control-sm"></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <label class="small">CATEGORÍA</label>
                                    <select name="categoria_id" id="edit_med_cat" class="form-control form-control-sm">
                                        <option value="">-- Sin Categoría --</option>
                                        @foreach(\App\Models\Inventario\Categoria::all() as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-4"><label class="small">PRES.</label><input type="text" name="presentacion" id="edit_med_pres" class="form-control form-control-sm"></div>
                                <div class="col-4"><label class="small">CONC.</label><input type="text" name="concentracion" id="edit_med_conc" class="form-control form-control-sm"></div>
                            </div>

                            <div class="row g-2">
                                <div class="col-6"><label class="small text-danger font-weight-bold">UNIDADES (FACTOR)</label><input type="number" name="unidades_por_envase" id="edit_med_unidades" class="form-control form-control-sm fw-bold" required></div>
                                <div class="col-6 pt-4">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="edit_med_igv" name="afecto_igv" value="1">
                                        <label class="custom-control-label font-weight-bold text-dark" for="edit_med_igv">¿Afecto a IGV?</label>
                                    </div>
                                </div>
                            </div>
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