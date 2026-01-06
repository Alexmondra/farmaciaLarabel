<div class="card border-0 shadow-sm mb-4">
    {{-- HEADER CON DEGRADADO --}}
    <div class="card-header card-header-gradient py-3">
        <h5 class="mb-0 font-weight-bold">
            <i class="fas fa-file-invoice-dollar mr-2"></i> Datos de la Compra
        </h5>
    </div>

    <div class="card-body p-4">
        {{-- GRUPO 1: PROVEEDOR Y FECHAS --}}
        <div class="group-box">
            <div class="row">
                {{-- CAMPO PROVEEDOR --}}
                <div class="col-md-6 mb-3">
                    <label for="proveedor_id" class="form-label-icon">
                        <i class="fas fa-truck-moving"></i> Proveedor
                    </label>
                    <div class="input-group">
                        <select name="proveedor_id" id="proveedor_id" class="form-control select2-proveedor">
                            <option value="">-- Buscar por RUC o Razón Social --</option>
                            @foreach ($proveedores ?? [] as $prov)
                            <option value="{{ $prov->id }}"
                                data-ruc="{{ $prov->ruc }}"
                                data-telefono="{{ $prov->telefono ?? 'No registrado' }}"
                                data-direccion="{{ $prov->direccion ?? 'No registrada' }}"
                                data-email="{{ $prov->email ?? 'No registrado' }}"
                                {{ old('proveedor_id') == $prov->id ? 'selected' : '' }}>
                                {{ $prov->ruc }} - {{ $prov->razon_social }}
                            </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary btn-addon-icon" id="btn-accion-proveedor"
                            data-bs-toggle="tooltip" title="Agregar Nuevo Proveedor">
                            <i class="fas fa-plus" id="icon-accion-proveedor"></i>
                        </button>
                    </div>
                </div>

                {{-- FECHA RECEPCIÓN --}}
                <div class="col-md-3 mb-3">
                    <label for="fecha_recepcion" class="form-label-icon">
                        <i class="far fa-calendar-alt"></i> Fecha Recepción
                    </label>
                    <input type="date" name="fecha_recepcion" id="fecha_recepcion"
                        class="form-control input-enhanced"
                        value="{{ old('fecha_recepcion', date('Y-m-d')) }}">
                </div>

                {{-- TIPO COMPROBANTE --}}
                <div class="col-md-3 mb-3">
                    <label for="tipo_comprobante" class="form-label-icon">
                        <i class="fas fa-receipt"></i> Tipo Doc.
                    </label>
                    <select name="tipo_comprobante" id="tipo_comprobante" class="form-control input-enhanced">
                        <option value="">-- Seleccione --</option>
                        <option value="FACTURA" {{ old('tipo_comprobante') == 'FACTURA' ? 'selected' : '' }}>Factura</option>
                        <option value="BOLETA" {{ old('tipo_comprobante') == 'BOLETA' ? 'selected' : '' }}>Boleta</option>
                        <option value="GUIA REMISION" {{ old('tipo_comprobante') == 'GUIA REMISION' ? 'selected' : '' }}>Guía de Remisión</option>
                        <option value="NOTA CREDITO" {{ old('tipo_comprobante') == 'NOTA CREDITO' ? 'selected' : '' }}>Nota Crédito</option>

                    </select>
                </div>
            </div>
        </div>

        {{-- GRUPO 2 --}}
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="numero_factura_proveedor" class="form-label-icon"><i class="fas fa-barcode"></i> N° Comprobante</label>
                <input type="text" name="numero_factura_proveedor" class="form-control input-enhanced" placeholder="Ej: F001-000123">
            </div>
            <div class="col-md-4 mb-3">
                <label for="archivo_comprobante" class="form-label-icon"><i class="fas fa-paperclip"></i> Archivo Adjunto</label>
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" name="archivo_comprobante" class="custom-file-input" id="archivo_comprobante" lang="es">
                        <label class="custom-file-label input-enhanced" for="archivo_comprobante">Examinar...</label>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="observaciones" class="form-label-icon"><i class="fas fa-sticky-note"></i> Observaciones</label>
                <textarea name="observaciones" rows="1" class="form-control input-enhanced" placeholder="Notas..."></textarea>
            </div>
        </div>
    </div>
</div>