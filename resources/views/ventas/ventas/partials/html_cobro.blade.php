<div class="card card-success shadow-sm h-100">
    <div class="card-header text-center py-3">
        <h3 class="card-title float-none font-weight-bold"><i class="fas fa-wallet mr-1"></i> COBRO</h3>
    </div>
    <div class="card-body text-center d-flex flex-column justify-content-between p-4">
        <div>
            <h6 class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 0.8rem;">Total a Pagar</h6>
            <div class="total-display mb-3">S/ <span id="total-venta">0.00</span></div>

            <div class="form-group text-left mb-3">
                <label class="small font-weight-bold text-uppercase text-muted">Medio de Pago</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text bg-white"><i class="fas fa-money-bill-wave text-success"></i></span></div>
                    <select name="medio_pago" id="medio_pago" class="form-control form-control-lg font-weight-bold">
                        <option value="EFECTIVO">EFECTIVO</option>
                        <option value="TARJETA">TARJETA</option>
                        <option value="YAPE">YAPE</option>
                        <option value="PLIN">PLIN</option>
                    </select>
                </div>
            </div>

            <div id="bloque-calculadora">
                <div class="form-group text-left mb-2">
                    <label class="small font-weight-bold text-uppercase text-muted">Paga con (S/)</label>
                    <input type="number" id="input-paga-con" class="form-control input-pago" placeholder="0.00" step="0.10" min="0">
                </div>
                <div class="mt-2 p-2 bg-light rounded border border-light">
                    <small class="text-muted text-uppercase font-weight-bold">Vuelto</small><br>
                    <span class="vuelto-display">S/ <span id="txt-vuelto">0.00</span></span>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-light btn-block btn-lg text-success font-weight-bold shadow-sm py-3 mb-3">
                <i class="fas fa-check-circle mr-2"></i> CONFIRMAR
            </button>
            <a href="{{ route('ventas.index') }}" class="btn btn-outline-light btn-block text-white border-white btn-sm" style="opacity: 0.8;">Cancelar</a>
        </div>
    </div>
</div>