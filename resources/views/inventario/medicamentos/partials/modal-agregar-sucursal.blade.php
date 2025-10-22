<!-- Modal Agregar a Sucursales -->
<div class="modal fade" id="modalAgregarSucursal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Agregar Medicamento a Sucursales
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('inventario.medicamentos.sucursales.store', $medicamento) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Selección de Sucursales -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Seleccionar Sucursales *</label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    @php
                                        $sucursalesDisponibles = \App\Models\Sucursal::whereNotIn('id', $sucursalesMedicamento->pluck('id'))->get();
                                    @endphp
                                    @if($sucursalesDisponibles->count() > 0)
                                        @foreach($sucursalesDisponibles as $sucursal)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="sucursales[]" value="{{ $sucursal->id }}" 
                                                   id="sucursal_{{ $sucursal->id }}">
                                            <label class="form-check-label" for="sucursal_{{ $sucursal->id }}">
                                                <strong>{{ $sucursal->nombre }}</strong><br>
                                                <small class="text-muted">{{ $sucursal->direccion }}</small>
                                            </label>
                                        </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">Todas las sucursales ya tienen este medicamento</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Configuración de Precios y Stock -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Precio de Compra *</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-primary text-white">S/</span>
                                    </div>
                                    <input type="number" step="0.01" min="0" name="precio_compra" 
                                           class="form-control" required value="{{ old('precio_compra', 0) }}">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="font-weight-bold">Precio de Venta *</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-success text-white">S/</span>
                                    </div>
                                    <input type="number" step="0.01" min="0" name="precio_venta" 
                                           class="form-control" required value="{{ old('precio_venta', 0) }}">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="font-weight-bold">Stock Inicial *</label>
                                <div class="input-group">
                                    <input type="number" min="0" name="stock_inicial" 
                                           class="form-control" required value="{{ old('stock_inicial', 0) }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">unidades</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="font-weight-bold">Stock Mínimo *</label>
                                <div class="input-group">
                                    <input type="number" min="0" name="stock_minimo" 
                                           class="form-control" required value="{{ old('stock_minimo', 0) }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">unidades</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="font-weight-bold">Ubicación en Almacén</label>
                                <input type="text" name="ubicacion" class="form-control" 
                                       value="{{ old('ubicacion') }}" 
                                       placeholder="Ej: Estante A-1">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información del Lote Inicial -->
                    <hr>
                    <h6><i class="fas fa-box"></i> Información del Lote Inicial (Opcional)</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Código de Lote</label>
                                <input type="text" name="codigo_lote" class="form-control" 
                                       value="{{ old('codigo_lote') }}" 
                                       placeholder="Ej: LOTE001">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha de Vencimiento</label>
                                <input type="date" name="fecha_vencimiento" class="form-control" 
                                       value="{{ old('fecha_vencimiento') }}" 
                                       min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Agregar a Sucursales
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validación del formulario
document.querySelector('form[action*="sucursales.store"]').addEventListener('submit', function(e) {
    const sucursalesSeleccionadas = document.querySelectorAll('input[name="sucursales[]"]:checked');
    const precioCompra = parseFloat(document.querySelector('input[name="precio_compra"]').value);
    const precioVenta = parseFloat(document.querySelector('input[name="precio_venta"]').value);
    
    if (sucursalesSeleccionadas.length === 0) {
        e.preventDefault();
        alert('Debes seleccionar al menos una sucursal');
        return false;
    }
    
    if (precioVenta < precioCompra) {
        e.preventDefault();
        alert('El precio de venta debe ser mayor o igual al precio de compra');
        return false;
    }
});

// Auto-generar código de lote si está vacío
document.querySelector('input[name="codigo_lote"]').addEventListener('blur', function() {
    if (!this.value) {
        const timestamp = Date.now();
        this.value = 'LOTE-' + timestamp;
    }
});
</script>

