<div class="glass-panel p-3 mt-3">
    <div class="section-title"><i class="fas fa-truck"></i> TRANSPORTE</div>

    <div class="row">
        <div class="col-md-3">
            <label class="label-futuristic">Modalidad</label>
            <select name="modalidad_traslado" id="selectModalidad" class="form-control form-control-futuristic">
                <option value="02">PRIVADO (Propio)</option>
                <option value="01">PÚBLICO (Tercero)</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="label-futuristic">Peso (KG)</label>
            <input type="number" name="peso_bruto" class="form-control form-control-futuristic text-center" value="1.000" step="0.001">
        </div>
        <div class="col-md-2">
            <label class="label-futuristic">Bultos</label>
            <input type="number" name="numero_bultos" class="form-control form-control-futuristic text-center" value="1">
        </div>

        {{-- TRANSPORTE PRIVADO --}}
        <input type="hidden" name="doc_chofer_tipo" value="1">

        <div class="col-md-2 campo-privado">
            <label class="label-futuristic">Placa</label>
            <input type="text" name="placa_vehiculo" class="form-control form-control-futuristic"
                placeholder="ABC-123"
                oninput="this.value = this.value.toUpperCase()">
        </div>
        <div class="col-md-3 campo-privado">
            <label class="label-futuristic">DNI Conductor</label>
            <input type="text" name="doc_chofer_numero" class="form-control form-control-futuristic"
                maxlength="8"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        </div>
        <div class="col-md-12 mt-2 campo-privado">
            <div class="row">
                <div class="col-md-6">
                    <label class="label-futuristic">Nombre Conductor</label>
                    <input type="text" name="nombre_chofer" class="form-control form-control-futuristic">
                </div>
                <div class="col-md-6">
                    <label class="label-futuristic">Licencia</label>
                    <input type="text" name="licencia_conducir" class="form-control form-control-futuristic">
                </div>
            </div>
        </div>

        {{-- TRANSPORTE PÚBLICO --}}
        <input type="hidden" name="doc_transportista_tipo" value="6">

        <div class="col-md-5 campo-publico d-none">
            <label class="label-futuristic">RUC Empresa</label>
            <input type="text" name="doc_transportista_numero" class="form-control form-control-futuristic"
                maxlength="11"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        </div>
        <div class="col-md-7 campo-publico d-none">
            <label class="label-futuristic">Razón Social</label>
            <input type="text" name="razon_social_transportista" class="form-control form-control-futuristic">
        </div>
    </div>
</div>