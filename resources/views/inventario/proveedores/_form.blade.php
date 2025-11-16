{{-- Mensajes de error --}}
@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <h5 class="mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Revisa los errores:</h5>
    <ul class="mb-0">
        @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

{{-- Formulario --}}
<form action="{{ $route }}" method="POST" autocomplete="off">
    @csrf
    @if ($method !== 'POST')
    @method($method)
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="row">
                {{-- Razón Social --}}
                <div class="col-md-8 form-group mb-3">
                    <label for="razon_social" class="font-weight-semibold">
                        <i class="fas fa-user-tie mr-1 text-muted"></i>Razón Social
                    </label>
                    <input
                        type="text"
                        name="razon_social"
                        id="razon_social"
                        class="form-control @error('razon_social') is-invalid @enderror"
                        placeholder="Ej. Droguería FARMA-DIST S.A.C."
                        value="{{ old('razon_social', $proveedor->razon_social ?? '') }}"
                        maxlength="180"
                        required>
                    @error('razon_social')
                    <small class="text-danger"><i class="fas fa-times-circle mr-1"></i>{{ $message }}</small>
                    @enderror
                </div>

                {{-- RUC --}}
                <div class="col-md-4 form-group mb-3">
                    <label for="ruc" class="font-weight-semibold">
                        <i class="fas fa-id-card mr-1 text-muted"></i>RUC
                    </label>
                    <input
                        type="text"
                        name="ruc"
                        id="ruc"
                        class="form-control @error('ruc') is-invalid @enderror"
                        placeholder="Ej. 20501234567"
                        value="{{ old('ruc', $proveedor->ruc ?? '') }}"
                        maxlength="11"
                        minlength="11"
                        required>
                    @error('ruc')
                    <small class="text-danger"><i class="fas fa-times-circle mr-1"></i>{{ $message }}</small>
                    @enderror
                </div>
            </div>

            {{-- Dirección --}}
            <div class="form-group mb-3">
                <label for="direccion" class="font-weight-semibold">
                    <i class="fas fa-map-marker-alt mr-1 text-muted"></i>Dirección
                </label>
                <input
                    type="text"
                    name="direccion"
                    id="direccion"
                    class="form-control @error('direccion') is-invalid @enderror"
                    placeholder="Ej. Av. El Sol 123, Lima"
                    value="{{ old('direccion', $proveedor->direccion ?? '') }}">
                @error('direccion')
                <small class="text-danger"><i class="fas fa-times-circle mr-1"></i>{{ $message }}</small>
                @enderror
            </div>

            <div class="row">
                {{-- Teléfono --}}
                <div class="col-md-6 form-group mb-3">
                    <label for="telefono" class="font-weight-semibold">
                        <i class="fas fa-phone-alt mr-1 text-muted"></i>Teléfono
                    </label>
                    <input
                        type="text"
                        name="telefono"
                        id="telefono"
                        class="form-control @error('telefono') is-invalid @enderror"
                        placeholder="Ej. 987654321"
                        value="{{ old('telefono', $proveedor->telefono ?? '') }}">
                    @error('telefono')
                    <small class="text-danger"><i class="fas fa-times-circle mr-1"></i>{{ $message }}</small>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="col-md-6 form-group mb-3">
                    <label for="email" class="font-weight-semibold">
                        <i class="fas fa-envelope mr-1 text-muted"></i>Email
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="Ej. ventas@distribuidora.com"
                        value="{{ old('email', $proveedor->email ?? '') }}">
                    @error('email')
                    <small class="text-danger"><i class="fas fa-times-circle mr-1"></i>{{ $message }}</small>
                    @enderror
                </div>
            </div>

            {{-- Activo --}}
            <div class="custom-control custom-switch mb-4">
                <input
                    type="checkbox"
                    class="custom-control-input"
                    id="activo"
                    name="activo"
                    value="1" {{-- Importante para el 'required|boolean' --}}
                    {{ old('activo', $proveedor->activo ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="activo">
                    <i class="fas fa-toggle-on mr-1 text-muted"></i>Activo
                </label>
            </div>
        </div>

        {{-- Footer con botones --}}
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
            <a href="{{ route('proveedores.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i>{{ $submitText }}
            </button>
        </div>
    </div>
</form>