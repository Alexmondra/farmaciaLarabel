<div class="modal fade" id="modalAbrirCaja" tabindex="-1" role="dialog" aria-labelledby="modalAbrirCajaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">

        <form action="{{ route('cajas.store') }}" method="POST">
            @csrf

            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="modalAbrirCajaLabel">
                        <i class="fas fa-cash-register mr-2"></i>Abrir Nueva Sesión
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    {{-- Mostrar error general (si lo hay) --}}
                    @error('general')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    {{-- 1. Selección de Sucursal (AUTOMÁTICA) --}}
                    <div class="form-group">
                        <label for="sucursal_id" class="font-weight-bold">Sucursal</label>
                        <select class="form-control @error('sucursal_id') is-invalid @enderror"
                            id="sucursal_id"
                            name="sucursal_id"
                            required>
                            <option value="">-- Seleccione una sucursal --</option>

                            @foreach($sucursalesParaApertura as $sucursal)
                            {{-- LÓGICA: Si hay un 'old' (error previo) úsalo, sino usa la de la sesión actual --}}
                            <option value="{{ $sucursal->id }}"
                                {{ old('sucursal_id', session('sucursal_id')) == $sucursal->id ? 'selected' : '' }}>
                                {{ $sucursal->nombre }}
                            </option>
                            @endforeach
                        </select>

                        @error('sucursal_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    {{-- 2. Saldo Inicial (BLOQUEADO LETRAS) --}}
                    <div class="form-group">
                        <label for="saldo_inicial" class="font-weight-bold">Saldo Inicial</label>

                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text font-weight-bold">S/</span>
                            </div>

                            <input type="number"
                                step="0.01"
                                min="0"
                                class="form-control @error('saldo_inicial') is-invalid @enderror"
                                id="saldo_inicial"
                                name="saldo_inicial"
                                value="{{ old('saldo_inicial') }}"
                                placeholder="0.00"
                                required
                                {{-- TRUCO: Bloquea 'e', '+', '-' al presionar tecla --}}
                                onkeydown="return ['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes(event.code) || (event.key === '.' && !this.value.includes('.')) || !isNaN(Number(event.key))"
                                {{-- TRUCO 2: Elimina cualquier cosa pegada que no sea numero --}}
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');">
                        </div>
                        <small class="text-muted">Ingresa el dinero en efectivo con el que inicias.</small>

                        @error('saldo_inicial')
                        <span class="text-danger small mt-1 d-block">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    {{-- 3. Observaciones --}}
                    <div class="form-group">
                        <label for="observaciones" class="font-weight-bold">Observaciones (Opcional)</label>
                        <textarea class="form-control @error('observaciones') is-invalid @enderror"
                            id="observaciones"
                            name="observaciones"
                            rows="2"
                            placeholder="Ej: Turno mañana...">{{ old('observaciones') }}</textarea>

                        @error('observaciones')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
                        <i class="fas fa-play mr-1"></i> Iniciar Apertura
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>