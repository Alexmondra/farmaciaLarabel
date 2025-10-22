@extends('adminlte::page')

@section('title','Nuevo medicamento')
@section('content_header') 
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-plus-circle"></i> Nuevo Medicamento</h1>
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

<form method="POST" action="{{ route('inventario.medicamentos.store') }}" enctype="multipart/form-data">
@csrf

<div class="row">
    <!-- Información Principal -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-pills"></i> Información del Medicamento</h5>
            </div>
            <div class="card-body">
                <!-- Código y Nombre -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Código *</label>
                            <input type="text" name="codigo" class="form-control form-control-lg" 
                                   required value="{{ old('codigo') }}" 
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
                                   required value="{{ old('nombre') }}" 
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
                                <option value="Tableta" {{ old('forma_farmaceutica') == 'Tableta' ? 'selected' : '' }}>Tableta</option>
                                <option value="Cápsula" {{ old('forma_farmaceutica') == 'Cápsula' ? 'selected' : '' }}>Cápsula</option>
                                <option value="Jarabe" {{ old('forma_farmaceutica') == 'Jarabe' ? 'selected' : '' }}>Jarabe</option>
                                <option value="Inyección" {{ old('forma_farmaceutica') == 'Inyección' ? 'selected' : '' }}>Inyección</option>
                                <option value="Crema" {{ old('forma_farmaceutica') == 'Crema' ? 'selected' : '' }}>Crema</option>
                                <option value="Gotas" {{ old('forma_farmaceutica') == 'Gotas' ? 'selected' : '' }}>Gotas</option>
                                <option value="Otro" {{ old('forma_farmaceutica') == 'Otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Concentración</label>
                            <input type="text" name="concentracion" class="form-control" 
                                   value="{{ old('concentracion') }}" 
                                   placeholder="Ej: 500mg">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Presentación</label>
                            <input type="text" name="presentacion" class="form-control" 
                                   value="{{ old('presentacion') }}" 
                                   placeholder="Ej: Caja x 20">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Laboratorio</label>
                            <input type="text" name="laboratorio" class="form-control" 
                                   value="{{ old('laboratorio') }}" 
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
                                   value="{{ old('registro_sanitario') }}" 
                                   placeholder="Número de registro">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Código de barras</label>
                            <input type="text" name="codigo_barra" class="form-control" 
                                   value="{{ old('codigo_barra') }}" 
                                   placeholder="Código de barras">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Categoría</label>
                            <select name="categoria_id" class="form-control">
                                <option value="">Seleccionar categoría...</option>
                                @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
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
                                    Seleccionar imagen...
                                </label>
                            </div>
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
                                       {{ old('activo', true) ? 'checked' : '' }}>
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
                              placeholder="Descripción adicional del medicamento">{{ old('descripcion') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="col-lg-4">
        @if($tipoVista === 'sucursal_unica')
            <!-- Vista para sucursal única -->
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-store"></i> Sucursal Única</h6>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        <strong>{{ $sucursalesDisponibles->first()->nombre }}</strong><br>
                        <small class="text-muted">{{ $sucursalesDisponibles->first()->direccion }}</small>
                    </p>
                    
                    <!-- Campos específicos de la sucursal -->
                    <div class="form-group">
                        <label class="font-weight-bold">Precio de Compra</label>
                        <input type="number" name="precio_compra" class="form-control" 
                               step="0.01" min="0" value="{{ old('precio_compra') }}" 
                               placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Precio de Venta</label>
                        <input type="number" name="precio_venta" class="form-control" 
                               step="0.01" min="0" value="{{ old('precio_venta') }}" 
                               placeholder="0.00">
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Stock Inicial</label>
                                <input type="number" name="stock_actual" class="form-control" 
                                       min="0" value="{{ old('stock_actual', 0) }}" 
                                       placeholder="0">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Stock Mínimo</label>
                                <input type="number" name="stock_minimo" class="form-control" 
                                       min="0" value="{{ old('stock_minimo', 0) }}" 
                                       placeholder="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Ubicación en Sucursal</label>
                        <input type="text" name="ubicacion" class="form-control" 
                               value="{{ old('ubicacion') }}" 
                               placeholder="Ej: Estante A-1">
                    </div>
                    
                    <input type="hidden" name="sucursal_id" value="{{ $sucursalesDisponibles->first()->id }}">
                </div>
            </div>
            
        @elseif($tipoVista === 'multiples_sucursales')
            <!-- Vista para múltiples sucursales -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-store"></i> Seleccionar Sucursal</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Sucursal para agregar medicamento</label>
                        <select name="sucursal_id" class="form-control" id="sucursal-select">
                            <option value="">Seleccionar sucursal...</option>
                            @foreach($sucursalesDisponibles as $sucursal)
                            <option value="{{ $sucursal->id }}" {{ old('sucursal_id') == $sucursal->id ? 'selected' : '' }}>
                                {{ $sucursal->nombre }}
                            </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            Si no seleccionas ninguna, podrás agregar el medicamento a las sucursales después
                        </small>
                    </div>
                    
                    <!-- Campos que aparecen solo si se selecciona una sucursal -->
                    <div id="sucursal-campos" style="display: none;">
                        <div class="form-group">
                            <label class="font-weight-bold">Precio de Compra</label>
                            <input type="number" name="precio_compra" class="form-control" 
                                   step="0.01" min="0" value="{{ old('precio_compra') }}" 
                                   placeholder="0.00">
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Precio de Venta</label>
                            <input type="number" name="precio_venta" class="form-control" 
                                   step="0.01" min="0" value="{{ old('precio_venta') }}" 
                                   placeholder="0.00">
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Stock Inicial</label>
                                    <input type="number" name="stock_actual" class="form-control" 
                                           min="0" value="{{ old('stock_actual', 0) }}" 
                                           placeholder="0">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Stock Mínimo</label>
                                    <input type="number" name="stock_minimo" class="form-control" 
                                           min="0" value="{{ old('stock_minimo', 0) }}" 
                                           placeholder="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Ubicación en Sucursal</label>
                            <input type="text" name="ubicacion" class="form-control" 
                                   value="{{ old('ubicacion') }}" 
                                   placeholder="Ej: Estante A-1">
                        </div>
                    </div>
                </div>
            </div>
            
        @else
            <!-- Vista sin sucursales (solo información general) -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Sin Sucursales</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> No tienes sucursales asignadas. 
                        Solo se registrará la información general del medicamento.
                    </div>
                </div>
            </div>
        @endif

        <!-- Información del proceso -->
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Proceso de Registro</h6>
            </div>
            <div class="card-body">
                @if($tipoVista === 'sucursal_unica')
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <strong>Registro completo:</strong> 
                        El medicamento se agregará automáticamente a tu sucursal con los datos especificados
                    </div>
                @elseif($tipoVista === 'multiples_sucursales')
                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb"></i> <strong>Opciones:</strong> 
                        Puedes agregar el medicamento a una sucursal específica ahora, o hacerlo después
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-lightbulb"></i> <strong>Paso 1:</strong> Registra la información general del medicamento
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-arrow-right"></i> <strong>Paso 2:</strong> Después podrás agregarlo a las sucursales con precios y stock específicos
                    </div>
                @endif
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    <i class="fas fa-save"></i> 
                    @if($tipoVista === 'sucursal_unica')
                        Guardar y Agregar a Sucursal
                    @else
                        Guardar Medicamento
                    @endif
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

// Manejar selección de sucursal para múltiples sucursales
const sucursalSelect = document.getElementById('sucursal-select');
const sucursalCampos = document.getElementById('sucursal-campos');

if (sucursalSelect && sucursalCampos) {
    sucursalSelect.addEventListener('change', function() {
        if (this.value) {
            sucursalCampos.style.display = 'block';
        } else {
            sucursalCampos.style.display = 'none';
        }
    });
    
    // Mostrar campos si ya hay una sucursal seleccionada (por old values)
    if (sucursalSelect.value) {
        sucursalCampos.style.display = 'block';
    }
}

// Validación básica del formulario
document.querySelector('form').addEventListener('submit', function(e) {
    const codigo = document.querySelector('input[name="codigo"]').value;
    const nombre = document.querySelector('input[name="nombre"]').value;
    
    if (!codigo || !nombre) {
        e.preventDefault();
        alert('Los campos Código y Nombre son obligatorios');
        return false;
    }
    
    // Si hay sucursal seleccionada, validar campos de sucursal
    const sucursalId = document.querySelector('select[name="sucursal_id"]');
    if (sucursalId && sucursalId.value) {
        const precioVenta = document.querySelector('input[name="precio_venta"]');
        if (precioVenta && !precioVenta.value) {
            e.preventDefault();
            alert('Debes especificar un precio de venta para la sucursal seleccionada');
            return false;
        }
    }
});
</script>
@stop