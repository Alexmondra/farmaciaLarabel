@extends('tienda.layout')

@section('title', 'Crear cuenta')

@section('content')
<div class="row justify-content-center my-4 md:my-5">
    <div class="col-md-6 d-flex justify-content-center">
        <div class="bg-white shadow-xl shadow-slate-100/80 border border-slate-100/50 rounded-3xl p-4 p-md-5 w-100" style="max-width: 520px; border-radius: 1.5rem;">
            
            <!-- Cabecera de Identidad Visual -->
            <div class="text-center mb-4">
                <div class="rounded-2xl d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 52px; height: 52px; background: linear-gradient(135deg, rgba(13, 148, 136, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%); color: var(--store-green); border-radius: 1rem;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.6rem; height: 1.6rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
                <h1 class="h4 font-extrabold text-slate-800 tracking-tight mb-1" style="font-weight: 800;">Crear tu cuenta</h1>
                <p class="text-xs text-slate-400 font-medium px-2 leading-relaxed" style="font-size: 0.8rem; color: #64748b;">Regístrate para realizar compras, guardar tus recetas y consultar tus pedidos.</p>
            </div>

            <!-- Formulario de Registro -->
            <form method="POST" action="{{ route('tienda.register') }}" id="form-register">
                @csrf
                <input type="hidden" name="tipo_documento" id="tipo_documento_hidden" value="{{ old('tipo_documento', 'DNI') }}">
                
                <!-- Tipo de Doc y Documento en Fila Compacta -->
                <div class="row g-2 mb-3" style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                    <div style="flex: 0 0 30%;">
                        <label class="text-xs font-semibold text-slate-500 mb-1.5 d-block" style="font-size: 0.75rem; font-weight: 600; color: #64748b;">Tipo Doc.</label>
                        <select id="tipo_documento" class="w-100 py-2.5 rounded-2xl border border-slate-200 text-slate-700 bg-white focus:outline-none focus:border-emerald-500 transition-all" style="width: 100%; border-radius: 1rem; border: 1px solid #e2e8f0; outline: none; padding-top: 0.65rem; padding-bottom: 0.65rem; padding-left: 0.5rem; padding-right: 0.5rem; font-size: 0.92rem;">
                            <option value="DNI" @selected(old('tipo_documento') === 'DNI')>DNI</option>
                            <option value="RUC" @selected(old('tipo_documento') === 'RUC')>RUC</option>
                            <option value="CE" @selected(old('tipo_documento') === 'CE')>CE</option>
                        </select>
                        @error('tipo_documento')
                            <div class="invalid-feedback d-block text-xs font-semibold mt-1 pl-2" style="font-size: 0.75rem; color: #dc3545; display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div style="flex: 1;">
                        <label class="text-xs font-semibold text-slate-500 mb-1.5 d-block" style="font-size: 0.75rem; font-weight: 600; color: #64748b;">Número de Documento</label>
                        <div class="position-relative" style="position: relative;">
                            <span class="position-absolute top-50 translate-middle-y text-slate-400" style="position: absolute; top: 50%; transform: translateY(-50%); left: 1rem; z-index: 10; color: #94a3b8;">
                                <i class="fas fa-id-card"></i>
                            </span>
                            <input type="text" name="documento" id="documento" value="{{ old('documento') }}" 
                                   class="w-100 py-2.5 rounded-2xl border border-slate-200 text-slate-700 bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all duration-300 @error('documento') is-invalid @enderror" 
                                   style="padding-left: 2.5rem; padding-right: 2.75rem; font-size: 0.92rem; width: 100%; border-radius: 1rem; border: 1px solid #e2e8f0; outline: none; padding-top: 0.65rem; padding-bottom: 0.65rem;" 
                                   placeholder="Ej. 72345678" required maxlength="20" autofocus>
                            <button type="button" id="btn-buscar-doc" class="position-absolute top-50 translate-middle-y border-0 bg-transparent text-emerald-600 hover-text-primary transition-all" 
                                    style="position: absolute; top: 50%; transform: translateY(-50%); right: 1rem; z-index: 10; cursor: pointer; color: var(--store-green); font-size: 1.05rem;" title="Buscar datos">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        @error('documento')
                            <div class="invalid-feedback d-block text-xs font-semibold mt-1 pl-2" style="font-size: 0.75rem; color: #dc3545; display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div id="doc-feedback" class="form-text text-xs pl-2 mb-3" style="font-size: 0.75rem; margin-top: -0.5rem; margin-bottom: 1rem; display: block; color: #64748b;"></div>

                <!-- Input: Nombre Completo -->
                <div class="mb-3">
                    <label class="text-xs font-semibold text-slate-500 mb-1.5 d-block" style="font-size: 0.75rem; font-weight: 600; color: #64748b;">Nombre completo / Razón social</label>
                    <div class="position-relative" style="position: relative;">
                        <span class="position-absolute top-50 translate-middle-y text-slate-400" style="position: absolute; top: 50%; transform: translateY(-50%); left: 1rem; z-index: 10; color: #94a3b8;">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" 
                               class="w-100 py-2.5 rounded-2xl border border-slate-200 text-slate-700 bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all duration-300 @error('nombre') is-invalid @enderror" 
                               style="padding-left: 2.5rem; padding-right: 1rem; font-size: 0.92rem; width: 100%; border-radius: 1rem; border: 1px solid #e2e8f0; outline: none; padding-top: 0.65rem; padding-bottom: 0.65rem;" required>
                    </div>
                    @error('nombre')
                        <div class="invalid-feedback d-block text-xs font-semibold mt-1 pl-2" style="font-size: 0.75rem; color: #dc3545; display: block; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Input: Email -->
                <div class="mb-3">
                    <label class="text-xs font-semibold text-slate-500 mb-1.5 d-block" style="font-size: 0.75rem; font-weight: 600; color: #64748b;">Correo electrónico <span class="text-slate-400 font-normal">(Opcional)</span></label>
                    <div class="position-relative" style="position: relative;">
                        <span class="position-absolute top-50 translate-middle-y text-slate-400" style="position: absolute; top: 50%; transform: translateY(-50%); left: 1rem; z-index: 10; color: #94a3b8;">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" 
                               class="w-100 py-2.5 rounded-2xl border border-slate-200 text-slate-700 bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all duration-300 @error('email') is-invalid @enderror" 
                               style="padding-left: 2.5rem; padding-right: 1rem; font-size: 0.92rem; width: 100%; border-radius: 1rem; border: 1px solid #e2e8f0; outline: none; padding-top: 0.65rem; padding-bottom: 0.65rem;"
                               placeholder="ejemplo@correo.com">
                    </div>
                    <small class="text-muted d-block mt-1 pl-2" style="font-size: 0.7rem; color: #94a3b8;">Te enviaremos alertas del estado de tus pedidos.</small>
                    @error('email')
                        <div class="invalid-feedback d-block text-xs font-semibold mt-1 pl-2" style="font-size: 0.75rem; color: #dc3545; display: block; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Input: Teléfono -->
                <div class="mb-3">
                    <label class="text-xs font-semibold text-slate-500 mb-1.5 d-block" style="font-size: 0.75rem; font-weight: 600; color: #64748b;">Teléfono / Celular <span class="text-slate-400 font-normal">(Opcional)</span></label>
                    <div class="position-relative" style="position: relative;">
                        <span class="position-absolute top-50 translate-middle-y text-slate-400" style="position: absolute; top: 50%; transform: translateY(-50%); left: 1rem; z-index: 10; color: #94a3b8;">
                            <i class="fas fa-phone-alt"></i>
                        </span>
                        <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" 
                               class="w-100 py-2.5 rounded-2xl border border-slate-200 text-slate-700 bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all duration-300 @error('telefono') is-invalid @enderror" 
                               style="padding-left: 2.5rem; padding-right: 1rem; font-size: 0.92rem; width: 100%; border-radius: 1rem; border: 1px solid #e2e8f0; outline: none; padding-top: 0.65rem; padding-bottom: 0.65rem;"
                               placeholder="Ej. 987654321">
                    </div>
                    @error('telefono')
                        <div class="invalid-feedback d-block text-xs font-semibold mt-1 pl-2" style="font-size: 0.75rem; color: #dc3545; display: block; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Input: Contraseña -->
                <div class="mb-3">
                    <label class="text-xs font-semibold text-slate-500 mb-1.5 d-block" style="font-size: 0.75rem; font-weight: 600; color: #64748b;">Contraseña</label>
                    <div class="position-relative" style="position: relative;">
                        <span class="position-absolute top-50 translate-middle-y text-slate-400" style="position: absolute; top: 50%; transform: translateY(-50%); left: 1rem; z-index: 10; color: #94a3b8;">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="password" 
                               class="w-100 py-2.5 rounded-2xl border border-slate-200 text-slate-700 bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all duration-300 @error('password') is-invalid @enderror" 
                               style="padding-left: 2.5rem; padding-right: 1rem; font-size: 0.92rem; width: 100%; border-radius: 1rem; border: 1px solid #e2e8f0; outline: none; padding-top: 0.65rem; padding-bottom: 0.65rem;"
                               placeholder="Mínimo 6 caracteres" required>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block text-xs font-semibold mt-1 pl-2" style="font-size: 0.75rem; color: #dc3545; display: block; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Input: Confirmar Contraseña -->
                <div class="mb-4">
                    <label class="text-xs font-semibold text-slate-500 mb-1.5 d-block" style="font-size: 0.75rem; font-weight: 600; color: #64748b;">Confirmar contraseña</label>
                    <div class="position-relative" style="position: relative;">
                        <span class="position-absolute top-50 translate-middle-y text-slate-400" style="position: absolute; top: 50%; transform: translateY(-50%); left: 1rem; z-index: 10; color: #94a3b8;">
                            <i class="fas fa-lock-open"></i>
                        </span>
                        <input type="password" name="password_confirmation" 
                               class="w-100 py-2.5 rounded-2xl border border-slate-200 text-slate-700 bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all duration-300" 
                               style="padding-left: 2.5rem; padding-right: 1rem; font-size: 0.92rem; width: 100%; border-radius: 1rem; border: 1px solid #e2e8f0; outline: none; padding-top: 0.65rem; padding-bottom: 0.65rem;"
                               placeholder="Confirma tu contraseña" required>
                    </div>
                </div>

                <!-- Botón Submit -->
                <button class="w-100 py-2.5 text-white font-bold rounded-2xl border-0 transition-all duration-300 active:scale-98 shadow-md hover:shadow-lg d-flex justify-content-center align-items-center gap-2"
                        id="btn-submit"
                        style="background: linear-gradient(135deg, var(--store-green) 0%, var(--store-green-dark) 100%); font-size: 0.95rem; cursor: pointer; width: 100%; border: none; border-radius: 1rem; padding-top: 0.75rem; padding-bottom: 0.75rem; color: white; font-weight: bold; display: flex; justify-content: center; align-items: center; gap: 0.5rem; transition: all 0.2s ease-in-out;">
                    <span>Crear cuenta</span>
                </button>
            </form>

            <!-- Login Enlace -->
            <div class="text-center mt-4 pt-2 border-t border-slate-100" style="text-align: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                <span class="text-xs text-slate-400 font-medium" style="font-size: 0.8rem; color: #94a3b8;">¿Ya tienes una cuenta?</span>
                <a href="{{ route('tienda.login') }}" class="text-xs font-bold text-decoration-none hover-text-primary transition-all ml-1" style="color: var(--store-green); font-size: 0.8rem; font-weight: bold; text-decoration: none; margin-left: 0.25rem;">Iniciar sesión</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Alerta Cuenta Existente -->
<div class="modal fade" id="modalCuentaExistente" tabindex="-1" aria-hidden="true" style="display: none; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.25rem; border: none;">
            <div class="modal-header border-0 pb-2 justify-content-center pt-4" style="border: none; display: flex; justify-content: center; padding-top: 1.5rem;">
                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm animate-pulse" style="width: 62px; height: 62px; background-color: #ecfdf5; color: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 2rem; height: 2rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
            </div>
            <div class="modal-body text-center px-4 pt-2 pb-4" style="text-align: center; padding-left: 1.5rem; padding-right: 1.5rem;">
                <h5 class="fw-bold mb-2 text-slate-800" style="font-weight: 800; font-size: 1.25rem; color: #1e293b;">¡Cuenta ya registrada!</h5>
                <p class="text-muted small mb-0 px-2" style="font-size: 0.85rem; color: #64748b; line-height: 1.5;">Este número de documento ya está registrado en nuestra tienda virtual. Si eres el propietario, inicia sesión para continuar con tus pedidos.</p>
            </div>
            <div class="modal-footer border-0 p-3 bg-light d-flex justify-content-center gap-2" style="border-bottom-left-radius: 1.25rem; border-bottom-right-radius: 1.25rem; display: flex; justify-content: center; gap: 0.5rem; background-color: #f8fafc; padding: 1rem; border-top: 1px solid #f1f5f9;">
                <button type="button" class="btn btn-outline-secondary py-2 px-4 rounded-xl font-semibold small" id="btn-cerrar-modal-alerta" style="border-radius: 0.75rem; border: 1px solid #cbd5e1; background: white; padding: 0.5rem 1.25rem; font-size: 0.8rem; font-weight: 600; cursor: pointer; color: #64748b; transition: all 0.2s;">Cerrar</button>
                <a href="{{ route('tienda.login') }}" class="btn btn-store py-2 px-4 rounded-xl font-bold small d-inline-flex align-items-center gap-1.5 shadow-sm" style="background: linear-gradient(135deg, var(--store-green) 0%, var(--store-green-dark) 100%); border-radius: 0.75rem; border: none; padding: 0.5rem 1.25rem; font-size: 0.8rem; font-weight: bold; color: white; display: inline-flex; align-items: center; gap: 0.25rem; text-decoration: none; box-shadow: 0 4px 6px -1px rgba(13, 148, 136, 0.1);">
                    <span>Iniciar Sesión</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const tipoDoc = document.getElementById('tipo_documento');
    const tipoDocHidden = document.getElementById('tipo_documento_hidden');
    const docInput = document.getElementById('documento');
    const btnBuscar = document.getElementById('btn-buscar-doc');
    const feedback = document.getElementById('doc-feedback');
    const nombreInput = document.getElementById('nombre');
    const emailInput = document.getElementById('email');
    const telefonoInput = document.getElementById('telefono');
    const btnSubmit = document.getElementById('btn-submit');
    let buscando = false;
    let tieneCuenta = false;
    let esClienteExistente = false;
 
    tipoDoc.addEventListener('change', () => {
        tipoDocHidden.value = tipoDoc.value;
        validarLongitud();
        docInput.value = '';
        docInput.classList.remove('is-invalid');
        feedback.textContent = '';
        feedback.className = 'form-text text-muted text-xs pl-2';
        feedback.style.color = '#64748b';
        esClienteExistente = false;
        tieneCuenta = false;
        nombreInput.readOnly = false;
        nombreInput.classList.remove('bg-light');
        btnSubmit.textContent = 'Crear cuenta';
    });

    function validarLongitud() {
        const tipo = tipoDoc.value;
        const valor = docInput.value.replace(/\D/g, '');
        if (tipo === 'DNI' && valor.length > 8) docInput.value = valor.substring(0, 8);
        if (tipo === 'RUC' && valor.length > 11) docInput.value = valor.substring(0, 11);
    }

    function getDocDigits() {
        return docInput.value.replace(/\D/g, '');
    }

    function longitudCorrecta() {
        const tipo = tipoDoc.value;
        const digits = getDocDigits();
        return (tipo === 'DNI' && digits.length === 8) || (tipo === 'RUC' && digits.length === 11);
    }

    async function buscarDocumento() {
        const digits = getDocDigits();
        if (!longitudCorrecta()) return;
        if (buscando) return;
        buscando = true;
        btnBuscar.disabled = true;
        btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        feedback.textContent = 'Buscando datos del cliente...';
        feedback.style.color = '#64748b';

        try {
            const resp = await fetch(`{{ route('tienda.check_documento') }}?doc=${encodeURIComponent(digits)}`);
            const data = await resp.json();

            if (data.found && data.data) {
                const d = data.data;
                nombreInput.value = d.nombre_completo || '';
                emailInput.value = d.email || '';
                telefonoInput.value = d.telefono || '';
                esClienteExistente = true;

                if (d.tiene_cuenta) {
                    tieneCuenta = true;
                    docInput.classList.add('is-invalid');
                    feedback.textContent = 'Este documento ya tiene una cuenta activa en la tienda virtual.';
                    feedback.style.color = '#dc3545';
                    nombreInput.readOnly = true;
                    nombreInput.classList.add('bg-light');
                    btnSubmit.disabled = true;
                    mostrarModalAlerta();
                } else {
                    tieneCuenta = false;
                    docInput.classList.remove('is-invalid');
                    feedback.textContent = 'Cliente ubicado en la farmacia física. Crea una contraseña para tu cuenta online.';
                    feedback.style.color = '#0d9488';
                    nombreInput.readOnly = true;
                    nombreInput.classList.add('bg-light');
                    btnSubmit.textContent = 'Activar cuenta';
                    btnSubmit.disabled = false;
                }
            } else {
                esClienteExistente = false;
                tieneCuenta = false;
                docInput.classList.remove('is-invalid');
                feedback.textContent = data.message || 'Documento no registrado. Ingresa tus datos manualmente.';
                feedback.style.color = '#64748b';
                nombreInput.readOnly = false;
                nombreInput.classList.remove('bg-light');
                btnSubmit.textContent = 'Crear cuenta';
                btnSubmit.disabled = false;
            }
        } catch (err) {
            feedback.textContent = 'No se pudo validar en la central. Ingresa tus datos manualmente.';
            feedback.style.color = '#e74a3b';
        } finally {
            buscando = false;
            btnBuscar.disabled = false;
            btnBuscar.innerHTML = '<i class="fas fa-search"></i>';
        }
    }

    const modalAlerta = document.getElementById('modalCuentaExistente');
    
    function mostrarModalAlerta() {
        if (!modalAlerta) return;
        modalAlerta.classList.add('show');
        modalAlerta.style.display = 'block';
        modalAlerta.setAttribute('aria-hidden', 'false');
        modalAlerta.setAttribute('aria-modal', 'true');
        document.body.classList.add('modal-open');
        if (!document.querySelector('.modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
    }

    function ocultarModalAlerta() {
        if (!modalAlerta) return;
        modalAlerta.classList.remove('show');
        modalAlerta.style.display = 'none';
        modalAlerta.setAttribute('aria-hidden', 'true');
        modalAlerta.removeAttribute('aria-modal');
        document.body.classList.remove('modal-open');
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    }

    document.getElementById('btn-cerrar-modal-alerta').addEventListener('click', ocultarModalAlerta);

    docInput.addEventListener('input', () => {
        validarLongitud();
        if (longitudCorrecta()) {
            buscarDocumento();
        } else {
            docInput.classList.remove('is-invalid');
            tieneCuenta = false;
            esClienteExistente = false;
            feedback.textContent = tipoDoc.value === 'DNI' ? 'Ingresa los 8 digitos del DNI' : 'Ingresa los 11 digitos del RUC';
            feedback.style.color = '#64748b';
            nombreInput.readOnly = false;
            nombreInput.classList.remove('bg-light');
            btnSubmit.textContent = 'Crear cuenta';
            btnSubmit.disabled = false;
        }
    });

    docInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (longitudCorrecta()) {
                buscarDocumento();
            }
        }
    });

    btnBuscar.addEventListener('click', buscarDocumento);

    document.getElementById('form-register').addEventListener('submit', (e) => {
        if (buscando) {
            e.preventDefault();
            alert('Por favor espera a que termine la búsqueda del documento.');
            return;
        }

        const tipo = tipoDoc.value;
        const digits = getDocDigits();
        if ((tipo === 'DNI' && digits.length !== 8) || (tipo === 'RUC' && digits.length !== 11)) {
            e.preventDefault();
            docInput.classList.add('is-invalid');
            feedback.textContent = tipo === 'DNI' ? 'El DNI debe tener 8 digitos.' : 'El RUC debe tener 11 digitos.';
            feedback.style.color = '#dc3545';
            return;
        }

        if (tieneCuenta) {
            e.preventDefault();
            mostrarModalAlerta();
            feedback.textContent = 'Ya tienes cuenta. Inicia sesion.';
        }
    });
</script>
@endpush
