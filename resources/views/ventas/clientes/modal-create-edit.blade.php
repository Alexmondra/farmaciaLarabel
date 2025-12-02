<style>
    /* Fondo y Bordes */
    .modal-future .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        background: #ffffff;
        overflow: hidden;
    }

    /* Header Minimalista */
    .modal-future .modal-header {
        background: #ffffff;
        border-bottom: 1px solid #f0f0f0;
        padding: 20px 30px;
    }

    .title-future {
        font-family: 'Nunito', sans-serif;
        /* O la fuente de tu tema */
        font-weight: 800;
        letter-spacing: -0.5px;
        color: #2c3e50;
    }

    /* Inputs "Floating" Futuristas */
    .group-future {
        position: relative;
        margin-bottom: 25px;
    }

    .input-future {
        width: 100%;
        border: none;
        border-bottom: 2px solid #e0e0e0;
        padding: 10px 5px;
        background: transparent;
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        transition: all 0.3s ease;
        border-radius: 0;
        /* Cuadrado para efecto línea */
    }

    .input-future:focus {
        outline: none;
        border-bottom-color: #00d2d3;
        /* Color Neon Teal */
        box-shadow: 0 4px 6px -6px #00d2d3;
    }

    .input-future::placeholder {
        color: transparent;
        /* Truco para floating label */
    }

    /* Labels Flotantes */
    .label-future {
        position: absolute;
        top: 10px;
        left: 5px;
        font-size: 0.9rem;
        color: #999;
        transition: 0.3s ease all;
        pointer-events: none;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 1px;
    }

    /* Animación del Label al escribir */
    .input-future:focus~.label-future,
    .input-future:not(:placeholder-shown)~.label-future {
        top: -15px;
        font-size: 0.75rem;
        color: #00d2d3;
    }

    /* Botón Toggle (Ver Completo) */
    .toggle-details {
        cursor: pointer;
        color: #00d2d3;
        font-weight: bold;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        transition: 0.3s;
    }

    .toggle-details:hover {
        color: #01a3a4;
    }

    .toggle-icon {
        transition: transform 0.3s;
        margin-right: 5px;
    }

    .rotate-icon {
        transform: rotate(90deg);
    }

    /* Sección Oculta */
    #extra-fields {
        display: none;
        /* Oculto por defecto */
        background: #fdfdfd;
        padding: 20px;
        border-radius: 15px;
        margin-top: 10px;
        border: 1px dashed #e0e0e0;
    }
</style>

<div class="modal fade modal-future" id="modalCliente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header d-flex justify-content-between align-items-center">
                <h4 class="modal-title title-future" id="modalTitulo">
                    <span style="color: #00d2d3;">●</span> Nuevo Cliente
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="opacity: 0.3;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="formCliente" autocomplete="off">
                @csrf
                <input type="hidden" id="cliente_id" name="cliente_id">

                <div class="modal-body p-4">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="group-future">
                                <select name="tipo_documento" id="tipo_documento" class="input-future" style="padding-top: 10px;" required>
                                    <option value="DNI">DNI (Persona)</option>
                                    <option value="RUC">RUC (Empresa)</option>
                                </select>
                                <label class="label-future" style="top: -15px; font-size: 0.75rem; color: #00d2d3;">Tipo Documento</label>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="group-future d-flex">
                                <div style="flex-grow: 1; position: relative;">
                                    <input type="text" name="documento" id="documento" class="input-future" placeholder="Número" required maxlength="15">
                                    <label class="label-future">Número Documento</label>
                                </div>
                                <button type="button" id="btnBuscarAPI" class="btn btn-link text-info p-0 ml-2" title="Consultar API">
                                    <i class="fas fa-search fa-lg"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-3 bloque-dni">
                            <div class="group-future">
                                <select name="sexo" id="sexo" class="input-future" style="padding-top: 10px;">
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                                <label class="label-future" style="top: -15px; font-size: 0.75rem; color: #00d2d3;">Sexo</label>
                            </div>
                        </div>
                    </div>

                    <div class="row bloque-dni">
                        <div class="col-md-6">
                            <div class="group-future">
                                <input type="text" name="nombre" id="nombre" class="input-future text-uppercase" placeholder="Nombres">
                                <label class="label-future">Nombres</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="group-future">
                                <input type="text" name="apellidos" id="apellidos" class="input-future text-uppercase" placeholder="Apellidos">
                                <label class="label-future">Apellidos</label>
                            </div>
                        </div>
                    </div>

                    <div class="row d-none bloque-ruc">
                        <div class="col-md-12">
                            <div class="group-future">
                                <input type="text" name="razon_social" id="razon_social" class="input-future text-uppercase font-weight-bold" placeholder="Razón Social">
                                <label class="label-future">Razón Social</label>
                            </div>
                        </div>
                    </div>

                    <div class="text-right mb-2">
                        <div class="toggle-details" onclick="toggleExtraFields()">
                            <i class="fas fa-chevron-right toggle-icon" id="toggleIcon"></i>
                            <span id="toggleText">Ver Completo (Contacto)</span>
                        </div>
                    </div>

                    <div id="extra-fields">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="group-future">
                                    <input type="email" name="email" id="email" class="input-future" placeholder="Email">
                                    <label class="label-future">Correo Electrónico</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="group-future">
                                    <input type="text" name="telefono" id="telefono" class="input-future" placeholder="Teléfono">
                                    <label class="label-future">Celular / Teléfono</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="group-future mb-0">
                                    <input type="text" name="direccion" id="direccion" class="input-future" placeholder="Dirección">
                                    <label class="label-future">Dirección / Ubicación</label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-end">
                    <button type="button" class="btn btn-light text-muted mr-2" data-dismiss="modal" style="border-radius: 20px; padding: 8px 20px;">Cancelar</button>
                    <button type="submit" class="btn btn-info shadow-sm" id="btnGuardar" style="background: #00d2d3; border:none; border-radius: 20px; padding: 8px 30px; font-weight: bold;">
                        <i class="fas fa-save mr-1"></i> GUARDAR
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>