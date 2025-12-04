<div class="modal fade" id="modalCerrarCaja" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="" method="POST" id="formCerrarCaja">
            @csrf
            @method('PATCH')

            <div class="modal-content border-warning">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark"><i class="fas fa-cash-register mr-1"></i> Cerrar Sesión de Caja</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body bg-light">
                    @error('general_cierre')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    {{-- INFORMACIÓN (Solo lectura) --}}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small text-muted mb-0">Saldo Inicial</label>
                            <input type="text" class="form-control form-control-sm bg-white" id="displaySaldoInicial" readonly style="border:none; font-weight:bold;">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small text-muted mb-0">Ventas (+)</label>
                            <input type="text" class="form-control form-control-sm bg-white text-success" id="displayVentasTotal" readonly style="border:none; font-weight:bold;">
                        </div>
                    </div>

                    <hr class="mt-0">

                    {{-- Saldo Esperado --}}
                    <div class="form-group text-center">
                        <label class="h6 text-info">Saldo Esperado</label>
                        <input type="text" id="displaySaldoEstimado" class="form-control form-control-lg text-center bg-info text-white border-0" readonly>
                    </div>

                    <hr>

                    {{-- INPUT: SALDO REAL --}}
                    <div class="form-group">
                        <label for="saldo_real" class="font-weight-bold">Dinero Físico (Real)</label>
                        <div class="input-group input-group-lg">
                            <div class="input-group-prepend">
                                <span class="input-group-text">S/</span>
                            </div>
                            <input type="number" step="0.01" min="0" class="form-control font-weight-bold" id="saldo_real" name="saldo_real" required>
                        </div>
                    </div>

                    {{-- NUEVO CAMPO: OBSERVACIONES DE CIERRE --}}
                    <div class="form-group mt-3">
                        <label for="observaciones_cierre" class="font-weight-bold"><i class="far fa-comment-dots mr-1"></i> Observaciones de Cierre</label>
                        <textarea class="form-control"
                            id="observaciones_cierre"
                            name="observaciones"
                            rows="2"
                            placeholder="Ej: Faltan 0.10 céntimos por redondeo..."></textarea>
                        <small class="text-muted">Explica aquí cualquier diferencia de dinero.</small>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark shadow">
                        <i class="fas fa-lock mr-1"></i> Confirmar Cierre
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>