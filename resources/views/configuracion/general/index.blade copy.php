@extends('adminlte::page')

@section('title', 'Configuración General')

@section('content_header')
<div class="row mb-2 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0 text-dark">
            <i class="fas fa-cogs text-primary mr-2"></i>Ajustes del Sistema
        </h1>
        <small class="text-muted">Controla los parámetros globales para tu farmacia</small>
    </div>
    <div class="col-sm-6 text-right mt-3 mt-sm-0">
        {{-- SWITCH DE MODO EDICIÓN --}}
        <div class="d-inline-flex align-items-center bg-white p-2 rounded shadow-sm control-panel">
            <span class="mr-3 font-weight-bold text-muted transition-text" id="textoModo">
                <i class="fas fa-lock mr-1"></i> Solo Lectura
            </span>
            <div class="custom-control custom-switch custom-switch-lg">
                <input type="checkbox" class="custom-control-input" id="switchEdicion">
                <label class="custom-control-label" for="switchEdicion"></label>
            </div>
        </div>
    </div>
</div>
@stop

@section('content')

<form action="{{ route('configuracion.general.update') }}" method="POST" enctype="multipart/form-data" id="formConfig" autocomplete="off">
    @csrf
    @method('PUT')

    {{--
           HACK PARA NAVEGADORES: 
           Estos inputs ocultos absorben el autocompletado de Chrome/Edge 
           para que no ensucien el RUC ni la contraseña real.
        --}}
    <input type="text" style="display:none">
    <input type="password" style="display:none">

    <div class="row">

        {{-- === COLUMNA 1: IDENTIDAD === --}}
        <div class="col-12 col-lg-4 mb-4">
            <div class="card card-primary card-outline h-100 shadow-sm card-modern">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold text-primary">
                        <i class="fas fa-id-card mr-2"></i> Identidad
                    </h3>
                </div>
                <div class="card-body text-center pt-0">

                    {{-- LOGO --}}
                    <div class="position-relative d-inline-block mb-4 mt-2 group-logo">
                        <div class="logo-preview-container shadow-sm border rounded-circle d-flex align-items-center justify-content-center bg-light">
                            @if($config->ruta_logo)
                            <img src="{{ asset('storage/'.$config->ruta_logo) }}" id="imgPreview" class="img-fluid rounded-circle" alt="Logo Empresa">
                            @else
                            <i class="fas fa-image text-secondary fa-3x" id="iconPlaceholder"></i>
                            <img src="" id="imgPreview" class="img-fluid rounded-circle d-none" alt="Logo Preview">
                            @endif
                        </div>
                        {{-- Botón Subir (Overlay) --}}
                        <div class="edit-overlay fade-in-hidden">
                            <label for="inputLogo" class="btn btn-sm btn-primary btn-circle shadow click-anim" title="Cambiar Logo">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" name="logo" id="inputLogo" class="d-none input-edit" accept="image/*" disabled>
                        </div>
                    </div>

                    <div class="text-left">
                        {{-- RUC (Con atributos anti-autofill) --}}
                        <div class="form-group">
                            <label class="small text-muted font-weight-bold text-uppercase">RUC</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-fingerprint text-muted"></i></span>
                                </div>
                                <input type="text"
                                    name="empresa_ruc"
                                    value="{{ $config->empresa_ruc }}"
                                    class="form-control bg-light border-0 input-edit"
                                    disabled
                                    readonly {{-- Importante para Chrome --}}
                                    autocomplete="off"
                                    placeholder="20100000001">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="small text-muted font-weight-bold text-uppercase">Razón Social</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-building text-muted"></i></span>
                                </div>
                                <input type="text" name="empresa_razon_social" value="{{ $config->empresa_razon_social }}"
                                    class="form-control bg-light border-0 input-edit" disabled readonly autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="small text-muted font-weight-bold text-uppercase">Dirección Fiscal</label>
                            <textarea name="empresa_direccion" class="form-control bg-light border-0 input-edit"
                                rows="3" disabled readonly>{{ $config->empresa_direccion }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- === COLUMNA 2: FACTURACIÓN === --}}
        <div class="col-12 col-lg-4 mb-4">
            <div class="card card-danger card-outline h-100 shadow-sm card-modern">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold text-danger">
                        <i class="fas fa-file-invoice mr-2"></i> SUNAT / Facturación
                    </h3>
                </div>
                <div class="card-body">

                    {{-- Entorno --}}
                    <div class="form-group bg-light p-3 rounded d-flex justify-content-between align-items-center mb-4">
                        <span class="small font-weight-bold text-uppercase text-muted">Modo Entorno</span>
                        <div class="custom-control custom-switch custom-switch-off-warning custom-switch-on-success">
                            <input type="checkbox" class="custom-control-input input-edit" id="sunatProduccion"
                                name="sunat_produccion" value="1" {{ $config->sunat_produccion ? 'checked' : '' }} disabled>
                            <label class="custom-control-label font-weight-bold {{ $config->sunat_produccion ? 'text-success' : 'text-warning' }}" for="sunatProduccion">
                                {{ $config->sunat_produccion ? 'EN PRODUCCIÓN' : 'BETA / PRUEBAS' }}
                            </label>
                        </div>
                    </div>

                    {{-- Credenciales SOL --}}
                    <div class="form-group">
                        <label class="small text-muted font-weight-bold">Usuario SOL</label>
                        <input type="text" name="sunat_sol_user" value="{{ $config->sunat_sol_user }}"
                            class="form-control input-modern input-edit" disabled readonly autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label class="small text-muted font-weight-bold">Clave SOL</label>
                        <input type="password" name="sunat_sol_pass" value="{{ $config->sunat_sol_pass }}"
                            class="form-control input-modern input-edit" disabled readonly autocomplete="new-password">
                    </div>

                    <hr class="my-4">

                    {{-- Certificado Digital --}}
                    <div class="form-group">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="small text-muted font-weight-bold m-0">Certificado Digital</label>
                            @if($config->sunat_certificado_path)
                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Activo</span>
                            @else
                            <span class="badge badge-danger">Faltante</span>
                            @endif
                        </div>

                        <div class="custom-file-modern input-edit-wrapper opacity-disabled">
                            <label class="w-100 btn btn-outline-secondary text-left text-truncate input-edit transition-all" for="certFile" style="cursor: pointer;" disabled>
                                <i class="fas fa-upload mr-2"></i> <span id="certFileName">Actualizar certificado (.pfx, .pem)...</span>
                            </label>
                            <input type="file" id="certFile" name="sunat_certificado_path" class="d-none input-edit" accept=".pfx,.p12,.pem,.txt" disabled>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small text-muted font-weight-bold">Clave del Certificado</label>
                        <input type="password" name="sunat_certificado_pass" value="{{ $config->sunat_certificado_pass }}"
                            class="form-control input-modern input-edit" disabled readonly placeholder="Solo si es PFX">
                    </div>

                </div>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="sunat_client_id">
                        Client ID (SUNAT API GRE)
                    </label>
                    <input type="text"
                        name="sunat_client_id"
                        id="sunat_client_id"
                        class="form-control"
                        value="{{ old('sunat_client_id', $config->sunat_client_id) }}"
                        placeholder="Client ID generado en SUNAT SOL">
                    <small class="text-muted">
                        Obligatorio solo si SUNAT Producción está activado
                    </small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="sunat_client_secret">
                        Client Secret (SUNAT API GRE)
                    </label>
                    <input type="password"
                        name="sunat_client_secret"
                        id="sunat_client_secret"
                        class="form-control"
                        value="{{ old('sunat_client_secret', $config->sunat_client_secret) }}"
                        placeholder="Client Secret generado en SUNAT SOL">
                    <small class="text-muted">
                        Guárdalo con cuidado (SUNAT no lo vuelve a mostrar)
                    </small>
                </div>
            </div>
        </div>


        {{-- === COLUMNA 3: TIENDA === --}}
        <div class="col-12 col-lg-4 mb-4">
            <div class="card card-success card-outline h-100 shadow-sm card-modern">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold text-success">
                        <i class="fas fa-store mr-2"></i> Configuración Descuento
                    </h3>
                </div>
                <div class="card-body">

                    <div class="alert alert-info bg-info-light border-0 small">
                        <i class="fas fa-info-circle mr-1"></i> Reglas de canje y mensajes.
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="small text-muted font-weight-bold">Puntos x S/1</label>
                                <input type="number" name="puntos_por_moneda" value="{{ $config->puntos_por_moneda }}"
                                    class="form-control input-modern text-center font-weight-bold input-edit" disabled readonly>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="small text-muted font-weight-bold">Valor 1 Pto (S/)</label>
                                <input type="number" step="0.0001" name="valor_punto_canje" value="{{ $config->valor_punto_canje }}"
                                    class="form-control input-modern text-center font-weight-bold input-edit" disabled readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label class="small text-muted font-weight-bold">Mensaje al pie del Ticket</label>
                        <textarea name="mensaje_ticket" class="form-control input-modern input-edit"
                            rows="5" disabled readonly style="resize: none;">{{ $config->mensaje_ticket }}</textarea>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- BOTÓN FLOTANTE --}}
    <div id="fabSave" class="fab-container">
        <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-lg px-4 pulse-animation">
            <i class="fas fa-save mr-2"></i> GUARDAR CAMBIOS
        </button>
        <button type="button" class="btn btn-secondary rounded-circle shadow ml-2" onclick="cancelarEdicion()" title="Cancelar">
            <i class="fas fa-times"></i>
        </button>
    </div>

</form>
@stop

@section('css')
<style>
    /* === VARIABLES & THEME === */
    :root {
        --transition-speed: 0.3s;
        --disabled-opacity: 0.7;
    }

    /* === UTILITIES === */
    .card-modern {
        border-radius: 15px;
        transition: transform 0.2s ease;
    }

    .input-modern {
        border-radius: 8px;
        border: 1px solid #e9ecef;
        padding: 10px 15px;
    }

    .input-modern:focus {
        box-shadow: 0 0 0 3px rgba(0, 123, 255, .1);
        border-color: #80bdff;
    }

    /* === MODO LECTURA VS EDICION === */
    /* Cuando está disabled y readonly, que parezca texto plano o "apagado" */
    .form-control:disabled,
    .form-control[readonly] {
        background-color: transparent !important;
        opacity: var(--disabled-opacity);
        border-color: transparent !important;
        cursor: default;
        box-shadow: none;
    }

    /* Excepción para el modo oscuro para que se lea */
    .dark-mode .form-control:disabled,
    .dark-mode .form-control[readonly] {
        color: #ddd;
    }

    /* === LOGO & AVATAR === */
    .logo-preview-container {
        width: 120px;
        height: 120px;
        overflow: hidden;
        margin: 0 auto;
        position: relative;
        background-color: #f8f9fa;
    }

    .edit-overlay {
        position: absolute;
        bottom: 0;
        right: -10px;
        display: none;
        /* JS Toggle */
    }

    .btn-circle {
        width: 35px;
        height: 35px;
        padding: 6px 0;
        border-radius: 50%;
        text-align: center;
        font-size: 14px;
    }

    /* === SWITCH GRANDE === */
    .custom-switch-lg .custom-control-label::before {
        height: 1.5rem;
        width: 2.75rem;
        border-radius: 1.5rem;
    }

    .custom-switch-lg .custom-control-label::after {
        width: calc(1.5rem - 4px);
        height: calc(1.5rem - 4px);
        border-radius: 50%;
    }

    .custom-switch-lg .custom-control-input:checked~.custom-control-label::after {
        transform: translateX(1.25rem);
    }

    /* === ANIMACIONES & FAB === */
    .fab-container {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1050;
        display: none;
        animation: slideUp var(--transition-speed) ease-out;
    }

    .opacity-disabled {
        opacity: 0.6;
        pointer-events: none;
    }

    .fade-in-hidden {
        display: none;
        animation: fadeIn 0.3s;
    }

    @keyframes slideUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .pulse-animation {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(0, 123, 255, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
        }
    }
</style>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Cacheo de elementos DOM para mejor rendimiento
        const ui = {
            switch: document.getElementById('switchEdicion'),
            textoModo: document.getElementById('textoModo'),
            fabSave: document.getElementById('fabSave'),
            inputs: document.querySelectorAll('.input-edit'),
            logoOverlay: document.querySelector('.edit-overlay'),
            inputLogo: document.getElementById('inputLogo'),
            imgPreview: document.getElementById('imgPreview'),
            iconPlaceholder: document.getElementById('iconPlaceholder'),
            certWrapper: document.querySelector('.input-edit-wrapper'),
            certInput: document.getElementById('certFile'),
            certLabel: document.getElementById('certFileName')
        };

        // Estado inicial
        let isEditing = false;

        // --- MANEJO DE MODO EDICIÓN ---
        ui.switch.addEventListener('change', function() {
            isEditing = this.checked;
            toggleEditMode(isEditing);
        });

        function toggleEditMode(active) {
            if (active) {
                // ACTIVAR EDICIÓN
                ui.textoModo.innerHTML = '<i class="fas fa-pen mr-1"></i> Modo Edición';
                ui.textoModo.classList.replace('text-muted', 'text-primary');

                ui.fabSave.style.display = 'flex';
                ui.logoOverlay.style.display = 'block';

                ui.certWrapper.classList.remove('opacity-disabled');
                ui.certWrapper.style.pointerEvents = 'auto';

                ui.inputs.forEach(el => {
                    el.removeAttribute('disabled');
                    el.removeAttribute('readonly'); // <--- CRÍTICO: Quita readonly para permitir escribir

                    // Clases visuales para indicar que es editable
                    el.classList.add('bg-white');
                    if (document.body.classList.contains('dark-mode')) el.classList.remove('bg-white');
                });

            } else {
                // CANCELAR / MODO LECTURA
                // Recargar es la forma más limpia de resetear valores modificados sin guardar
                location.reload();
            }
        }

        // --- PREVIEWS Y UX ---

        // Preview Logo
        ui.inputLogo.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    if (ui.iconPlaceholder) ui.iconPlaceholder.style.display = 'none';
                    ui.imgPreview.src = ev.target.result;
                    ui.imgPreview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });

        // Nombre de archivo certificado
        ui.certInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                ui.certLabel.textContent = this.files[0].name;
                const btn = ui.certLabel.parentElement;
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-primary', 'text-white');
            }
        });

        // Exponer función cancelar globalmente para el botón del FAB
        window.cancelarEdicion = function() {
            ui.switch.checked = false;
            ui.switch.dispatchEvent(new Event('change'));
        };
    });
</script>
@stop