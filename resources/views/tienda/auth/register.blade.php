@extends('tienda.layout')

@section('title', 'Crear cuenta')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="store-card bg-white p-4">
            <h1 class="h3 mb-4">Crear cuenta</h1>
            <form method="POST" action="{{ route('tienda.register') }}" id="form-register">
                @csrf
                <input type="hidden" name="tipo_documento" id="tipo_documento_hidden" value="{{ old('tipo_documento', 'DNI') }}">
                <div class="mb-3">
                    <label class="form-label">Tipo de documento</label>
                    <select id="tipo_documento" class="form-control">
                        <option value="DNI" @selected(old('tipo_documento') === 'DNI')>DNI (8 digitos)</option>
                        <option value="RUC" @selected(old('tipo_documento') === 'RUC')>RUC (11 digitos)</option>
                        <option value="CE" @selected(old('tipo_documento') === 'CE')>CE</option>
                    </select>
                    @error('tipo_documento')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Documento</label>
                    <div class="input-group">
                        <input type="text" name="documento" id="documento" value="{{ old('documento') }}" class="form-control @error('documento') is-invalid @enderror" required maxlength="20" autofocus>
                        <button type="button" id="btn-buscar-doc" class="btn btn-outline-secondary" title="Buscar datos">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div id="doc-feedback" class="form-text"></div>
                    @error('documento')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" class="form-control @error('nombre') is-invalid @enderror" required>
                    @error('nombre')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Email (opcional)</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
                    <small class="text-muted">Te avisaremos cuando tu pedido este listo.</small>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Telefono (opcional)</label>
                    <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" class="form-control @error('telefono') is-invalid @enderror">
                    @error('telefono')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button class="btn btn-store btn-lg w-100" id="btn-submit">Crear cuenta</button>
            </form>
            <div class="text-center mt-3">
                <span class="text-muted">Ya tienes cuenta?</span>
                <a href="{{ route('tienda.login') }}" class="fw-bold text-decoration-none">Iniciar sesion</a>
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
        feedback.className = 'form-text text-muted';
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
        feedback.textContent = 'Buscando...';
        feedback.className = 'form-text text-muted';

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
                    feedback.textContent = 'Este documento ya tiene cuenta. Si eres tu, inicia sesion.';
                    feedback.className = 'form-text text-danger';
                    nombreInput.readOnly = true;
                    nombreInput.classList.add('bg-light');
                    btnSubmit.disabled = true;
                } else {
                    tieneCuenta = false;
                    docInput.classList.remove('is-invalid');
                    feedback.textContent = 'Ya estas registrado en la farmacia. Solo crea tu contraseña para la tienda.';
                    feedback.className = 'form-text text-success';
                    nombreInput.readOnly = true;
                    nombreInput.classList.add('bg-light');
                    btnSubmit.textContent = 'Activar cuenta';
                    btnSubmit.disabled = false;
                }
            } else {
                esClienteExistente = false;
                tieneCuenta = false;
                docInput.classList.remove('is-invalid');
                feedback.textContent = data.message || 'Documento no encontrado. Ingresa tus datos.';
                feedback.className = 'form-text text-muted';
                nombreInput.readOnly = false;
                nombreInput.classList.remove('bg-light');
                btnSubmit.textContent = 'Crear cuenta';
                btnSubmit.disabled = false;
            }
        } catch (err) {
            feedback.textContent = 'Error al consultar. Ingresa tus datos manualmente.';
            feedback.className = 'form-text text-muted';
        } finally {
            buscando = false;
            btnBuscar.disabled = false;
            btnBuscar.innerHTML = '<i class="fas fa-search"></i>';
        }
    }

    docInput.addEventListener('input', () => {
        validarLongitud();
        if (longitudCorrecta()) {
            buscarDocumento();
        } else {
            docInput.classList.remove('is-invalid');
            hasCuenta = false;
            esClienteExistente = false;
            feedback.textContent = tipoDoc.value === 'DNI' ? 'Ingresa los 8 digitos del DNI' : 'Ingresa los 11 digitos del RUC';
            feedback.className = 'form-text text-muted';
            nombreInput.readOnly = false;
            nombreInput.classList.remove('bg-light');
            btnSubmit.textContent = 'Crear cuenta';
            btnSubmit.disabled = false;
        }
    });

    btnBuscar.addEventListener('click', buscarDocumento);

    document.getElementById('form-register').addEventListener('submit', (e) => {
        const tipo = tipoDoc.value;
        const digits = getDocDigits();
        if ((tipo === 'DNI' && digits.length !== 8) || (tipo === 'RUC' && digits.length !== 11)) {
            e.preventDefault();
            docInput.classList.add('is-invalid');
            feedback.textContent = tipo === 'DNI' ? 'El DNI debe tener 8 digitos.' : 'El RUC debe tener 11 digitos.';
            feedback.className = 'form-text text-danger';
        }
        if (tieneCuenta) {
            e.preventDefault();
            feedback.textContent = 'Ya tienes cuenta. Inicia sesion.';
        }
    });
</script>
@endpush
