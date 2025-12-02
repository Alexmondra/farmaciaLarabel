<div class="card card-primary card-outline card-cliente-pos">
    <div class="card-header py-2">
        <h3 class="card-title font-weight-bold"><i class="fas fa-user-tag text-primary mr-1"></i> Cliente</h3>
    </div>
    <div class="card-body py-2">
        <div class="form-row align-items-end">
            <div class="col-4">
                <label class="mb-1 text-muted small font-weight-bold">TIPO</label>
                <select name="tipo_comprobante" id="tipo_comprobante" class="form-control form-control-sm font-weight-bold">
                    <option value="BOLETA">DNI / BOL</option>
                    <option value="FACTURA">RUC / FACT</option>
                    <option value="TICKET">TICKET</option>
                </select>
            </div>
            <div class="col-8">
                <label class="mb-1 text-muted small font-weight-bold" id="label-documento">NÚMERO</label>
                <div class="input-group input-group-sm">
                    <input type="text" id="busqueda_cliente" class="form-control input-cliente-pos" placeholder="8 dígitos" autocomplete="off">
                    <div class="input-group-append">
                        <span class="input-group-text loader-input d-none" id="loader-cliente"><i class="fas fa-circle-notch fa-spin"></i></span>
                        <button class="btn btn-success d-none" type="button" id="btn-crear-cliente" title="Nuevo"><i class="fas fa-plus"></i></button>
                        <button class="btn btn-primary d-none" type="button" id="btn-ver-cliente" title="Ver"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group mb-0 mt-2">
            <input type="text" id="nombre_cliente_display" class="form-control display-nombre-cliente text-center" readonly placeholder="--- Cliente General ---">
        </div>
    </div>
</div>