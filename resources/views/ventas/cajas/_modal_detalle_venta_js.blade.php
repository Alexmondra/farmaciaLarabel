<div class="modal fade" id="modalDetalleVenta" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">

            {{-- HEADER DEL MODAL --}}
            <div class="modal-header bg-light py-2 border-bottom">
                <h6 class="modal-title font-weight-bold text-dark" id="modalLabel">
                    <i class="fas fa-shopping-bag mr-2 text-muted"></i> Detalle de Venta
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- BODY (TABLA) --}}
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="bg-white">
                            <tr>
                                <th class="pl-3 border-0 text-muted text-uppercase small font-weight-bold">Producto</th>
                                <th class="text-center border-0 text-muted text-uppercase small font-weight-bold" style="width: 60px;">Cant.</th>
                                <th class="text-right border-0 text-muted text-uppercase small font-weight-bold">P. Unit</th>
                                <th class="text-right pr-3 border-0 text-muted text-uppercase small font-weight-bold">Total</th>
                            </tr>
                        </thead>
                        {{--
                           El tbody tiene el ID 'modalDetalleBody'.
                           El JavaScript en show.blade.php busca este ID para inyectar las filas.
                        --}}
                        <tbody id="modalDetalleBody">
                            {{-- Se llena vía JS --}}
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer py-1 bg-light border-top">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>