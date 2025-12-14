<div class="glass-card p-3 bg-gradient-navy text-white d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
        <div class="rounded-circle bg-white text-navy d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 45px; height: 45px;">
            <i class="fas fa-warehouse"></i>
        </div>
        <div>
            <h6 class="m-0 font-weight-bold">EMISIÓN DE GUÍA</h6>
            <small class="opacity-75">Sucursal: <strong>{{ $sucursalOrigen->nombre }}</strong></small>
        </div>
    </div>
    <div class="text-right">
        <span class="badge badge-teal px-3 py-2 text-md shadow-sm" style="background-color: #20c997; color: white;">
            {{ $serie }} - {{ str_pad($numero, 6, '0', STR_PAD_LEFT) }}
        </span>
        <input type="hidden" name="serie" value="{{ $serie }}">
        <input type="hidden" name="numero" value="{{ $numero }}">
    </div>
</div>