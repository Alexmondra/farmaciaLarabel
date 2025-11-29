<div class="row">
    <div class="col-md-3 text-center d-flex flex-column align-items-center justify-content-start pt-2">

        <div class="position-relative" style="cursor: pointer;" onclick="document.getElementById('customFile').click()">
            <img id="previewImagen"
                src="{{ isset($sucursal) && $sucursal->imagen_sucursal ? asset('storage/'.$sucursal->imagen_sucursal) : asset('img/default-store.png') }}"
                class="rounded-circle shadow border border-teal"
                style="width: 120px; height: 120px; object-fit: cover;"
                onerror="this.src='https://ui-avatars.com/api/?name=Farmacia&background=20c997&color=fff&size=128';">

            <div class="position-absolute bg-dark text-white rounded-circle d-flex justify-content-center align-items-center"
                style="bottom: 0; right: 0; width: 35px; height: 35px; opacity: 0.8;">
                <i class="fas fa-camera"></i>
            </div>
        </div>

        <small class="text-muted mt-2">Click para cambiar</small>

        <input type="file" name="imagen_sucursal" id="customFile" accept="image/*" style="display: none;">

        <div class="mt-4">
            <label class="d-block text-muted text-sm">Estado</label>
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input" id="checkActivo" name="activo" value="1" checked>
                <label class="custom-control-label font-weight-bold" for="checkActivo">
                    <span id="labelActivo">Operativa</span>
                </label>
            </div>
        </div>
    </div>

    <div class="col-md-9 border-left pl-4">

        <div class="form-row">
            @if(isset($sucursal))
            <div class="form-group col-md-3">
                <label class="text-teal font-weight-bold text-sm">CÓDIGO</label>
                <input type="text" value="{{ $sucursal->codigo }}" class="form-control bg-light" disabled>
                <input type="hidden" name="codigo" value="{{ $sucursal->codigo }}">
            </div>
            <div class="form-group col-md-9">
                @else
                <div class="form-group col-md-12">
                    @endif
                    <label class="text-teal font-weight-bold text-sm">NOMBRE DE SUCURSAL *</label>
                    <input type="text" name="nombre" id="inputNombre" class="form-control" placeholder="Ej: Farmacia Central" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-7">
                    <label class="text-sm">Dirección</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-map-marker-alt text-danger"></i></span>
                        </div>
                        <input type="text" name="direccion" id="inputDireccion" class="form-control border-left-0" placeholder="Av. Principal 123...">
                    </div>
                </div>
                <div class="form-group col-md-5">
                    <label class="text-sm">Teléfono</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-phone text-primary"></i></span>
                        </div>
                        <input type="text" name="telefono" id="inputTelefono" class="form-control border-left-0" placeholder="(01) 999...">
                    </div>
                </div>
            </div>

            <h6 class="text-muted text-sm mt-2 border-bottom pb-1"><i class="fas fa-coins text-warning"></i> Configuración Fiscal</h6>

            <div class="form-row mt-2">
                <div class="form-group col-md-4">
                    <label class="text-sm">Impuesto (IGV) *</label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="0.01" min="0" name="impuesto_porcentaje" id="inputImpuesto" class="form-control" required value="18.00">
                        <div class="input-group-append">
                            <span class="input-group-text font-weight-bold">%</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>