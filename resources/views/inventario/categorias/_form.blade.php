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
            {{-- Nombre --}}
            <div class="form-group mb-3">
                <label for="nombre" class="font-weight-semibold">
                    <i class="fas fa-tag mr-1 text-muted"></i>Nombre
                </label>
                <input
                    type="text"
                    name="nombre"
                    id="nombre"
                    class="form-control @error('nombre') is-invalid @enderror"
                    placeholder="Ej. Analgésicos"
                    value="{{ old('nombre', $categoria->nombre ?? '') }}"
                    maxlength="120"
                    required>
                @error('nombre')
                <small class="text-danger"><i class="fas fa-times-circle mr-1"></i>{{ $message }}</small>
                @enderror
            </div>

            {{-- Descripción --}}
            <div class="form-group mb-3">
                <label for="descripcion" class="font-weight-semibold">
                    <i class="fas fa-align-left mr-1 text-muted"></i>Descripción
                </label>
                <textarea
                    name="descripcion"
                    id="descripcion"
                    class="form-control @error('descripcion') is-invalid @enderror"
                    rows="3"
                    placeholder="Describe brevemente la categoría">{{ old('descripcion', $categoria->descripcion ?? '') }}</textarea>
                @error('descripcion')
                <small class="text-danger"><i class="fas fa-times-circle mr-1"></i>{{ $message }}</small>
                @enderror
            </div>

            {{-- Activo --}}
            <div class="custom-control custom-switch mb-4">
                <input
                    type="checkbox"
                    class="custom-control-input"
                    id="activo"
                    name="activo"
                    {{ old('activo', $categoria->activo ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="activo">
                    <i class="fas fa-toggle-on mr-1 text-muted"></i>Activo
                </label>
            </div>
        </div>

        {{-- Footer con botones --}}
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
            <a href="{{ route('inventario.categorias.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i>{{ $submitText }}
            </button>
        </div>
    </div>
</form>