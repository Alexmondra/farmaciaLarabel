<div class="modal fade" id="modalConfigPuntos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h6 class="modal-title font-weight-bold text-dark">
                    <i class="fas fa-cog text-primary mr-1"></i> Configurar Sistema
                </h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <form id="formConfig">
                @csrf
                <div class="modal-body">

                    <div class="form-group mb-4">
                        <label class="d-block small text-muted font-weight-bold mb-2">Regla de Acumulación</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text border-0 bg-light">S/ 1.00 Venta =</span>
                            </div>
                            {{-- Nota: Usamos null coalesce (??) para evitar error si $config no existe --}}
                            <input type="number"
                                step="1"
                                min="0"
                                name="puntos_por_moneda"
                                class="form-control font-weight-bold text-center border-0 bg-light h-auto py-2"
                                value="{{ $config->puntos_por_moneda ?? 1 }}"
                                oninput="if(this.value < 0) this.value = 0;">
                            <div class="input-group-append">
                                <span class="input-group-text border-0 bg-light">Puntos</span>
                            </div>
                        </div>
                        <small class="text-info mt-1 d-block">
                            <i class="fas fa-plus-circle"></i> Puntos ganados por cada moneda.
                        </small>
                    </div>

                    <hr>

                    <div class="form-group mb-4">
                        <label class="d-block small text-muted font-weight-bold mb-2">Valor del Canje (Descuento)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text border-0 bg-light">1 Punto =</span>
                            </div>
                            <input type="number"
                                step="0.0001"
                                min="0"
                                name="valor_punto_canje"
                                id="conf_valor"
                                class="form-control font-weight-bold text-center border-0 bg-light h-auto py-2"
                                value="{{ $config->valor_punto_canje ?? 0.02 }}"
                                oninput="if(this.value < 0) this.value = 0;">
                            <div class="input-group-append">
                                <span class="input-group-text border-0 bg-light">Soles</span>
                            </div>
                        </div>
                        <small class="text-info mt-1 d-block">
                            <i class="fas fa-money-bill-wave"></i> Dinero que vale cada punto.
                        </small>
                    </div>

                </div>
                <div class="modal-footer p-2 bg-light justify-content-center">
                    <button type="submit" class="btn btn-primary btn-block rounded-pill font-weight-bold">
                        Actualizar Reglas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>