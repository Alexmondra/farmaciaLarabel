{{-- resources/views/guias/partials/partida.blade.php --}}
<div class="glass-panel p-3 mt-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="section-title mb-0"><i class="fas fa-map-pin"></i> PUNTO DE PARTIDA</div>

        {{-- Switch para activar edición --}}
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="checkPartidaDistinta">
            <label class="custom-control-label small text-muted" for="checkPartidaDistinta" style="font-size: 0.75rem; padding-top: 2px;">
                ¿Otro origen?
            </label>
        </div>
    </div>

    {{-- Dirección (Bloqueada por defecto con datos de tu sucursal) --}}
    <div class="form-group mb-2">
        <label class="label-futuristic">Dirección de Salida</label>
        <textarea name="direccion_partida" id="inputDirPartida" rows="2"
            class="form-control form-control-futuristic bg-light"
            readonly>{{ $sucursalOrigen->direccion }}</textarea>
    </div>

    <div class="row">
        <div class="col-7">
            <label class="label-futuristic">Ubigeo</label>
            <input type="text" name="ubigeo_partida" id="inputUbiPartida"
                class="form-control form-control-futuristic bg-light"
                value="{{ $sucursalOrigen->ubigeo }}" readonly maxlength="6">
        </div>
        <div class="col-5">
            <label class="label-futuristic">Cód. Sunat</label>
            {{-- Si es propio usa el codigo, si es externo (switch) se pone 0000 --}}
            <input type="text" name="codigo_establecimiento_partida" id="inputCodPartida"
                class="form-control form-control-futuristic bg-light"
                value="{{ $sucursalOrigen->codigo ?? '0000' }}" readonly>
        </div>
    </div>
</div>