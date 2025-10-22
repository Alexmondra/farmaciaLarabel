@extends('adminlte::page')

@section('title','Editar medicamento')
@section('content_header') 
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-edit"></i> Editar Medicamento</h1>
    <a href="{{ route('inventario.medicamentos.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>
@stop

@section('content')
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <h5><i class="fas fa-exclamation-triangle"></i> Errores de validación:</h5>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif

<form method="POST" action="{{ route('inventario.medicamentos.update', $medicamento) }}" enctype="multipart/form-data">
@csrf @method('PUT')

<div class="row">
    <!-- Información Principal -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="fas fa-pills"></i> Información del Medicamento</h5>
            </div>
            <div class="card-body">
                <!-- Código y Nombre -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Código *</label>
                            <input type="text" name="codigo" class="form-control form-control-lg" 
                                   required value="{{ old('codigo',$medicamento->codigo) }}" 
                                   placeholder="Ej: MED001">
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Código único del medicamento
                            </small>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="font-weight-bold">Nombre comercial *</label>
                            <input type="text" name="nombre" class="form-control form-control-lg" 
                                   required value="{{ old('nombre',$medicamento->nombre) }}" 
                                   placeholder="Nombre del medicamento">
                        </div>
                    </div>
                </div>

                <!-- Forma farmacéutica y concentración -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Forma farmacéutica</label>
                            <select name="forma_farmaceutica" class="form-control">
                                <option value="">Seleccionar...</option>
                                <option value="Tableta" {{ old('forma_farmaceutica', $medicamento->forma_farmaceutica) == 'Tableta' ? 'selected' : '' }}>Tableta</option>
                                <option value="Cápsula" {{ old('forma_farmaceutica', $medicamento->forma_farmaceutica) == 'Cápsula' ? 'selected' : '' }}>Cápsula</option>
                                <option value="Jarabe" {{ old('forma_farmaceutica', $medicamento->forma_farmaceutica) == 'Jarabe' ? 'selected' : '' }}>Jarabe</option>
                                <option value="Inyección" {{ old('forma_farmaceutica', $medicamento->forma_farmaceutica) == 'Inyección' ? 'selected' : '' }}>Inyección</option>
                                <option value="Crema" {{ old('forma_farmaceutica', $medicamento->forma_farmaceutica) == 'Crema' ? 'selected' : '' }}>Crema</option>
                                <option value="Gotas" {{ old('forma_farmaceutica', $medicamento->forma_farmaceutica) == 'Gotas' ? 'selected' : '' }}>Gotas</option>
                                <option value="Otro" {{ old('forma_farmaceutica', $medicamento->forma_farmaceutica) == 'Otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Concentración</label>
                            <input type="text" name="concentracion" class="form-control" 
                                   value="{{ old('concentracion',$medicamento->concentracion) }}" 
                                   placeholder="Ej: 500mg">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Presentación</label>
                            <input type="text" name="presentacion" class="form-control" 
                                   value="{{ old('presentacion',$medicamento->presentacion) }}" 
                                   placeholder="Ej: Caja x 20">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Laboratorio</label>
                            <input type="text" name="laboratorio" class="form-control" 
                                   value="{{ old('laboratorio',$medicamento->laboratorio) }}" 
                                   placeholder="Laboratorio farmacéutico">
                        </div>
                    </div>
                </div>

                <!-- Registro y códigos -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Registro sanitario</label>
                            <input type="text" name="registro_sanitario" class="form-control" 
                                   value="{{ old('registro_sanitario',$medicamento->registro_sanitario) }}" 
                                   placeholder="Número de registro">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Código de barras</label>
                            <input type="text" name="codigo_barra" class="form-control" 
                                   value="{{ old('codigo_barra',$medicamento->codigo_barra) }}" 
                                   placeholder="Código de barras">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Categoría</label>
                            <select name="categoria_id" class="form-control">
                                <option value="">Seleccionar categoría...</option>
                                @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ old('categoria_id', $medicamento->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Imagen -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Imagen del medicamento</label>
                            <div class="custom-file">
                                <input type="file" name="imagen" class="custom-file-input" 
                                       id="imagen" accept="image/*">
                                <label class="custom-file-label" for="imagen">
                                    Seleccionar nueva imagen...
                                </label>
                            </div>
                            @if($medicamento->imagen_path)
                            <div class="mt-2">
                                <img src="{{ asset('storage/'.$medicamento->imagen_path) }}" 
                                     class="img-thumbnail" style="max-width: 150px;">
                                <small class="form-text text-muted">Imagen actual</small>
                            </div>
                            @endif
                            <small class="form-text text-muted">
                                <i class="fas fa-image"></i> Opcional: imagen del medicamento (máx. 2MB)
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Estado</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="activo" value="1" 
                                       class="custom-control-input" id="activo" 
                                       {{ old('activo', $medicamento->activo) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="activo">
                                    Medicamento activo
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="form-group">
                    <label class="font-weight-bold">Descripción</label>
                    <textarea name="descripcion" rows="3" class="form-control" 
                              placeholder="Descripción adicional del medicamento">{{ old('descripcion',$medicamento->descripcion) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="col-lg-4">
        <!-- Información del proceso -->
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Proceso de Edición</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-lightbulb"></i> <strong>Paso 1:</strong> Edita la información general del medicamento
                </div>
                <div class="alert alert-warning">
                    <i class="fas fa-arrow-right"></i> <strong>Paso 2:</strong> Para gestionar precios y stock por sucursal, ve a la vista de detalles
                </div>
                <a href="{{ route('inventario.medicamentos.show', $medicamento) }}" class="btn btn-primary btn-sm btn-block">
                    <i class="fas fa-eye"></i> Ver Detalles y Gestionar Sucursales
                </a>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <button type="submit" class="btn btn-warning btn-lg btn-block">
                    <i class="fas fa-save"></i> Actualizar Medicamento
                </button>
                <a href="{{ route('inventario.medicamentos.index') }}" class="btn btn-secondary btn-block">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </div>
    </div>
</div>
</form>
@stop

@section('js')
<script>
// Preview de imagen
document.getElementById('imagen').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const label = document.querySelector('label[for="imagen"]');
        label.textContent = file.name;
    }
});

// Validación en tiempo real
document.querySelector('form').addEventListener('submit', function(e) {
    const precioCompra = parseFloat(document.querySelector('input[name="precio_compra"]').value);
    const precioVenta = parseFloat(document.querySelector('input[name="precio_venta"]').value);
    
    if (precioVenta < precioCompra) {
        e.preventDefault();
        alert('El precio de venta debe ser mayor o igual al precio de compra');
        return false;
    }
});
</script>
@stop