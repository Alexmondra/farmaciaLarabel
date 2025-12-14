<div class="glass-panel p-3 h-100 mt-3 mt-lg-0">
    <div class="section-title"><i class="fas fa-map-marker-alt"></i> PUNTO DE LLEGADA</div>

    <div class="form-group mb-2">
        <label class="label-futuristic">Destinatario (Razón Social)</label>
        <input type="text" name="nombre_destinatario" id="inputDestinatario" class="form-control form-control-futuristic" required>
    </div>

    <div class="row mb-2">
        <div class="col-5">
            <label class="label-futuristic">DNI / RUC</label>
            {{-- Este input es visual/ayuda, el ID del cliente va oculto --}}
            <input type="text" name="doc_destinatario" id="inputDocDestinatario" class="form-control form-control-futuristic" required>
        </div>
        <div class="col-7">
            <label class="label-futuristic">Ubigeo (6 Dígitos)</label>
            {{-- Coincide con tabla: ubigeo_llegada --}}
            <input type="text" name="ubigeo_llegada" id="inputUbigeo" class="form-control form-control-futuristic" required maxlength="6">
        </div>
    </div>

    <div class="form-group mb-0">
        <label class="label-futuristic">Dirección Exacta</label>
        <textarea name="direccion_llegada" id="inputDireccion" rows="2" class="form-control form-control-futuristic" required></textarea>

        <input type="hidden" name="cliente_id" id="inputClienteId">
        <input type="hidden" name="codigo_establecimiento_llegada" id="inputCodLlegada" value="0000">
    </div>
</div>