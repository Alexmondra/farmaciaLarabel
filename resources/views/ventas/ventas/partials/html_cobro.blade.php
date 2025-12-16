<div class="card card-success shadow-sm h-100">
    <div class="card-header text-center py-3">
        <h3 class="card-title float-none font-weight-bold text-dark-mode-light"><i class="fas fa-wallet mr-1"></i> COBRO</h3>
    </div>
    <div class="card-body text-center d-flex flex-column justify-content-between p-4">
        <div>
            <h6 class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 0.8rem;">Total a Pagar</h6>
            <div class="total-display mb-3">S/ <span id="total-venta">0.00</span></div>

            {{-- SELECCIÓN DE MEDIO DE PAGO --}}
            <div class="form-group text-left mb-3">
                <label class="small font-weight-bold text-uppercase text-muted-mode">Medio de Pago</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text bg-white"><i class="fas fa-money-bill-wave text-success"></i></span></div>
                    <select name="medio_pago" id="medio_pago" class="form-control form-control-lg font-weight-bold">
                        <option value="EFECTIVO">EFECTIVO</option>
                        <option value="TARJETA">TARJETA (POS)</option>
                        <option value="YAPE">YAPE</option>
                        <option value="PLIN">PLIN</option>
                    </select>
                </div>
            </div>

            {{-- OPCIÓN 1: PAGO EN EFECTIVO (Visible por defecto) --}}
            <div id="bloque-calculadora">
                <div class="form-group text-left mb-2">
                    <label class="small font-weight-bold text-uppercase text-muted-mode">Paga con (S/)</label>
                    <input type="number"
                        name="paga_con"
                        id="input-paga-con"
                        class="form-control"
                        step="0.01"
                        placeholder="0.00">
                </div>
                {{-- Usamos bg-white-mode --}}
                <div class="mt-2 p-2 bg-white-mode rounded border border-light">
                    <small class="text-muted-mode text-uppercase font-weight-bold">Vuelto</small><br>
                    <span class="vuelto-display">S/ <span id="txt-vuelto">0.00</span></span>
                </div>
            </div>

            {{-- OPCIÓN 2: PAGO DIGITAL/TARJETA (Oculto por defecto) --}}
            <div id="bloque-referencia" style="display: none;">
                <div class="form-group text-left mb-2">
                    <label class="small font-weight-bold text-uppercase text-muted-mode">Nro. Operación / Ref.</label>
                    <input type="text" name="referencia_pago" id="referencia_pago" class="form-control font-weight-bold text-center"
                        placeholder="Ej: 123456" autocomplete="off" style="font-size: 1.2rem;">
                    <small class="text-info"><i class="fas fa-info-circle"></i> Ingrese los últimos dígitos del voucher</small>
                </div>
            </div>

        </div>
        <div class="mt-3">
            {{-- Botón con ID y Disabled por defecto --}}
            <button type="submit" id="btn-confirmar-venta" class="btn btn-light btn-block btn-lg text-success font-weight-bold shadow-sm py-3 mb-3" disabled>
                <i class="fas fa-check-circle mr-2"></i> CONFIRMAR
            </button>
            <a href="{{ route('ventas.index') }}" class="btn btn-outline-light btn-block text-white border-white btn-sm" style="opacity: 0.8;">Cancelar</a>
        </div>
    </div>
</div>