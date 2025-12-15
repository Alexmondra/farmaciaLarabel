<div class="glass-panel p-3 h-100">
    <div class="section-title"><i class="fas fa-cog"></i> CONFIGURACIÓN</div>

    <div class="row">
        <div class="col-md-6">
            <label class="label-futuristic">Fecha Traslado</label>
            <input type="date" name="fecha_traslado" class="form-control form-control-futuristic"
                value="{{ old('fecha_traslado', date('Y-m-d')) }}">
        </div>
        <div class="col-md-6">
            <label class="label-futuristic">Motivo</label>
            <select name="motivo_traslado" id="selectMotivo" class="form-control form-control-futuristic">
                <option value="01" {{ old('motivo_traslado') == '01' ? 'selected' : '' }}>VENTA (Salida Cliente)</option>
                <option value="04" {{ old('motivo_traslado') == '04' ? 'selected' : '' }}>TRASLADO ENTRE SUCURSALES</option>
                <option value="13" {{ old('motivo_traslado') == '13' ? 'selected' : '' }}>OTROS</option>
            </select>
        </div>

        <div class="col-md-12 mt-2 d-none" id="divDescripcionMotivo">
            <label class="label-futuristic text-warning">Especifique el Motivo</label>
            <input type="text" name="descripcion_motivo" id="inputDescripcionMotivo"
                class="form-control form-control-futuristic"
                placeholder="Ej: Devolución, Muestras médicas, Regalo..."
                value="{{ old('descripcion_motivo') }}"> {{-- ¡Añadir old()! --}}
        </div>
    </div>

    {{-- Importador de Venta --}}
    <div id="panel-importar-venta" class="mt-3 p-3 rounded" style="background: rgba(32, 201, 151, 0.05); border: 1px dashed #20c997;">
        <label class="label-futuristic text-teal">Importar desde Venta</label>
        <div class="input-group">
            <input type="text" id="txtBuscarVenta" class="form-control form-control-futuristic bg-white border-right-0" placeholder="Serie-Nro (Ej: B001-45)">
            <div class="input-group-append">
                <button class="btn btn-teal text-white" type="button" id="btnBuscarVenta" style="border-radius: 0 8px 8px 0;">
                    <i class="fas fa-download"></i>
                </button>
            </div>
        </div>
        <input type="hidden" name="venta_id" id="inputVentaId">
    </div>

    {{-- Selector Sucursal Destino (Oculto default) --}}
    <div id="panel-sucursal-destino" class="mt-3 d-none">
        <label class="label-futuristic text-primary">Sucursal Destino</label>
        {{-- En resources/views/guias/partials/configuracion.blade.php --}}
        <select id="selectSucursalDestino" class="form-control form-control-futuristic">
            <option value="">-- Seleccionar Sucursal --</option>
            @foreach($sucursalesDestino as $suc)
            <option value="{{ $suc->id }}"
                data-dir="{{ $suc->direccion }}"
                data-ubi="{{ $suc->ubigeo }}"
                data-ruc="{{ $configuracion->empresa_ruc ?? '30000000001' }}"
                data-razon="{{ $configuracion->empresa_razon_social ?? config('app.name') }}"
                data-nombre="{{ $suc->nombre }}"
                data-codigo="{{ $suc->codigo ?? '9999' }}"> {{ $suc->nombre }}
            </option>
            @endforeach
        </select>
    </div>
</div>