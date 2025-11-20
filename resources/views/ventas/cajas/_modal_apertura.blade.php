{{--
  Este modal espera una variable $sucursalesParaApertura
  que se pasa desde el controlador 'index'
--}}
<div class="modal fade" id="modalAbrirCaja" tabindex="-1" role="dialog" aria-labelledby="modalAbrirCajaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">

        <form action="{{ route('cajas.store') }}" method="POST">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAbrirCajaLabel">Abrir Nueva Sesión de Caja</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    {{-- Mostrar error general (si lo hay) --}}
                    @error('general')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    {{-- 1. Selección de Sucursal --}}
                    <div class="form-group">
                        <label for="sucursal_id">Sucursal</label>
                        <select class="form-control @error('sucursal_id') is-invalid @enderror"
                            id="sucursal_id"
                            name="sucursal_id"
                            required>
                            <option value="">-- Seleccione una sucursal --</option>

                            {{-- Iteramos sobre las sucursales permitidas --}}
                            @foreach($sucursalesParaApertura as $sucursal)
                            <option value="{{ $sucursal->id }}"
                                {{ old('sucursal_id') == $sucursal->id ? 'selected' : '' }}>
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

                    {{-- 2. Saldo Inicial --}}
                    <div class="form-group">
                        <label for="saldo_inicial">Saldo Inicial (S/)</label>
                        <input type="number"
                            step="0.01"
                            min="0"
                            class="form-control @error('saldo_inicial') is-invalid @enderror"
                            id="saldo_inicial"
                            name="saldo_inicial"
                            value="{{ old('saldo_inicial', '0.00') }}"
                            required>

                        @error('saldo_inicial')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    {{-- 3. Observaciones --}}
                    <div class="form-group">
                        <label for="observaciones">Observaciones (Opcional)</label>
                        <textarea class="form-control @error('observaciones') is-invalid @enderror"
                            id="observaciones"
                            name="observaciones"
                            rows="2">{{ old('observaciones') }}</textarea>

                        @error('observaciones')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-play"></i> Iniciar Apertura
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>