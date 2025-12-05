<div class="row">
    {{-- COLUMNA IZQUIERDA: FOTO Y ESTADO --}}
    <div class="col-md-3 text-center border-right">

        <div class="mt-2 mb-3 position-relative d-inline-block" style="cursor: pointer;" onclick="document.getElementById('customFile').click()">
            <img id="previewImagen"
                src="{{ isset($sucursal) && $sucursal->imagen_sucursal ? asset('storage/'.$sucursal->imagen_sucursal) : asset('img/default-store.png') }}"
                class="rounded-circle shadow-sm border"
                style="width: 120px; height: 120px; object-fit: cover;"
                onerror="this.src='https://ui-avatars.com/api/?name=S&background=f4f6f9&color=666&size=128';">

            <div class="position-absolute bg-dark text-white rounded-circle d-flex justify-content-center align-items-center shadow-sm"
                style="bottom: 5px; right: 5px; width: 32px; height: 32px;">
                <i class="fas fa-camera fa-xs"></i>
            </div>
        </div>

        <input type="file" name="imagen_sucursal" id="customFile" accept="image/*" style="display: none;">

        <div class="form-group mt-3">
            <label class="d-block text-muted small text-uppercase font-weight-bold">Estado</label>
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input" id="checkActivo" name="activo" value="1" checked>
                <label class="custom-control-label font-weight-bold" for="checkActivo">
                    <span id="labelActivo">Operativa</span>
                </label>
            </div>
        </div>
    </div>

    {{-- COLUMNA DERECHA: DATOS DEL FORMULARIO --}}
    <div class="col-md-9 pl-4">

        {{-- FILA 1: CÓDIGO SUNAT Y NOMBRE --}}
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-dark">COD. SUNAT *</label>
                {{-- Ahora el código es editable y de 4 dígitos --}}
                <input type="text" name="codigo" id="inputCodigo" class="form-control font-weight-bold"
                    placeholder="0000" maxlength="4" required
                    value="{{ isset($sucursal) ? $sucursal->codigo : '' }}">
                <small class="text-muted">Ej: 0001 (Anexo)</small>
            </div>
            <div class="form-group col-md-9">
                <label class="small font-weight-bold text-teal">NOMBRE SUCURSAL *</label>
                <input type="text" name="nombre" id="inputNombre" class="form-control" placeholder="Ej: Oficina Principal" required>
            </div>
        </div>

        {{-- BLOQUE DE UBICACIÓN --}}
        <h6 class="text-teal font-weight-bold mt-2 mb-2" style="font-size: 0.9rem;">
            <i class="fas fa-map-marked-alt mr-1"></i> Ubicación
        </h6>

        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-muted">DEPARTAMENTO</label>
                <input type="text" name="departamento" id="inputDepartamento" class="form-control form-control-sm" placeholder="Lima">
            </div>
            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-muted">PROVINCIA</label>
                <input type="text" name="provincia" id="inputProvincia" class="form-control form-control-sm" placeholder="Lima">
            </div>
            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-muted">DISTRITO</label>
                <input type="text" name="distrito" id="inputDistrito" class="form-control form-control-sm" placeholder="Miraflores">
            </div>
            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-muted">UBIGEO</label>
                <input type="text" name="ubigeo" id="inputUbigeo" class="form-control form-control-sm" maxlength="6" placeholder="150101">
            </div>
        </div>

        <div class="form-group">
            <label class="small font-weight-bold text-muted">DIRECCIÓN FISCAL</label>
            <div class="input-group input-group-sm">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-danger"></i></span>
                </div>
                <input type="text" name="direccion" id="inputDireccion" class="form-control" placeholder="Av. Larco 123, Of. 401">
            </div>
        </div>

        {{-- FILA CONTACTO --}}
        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="small font-weight-bold text-muted">EMAIL</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light"><i class="fas fa-envelope text-primary"></i></span>
                    </div>
                    <input type="email" name="email" id="inputEmail" class="form-control" placeholder="sucursal@empresa.com">
                </div>
            </div>
            <div class="form-group col-md-6">
                <label class="small font-weight-bold text-muted">TELÉFONO</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light"><i class="fas fa-phone text-success"></i></span>
                    </div>
                    <input type="text" name="telefono" id="inputTelefono" class="form-control" placeholder="(01) 000-000">
                </div>
            </div>
        </div>

        <hr class="my-2">

        {{-- FILA 3: CONFIGURACIÓN SUNAT/FISCAL --}}
        <h6 class="text-teal font-weight-bold mb-3" style="font-size: 0.9rem;">
            <i class="fas fa-file-invoice mr-1"></i> Facturación & Series
        </h6>

        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-dark">IGV (%)</label>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.01" name="impuesto_porcentaje" id="inputImpuesto" class="form-control font-weight-bold" value="18.00" required>
                    <div class="input-group-append">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>

            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-dark">SERIE BOLETA</label>
                <input type="text" name="serie_boleta" id="inputSerieBoleta" class="form-control form-control-sm text-uppercase font-weight-bold letter-spacing-1" maxlength="4" placeholder="B001" required>
            </div>

            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-dark">SERIE FACTURA</label>
                <input type="text" name="serie_factura" id="inputSerieFactura" class="form-control form-control-sm text-uppercase font-weight-bold letter-spacing-1" maxlength="4" placeholder="F001" required>
            </div>

            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-dark">SERIE TICKET</label>
                <input type="text" name="serie_ticket" id="inputSerieTicket" class="form-control form-control-sm text-uppercase font-weight-bold letter-spacing-1" maxlength="4" placeholder="T001" required>
            </div>
        </div>

    </div>
</div>