<!-- Modal Agregar Lote -->
<div class="modal fade" id="modalAgregarLote" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Agregar Nuevo Lote
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formAgregarLote" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Sucursal *</label>
                        <select name="sucursal_id" class="form-control" required>
                            <option value="">Seleccionar sucursal...</option>
                            @foreach($sucursalesMedicamento as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Código de Lote *</label>
                        <input type="text" name="codigo_lote" class="form-control" 
                               required placeholder="Ej: LOTE001">
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Código único del lote
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Fecha de Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" class="form-control" 
                               min="{{ date('Y-m-d') }}">
                        <small class="form-text text-muted">
                            <i class="fas fa-calendar"></i> Opcional: fecha de vencimiento del lote
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Cantidad Inicial *</label>
                        <div class="input-group">
                            <input type="number" min="1" name="cantidad_inicial" 
                                   class="form-control" required>
                            <div class="input-group-append">
                                <span class="input-group-text">unidades</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-boxes"></i> Cantidad de unidades en este lote
                        </small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save"></i> Agregar Lote
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Función para abrir modal con sucursal preseleccionada
function agregarLote(sucursalId, sucursalNombre) {
    document.querySelector('#modalAgregarLote select[name="sucursal_id"]').value = sucursalId;
    document.querySelector('#modalAgregarLote .modal-title').innerHTML = 
        '<i class="fas fa-plus"></i> Agregar Lote - ' + sucursalNombre;
    $('#modalAgregarLote').modal('show');
}

// Auto-generar código de lote
document.querySelector('input[name="codigo_lote"]').addEventListener('blur', function() {
    if (!this.value) {
        const timestamp = Date.now();
        this.value = 'LOTE-' + timestamp;
    }
});

// Configurar formulario dinámicamente
document.querySelector('#modalAgregarLote').addEventListener('show.bs.modal', function() {
    const sucursalId = document.querySelector('select[name="sucursal_id"]').value;
    if (sucursalId) {
        document.querySelector('#formAgregarLote').action = 
            '{{ route("inventario.medicamentos.sucursales.lotes.store", [$medicamento, ":sucursal"]) }}'.replace(':sucursal', sucursalId);
    }
});

// Actualizar action del formulario cuando cambie la sucursal
document.querySelector('select[name="sucursal_id"]').addEventListener('change', function() {
    if (this.value) {
        document.querySelector('#formAgregarLote').action = 
            '{{ route("inventario.medicamentos.sucursales.lotes.store", [$medicamento, ":sucursal"]) }}'.replace(':sucursal', this.value);
    }
});
</script>

