{{-- Mensajes de error del servidor --}}
@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle mr-2 text-lg"></i>
        <div>
            <strong>¡Atención!</strong> Revisa los errores detectados.
        </div>
    </div>
    <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<form action="{{ $route }}" method="POST" autocomplete="off" id="formProveedor" novalidate>
    @csrf
    @if ($method !== 'POST')
    @method($method)
    @endif

    <div class="card card-outline card-primary shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-4">

            {{-- SECCIÓN 1: DATOS FISCALES --}}
            <h6 class="text-primary font-weight-bold text-uppercase mb-3" style="font-size: 0.8rem; letter-spacing: 1px;">
                <i class="fas fa-building mr-1"></i> Datos Fiscales
            </h6>
            <div class="form-row">
                {{-- RUC --}}
                <div class="col-md-4 mb-3">
                    <label for="ruc" class="font-weight-bold text-muted small">RUC <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fas fa-id-card text-primary"></i></span>
                        </div>
                        <input
                            type="text"
                            name="ruc"
                            id="ruc"
                            class="form-control border-left-0 @error('ruc') is-invalid @enderror"
                            placeholder="Ej. 20..."
                            value="{{ old('ruc', $proveedor->ruc ?? '') }}"
                            maxlength="11"
                            inputmode="numeric"
                            required
                            style="font-size: 1.1em; letter-spacing: 0.5px;">

                        {{-- MENSAJE DEBAJO DEL CAMPO --}}
                        <div class="invalid-feedback font-weight-bold" id="error-ruc-msg">
                            El RUC debe tener 11 dígitos.
                        </div>
                    </div>
                    @error('ruc')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Razón Social --}}
                <div class="col-md-8 mb-3">
                    <label for="razon_social" class="font-weight-bold text-muted small">Razón Social <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fas fa-signature text-muted"></i></span>
                        </div>
                        <input
                            type="text"
                            name="razon_social"
                            id="razon_social"
                            class="form-control border-left-0 @error('razon_social') is-invalid @enderror"
                            placeholder="Nombre de la empresa"
                            value="{{ old('razon_social', $proveedor->razon_social ?? '') }}"
                            maxlength="180"
                            required>
                        <div class="invalid-feedback">
                            Escribe la Razón Social.
                        </div>
                    </div>
                    @error('razon_social')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <hr class="my-4 border-light">

            {{-- SECCIÓN 2: UBICACIÓN Y CONTACTO --}}
            <h6 class="text-info font-weight-bold text-uppercase mb-3" style="font-size: 0.8rem; letter-spacing: 1px;">
                <i class="fas fa-map-marked-alt mr-1"></i> Ubicación y Contacto
            </h6>

            {{-- Dirección --}}
            <div class="form-group mb-3">
                <label for="direccion" class="font-weight-bold text-muted small">Dirección Fiscal</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-map-marker-alt text-danger"></i></span>
                    </div>
                    <input
                        type="text"
                        name="direccion"
                        id="direccion"
                        class="form-control border-left-0 @error('direccion') is-invalid @enderror"
                        placeholder="Dirección completa"
                        value="{{ old('direccion', $proveedor->direccion ?? '') }}">
                </div>
            </div>

            <div class="form-row">
                {{-- Teléfono --}}
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="font-weight-bold text-muted small">Celular / Teléfono</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fas fa-phone text-success"></i></span>
                        </div>
                        <input
                            type="text"
                            name="telefono"
                            id="telefono"
                            class="form-control border-left-0 @error('telefono') is-invalid @enderror"
                            placeholder="999888777"
                            value="{{ old('telefono', $proveedor->telefono ?? '') }}"
                            maxlength="9"
                            inputmode="numeric">

                        {{-- MENSAJE DEBAJO DEL CAMPO --}}
                        <div class="invalid-feedback font-weight-bold">
                            El celular debe tener 9 dígitos.
                        </div>
                    </div>
                    @error('telefono')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="col-md-6 mb-3">
                    <label for="email" class="font-weight-bold text-muted small">Correo Electrónico</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fas fa-envelope text-warning"></i></span>
                        </div>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control border-left-0 @error('email') is-invalid @enderror"
                            placeholder="contacto@empresa.com"
                            value="{{ old('email', $proveedor->email ?? '') }}">
                        <div class="invalid-feedback">
                            Correo inválido (falta @ o punto).
                        </div>
                    </div>
                    @error('email')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Switch Estado --}}
            <div class="d-flex align-items-center justify-content-end mt-3 p-3 bg-light rounded">
                <span class="mr-3 font-weight-bold text-muted">Estado:</span>
                <div class="custom-control custom-switch custom-switch-lg">
                    <input
                        type="checkbox"
                        class="custom-control-input"
                        id="activo"
                        name="activo"
                        value="1"
                        {{ old('activo', $proveedor->activo ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold text-success" for="activo">
                        {{ old('activo', $proveedor->activo ?? true) ? 'Habilitado' : 'Inhabilitado' }}
                    </label>
                </div>
            </div>

        </div>

        <div class="card-footer bg-white p-3 border-top d-flex justify-content-between">
            <a href="{{ route('inventario.proveedores.index') }}" class="btn btn-outline-secondary btn-lg px-4 rounded-pill">
                <i class="fas fa-arrow-left mr-2"></i>Cancelar
            </a>
            <button type="submit" id="btnGuardarProveedor" class="btn btn-primary btn-lg px-5 rounded-pill shadow-sm">
                <i class="fas fa-save mr-2"></i>{{ $submitText }}
            </button>
        </div>
    </div>
</form>

{{-- LÓGICA DE VALIDACIÓN "AGRESIVA" --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formProveedor');
        const ruc = document.getElementById('ruc');
        const tel = document.getElementById('telefono');
        const email = document.getElementById('email');
        const razonSocial = document.getElementById('razon_social');
        const submitBtn = document.getElementById('btnGuardarProveedor');

        const rucRegex = /^(10|15|16|17|20)\d{9}$/;
        const telRegex = /^\d{9}$/;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        // 1. Limpieza instantánea (no deja escribir letras)
        function limpiarNumeros(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
            e.target.classList.remove('is-invalid');
        }

        if (ruc) ruc.addEventListener('input', limpiarNumeros);
        if (tel) tel.addEventListener('input', limpiarNumeros);

        // Quitar rojo al escribir
        if (razonSocial) razonSocial.addEventListener('input', () => razonSocial.classList.remove('is-invalid'));
        if (email) email.addEventListener('input', () => email.classList.remove('is-invalid'));

        // 2. VALIDACIÓN AL DAR CLICK EN GUARDAR
        if (form) {
            form.addEventListener('submit', function(e) {
                let errores = [];
                let foco = null;

                // A. Validar RUC
                if (!ruc.value) {
                    ruc.classList.add('is-invalid');
                    document.getElementById('error-ruc-msg').textContent = "El RUC es obligatorio.";
                    errores.push("Falta ingresar el <b>RUC</b>.");
                    if (!foco) foco = ruc;
                } else if (ruc.value.length !== 11) {
                    ruc.classList.add('is-invalid');
                    document.getElementById('error-ruc-msg').textContent = "Debe tener 11 dígitos (tienes " + ruc.value.length + ").";
                    errores.push("El <b>RUC</b> está incompleto (debe tener 11 dígitos).");
                    if (!foco) foco = ruc;
                } else if (!rucRegex.test(ruc.value)) {
                    ruc.classList.add('is-invalid');
                    document.getElementById('error-ruc-msg').textContent = "RUC no válido (debe empezar con 10, 20...).";
                    errores.push("El <b>RUC</b> no tiene un formato válido.");
                    if (!foco) foco = ruc;
                } else {
                    ruc.classList.remove('is-invalid');
                }

                // B. Validar Razón Social
                if (!razonSocial.value.trim()) {
                    razonSocial.classList.add('is-invalid');
                    errores.push("Falta la <b>Razón Social</b>.");
                    if (!foco) foco = razonSocial;
                } else {
                    razonSocial.classList.remove('is-invalid');
                }

                // C. Validar Teléfono (Si escribió algo)
                if (tel.value.length > 0 && tel.value.length !== 9) {
                    tel.classList.add('is-invalid');
                    errores.push("El <b>Celular</b> debe tener 9 dígitos.");
                    if (!foco) foco = tel;
                } else {
                    tel.classList.remove('is-invalid');
                }

                // D. Validar Email (Si escribió algo)
                if (email.value.length > 0 && !emailRegex.test(email.value)) {
                    email.classList.add('is-invalid');
                    errores.push("El <b>Correo</b> no es válido.");
                    if (!foco) foco = email;
                } else {
                    email.classList.remove('is-invalid');
                }

                // === RESULTADO FINAL ===
                if (errores.length > 0) {
                    e.preventDefault(); // DETENER ENVÍO

                    // 1. Mostrar Alerta en el Centro (SweetAlert2)
                    Swal.fire({
                        icon: 'warning',
                        title: '¡Faltan datos!',
                        html: '<ul style="text-align: left;">' + errores.map(e => `<li>${e}</li>`).join('') + '</ul>',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#d33'
                    });

                    // 2. Enfocar el primer error
                    if (foco) foco.focus();

                } else {
                    // Si todo está OK, bloqueo el botón para que no den doble click
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Guardando...';
                }
            });
        }
    });
</script>