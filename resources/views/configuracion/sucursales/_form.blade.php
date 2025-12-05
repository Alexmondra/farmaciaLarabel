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

        {{-- FILA 1: CÓDIGO (SOLO EDITAR) Y NOMBRE --}}
        <div class="form-row">
            @if(isset($sucursal))
            <div class="form-group col-md-3">
                <label class="small font-weight-bold text-muted">CÓDIGO</label>
                <input type="text" value="{{ $sucursal->codigo }}" class="form-control bg-light" disabled>
                <input type="hidden" name="codigo" value="{{ $sucursal->codigo }}">
            </div>
            <div class="form-group col-md-9">
                <label class="small font-weight-bold text-teal">NOMBRE SUCURSAL *</label>
                <input type="text" name="nombre" id="inputNombre" class="form-control" placeholder="Ej: Farmacia Principal" required>
            </div>
            @else
            <div class="form-group col-md-12">
                <label class="small font-weight-bold text-teal">NOMBRE SUCURSAL *</label>
                <input type="text" name="nombre" id="inputNombre" class="form-control" placeholder="Ej: Farmacia Principal" required>
            </div>
            @endif
        </div>

        {{-- FILA 2: DIRECCIÓN Y TELÉFONO --}}
        <div class="form-row">
            <div class="form-group col-md-8">
                <label class="small font-weight-bold text-muted">DIRECCIÓN</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white"><i class="fas fa-map-marker-alt text-danger"></i></span>
                    </div>
                    <input type="text" name="direccion" id="inputDireccion" class="form-control border-left-0" placeholder="Av. Siempre Viva 123">
                </div>
            </div>
            <div class="form-group col-md-4">
                <label class="small font-weight-bold text-muted">TELÉFONO</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white"><i class="fas fa-phone text-primary"></i></span>
                    </div>
                    <input type="text" name="telefono" id="inputTelefono" class="form-control border-left-0" placeholder="(01) 000-000">
                </div>
            </div>
        </div>

        <hr class="my-2">

        {{-- FILA 3: CONFIGURACIÓN SUNAT/FISCAL --}}
        <h6 class="text-teal font-weight-bold mb-3" style="font-size: 0.9rem;">
            <i class="fas fa-file-invoice mr-1"></i> Facturación & Series
        </h6>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="small font-weight-bold text-dark">IGV (%)</label>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.01" name="impuesto_porcentaje" id="inputImpuesto" class="form-control font-weight-bold" value="18.00" required>
                    <div class="input-group-append">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>

            <div class="form-group col-md-4">
                <label class="small font-weight-bold text-dark">SERIE BOLETA *</label>
                <input type="text" name="serie_boleta" id="inputSerieBoleta" class="form-control form-control-sm text-uppercase font-weight-bold letter-spacing-1" maxlength="4" placeholder="B001" required>
            </div>

            <div class="form-group col-md-4">
                <label class="small font-weight-bold text-dark">SERIE FACTURA *</label>
                <input type="text" name="serie_factura" id="inputSerieFactura" class="form-control form-control-sm text-uppercase font-weight-bold letter-spacing-1" maxlength="4" placeholder="F001" required>
            </div>

            <div class="form-group col-md-4">
                <label class="small font-weight-bold text-dark">SERIE TIKET *</label>
                <input type="text" name="serie_ticket" id="inputSerieTicket" class="form-control form-control-sm text-uppercase font-weight-bold letter-spacing-1" maxlength="4" placeholder="T001" required>
            </div>
        </div>

    </div>
</div>