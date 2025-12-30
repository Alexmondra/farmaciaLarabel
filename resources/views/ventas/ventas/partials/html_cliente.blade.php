<div class="card card-primary card-outline card-cliente-pos">
    <div class="card-header py-2">
        <h3 class="card-title font-weight-bold text-dark-mode-light"><i class="fas fa-user-tag text-primary mr-1"></i> Cliente</h3>
    </div>
    <div class="card-body py-2">
        <div class="form-row align-items-end">
            <div class="col-4">
                <label class="mb-1 text-muted-mode small font-weight-bold">TIPO</label>
                <select name="tipo_comprobante" id="tipo_comprobante" class="form-control form-control-sm font-weight-bold">
                    <option value="BOLETA">DNI / BOL</option>
                    <option value="FACTURA">RUC / FACT</option>
                </select>
            </div>
            <div class="col-8">
                <label class="mb-1 text-muted-mode small font-weight-bold" id="label-documento">NÚMERO</label>
                <div class="input-group input-group-sm">
                    <input type="text" id="busqueda_cliente" class="form-control input-cliente-pos" placeholder="8 dígitos" autocomplete="off">
                    <div class="input-group-append">
                        <span class="input-group-text loader-input d-none" id="loader-cliente"><i class="fas fa-circle-notch fa-spin"></i></span>
                        <button class="btn btn-success d-none" type="button" id="btn-crear-cliente" title="Nuevo"><i class="fas fa-plus"></i></button>
                        <button class="btn btn-primary d-none" type="button" id="btn-ver-cliente" title="Ver"><i class="fas fa-eye"></i></button>
                        <input type="hidden" id="cliente_id_hidden" name="cliente_id">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group mb-0 mt-2">
            <input type="text" id="nombre_cliente_display" class="form-control display-nombre-cliente text-center" readonly placeholder="--- Cliente General ---">
        </div>

        <div id="panel-canje-puntos" class="mt-2" style="display:none;">
            <div class="input-group input-group-sm">

                <div class="input-group-prepend" title="Puntos Disponibles">
                    <span class="input-group-text bg-warning text-white font-weight-bold px-2 border-warning">
                        <i class="fas fa-star mr-1"></i> <span id="lbl-puntos-total">0</span>
                    </span>
                </div>

                <input type="number"
                    id="input-puntos-usar"
                    name="puntos_usados"
                    class="form-control text-center font-weight-bold text-primary"
                    placeholder="Pts a usar..."
                    min="0">

                <div class="input-group-append">
                    <span class="input-group-text bg-white text-success font-weight-bold border-info" style="border-right: 0;">
                        S/ <span id="lbl-equivalencia-dinero">0.00</span>
                    </span>
                </div>

                <div class="input-group-append">
                    <button class="btn btn-outline-info font-weight-bold" type="button" id="btn-aplicar-puntos" title="Aplicar Descuento">
                        Aplicar
                    </button>
                </div>
            </div>
            <input type="hidden"
                id="descuento-aplicado-soles"
                name="descuento_puntos"
                value="0">
        </div>
    </div>
</div>