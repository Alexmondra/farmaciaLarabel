<div class="glass-panel p-3 h-100 mt-3 mt-lg-0">
    <div class="section-title"><i class="fas fa-map-marker-alt"></i> PUNTO DE LLEGADA</div>

    <div class="form-group mb-2">
        <label class="label-futuristic">Destinatario (Razón Social)</label>
        <input type="text" name="nombre_destinatario" id="inputDestinatario" class="form-control form-control-futuristic" required>
    </div>

    <div class="row mb-2">
        <div class="row mb-2">
            <div class="col-5">
                <label class="label-futuristic">DNI / RUC</label>
                <input type="text" name="doc_destinatario" id="inputDocDestinatario"
                    class="form-control form-control-futuristic @error('doc_destinatario') is-invalid @enderror"
                    value="{{ old('doc_destinatario') }}" required>
            </div>
            <div class="col-7">
                <label class="label-futuristic">Ubigeo (6 Dígitos)</label>
                <input type="text" name="ubigeo_llegada" id="inputUbigeo"
                    class="form-control form-control-futuristic @error('ubigeo_llegada') is-invalid @enderror"
                    value="{{ old('ubigeo_llegada') }}" required maxlength="6">
            </div>
        </div>
    </div>

    <div class="form-group mb-0">
        <label class="label-futuristic">Dirección Exacta</label>
        <textarea name="direccion_llegada" id="inputDireccion" rows="2" class="form-control form-control-futuristic @error('direccion_llegada') is-invalid @enderror" required>{{ old('direccion_llegada') }}</textarea>

        <input type="hidden" name="cliente_id" id="inputClienteId" value="{{ old('cliente_id') }}">
        <input type="hidden" name="codigo_establecimiento_llegada" id="inputCodLlegada" value="{{ old('codigo_establecimiento_llegada', '0000') }}">
    </div>
</div>