<div class="row">
    {{-- COLUMNA IZQUIERDA: FOTO Y ESTADO --}}
    <div class="col-12 col-md-3 text-center border-right border-right-md pb-3 pb-md-0">
        <div class="mt-2 mb-3 position-relative d-inline-block" style="cursor: pointer;" onclick="document.getElementById('customFile').click()">
            <img id="previewImagen"
                src="{{ isset($sucursal) && $sucursal->imagen_sucursal ? asset('storage/'.$sucursal->imagen_sucursal) : asset('img/default-store.png') }}"
                class="rounded-circle shadow-sm border"
                style="width: 120px; height: 120px; object-fit: cover;"
                onerror="this.onerror=null; this.src='{{ asset('img/default-store.png') }}';">

            <div class="position-absolute bg-dark text-white rounded-circle d-flex justify-content-center align-items-center shadow-sm"
                style="bottom: 5px; right: 5px; width: 32px; height: 32px;">
                <i class="fas fa-camera fa-xs"></i>
            </div>
        </div>

        <input type="file" name="imagen_sucursal" id="customFile" accept="image/*" style="display: none;">

        <div class="form-group mt-3">
            <label class="d-block text-muted small text-uppercase font-weight-bold">Estado</label>
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input" id="checkActivo" name="activo" value="1"
                    {{ (!isset($sucursal) || $sucursal->activo) ? 'checked' : '' }}>
                <label class="custom-control-label font-weight-bold" for="checkActivo">
                    <span id="labelActivo">Operativa</span>
                </label>
            </div>
        </div>
        <hr class="d-block d-md-none my-3">
    </div>

    {{-- COLUMNA DERECHA: DATOS --}}
    <div class="col-12 col-md-9 pl-md-4">
        {{-- FILA 1: CÓDIGOS Y NOMBRE --}}
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-dark">COD. SUNAT *</label>
                <input type="text" name="codigo" class="form-control font-weight-bold" placeholder="0000" maxlength="4" required
                    value="{{ old('codigo', isset($sucursal) ? $sucursal->codigo : ($sugerenciaCodigo ?? '')) }}" autocomplete="off">
            </div>

            {{-- === NUEVO CAMPO DIGEMID === --}}
            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-primary">COD. DIGEMID</label>
                <input type="text" name="cod_establecimiento_digemid" class="form-control font-weight-bold text-primary" placeholder="Ej: 0001234"
                    value="{{ old('cod_establecimiento_digemid', $sucursal->cod_establecimiento_digemid ?? '') }}" autocomplete="off">
                <small class="text-muted" style="font-size: 10px;">Requerido para Observatorio</small>
            </div>
            {{-- =========================== --}}

            <div class="form-group col-md-6">
                <label class="small font-weight-bold text-teal">NOMBRE SUCURSAL *</label>
                <input type="text" name="nombre" class="form-control" placeholder="Ej: Oficina Principal" required
                    value="{{ old('nombre', $sucursal->nombre ?? '') }}" autocomplete="organization">
            </div>
        </div>

        {{-- UBICACIÓN --}}
        <h6 class="text-teal font-weight-bold mt-2 mb-2" style="font-size: 0.9rem;">
            <i class="fas fa-map-marked-alt mr-1"></i> Ubicación
        </h6>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-muted">DEPARTAMENTO</label>
                <input type="text" name="departamento" class="form-control form-control-sm" placeholder="Lima"
                    value="{{ old('departamento', $sucursal->departamento ?? '') }}" autocomplete="address-level1">
            </div>
            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-muted">PROVINCIA</label>
                <input type="text" name="provincia" class="form-control form-control-sm" placeholder="Lima"
                    value="{{ old('provincia', $sucursal->provincia ?? '') }}" autocomplete="address-level2">
            </div>
            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-muted">DISTRITO</label>
                <input type="text" name="distrito" class="form-control form-control-sm" placeholder="Miraflores"
                    value="{{ old('distrito', $sucursal->distrito ?? '') }}" autocomplete="address-level3">
            </div>
            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-muted">UBIGEO</label>
                <input type="text" name="ubigeo" class="form-control form-control-sm" maxlength="6" placeholder="150101"
                    value="{{ old('ubigeo', $sucursal->ubigeo ?? '') }}" autocomplete="off">
            </div>
        </div>
        <div class="form-group">
            <label class="small font-weight-bold text-muted">DIRECCIÓN FISCAL</label>
            <div class="input-group input-group-sm">
                <div class="input-group-prepend"><span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-danger"></i></span></div>
                <input type="text" name="direccion" class="form-control" placeholder="Av. Larco 123"
                    value="{{ old('direccion', $sucursal->direccion ?? '') }}" autocomplete="street-address">
            </div>
        </div>

        {{-- CONTACTO --}}
        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="small font-weight-bold text-muted">EMAIL</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="input-group-text bg-light"><i class="fas fa-envelope text-primary"></i></span></div>
                    <input type="email" name="email" class="form-control" placeholder="sucursal@empresa.com"
                        value="{{ old('email', $sucursal->email ?? '') }}" autocomplete="email">
                </div>
            </div>
            <div class="form-group col-md-6">
                <label class="small font-weight-bold text-muted">TELÉFONO</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="input-group-text bg-light"><i class="fas fa-phone text-success"></i></span></div>
                    <input type="text" name="telefono" class="form-control" placeholder="(01) 000-000"
                        value="{{ old('telefono', $sucursal->telefono ?? '') }}" autocomplete="tel">
                </div>
            </div>
        </div>

        <hr class="my-2">

        {{-- FACTURACIÓN & SERIES --}}
        <h6 class="text-teal font-weight-bold mb-3" style="font-size: 0.9rem;">
            <i class="fas fa-file-invoice mr-1"></i> Facturación & Series
        </h6>

        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-dark">IGV (%)</label>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.01" name="impuesto_porcentaje" class="form-control font-weight-bold" required
                        value="{{ old('impuesto_porcentaje', $sucursal->impuesto_porcentaje ?? '18.00') }}" autocomplete="off">
                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
            </div>

            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-primary">SERIE FACTURA</label>
                <input type="text" name="serie_factura" class="form-control form-control-sm text-uppercase font-weight-bold letter-spacing-1" maxlength="4" required placeholder="F001"
                    value="{{ old('serie_factura', isset($sucursal) ? $sucursal->serie_factura : ($sugerenciaFactura ?? '')) }}" autocomplete="off">
            </div>
            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-info">SERIE BOLETA</label>
                <input type="text" name="serie_boleta" class="form-control form-control-sm text-uppercase font-weight-bold letter-spacing-1" maxlength="4" required placeholder="B001"
                    value="{{ old('serie_boleta', isset($sucursal) ? $sucursal->serie_boleta : ($sugerenciaBoleta ?? '')) }}" autocomplete="off">
            </div>
            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-muted">SERIE TICKET</label>
                <input type="text" name="serie_ticket" class="form-control form-control-sm text-uppercase font-weight-bold letter-spacing-1" maxlength="4" required placeholder="TK01"
                    value="{{ old('serie_ticket', isset($sucursal) ? $sucursal->serie_ticket : ($sugerenciaTicket ?? '')) }}" autocomplete="off">
            </div>
        </div>

        <div class="form-row bg-white pt-2 rounded border-top">
            <div class="form-group col-md-4">
                <label class="small font-weight-bold text-danger">NC FACTURA</label>
                <input type="text" name="serie_nc_factura" class="form-control form-control-sm text-uppercase font-weight-bold" maxlength="4" required placeholder="FC01"
                    value="{{ old('serie_nc_factura', isset($sucursal) ? $sucursal->serie_nc_factura : ($sugerenciaNCFactura ?? '')) }}" autocomplete="off">
            </div>
            <div class="form-group col-md-4">
                <label class="small font-weight-bold text-danger">NC BOLETA</label>
                <input type="text" name="serie_nc_boleta" class="form-control form-control-sm text-uppercase font-weight-bold" maxlength="4" required placeholder="BC01"
                    value="{{ old('serie_nc_boleta', isset($sucursal) ? $sucursal->serie_nc_boleta : ($sugerenciaNCBoleta ?? '')) }}" autocomplete="off">
            </div>
            <div class="form-group col-md-4">
                <label class="small font-weight-bold text-success">GUÍA REMISIÓN</label>
                <input type="text" name="serie_guia" class="form-control form-control-sm text-uppercase font-weight-bold" maxlength="4" required placeholder="T001"
                    value="{{ old('serie_guia', isset($sucursal) ? $sucursal->serie_guia : ($sugerenciaGuia ?? '')) }}" autocomplete="off">
            </div>
        </div>
    </div>
</div>