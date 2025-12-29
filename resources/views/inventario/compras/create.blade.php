@extends('adminlte::page')

@section('title', 'Registrar compra')

@section('content_header')
<h1>Registrar nueva compra</h1>
@stop

@include('inventario.compras.partials.styles')

@section('content')

<form action="{{ route('compras.store') }}" method="POST" enctype="multipart/form-data" id="form-compra">
    @csrf

    {{-- 1. HEADER (Datos Proveedor) --}}
    @include('inventario.compras.partials.header')

    {{-- 2. ITEMS (Tabla) --}}
    @include('inventario.compras.partials.items')

    {{-- Espacio para que no tape la barra fija --}}
    <div style="height: 150px;"></div>

    {{-- 3. FOOTER (Botón Guardar) --}}
    @include('inventario.compras.partials.footer')

</form>
{{-- AQUÍ CIERRA EL FORMULARIO DE COMPRA --}}


{{-- ========================================================= --}}
{{-- ZONA DE MODALES --}}
{{-- ========================================================= --}}

{{-- 1. MODAL VER PROVEEDOR --}}
<div class="modal fade" id="modalVerProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white py-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-id-card mr-2"></i> Datos del Proveedor</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <h5 class="text-center font-weight-bold mb-1" id="view_razon_social">--</h5>
                <p class="text-center text-muted mb-3" id="view_ruc">RUC: --</p>
                <hr>
                <p class="mb-1"><i class="fas fa-phone-alt text-info mr-2"></i> <span id="view_telefono"></span></p>
                <p class="mb-1"><i class="fas fa-envelope text-info mr-2"></i> <span id="view_email"></span></p>
                <p class="mb-0"><i class="fas fa-map-marker-alt text-info mr-2"></i> <span id="view_direccion"></span></p>
            </div>
        </div>
    </div>
</div>

{{-- 2. MODAL CREAR PROVEEDOR --}}
<div class="modal fade" id="modalCrearProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-plus-circle mr-2"></i> Nuevo Proveedor</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formNuevoProveedor">
                <div class="modal-body">
                    <div class="form-group mb-2"><label class="small font-weight-bold">RUC *</label><input type="text" name="ruc" class="form-control input-enhanced" required></div>
                    <div class="form-group mb-2"><label class="small font-weight-bold">Razón Social *</label><input type="text" name="razon_social" class="form-control input-enhanced" required></div>
                    <div class="row">
                        <div class="col-6 mb-2"><label class="small font-weight-bold">Teléfono</label><input type="text" name="telefono" class="form-control input-enhanced"></div>
                        <div class="col-6 mb-2"><label class="small font-weight-bold">Email</label><input type="email" name="email" class="form-control input-enhanced"></div>
                    </div>
                    <div class="mb-0"><label class="small font-weight-bold">Dirección</label><input type="text" name="direccion" class="form-control input-enhanced"></div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm">Guardar Proveedor</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 3. MODAL VER / EDITAR MEDICAMENTO (ACTUALIZADO CON DIGEMID) --}}
<div class="modal fade" id="modalVerMedicamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form id="formEditarMedicamento">
                @csrf
                <input type="hidden" name="id" id="edit_med_id">

                <div class="modal-header bg-info text-white py-2">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i> Editar Producto</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row">
                        {{-- COLUMNA IZQUIERDA: FOTO --}}
                        <div class="col-md-3 text-center">
                            <img id="img_med_foto_edit" src="" class="img-fluid rounded shadow-sm border w-100 mb-2" style="height: 150px; object-fit: cover; display: none;">
                            <div id="div_med_placeholder_edit" class="img-placeholder-box mb-2">
                                <i class="fas fa-camera fa-3x mb-2 text-secondary opacity-50"></i>
                                <span class="small font-weight-bold text-muted d-block">Sin Imagen</span>
                            </div>
                            <label class="btn btn-outline-info btn-sm btn-block">
                                <i class="fas fa-camera"></i> Cambiar
                                <input type="file" name="imagen" class="d-none" accept="image/*" onchange="previewImage(this, '#img_med_foto_edit', '#div_med_placeholder_edit')">
                            </label>
                        </div>

                        {{-- COLUMNA DERECHA: CAMPOS --}}
                        <div class="col-md-9">
                            {{-- NOMBRE --}}
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-muted">NOMBRE COMERCIAL</label>
                                <input type="text" name="nombre" id="edit_med_nombre" class="form-control form-control-sm font-weight-bold text-dark" required>
                            </div>

                            {{-- FILA DE CÓDIGOS (4 COLUMNAS) --}}
                            <div class="row g-2 mb-2">
                                <div class="col-3">
                                    <label class="small font-weight-bold text-muted" style="font-size: 0.7rem;">COD. INTERNO</label>
                                    <input type="text" name="codigo" id="edit_med_codigo" class="form-control form-control-sm bg-light" readonly>
                                </div>
                                {{-- NUEVO CAMPO DIGEMID --}}
                                <div class="col-3">
                                    <label class="small font-weight-bold text-primary" style="font-size: 0.7rem;">COD. DIGEMID</label>
                                    <input type="text" name="codigo_digemid" id="edit_med_digemid" class="form-control form-control-sm border-primary fw-bold" placeholder="-----">
                                </div>
                                <div class="col-3">
                                    <label class="small font-weight-bold text-muted" style="font-size: 0.7rem;">COD. BARRAS</label>
                                    <input type="text" name="codigo_barra" id="edit_med_barra" class="form-control form-control-sm">
                                </div>
                                <div class="col-3">
                                    <label class="small font-weight-bold text-muted" style="font-size: 0.7rem;">REG. SANITARIO</label>
                                    <input type="text" name="registro_sanitario" id="edit_med_reg" class="form-control form-control-sm">
                                </div>
                            </div>

                            {{-- LABORATORIO Y CATEGORÍA --}}
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="small font-weight-bold text-muted">CATEGORÍA</label>
                                    <select name="categoria_id" id="edit_med_cat" class="form-control form-control-sm">
                                        <option value="">-- Sin Categoría --</option>
                                        @foreach($categorias ?? [] as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="small font-weight-bold text-muted">LABORATORIO</label>
                                    <input type="text" name="laboratorio" id="edit_med_lab" class="form-control form-control-sm">
                                </div>
                            </div>

                            {{-- DETALLES --}}
                            <div class="row g-2 mb-2">
                                <div class="col-4"><label class="small font-weight-bold text-muted">PRESENTACIÓN</label><input type="text" name="presentacion" id="edit_med_pres" class="form-control form-control-sm"></div>
                                <div class="col-4"><label class="small font-weight-bold text-muted">CONCENTRACIÓN</label><input type="text" name="concentracion" id="edit_med_conc" class="form-control form-control-sm"></div>
                                <div class="col-4"><label class="small font-weight-bold text-danger">FACTOR (UNID)</label><input type="number" name="unidades_por_envase" id="edit_med_unidades" class="form-control form-control-sm text-center fw-bold" required min="1"></div>
                            </div>

                            {{-- SWITCH IGV --}}
                            <div class="row align-items-center mb-1">
                                <div class="col-md-6">
                                    <div class="custom-control custom-switch pt-2">
                                        <input type="checkbox" class="custom-control-input" id="edit_med_igv" name="afecto_igv" value="1">
                                        <label class="custom-control-label font-weight-bold text-dark" for="edit_med_igv">¿Afecto a IGV?</label>
                                    </div>
                                </div>
                                <div class="col-md-6 text-right">
                                    <span class="badge bg-light text-dark border p-2">Venta: <strong class="text-success h6" id="view_med_precio_display">S/ 0.00</strong></span>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <textarea name="descripcion" id="edit_med_desc" class="form-control form-control-sm" rows="1" placeholder="Descripción..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info btn-sm shadow-sm font-weight-bold"><i class="fas fa-sync-alt mr-1"></i> Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 4. MODAL CREAR MEDICAMENTO (ACTUALIZADO CON DIGEMID) --}}
<div class="modal fade" id="modalCrearMedicamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-pills mr-2"></i> Nuevo Medicamento</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formNuevoMedicamentoRapid" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    {{-- FILA 1: CÓDIGOS --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="label-mini text-muted">CÓDIGO (AUTO)</label>
                            <input type="text" name="codigo" id="crear_codigo" class="form-control input-enhanced font-weight-bold text-center bg-light" readonly>
                        </div>
                        {{-- NUEVO CAMPO DIGEMID --}}
                        <div class="col-md-4">
                            <label class="label-mini text-primary font-weight-bold">COD. DIGEMID</label>
                            <input type="text" name="codigo_digemid" class="form-control input-enhanced border-primary" placeholder="Ej: 000123">
                        </div>
                        <div class="col-md-4">
                            <label class="label-mini">CÓDIGO BARRAS</label>
                            <input type="text" name="codigo_barra" class="form-control input-enhanced">
                        </div>
                    </div>

                    {{-- FILA 2: DATOS BÁSICOS --}}
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="label-mini">NOMBRE COMERCIAL *</label>
                            <input type="text" name="nombre" class="form-control input-enhanced" required>
                        </div>
                        <div class="col-md-4">
                            <label class="label-mini">REG. SANITARIO</label>
                            <input type="text" name="registro_sanitario" class="form-control input-enhanced">
                        </div>
                    </div>

                    {{-- FILA 3: DETALLES --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="label-mini">LABORATORIO</label>
                            <input type="text" name="laboratorio" class="form-control input-enhanced">
                        </div>
                        <div class="col-md-4">
                            <label class="label-mini">CATEGORÍA</label>
                            <select name="categoria_id" class="form-control input-enhanced">
                                <option value="">-- Sin Categoría --</option>
                                @foreach($categorias ?? [] as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="label-mini text-danger fw-bold">CONTENIDO (UNID) *</label>
                            <input type="number" name="unidades_por_envase" class="form-control input-enhanced text-center fw-bold" value="1" min="1" required>
                        </div>
                    </div>

                    {{-- FILA 4: EXTRAS --}}
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="label-mini">PRESENTACIÓN</label>
                            <input type="text" name="presentacion" class="form-control input-enhanced" placeholder="Caja">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="label-mini">CONCENTRACIÓN</label>
                            <input type="text" name="concentracion" class="form-control input-enhanced" placeholder="500mg">
                        </div>
                        <div class="col-md-4 mb-2 pt-4">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="crear_med_igv" name="afecto_igv" value="1" checked>
                                <label class="custom-control-label font-weight-bold" for="crear_med_igv">¿Afecto IGV?</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm" id="btnGuardarMedRapido"><i class="fas fa-save mr-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@include('inventario.compras.partials.scripts')