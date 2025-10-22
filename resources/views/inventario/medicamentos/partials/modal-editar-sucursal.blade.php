<!-- Modal Editar Configuración de Sucursal -->
<div class="modal fade" id="modalEditarSucursal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Configuración
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formEditarSucursal" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Sucursal</label>
                        <input type="text" id="sucursalNombre" class="form-control" readonly>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Precio de Compra *</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-primary text-white">S/</span>
                                    </div>
                                    <input type="number" step="0.01" min="0" name="precio_compra" 
                                           class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Precio de Venta *</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-success text-white">S/</span>
                                    </div>
                                    <input type="number" step="0.01" min="0" name="precio_venta" 
                                           class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Stock Mínimo *</label>
                        <div class="input-group">
                            <input type="number" min="0" name="stock_minimo" 
                                   class="form-control" required>
                            <div class="input-group-append">
                                <span class="input-group-text">unidades</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Ubicación en Almacén</label>
                        <input type="text" name="ubicacion" class="form-control" 
                               placeholder="Ej: Estante A-1">
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Actualizar Configuración
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Función para abrir modal con datos de la sucursal
function editarSucursal(sucursalId, sucursalNombre, precioCompra, precioVenta, stockMinimo, ubicacion) {
    document.querySelector('#sucursalNombre').value = sucursalNombre;
    document.querySelector('input[name="precio_compra"]').value = precioCompra;
    document.querySelector('input[name="precio_venta"]').value = precioVenta;
    document.querySelector('input[name="stock_minimo"]').value = stockMinimo;
    document.querySelector('input[name="ubicacion"]').value = ubicacion || '';
    
    // Configurar action del formulario
    document.querySelector('#formEditarSucursal').action = 
        '{{ route("inventario.medicamentos.sucursales.update", [$medicamento, ":sucursal"]) }}'.replace(':sucursal', sucursalId);
    
    $('#modalEditarSucursal').modal('show');
}

// Validación del formulario
document.querySelector('#formEditarSucursal').addEventListener('submit', function(e) {
    const precioCompra = parseFloat(document.querySelector('input[name="precio_compra"]').value);
    const precioVenta = parseFloat(document.querySelector('input[name="precio_venta"]').value);
    
    if (precioVenta < precioCompra) {
        e.preventDefault();
        alert('El precio de venta debe ser mayor o igual al precio de compra');
        return false;
    }
});
</script>

