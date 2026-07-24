@extends('tienda.layout')

@section('title', 'Finalizar pedido')

@section('content')
<h1 class="h3 mb-4">Finalizar pedido</h1>

<form method="POST" action="{{ route('tienda.checkout.store') }}" class="row" id="formCheckout">
    @csrf
    <div class="col-lg-7">
        <div class="store-card bg-white p-4 mb-4">
            <h2 class="h5 mb-3">Datos del cliente</h2>
            <div class="alert alert-success">
                Comprando como <strong>{{ $cliente->nombre_completo }}</strong> ({{ $cliente->tipo_documento }} {{ $cliente->documento }})
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Telefono <span id="labelTelefonoReq" class="text-danger">*</span></label>
                    <input name="cliente_telefono" id="clienteTelefono" value="{{ old('cliente_telefono', $cliente->telefono) }}" class="form-control @error('cliente_telefono') is-invalid @enderror" placeholder="987654321">
                    @error('cliente_telefono')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small id="helpTelefono" class="text-muted">Requerido para pago online.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="cliente_email" value="{{ old('cliente_email', $cliente->email) }}" class="form-control @error('cliente_email') is-invalid @enderror" placeholder="ejemplo@correo.com">
                    @error('cliente_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="store-card bg-white p-4">
            <h2 class="h5 mb-3">Entrega y pago</h2>
            <div class="row g-3">
                <input type="hidden" name="tipo_entrega" value="RECOJO_SUCURSAL">

                @if($esMultiSucursal)
                    <div class="col-12">
                        <div class="alert alert-warning small mb-3">
                            <strong>Productos de diferentes sucursales:</strong> El tiempo de espera sera de al menos <strong>una semana</strong> mientras trasladamos todos los productos a la sucursal que elijas para el recojo.
                        </div>
                        <label class="form-label">Sucursal donde recogeras tu pedido</label>
                        <select name="sucursal_recojo_id" class="form-control @error('sucursal_recojo_id') is-invalid @enderror" required>
                            <option value="">Selecciona una sucursal</option>
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}" @selected(old('sucursal_recojo_id') == $sucursal->id)>{{ $sucursal->nombre }}</option>
                            @endforeach
                        </select>
                        @error('sucursal_recojo_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div class="col-md-6">
                    <label class="form-label">Pago</label>
                    <select name="metodo_pago" class="form-control @error('metodo_pago') is-invalid @enderror">
                        <option value="PAGO_AL_RECOGER" @selected(old('metodo_pago') === 'PAGO_AL_RECOGER')>Pagar al recoger</option>
                        @if($montoInsuficienteOnline)
                            <option value="PAGO_ONLINE" disabled>Pago online (Monto mínimo S/ 15.00)</option>
                        @elseif($limiteOnlineAlcanzado)
                            <option value="PAGO_ONLINE" disabled>Pago online (Límite: 3 pendientes alcanzado)</option>
                        @else
                            <option value="PAGO_ONLINE" @selected(old('metodo_pago') === 'PAGO_ONLINE')>Pago online</option>
                        @endif
                    </select>
                    @error('metodo_pago')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @if($montoInsuficienteOnline)
                        <small class="text-danger d-block mt-1">Pago online requiere compras de S/ 15.00 o más.</small>
                    @elseif($limiteOnlineAlcanzado)
                        <small class="text-danger d-block mt-1">Tienes 3 pedidos pendientes de pago online. Paga tus pedidos anteriores para habilitar esta opción.</small>
                    @endif
                </div>
                <div class="col-12">
                    <label class="form-label">Fecha y hora de recojo</label>
                    <input type="datetime-local" name="fecha_recojo" value="{{ old('fecha_recojo', $fechaRecojoDefault) }}" class="form-control @error('fecha_recojo') is-invalid @enderror" min="{{ $fechaRecojoMin }}">
                    @error('fecha_recojo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if($esMultiSucursal)
                        <small class="text-muted">La fecha minima de recojo es una semana desde hoy.</small>
                    @endif
                </div>
                <div class="col-12">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones') }}</textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5 mt-4 mt-lg-0">
        <div class="store-card bg-white p-4">
            <h2 class="h5 mb-3">Resumen</h2>
            @foreach ($items as $item)
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>{{ $item['cantidad'] }} x {{ $item['producto']->nombre }}</span>
                    <strong>S/ {{ number_format($item['subtotal'], 2) }}</strong>
                </div>
            @endforeach
            <div class="d-flex justify-content-between h4 mt-3">
                <span>Total</span>
                <strong>S/ {{ number_format($total, 2) }}</strong>
            </div>
            <button type="submit" class="btn btn-store btn-lg w-100 mt-3">Registrar pedido</button>
        </div>
    </div>
</form>

@if(session('confirmar_cambio_datos'))
<div class="modal fade" id="modalCambioDatos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0 pb-2">
                <h5 class="modal-title fw-bold">Cambio en datos de contacto</h5>
                <button type="button" class="btn-close" id="btnCloseCambioDatos"></button>
            </div>
            <div class="modal-body pt-0">
                <p>Estas cambiando tu <strong>{{ session('confirmar_cambio_datos') }}</strong>.</p>
                <p class="mb-0 text-muted small">Estos datos se actualizaran en tu cuenta. Deseas continuar?</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" id="btnCancelarCambioDatos">Cancelar</button>
                <button type="button" class="btn btn-store" id="btnConfirmarDatos">Actualizar y continuar</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@if(session('confirmar_cambio_datos'))
@push('scripts')
<script>
    (function () {
        var modalEl = document.getElementById('modalCambioDatos');
        if (!modalEl) return;

        function showModal() {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            modalEl.setAttribute('aria-hidden', 'false');
            modalEl.setAttribute('aria-modal', 'true');
            document.body.classList.add('modal-open');
            if (!document.querySelector('.modal-backdrop')) {
                var backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
            }
        }

        function hideModal() {
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            modalEl.setAttribute('aria-hidden', 'true');
            modalEl.removeAttribute('aria-modal');
            document.body.classList.remove('modal-open');
            document.querySelectorAll('.modal-backdrop').forEach(function (el) { el.remove(); });
        }

        showModal();

        document.getElementById('btnConfirmarDatos').addEventListener('click', function () {
            var form = document.getElementById('formCheckout');
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'confirmar_datos';
            input.value = '1';
            form.appendChild(input);
            form.submit();
        });

        document.getElementById('btnCancelarCambioDatos').addEventListener('click', hideModal);
        document.getElementById('btnCloseCambioDatos').addEventListener('click', hideModal);
    })();
</script>
@endpush
@endif

@push('scripts')
<script>
(function () {
    var selectMetodo = document.querySelector('[name="metodo_pago"]');
    var telefonoInput = document.getElementById('clienteTelefono');
    var labelReq = document.getElementById('labelTelefonoReq');
    var helpTelefono = document.getElementById('helpTelefono');

    function actualizarTelefono() {
        var esOnline = selectMetodo && selectMetodo.value === 'PAGO_ONLINE';
        if (telefonoInput) {
            telefonoInput.required = esOnline;
        }
        if (labelReq) {
            labelReq.style.display = esOnline ? 'inline' : 'none';
        }
        if (helpTelefono) {
            helpTelefono.style.display = esOnline ? 'block' : 'none';
        }
    }

    if (selectMetodo) {
        selectMetodo.addEventListener('change', actualizarTelefono);
        actualizarTelefono();
    }
})();
</script>
@endpush
