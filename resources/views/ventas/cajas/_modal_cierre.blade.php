<div class="modal fade" id="modalCerrarCaja" tabindex="-1" role="dialog" aria-labelledby="modalCerrarCajaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">

        {{-- El 'action' de este form se pondrá con JavaScript --}}
        <form action="" method="POST" id="formCerrarCaja">
            @csrf
            @method('PATCH')

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCerrarCajaLabel">Cerrar Sesión de Caja</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    {{-- Mostrar error general (si lo hay) --}}
                    @error('general_cierre')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    {{-- 1. Saldo Inicial (Solo mostrar) --}}
                    <div class="form-group">
                        <label>Saldo Inicial (Apertura)</label>
                        <input type="text" class="form-control" id="displaySaldoInicial" readonly>
                    </div>

                    {{-- 2. Saldo Real (Conteo de dinero) --}}
                    <div class="form-group">
                        <label for="saldo_real">Saldo Real (S/) (Dinero contado)</label>
                        <input type="number"
                            step="0.01"
                            min="0"
                            class="form-control @error('saldo_real') is-invalid @enderror"
                            id="saldo_real"
                            name="saldo_real"
                            value="{{ old('saldo_real') }}"
                            required>

                        @error('saldo_real')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <p class="text-muted">
                        Al cerrar, el sistema calculará el "Saldo Teórico" (Inicial + Ventas) y lo comparará con tu "Saldo Real" para hallar la diferencia.
                    </p>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-lock"></i> Confirmar Cierre
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>