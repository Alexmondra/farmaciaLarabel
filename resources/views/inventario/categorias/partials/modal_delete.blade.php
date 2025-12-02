<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="deleteForm" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            @csrf
            @method('DELETE')

            <div class="modal-header border-0 pb-0 justify-content-center pt-4">
                {{-- Icono de advertencia animado --}}
                <div class="text-warning text-center">
                    <i class="fas fa-exclamation-circle fa-4x mb-2" style="opacity: 0.8;"></i>
                </div>
            </div>

            <div class="modal-body text-center px-4">
                <h5 class="font-weight-bold mb-2 text-dark">¿Estás seguro?</h5>
                <p class="text-muted">
                    Estás a punto de eliminar la categoría <strong id="deleteName" class="text-dark"></strong>.
                    <br><small>Esta acción no se puede deshacer.</small>
                </p>
            </div>

            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-light rounded-pill px-4 font-weight-bold text-muted mx-2" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-danger rounded-pill px-4 font-weight-bold shadow-sm mx-2">
                    <i class="fas fa-trash-alt mr-2"></i>Sí, eliminar
                </button>
            </div>
        </form>
    </div>
</div>