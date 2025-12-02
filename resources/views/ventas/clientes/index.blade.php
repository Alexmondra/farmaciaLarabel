@extends('adminlte::page')

@section('title', 'Directorio de Clientes')

@section('content')

<style>
    /* ==========================================
       ESTILOS BASE (Light Mode / Default)
       ========================================== */

    /* KPI CARDS (Tarjetas Superiores) */
    .kpi-card {
        background: #ffffff;
        border-radius: 15px;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        position: relative;
        height: 100%;
        /* Para igualar alturas */
    }

    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .kpi-value {
        font-size: 2rem;
        font-weight: 800;
        color: #2c3e50;
    }

    .kpi-label {
        color: #888;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .kpi-icon {
        position: absolute;
        right: -10px;
        bottom: -15px;
        font-size: 5rem;
        opacity: 0.08;
        transform: rotate(-15deg);
        transition: 0.3s;
    }

    .kpi-card:hover .kpi-icon {
        transform: rotate(0deg) scale(1.1);
        opacity: 0.15;
    }

    /* FILTROS (Tarjetas Pequeñas) */
    .filter-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 15px 20px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
        /* Borde invisible para evitar saltos */
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .filter-card:hover {
        transform: translateY(-2px);
        background: #f8f9fa;
    }

    .filter-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #6c757d;
        text-transform: uppercase;
    }

    .filter-count {
        background: #e9ecef;
        color: #495057;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 800;
        font-size: 0.85rem;
    }

    /* Estado ACTIVO del Filtro */
    .filter-card.active {
        background: #e0f7fa;
        border-color: #00bcd4;
    }

    .filter-card.active .filter-title {
        color: #00838f;
    }

    .filter-card.active .filter-count {
        background: #00bcd4;
        color: #fff;
    }

    /* BARRA DE BÚSQUEDA */
    .search-input {
        height: 55px;
        border-radius: 30px;
        border: none;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        padding-left: 55px;
        font-size: 1.1rem;
        background: #fff;
        color: #495057;
    }

    .search-input:focus {
        outline: none;
        box-shadow: 0 5px 20px rgba(0, 210, 211, 0.25);
    }

    .search-icon {
        position: absolute;
        left: 25px;
        top: 18px;
        color: #00d2d3;
        font-size: 1.2rem;
        z-index: 5;
    }

    /* CONTENEDOR TABLA */
    .table-card {
        background: #ffffff;
        border-radius: 15px;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
        border: none;
        overflow: hidden;
        /* Para que los bordes redondeados corten la tabla */
    }

    /* ESTILOS DE TABLA (Cruciales para pintar bien los datos) */
    .table-hover tbody tr:hover {
        background-color: #f1fbfd;
        /* Highlight cyan muy suave */
    }

    .avatar-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
        margin-right: 15px;
        /* Default DNI: Cyan */
        background-color: rgba(0, 188, 212, 0.15);
        color: #00bcd4;
    }

    .avatar-ruc {
        /* RUC: Naranja */
        background-color: rgba(255, 152, 0, 0.15);
        color: #f57c00;
    }

    /* BOTÓN FLOTANTE */
    .btn-new-client {
        background: linear-gradient(135deg, #00d2d3 0%, #00a8ff 100%);
        border: 0;
        border-radius: 50px;
        padding: 10px 25px;
        color: white;
        font-weight: bold;
        box-shadow: 0 5px 15px rgba(0, 168, 255, 0.3);
        transition: 0.3s;
    }

    .btn-new-client:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 20px rgba(0, 168, 255, 0.4);
        color: white;
    }

    /* ==========================================
       DARK MODE OVERRIDES (La Magia Oscura)
       ========================================== */
    .dark-mode .content-wrapper {
        background-color: #454d55 !important;
    }

    /* Tarjetas en Dark Mode */
    .dark-mode .kpi-card,
    .dark-mode .filter-card,
    .dark-mode .table-card {
        background-color: #343a40;
        /* Color gris oscuro estándar de AdminLTE */
        color: #fff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }

    /* Textos en Dark Mode */
    .dark-mode .kpi-value {
        color: #fff;
    }

    .dark-mode .kpi-label {
        color: #adb5bd;
    }

    .dark-mode .filter-title {
        color: #ced4da;
    }

    /* Input Búsqueda Dark Mode */
    .dark-mode .search-input {
        background-color: #343a40;
        color: #fff;
        border: 1px solid #6c757d;
    }

    .dark-mode .search-input::placeholder {
        color: #adb5bd;
    }

    /* Filtros Activos en Dark Mode */
    .dark-mode .filter-card:hover {
        background-color: #3f474e;
    }

    .dark-mode .filter-card.active {
        background-color: rgba(0, 188, 212, 0.2);
        /* Cyan transparente oscuro */
        border-color: #00bcd4;
    }

    .dark-mode .filter-card.active .filter-title {
        color: #00bcd4;
    }

    .dark-mode .filter-count {
        background-color: #495057;
        color: #fff;
    }

    .dark-mode .filter-card.active .filter-count {
        background-color: #00bcd4;
        color: #fff;
    }

    /* Tabla Dark Mode */
    .dark-mode .table-hover tbody tr:hover {
        background-color: #3f474e;
        /* Hover gris claro sobre oscuro */
    }

    .dark-mode .table {
        color: #fff;
        /* Asegura texto blanco en tabla */
    }

    .dark-mode .text-muted {
        color: #adb5bd !important;
        /* Muted más claro para leerse en oscuro */
    }

    .dark-mode .text-dark {
        color: #fff !important;
        /* Forzamos nombres a blanco */
    }

    /* Ajuste de Avatars para que brillen en lo oscuro */
    .dark-mode .avatar-circle {
        background-color: rgba(0, 188, 212, 0.25);
    }

    .dark-mode .avatar-ruc {
        background-color: rgba(255, 152, 0, 0.25);
    }
</style>

<div class="container-fluid pt-4">

    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="font-weight-bold mb-0">
                <i class="fas fa-users text-info mr-2"></i>Directorio de Clientes
            </h2>
            <p class="text-muted mb-0">Gestiona tu cartera de pacientes y empresas.</p>
        </div>
        <div class="col-md-6 text-right">
            <button class="btn btn-new-client" onclick="openCreateModal()">
                <i class="fas fa-plus mr-2"></i> Nuevo Cliente
            </button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-2">
            <div class="filter-card active" onclick="setFilter('all', this)">
                <div class="d-flex align-items-center">
                    <i class="fas fa-layer-group filter-icon mr-3"></i>
                    <div>
                        <div class="filter-title">Total Clientes</div>
                        <small class="text-muted d-block">Base de datos completa</small>
                    </div>
                </div>
                <span class="filter-count">{{ $total ?? 0 }}</span>
            </div>
        </div>

        <div class="col-md-4 mb-2">
            <div class="filter-card" onclick="setFilter('persona', this)">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-injured filter-icon mr-3"></i>
                    <div>
                        <div class="filter-title">Personas</div>
                        <small class="text-muted d-block">Pacientes con DNI</small>
                    </div>
                </div>
                <span class="filter-count">{{ $personas ?? 0 }}</span>
            </div>
        </div>

        <div class="col-md-4 mb-2">
            <div class="filter-card" onclick="setFilter('RUC', this)">
                <div class="d-flex align-items-center">
                    <i class="fas fa-building filter-icon mr-3"></i>
                    <div>
                        <div class="filter-title">Empresas</div>
                        <small class="text-muted d-block">Facturación RUC</small>
                    </div>
                </div>
                <span class="filter-count">{{ $empresas ?? 0 }}</span>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mb-4">
        <div class="col-md-10">
            <div class="position-relative">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="searchInput" class="form-control search-input" placeholder="Buscar por Nombre, DNI, RUC o Puntos...">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="table-card position-relative">
                <div id="table-container" class="p-0">
                    @include('ventas.clientes.partials.table')
                </div>

                <div class="overlay d-none" id="loadingOverlay"
                    style="position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.7); z-index:10; display:flex; justify-content:center; align-items:center;">
                    <div class="text-center">
                        <i class="fas fa-circle-notch fa-spin fa-3x text-info"></i>
                        <p class="mt-2 font-weight-bold">Buscando...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@include('ventas.clientes.modal-create-edit')
@include('ventas.clientes.modal-show')
@endsection

@section('js')
<script>
    /* ==========================================================================
       1. UTILIDADES Y ESTADO GLOBAL
       ========================================================================== */
    const AppState = {
        filter: 'all',
        searchTimer: null
    };

    // Helper para manejar el overlay de carga (Soporta Dark Mode)
    const toggleOverlay = (show) => {
        const overlay = $('#loadingOverlay');
        const bg = $('body').hasClass('dark-mode') ? 'rgba(0,0,0,0.5)' : 'rgba(255,255,255,0.7)';
        overlay.css('background', bg)[show ? 'removeClass' : 'addClass']('d-none');
    };

    /* ==========================================================================
       2. TABLA AJAX (Búsqueda, Paginación, Filtros)
       ========================================================================== */
    function reloadTable(page = 1) {
        toggleOverlay(true);
        const query = $('#searchInput').val();
        $.get("{{ route('clientes.search') }}", {
                q: query,
                page: page,
                type: AppState.filter
            })
            .done(html => $('#table-container').html(html))
            .always(() => toggleOverlay(false));
    }

    // Debounce para el buscador (evita saturar el servidor)
    $('#searchInput').on('keyup', () => {
        clearTimeout(AppState.searchTimer);
        AppState.searchTimer = setTimeout(() => reloadTable(), 300);
    });

    // Paginación y Filtros
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        reloadTable($(this).attr('href').split('page=')[1]);
    });

    function setFilter(type, element) {
        AppState.filter = type;
        $('.filter-card').removeClass('active');
        $(element).addClass('active');
        $('#searchInput').val('');
        reloadTable();
    }

    /* ==========================================================================
       3. LÓGICA DEL FORMULARIO (VALIDACIONES Y UX)
       ========================================================================== */

    // A. Configuración Visual DNI vs RUC
    const configureDocInput = (type) => {
        const isRUC = (type === 'RUC');
        $('#documento').attr({
            maxlength: isRUC ? 11 : 8,
            minlength: isRUC ? 11 : 8,
            placeholder: isRUC ? 'RUC (11 dígitos)' : 'DNI (8 dígitos)'
        }).removeClass('is-invalid');

        // Alternar bloques visuales
        $('.bloque-dni').toggleClass('d-none', isRUC);
        $('.bloque-ruc').toggleClass('d-none', !isRUC);

        // Limpiar alertas previas
        resetFormState();
    };

    // B. Verificación en Tiempo Real (El "Chismoso")
    const verifyDocument = (doc) => {
        const type = $('#tipo_documento').val();
        const requiredLen = (type === 'RUC') ? 11 : 8;
        const currentId = $('#cliente_id').val();

        if (doc.length === requiredLen) {
            $('#documento').addClass('is-loading');
            $.get("{{ route('clientes.check') }}", {
                    doc: doc,
                    except_id: currentId
                })
                .done(res => res.exists ? handleDuplicate(res.data) : handleFree())
                .always(() => $('#documento').removeClass('is-loading'));
        } else {
            resetFormState();
        }
    };

    // C. Manejo de Duplicado (Aquí está la lógica que pediste)
    const handleDuplicate = (data) => {
        const isRUC = data.tipo_documento === 'RUC';
        const nombre = isRUC ? data.razon_social : `${data.nombre} ${data.apellidos}`;

        // 1. Alerta Visual
        $('#documento').addClass('is-invalid');
        if ($('#doc-error').length === 0) {
            $('#documento').parent().after(`
                <div id="doc-error" class="text-danger small font-weight-bold mt-1 alert-duplicate">
                    <i class="fas fa-exclamation-circle"></i> Registrado como: <span class="text-dark">${nombre}</span>
                </div>
            `);
        } else {
            $('#doc-error').html(`<i class="fas fa-exclamation-circle"></i> Registrado como: <span class="text-dark">${nombre}</span>`);
        }

        // 2. Rellenar Datos
        if (isRUC) {
            $('#razon_social').val(data.razon_social);
        } else {
            $('#nombre').val(data.nombre);
            $('#apellidos').val(data.apellidos);
            $('#sexo').val(data.sexo);
        }
        $('#email').val(data.email);
        $('#telefono').val(data.telefono);
        $('#direccion').val(data.direccion);

        // 3. Bloquear Botón y Campos
        $('#btnGuardar').prop('disabled', true).removeClass('btn-info').addClass('btn-secondary')
            .html('<i class="fas fa-ban"></i> YA REGISTRADO');
        $('.input-future').not('#documento, #tipo_documento').prop('readonly', true).addClass('bg-light');

        // 4. LÓGICA DE DETALLES (Tu petición específica)
        // Si es RUC (Empresa) -> MANTENER CERRADO (Limpio)
        // Si es Persona -> ABRIR (Detalles)
        const shouldExpand = !isRUC && (data.email || data.telefono || data.direccion);
        toggleDetailsPanel(shouldExpand);
    };

    const handleFree = () => {
        resetFormState();
        // Limpiar campos si estaban bloqueados/llenos
        if ($('#nombre').prop('readonly') || $('#razon_social').prop('readonly')) {
            $('.input-future').not('#documento, #tipo_documento').val('').prop('readonly', false).removeClass('bg-light');
            $('#sexo').val('M');
        }
    };

    const resetFormState = () => {
        $('#documento').removeClass('is-invalid');
        $('.alert-duplicate, #doc-error').remove();
        $('#btnGuardar').prop('disabled', false).removeClass('btn-secondary').addClass('btn-info')
            .html('<i class="fas fa-save mr-1"></i> GUARDAR');
    };

    // Helper para abrir/cerrar panel de detalles
    const toggleDetailsPanel = (show) => {
        const fields = $('#extra-fields');
        if (show) {
            fields.slideDown();
            $('#toggleText').text('Ocultar Detalles');
            $('#toggleIcon').addClass('rotate-icon');
        } else {
            fields.slideUp();
            $('#toggleText').text('Ver Completo (Contacto)');
            $('#toggleIcon').removeClass('rotate-icon');
        }
    };

    /* ==========================================================================
       4. EVENTOS Y MODALES
       ========================================================================== */

    // Listeners Inputs
    $('#tipo_documento').change(e => {
        if (e.originalEvent) $('#documento').val('');
        configureDocInput(e.target.value);
    });

    $('#documento').on('input', function() {
        this.value = this.value.replace(/\D/g, ''); // Solo números
        verifyDocument(this.value);
    });

    // Botón Toggle Manual
    $('.toggle-details').click(() => {
        const isHidden = $('#extra-fields').is(':hidden');
        toggleDetailsPanel(isHidden);
    });

    // Abrir Modal (Crear/Editar Unificado)
    function openModalBase(mode, data = null) {
        $('#formCliente')[0].reset();
        resetFormState();
        $('.input-future').removeClass('is-invalid'); // Limpiar errores Laravel
        $('.input-future').prop('readonly', false).removeClass('bg-light'); // Desbloquear campos

        if (mode === 'create') {
            $('#cliente_id').val('');
            $('#modalTitulo').html('<span style="color: #00d2d3;">●</span> Nuevo Cliente');
            $('#tipo_documento').val('DNI').trigger('change');
            toggleDetailsPanel(false); // Siempre cerrado al crear

        } else { // Edit
            $('#cliente_id').val(data.id);
            $('#modalTitulo').html('<span style="color: #ff9f43;">●</span> Editar Cliente');
            $('#tipo_documento').val(data.tipo_documento).trigger('change');
            $('#documento').val(data.documento);
            $('#sexo').val(data.sexo);

            if (data.tipo_documento === 'RUC') {
                $('#razon_social').val(data.razon_social);
            } else {
                $('#nombre').val(data.nombre);
                $('#apellidos').val(data.apellidos);
            }

            $('#email').val(data.email);
            $('#telefono').val(data.telefono);
            $('#direccion').val(data.direccion);

            // Al editar, mostramos detalles si existen
            const hasExtras = data.email || data.telefono || data.direccion;
            toggleDetailsPanel(hasExtras);
        }
        $('#modalCliente').modal('show');
    }

    // Wrappers HTML
    function openCreateModal() {
        openModalBase('create');
    }

    function openEditModal(cliente) {
        openModalBase('edit', cliente);
    }

    // Submit AJAX
    $('#formCliente').submit(function(e) {
        e.preventDefault();
        const btn = $('#btnGuardar');
        if (btn.prop('disabled')) return;

        const id = $('#cliente_id').val();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        let formData = $(this).serialize();
        if (id) formData += '&_method=PUT';

        $.ajax({
            url: id ? `/clientes/${id}` : '/clientes',
            method: 'POST',
            data: formData,
            success: (res) => {
                $('#modalCliente').modal('hide');
                if (typeof toastr !== 'undefined') toastr.success(res.message);
                else alert(res.message);
                reloadTable();
            },
            error: (xhr) => {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, (k, v) => $(`[name="${k}"]`).addClass('is-invalid'));
                    if (typeof toastr !== 'undefined') toastr.error('Verifique campos.');
                } else alert('Error del servidor.');
            },
            complete: () => {
                if (!$('#documento').hasClass('is-invalid'))
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> GUARDAR');
            }
        });
    });

    /* ==========================================================================
       5. MODAL SHOW (VISUALIZAR)
       ========================================================================== */
    function openShowModal(id) {
        $('#show_avatar').html('<i class="fas fa-spinner fa-spin"></i>');
        $('#show_nombre').text('Cargando...');

        $.get(`/clientes/${id}`, function(res) {
            if (!res.success) return alert('Error al cargar.');
            const c = res.data;
            const isRUC = (c.tipo_documento === 'RUC');

            // Header Identidad
            const nombre = isRUC ? c.razon_social : `${c.nombre} ${c.apellidos}`;
            if (isRUC) {
                $('#show_avatar').css({
                    background: '#fff3e0',
                    color: '#ff9800'
                }).html('<i class="fas fa-building"></i>');
                $('#block-sexo').addClass('d-none');
            } else {
                $('#show_avatar').css({
                    background: '#e0f7fa',
                    color: '#00bcd4'
                }).text((nombre || '?').charAt(0));
                $('#block-sexo').removeClass('d-none');
                $('#show_sexo').text(c.sexo === 'M' ? 'Masculino' : 'Femenino');
            }

            $('#show_nombre').text(nombre || 'SIN DATOS');
            $('#show_tipo_doc').text(`${c.tipo_documento}: ${c.documento}`);
            $('#show_puntos').text(c.puntos || 0);
            $('#show_registro').text(new Date(c.created_at).toLocaleDateString('es-PE'));

            // Datos Opcionales (Helper interno)
            const showIfData = (sel, val) => {
                const row = $(sel).closest('div[class^="col"]');
                (val && val !== '--') ? $(sel).text(val) && row.show(): row.hide();
            };
            showIfData('#show_email', c.email);
            showIfData('#show_telefono', c.telefono);
            showIfData('#show_direccion', c.direccion);

            // Historial Ventas
            const rows = (c.ventas || []).map(v => `
                <tr><td>${new Date(v.created_at).toLocaleDateString('es-PE')}</td>
                <td class="font-weight-bold">S/ ${parseFloat(v.total).toFixed(2)}</td>
                <td><span class="badge badge-success">Completo</span></td></tr>`).join('');

            const tableHtml = rows ?
                `<div class="table-responsive"><table class="table table-hover table-sm text-center mb-0"><thead class="bg-light text-muted"><tr><th>FECHA</th><th>TOTAL</th><th>ESTADO</th></tr></thead><tbody>${rows}</tbody></table></div>` :
                `<div class="empty-state text-center py-5"><i class="fas fa-shopping-basket fa-3x text-muted mb-3 opacity-25"></i><p class="text-muted font-weight-bold">Sin historial</p></div>`;

            $('#history-container').html(tableHtml);
            $('#modalShowCliente').modal('show');
        });
    }

    $('.close, [data-dismiss="modal"]').on('click', () => $('.modal').modal('hide'));
</script>
@endsection