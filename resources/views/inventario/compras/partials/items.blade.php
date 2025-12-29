<div class="card border-0 shadow-none" style="background: transparent;">
    <div class="d-flex justify-content-between align-items-center mb-3 px-2">
        <h5 class="mb-0 text-dark font-weight-bold">
            <i class="fas fa-boxes mr-2 text-primary"></i> Detalle de Items
        </h5>
        <button type="button" class="btn btn-primary shadow-sm px-4" onclick="agregarFilaItem()" style="border-radius: 20px;">
            <i class="fas fa-plus mr-1"></i> Agregar (F2)
        </button>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive" style="overflow: visible;">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th style="width: 5%" class="text-center">#</th>
                        <th style="width: 30%">Medicamento & Lote</th>
                        <th style="width: 20%">Logística (Compra)</th>
                        <th style="width: 35%">Configuración de Precios (Venta)</th> {{-- COLUMNA MÁS ANCHA --}}
                        <th style="width: 10%" class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody id="items-table-body">
                    {{-- LAS FILAS SE GENERAN CON JS --}}
                </tbody>
            </table>
        </div>
    </div>
</div>