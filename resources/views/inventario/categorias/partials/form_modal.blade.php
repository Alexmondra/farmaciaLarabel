{{-- NOTA: Este archivo SOLO contiene el modal. No agregues <html> ni <body> ni @extends --}}

<style>
    /* Estilos encapsulados para este modal */
    .modal-header-pharmacy {
        background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);
        padding: 2rem;
        border-radius: 20px 20px 0 0;
        position: relative;
    }

    .modal-header-pharmacy::after {
        content: '';
        position: absolute;
        bottom: -25px;
        left: 0;
        right: 0;
        height: 50px;
        background: white;
        border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        transform: scaleX(1.2);
    }

    .form-floating-group {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .form-floating-group input,
    .form-floating-group textarea {
        width: 100%;
        height: 55px;
        border-radius: 12px;
        border: 2px solid #f1f2f6;
        padding: 1rem;
        background: #f8f9fa;
        outline: none;
        transition: 0.3s;
    }

    .form-floating-group input:focus,
    .form-floating-group textarea:focus {
        border-color: #00b894;
        background: white;
    }

    .form-floating-group label {
        position: absolute;
        top: 14px;
        left: 15px;
        color: #b2bec3;
        pointer-events: none;
        transition: 0.2s;
    }

    .form-floating-group input:focus~label,
    .form-floating-group input:not(:placeholder-shown)~label,
    .form-floating-group textarea:focus~label,
    .form-floating-group textarea:not(:placeholder-shown)~label {
        top: -10px;
        left: 10px;
        font-size: 0.8rem;
        color: #00b894;
        background: white;
        padding: 0 5px;
        font-weight: bold;
    }
</style>

<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border:none; border-radius: 25px;">

            {{-- Header --}}
            <div class="modal-header-pharmacy text-center text-white">
                <button type="button" class="close text-white" data-dismiss="modal" style="position: absolute; top: 15px; right: 20px; opacity: 0.8;">&times;</button>
                <i class="fas fa-notes-medical fa-3x mb-2"></i>
                <h4 class="font-weight-bold mb-0" id="modalTitle">Nueva Categoría</h4>
                <p class="small opacity-75 mb-0">Gestión Farmacéutica</p>
            </div>

            {{-- Formulario --}}
            <form id="categoryForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="methodField" value="POST">

                <div class="modal-body p-4 pt-5">
                    {{-- Alerta JS --}}
                    <div id="errorAlert" class="alert alert-danger d-none rounded-lg">
                        <ul class="mb-0 pl-3 small" id="errorList"></ul>
                    </div>

                    {{-- Campos --}}
                    <div class="form-floating-group">
                        <input type="text" name="nombre" id="inputNombre" placeholder=" " required autocomplete="off">
                        <label for="inputNombre">Nombre de la Categoría</label>
                    </div>

                    <div class="form-floating-group">
                        <textarea name="descripcion" id="inputDescripcion" placeholder=" " style="min-height: 100px; padding-top:1.5rem"></textarea>
                        <label for="inputDescripcion">Descripción (Opcional)</label>
                    </div>

                    <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded-lg mt-3">
                        <span class="font-weight-bold text-dark small">Disponible en Caja</span>
                        <div class="custom-control custom-switch custom-switch-lg">
                            <input type="checkbox" class="custom-control-input" id="inputActivo" name="activo" checked>
                            <label class="custom-control-label" for="inputActivo"></label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex justify-content-between">
                    <button type="button" class="btn text-muted font-weight-bold" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white shadow px-5" style="background: #00b894; border-radius: 50px;">
                        <span id="btnText">Guardar</span> <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>