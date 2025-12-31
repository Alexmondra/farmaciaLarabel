{{-- Mensajes de error globales --}}
@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle mr-2 text-lg"></i>
        <div>
            <strong>¡Atención!</strong> Por favor verifica los siguientes errores.
        </div>
    </div>
    <ul class="mt-2 mb-0 pl-3">
        @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<form action="{{ $route }}" method="POST" autocomplete="off">
    @csrf
    @if ($method !== 'POST')
    @method($method)
    @endif

    {{-- Tarjeta Principal con diseño moderno --}}
    <div class="card card-outline card-primary shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-4">

            {{-- SECCIÓN 1: DATOS FISCALES --}}
            <h6 class="text-primary font-weight-bold text-uppercase mb-3" style="letter-spacing: 1px; font-size: 0.8rem;">
                <i class="fas fa-building mr-1"></i> Datos Fiscales
            </h6>
            <div class="form-row">
                {{-- RUC (Destacado por ser clave) --}}
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
                            placeholder="Ej. 20501234567"
                            value="{{ old('ruc', $proveedor->ruc ?? '') }}"
                            maxlength="11"
                            minlength="11"
                            required
                            style="font-size: 1.1em; letter-spacing: 0.5px;">
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
                            placeholder="Ej. DROGUERÍA E INVERSIONES DEL NORTE S.A.C."
                            value="{{ old('razon_social', $proveedor->razon_social ?? '') }}"
                            maxlength="180"
                            required>
                    </div>
                    @error('razon_social')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <hr class="my-4 border-light">

            {{-- SECCIÓN 2: UBICACIÓN Y CONTACTO --}}
            <h6 class="text-info font-weight-bold text-uppercase mb-3" style="letter-spacing: 1px; font-size: 0.8rem;">
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
                        placeholder="Av. Principal 123, Urb. Industrial, Lima"
                        value="{{ old('direccion', $proveedor->direccion ?? '') }}">
                </div>
                @error('direccion')
                <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                {{-- Teléfono --}}
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="font-weight-bold text-muted small">Teléfono / Celular</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fas fa-phone text-success"></i></span>
                        </div>
                        <input
                            type="text"
                            name="telefono"
                            id="telefono"
                            class="form-control border-left-0 @error('telefono') is-invalid @enderror"
                            placeholder="987654321"
                            value="{{ old('telefono', $proveedor->telefono ?? '') }}">
                    </div>
                    @error('telefono')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                    {{-- JS Error Container (MANTENIDO) --}}
                    <small id="telefono_js_error" class="text-danger d-none mt-1 font-weight-bold">
                        <i class="fas fa-exclamation-circle mr-1"></i><span></span>
                    </small>
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
                    </div>
                    @error('email')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Estado Switch --}}
            <div class="d-flex align-items-center justify-content-end mt-3 p-3 bg-light rounded">
                <span class="mr-3 font-weight-bold text-muted">Estado del Proveedor:</span>
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

        {{-- Footer --}}
        <div class="card-footer bg-white p-3 border-top d-flex justify-content-between">
            <a href="{{ route('inventario.proveedores.index') }}" class="btn btn-outline-secondary btn-lg px-4 rounded-pill">
                <i class="fas fa-arrow-left mr-2"></i>Cancelar
            </a>
            <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow-sm">
                <i class="fas fa-save mr-2"></i>{{ $submitText }}
            </button>
        </div>
    </div>
</form>