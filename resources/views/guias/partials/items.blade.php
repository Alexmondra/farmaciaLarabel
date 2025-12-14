<div class="glass-card p-4">
    <div class="d-flex justify-content-between mb-3">
        <span class="section-header mb-0"><i class="fas fa-boxes mr-1"></i> Detalle</span>
        <span class="badge badge-light border" id="lbl-conteo">0 Items</span>
    </div>

    {{-- Buscador Manual --}}
    <div id="box-buscador-manual" class="mb-3 position-relative">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
            </div>
            <input type="text" id="busqueda_medicamento" class="form-control form-control-modern border-left-0" placeholder="Buscar medicamento..." autocomplete="off">
        </div>
        {{-- Lista Resultados --}}
        <div id="res-busqueda" class="list-group shadow position-absolute w-100" style="top:100%; z-index:1000; display:none; max-height:200px; overflow:auto;"></div>
    </div>

    {{-- Tabla --}}
    <div class="table-responsive rounded border" style="max-height: 250px;">
        <table class="table table-hover mb-0" id="tablaItems">
            <thead class="bg-light">
                <tr>
                    <th class="pl-3 py-2">Producto</th>
                    <th class="text-center py-2" width="15%">Cant.</th>
                    <th width="5%"></th>
                </tr>
            </thead>
            <tbody class="bg-white"></tbody>
        </table>
        <div id="msg-vacio" class="text-center py-4 text-muted small">
            Lista vacía
        </div>
    </div>
    <input type="hidden" name="items" id="inputItemsJson">
</div>