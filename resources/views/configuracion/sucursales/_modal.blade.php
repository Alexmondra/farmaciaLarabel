<div class="modal fade" id="modalSucursal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-teal text-white">
                <h5 class="modal-title font-weight-bold" id="modalTitulo">
                    <i class="fas fa-clinic-medical mr-2"></i> Gestión de Sucursal
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="formSucursal" method="POST" enctype="multipart/form-data">
                @csrf
                <div id="methodField"></div>

                <div class="modal-body bg-light">
                    <div class="container-fluid px-0">
                        @include('configuracion.sucursales._form')
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0">
                    <button type="button" class="btn btn-outline-secondary mr-2" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-teal shadow-sm">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>