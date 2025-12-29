<div class="modal fade" id="modalLotes" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success py-2">
                <h5 class="modal-title text-white" style="font-size: 1rem;">Seleccionar Lote</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-2">
                <div class="alert alert-secondary py-1 px-2 mb-2">
                    <strong id="modal-medicamento-nombre" class="text-dark"></strong>
                    <span class="float-right text-muted" style="font-size: 0.85rem">
                        Pres: <span id="modal-medicamento-presentacion"></span>
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0" style="font-size: 0.9rem;">
                        <thead class="thead-light">
                            <tr>
                                <th>Lote</th>
                                <th>Vence</th>
                                <th class="text-center">Stock (Unid)</th>

                                {{-- NUEVA COLUMNA --}}
                                <th style="width: 140px;">Presentación</th>

                                <th style="width: 80px;">Cant.</th>
                                <th class="text-right">Precio</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="modal-lotes-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>