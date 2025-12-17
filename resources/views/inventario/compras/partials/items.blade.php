{{-- ESTILOS EXCLUSIVOS PARA EL BUSCADOR DE MEDICAMENTOS --}}
<style>
    .search-container {
        position: relative;
    }

    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        max-height: 250px;
        overflow-y: auto;
        display: none;
    }

    .search-results.active {
        display: block;
    }

    .search-item {
        padding: 10px 15px;
        border-bottom: 1px solid #f1f1f1;
        cursor: pointer;
        transition: background 0.1s;
    }

    .search-item:hover {
        background-color: #f8f9fa;
    }

    .search-item:last-child {
        border-bottom: none;
    }

    .search-item-price {
        float: right;
        font-weight: bold;
        color: #28a745;
    }

    /* MODO OSCURO (Aseguramos que los estilos Dark Mode del archivo principal se apliquen) */
    body.dark-mode .search-results {
        background-color: #3f474e;
        border-color: #6c757d;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
    }

    body.dark-mode .search-item {
        color: #fff;
        border-bottom: 1px solid #4b545c;
    }

    body.dark-mode .search-item:hover {
        background-color: #454d55;
    }
</style>

<div class="card border-0 shadow-none" style="background: transparent;">
    <div class="d-flex justify-content-between align-items-center mb-3 px-2">
        <h5 class="mb-0 text-dark font-weight-bold">
            <i class="fas fa-boxes mr-2 text-primary"></i> Detalle de Items
        </h5>
        <button type="button" class="btn btn-primary shadow-sm px-4" onclick="agregarFilaItem()" style="border-radius: 20px;">
            <i class="fas fa-plus mr-1"></i> Agregar <span class="badge bg-light text-dark ml-2" style="font-size: 0.7em;">F2</span>
        </button>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive" style="overflow: visible;">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th style="width: 5%" class="text-center">#</th>
                        <th style="width: 35%">Medicamento & Lote</th>
                        <th style="width: 20%">Logística</th>
                        <th style="width: 25%">Precios</th>
                        <th style="width: 15%" class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody id="items-table-body">
                    {{-- FILA INICIAL (Index 0) --}}
                    <tr data-index="0" class="fila-item">
                        <td class="align-middle text-center">
                            <span class="badge bg-light text-dark border indice-fila">1</span>
                        </td>
                        <td>
                            <div class="mb-2 search-container">
                                <span class="label-mini">MEDICAMENTO</span>
                                <div class="input-group">
                                    <input type="text" class="form-control input-medicamento-search" placeholder="Escriba para buscar..." autocomplete="off" onkeyup="buscarMedicamentos(this)">
                                    <input type="hidden" name="items[0][medicamento_id]" class="input-medicamento-id">
                                    <button type="button" class="btn btn-outline-primary btn-addon-right btn-accion-med" onclick="clickAccionMedicamento(this)" title="Nuevo Medicamento">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div class="search-results"></div>
                            </div>
                            <div class="row g-2">
                                <div class="col-6"><span class="label-mini">LOTE</span><input type="text" name="items[0][codigo_lote]" class="input-modern text-uppercase input-lote" placeholder="Ej: B-450"></div>
                                <div class="col-6"><span class="label-mini">VENCIMIENTO</span><input type="date" name="items[0][fecha_vencimiento]" class="input-modern input-fechaVenci"></div>
                            </div>
                        </td>
                        <td>
                            <div class="mb-2"><span class="label-mini">CANTIDAD</span><input type="number" name="items[0][cantidad_recibida]" class="input-modern text-center font-weight-bold input-cantidad" min="1" value="1" oninput="recalcularSubtotal(this)"></div>
                            <div><span class="label-mini">UBICACIÓN</span><input type="text" name="items[0][ubicacion]" class="input-modern input-ubicacion" placeholder="Estante A1"></div>
                        </td>
                        <td>
                            <div class="row g-2 mb-2">
                                <div class="col-6"><span class="label-mini text-primary">P. COMPRA</span><input type="number" step="0.0001" name="items[0][precio_compra_unitario]" class="input-modern text-end input-precio" min="0" value="0" oninput="recalcularSubtotal(this)"></div>
                                <div class="col-6"><span class="label-mini text-success">P. VENTA</span><input type="number" step="0.01" name="items[0][precio_venta]" class="input-modern text-end input-precio-venta" min="0" value="0"></div>
                            </div>
                            <div><span class="label-mini text-muted">P. OFERTA</span><input type="number" step="0.01" name="items[0][precio_oferta]" class="input-modern text-end text-muted input-oferta" placeholder="0.00"></div>
                        </td>
                        <td class="text-end">
                            <div class="mb-3"><span class="label-mini">SUBTOTAL</span>
                                <div class="subtotal-fila subtotal-text">S/ 0.00</div>
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-sm border-0" onclick="eliminarFilaItem(this)"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>