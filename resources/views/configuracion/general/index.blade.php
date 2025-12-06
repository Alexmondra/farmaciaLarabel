@extends('adminlte::page')

@section('title', 'Configuración General')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-cogs mr-2"></i> Configuración del Sistema</h1>

    {{-- SWITCH DE MODO EDICIÓN --}}
    <div class="d-flex align-items-center bg-white px-3 py-2 rounded shadow-sm">
        <span class="mr-2 font-weight-bold text-muted" id="textoModo">Modo Lectura</span>
        <div class="custom-control custom-switch custom-switch-lg">
            <input type="checkbox" class="custom-control-input" id="switchEdicion">
            <label class="custom-control-label" for="switchEdicion"></label>
        </div>
    </div>
</div>
@stop

@section('content')

<form action="{{ route('configuracion.general.update') }}" method="POST" enctype="multipart/form-data" id="formConfig">
    @csrf
    @method('PUT')

    <div class="row">

        {{-- CARD 1: DATOS DE LA EMPRESA --}}
        <div class="col-md-4">
            <div class="card card-primary card-outline h-100 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-building mr-1"></i> Datos de Empresa
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="small text-muted font-weight-bold">RUC</label>
                        <input type="text" name="empresa_ruc" value="{{ $config->empresa_ruc }}"
                            class="form-control form-control-border input-edit" disabled placeholder="20100000001">
                    </div>

                    <div class="form-group">
                        <label class="small text-muted font-weight-bold">RAZÓN SOCIAL</label>
                        <input type="text" name="empresa_razon_social" value="{{ $config->empresa_razon_social }}"
                            class="form-control form-control-border input-edit" disabled placeholder="Mi Empresa S.A.C.">
                    </div>

                    <div class="form-group">
                        <label class="small text-muted font-weight-bold">DIRECCIÓN FISCAL</label>
                        <textarea name="empresa_direccion" class="form-control form-control-border input-edit"
                            rows="3" disabled>{{ $config->empresa_direccion }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 2: CONEXIÓN SUNAT --}}
        <div class="col-md-4">
            <div class="card card-danger card-outline h-100 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-file-invoice-dollar mr-1"></i> Facturación SUNAT
                    </h3>
                </div>
                <div class="card-body">

                    <div class="form-group">
                        <label class="small text-muted d-block font-weight-bold">ENTORNO</label>
                        <div class="custom-control custom-switch custom-switch-off-warning custom-switch-on-success">
                            <input type="checkbox" class="custom-control-input input-edit" id="sunatProduccion"
                                name="sunat_produccion" value="1" {{ $config->sunat_produccion ? 'checked' : '' }} disabled>
                            <label class="custom-control-label font-weight-bold" for="sunatProduccion">
                                {{ $config->sunat_produccion ? 'Producción' : 'Beta / Pruebas' }}
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small text-muted font-weight-bold">USUARIO SOL (RUC+USUARIO)</label>
                        <input type="text" name="sunat_sol_user" value="{{ $config->sunat_sol_user }}"
                            class="form-control form-control-border input-edit" disabled autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label class="small text-muted font-weight-bold">CLAVE SOL</label>
                        <input type="password" name="sunat_sol_pass" value="{{ $config->sunat_sol_pass }}"
                            class="form-control form-control-border input-edit" disabled autocomplete="new-password">
                    </div>

                    <hr>

                    <div class="form-group">
                        <label class="small text-muted font-weight-bold">CERTIFICADO DIGITAL (.pfx / .p12)</label>

                        @if($config->sunat_certificado_path)
                        <div class="mb-2 text-success small font-weight-bold bg-light p-1 rounded">
                            <i class="fas fa-check-circle mr-1"></i> Certificado cargado
                        </div>
                        @else
                        <div class="mb-2 text-danger small font-weight-bold bg-light p-1 rounded">
                            <i class="fas fa-times-circle mr-1"></i> No hay certificado
                        </div>
                        @endif

                        <div class="custom-file" style="display:none;" id="divCertificado">
                            {{-- Agregamos .pem y .txt al accept --}}
                            <input type="file" class="custom-file-input input-edit" id="customFile" name="sunat_certificado_path" accept=".pfx,.p12,.pem,.txt" disabled>
                            <label class="custom-file-label" for="customFile">Subir nuevo...</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small text-muted font-weight-bold">CLAVE CERTIFICADO</label>
                        <input type="password" name="sunat_certificado_pass" value="{{ $config->sunat_certificado_pass }}"
                            class="form-control form-control-border input-edit" disabled>
                    </div>

                </div>
            </div>
        </div>

        {{-- CARD 3: CONFIGURACIÓN DE NEGOCIO (PUNTOS) --}}
        <div class="col-md-4">
            <div class="card card-success card-outline h-100 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-coins mr-1"></i> Fidelización & Tickets
                    </h3>
                </div>
                <div class="card-body">

                    <div class="alert alert-light border">
                        <i class="fas fa-info-circle text-info"></i>
                        Configura las reglas de puntos y mensajes impresos.
                    </div>

                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="small text-muted font-weight-bold">PUNTOS POR S/ 1.00</label>
                            <input type="number" name="puntos_por_moneda" value="{{ $config->puntos_por_moneda }}"
                                class="form-control form-control-border input-edit font-weight-bold text-center" disabled>
                        </div>
                        <div class="form-group col-6">
                            <label class="small text-muted font-weight-bold">VALOR 1 PTO (S/)</label>
                            <input type="number" step="0.0001" name="valor_punto_canje" value="{{ $config->valor_punto_canje }}"
                                class="form-control form-control-border input-edit font-weight-bold text-center" disabled>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small text-muted font-weight-bold">MENSAJE TICKET</label>
                        <textarea name="mensaje_ticket" class="form-control form-control-border input-edit"
                            rows="4" disabled placeholder="¡Gracias por su compra!">{{ $config->mensaje_ticket }}</textarea>
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- BOTÓN FLOTANTE O FIJO DE GUARDAR --}}
    <div class="row mt-4 mb-5 pb-5" id="footerAcciones" style="display: none;">
        <div class="col-12 text-right">
            <button type="button" class="btn btn-secondary mr-2" onclick="cancelarEdicion()">
                <i class="fas fa-times mr-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary btn-lg shadow px-5">
                <i class="fas fa-save mr-2"></i> GUARDAR CAMBIOS
            </button>
        </div>
    </div>

</form>

@stop

@section('css')
<style>
    /* Estilo modo lectura */
    .form-control-border:disabled {
        background-color: transparent;
        border-bottom: 1px solid transparent;
        color: #495057;
        opacity: 1;
        cursor: default;
        font-size: 1rem;
    }

    /* Estilo modo edición */
    .form-control-border:enabled {
        background-color: #fff;
        border-bottom: 2px solid #007bff;
        transition: border-color 0.3s;
    }

    /* Switch más grande */
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
</style>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const switchEdicion = document.getElementById('switchEdicion');
        const textoModo = document.getElementById('textoModo');
        const footerAcciones = document.getElementById('footerAcciones');
        const inputs = document.querySelectorAll('.input-edit');
        const divCertificado = document.getElementById('divCertificado');
        const form = document.getElementById('formConfig');

        // Función para cambiar estado
        switchEdicion.addEventListener('change', function() {
            const isEditing = this.checked;

            if (isEditing) {
                // ACTIVAR EDICIÓN
                textoModo.innerText = 'Modo Edición';
                textoModo.classList.add('text-primary');
                textoModo.classList.remove('text-muted');

                // Efecto de aparición suave
                $(footerAcciones).fadeIn();
                divCertificado.style.display = 'flex';

                // Habilitar inputs
                inputs.forEach(input => {
                    input.removeAttribute('disabled');
                    // Efecto visual opcional
                    input.classList.add('bg-white');
                });

            } else {
                // CANCELAR / BLOQUEAR
                cancelarEdicion();
            }
        });

        // Función para cancelar
        window.cancelarEdicion = function() {
            if (switchEdicion.checked) {
                // Recargamos para descartar cambios
                location.reload();
            }
        }

        // Nombre archivo Bootstrap
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    });
</script>
@stop