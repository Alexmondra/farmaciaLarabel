<div class="modal fade" id="modalSucursal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg"> {{-- CLASES LIMPIAS AQUÍ --}}
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

                <div class="modal-body bg-light"> {{-- USAMOS CLASES CLARAS POR DEFECTO --}}
                    <div class="container-fluid px-0">
                        @include('configuracion.sucursales._form')
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0"> {{-- USAMOS CLASES CLARAS POR DEFECTO --}}
                    <button type="button" class="btn btn-outline-secondary mr-2" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-teal shadow-sm">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@section('css')
@parent
<style>
    /* === MODO OSCURO PARA MODAL Y FORMULARIO (Activado por la clase AdminLTE 'dark-mode') === */
    body.dark-mode .modal-content {
        background-color: #343a40 !important;
        color: #d1d9e0 !important;
    }

    body.dark-mode .modal-header.bg-teal {
        /* Asegura que el header mantenga un color oscuro basado en teal */
        background-color: #00796b !important;
        border-bottom-color: #495057 !important;
    }

    body.dark-mode .modal-body.bg-light {
        background-color: #3e444a !important;
        /* Tono más claro que el fondo principal */
    }

    body.dark-mode .modal-footer.bg-white {
        background-color: #343a40 !important;
        border-top-color: #495057 !important;
    }

    /* Inputs de formulario */
    body.dark-mode .form-control,
    body.dark-mode .input-group-text {
        background-color: #2b3035 !important;
        color: #d1d9e0 !important;
        border-color: #495057 !important;
    }

    body.dark-mode .form-control::placeholder {
        color: #9da5af;
    }

    /* Colores de texto y etiquetas */
    body.dark-mode .text-dark,
    body.dark-mode .text-muted,
    body.dark-mode .text-teal,
    body.dark-mode label {
        color: #d1d9e0 !important;
    }

    /* Divisores y Bordes */
    body.dark-mode hr {
        border-top: 1px solid #495057;
    }

    /* Borde entre foto/datos */
    body.dark-mode .border-right {
        border-color: #495057 !important;
    }

    /* Botones de control */
    body.dark-mode .btn-outline-secondary {
        color: #9da5af;
        border-color: #495057;
    }

    body.dark-mode .btn-outline-secondary:hover {
        background-color: #495057;
        color: #fff;
    }

    /* Estilos de responsividad para el formulario (Se mantienen para ambos modos) */
    @media (max-width: 767.98px) {
        .border-right-md {
            border-right: none !important;
        }
    }
</style>
@stop