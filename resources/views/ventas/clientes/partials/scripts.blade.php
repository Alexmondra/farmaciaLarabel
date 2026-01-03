{{-- 1. CALCULAMOS PERMISOS EN PHP (ARRIBA, FUERA DEL JS) --}}
@php
$configData = [
'valorPuntoCanje' => $config->valor_punto_canje ?? 0.02,
'urlSearch' => route('clientes.search'),
'urlCheck' => route('clientes.check'),
'urlConfigUpdate' => route('configuracion.update'),
];

$userPermisos = [
'canCreate' => auth()->user()->can('clientes.crear'),
'canEdit' => auth()->user()->can('clientes.editar'),
'canDelete' => auth()->user()->can('clientes.eliminar'),
];
@endphp

{{-- 2. AQUÍ EMPIEZA EL SCRIPT (SIN @SECTION) --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const configData = @json($configData);
        const userPermissions = @json($userPermisos);

        // ==========================================
        // UTILIDADES Y ESTADO
        // ==========================================
        const AppState = {
            filter: 'all',
            searchTimer: null
        };

        // Render inicial tarjeta puntos
        $('#lbl_puntos').text('100');
        $('#lbl_moneda').text('S/ ' + (100 * parseFloat(configData.valorPuntoCanje)).toFixed(2));

        const toggleOverlay = (show) => {
            const overlay = $('#loadingOverlay');
            const bg = $('body').hasClass('dark-mode') ? 'rgba(0,0,0,0.5)' : 'rgba(255,255,255,0.7)';
            overlay.css('background', bg)[show ? 'removeClass' : 'addClass']('d-none');
        };

        // ==========================================
        // TABLA AJAX
        // ==========================================
        window.reloadTable = function(page = 1) {
            toggleOverlay(true);
            const query = $('#searchInput').val();
            $.get(configData.urlSearch, {
                    q: query,
                    page: page,
                    type: AppState.filter
                })
                .done(html => $('#table-container').html(html))
                .always(() => toggleOverlay(false));
        };

        let lastQuery = '';

        $('#searchInput').on('input', function() {
            clearTimeout(AppState.searchTimer);
            let query = $(this).val().trim();
            if (query === lastQuery) return;
            if (query.length === 0) {
                if (lastQuery !== '') {
                    lastQuery = '';
                    reloadTable();
                }
                return;
            }
            if (query.length < 3) {
                return;
            }
            AppState.searchTimer = setTimeout(() => {
                if (query !== lastQuery) {
                    lastQuery = query;
                    reloadTable();
                }
            }, 500);
        });

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            reloadTable($(this).attr('href').split('page=')[1]);
        });

        window.setFilter = function(type, element) {
            AppState.filter = type;
            $('.filter-card').removeClass('active');
            $(element).addClass('active');
            $('#searchInput').val('');
            reloadTable();
        };

        // ==========================================
        // LÓGICA FORMULARIO
        // ==========================================
        const configureDocInput = (type) => {
            const isRUC = (type === 'RUC');
            $('#documento').attr({
                maxlength: isRUC ? 11 : 8,
                minlength: isRUC ? 11 : 8,
                placeholder: isRUC ? 'RUC (11 dígitos)' : 'DNI (8 dígitos)'
            }).removeClass('is-invalid');
            $('.bloque-dni').toggleClass('d-none', isRUC);
            $('.bloque-ruc').toggleClass('d-none', !isRUC);
            resetFormState();
        };

        const verifyDocument = (doc) => {
            const type = $('#tipo_documento').val();
            const requiredLen = (type === 'RUC') ? 11 : 8;
            const currentId = $('#cliente_id').val();

            if (doc.length === requiredLen) {
                $('#documento').addClass('is-loading');
                $.get(configData.urlCheck, {
                        doc: doc,
                        except_id: currentId
                    })
                    .done(res => res.exists ? handleDuplicate(res.data) : handleFree())
                    .always(() => $('#documento').removeClass('is-loading'));
            } else {
                resetFormState();
            }
        };

        const handleDuplicate = (data) => {
            const isRUC = data.tipo_documento === 'RUC';
            const nombre = isRUC ? data.razon_social : `${data.nombre} ${data.apellidos}`;
            $('#documento').addClass('is-invalid');

            let alertHtml = `<div id="doc-error" class="text-danger small font-weight-bold mt-1 alert-duplicate"><i class="fas fa-exclamation-circle"></i> Registrado como: <span class="text-dark">${nombre}</span></div>`;
            if ($('#doc-error').length === 0) $('#documento').parent().after(alertHtml);
            else $('#doc-error').html(alertHtml);

            if (isRUC) $('#razon_social').val(data.razon_social);
            else {
                $('#nombre').val(data.nombre);
                $('#apellidos').val(data.apellidos);
                $('#sexo').val(data.sexo);
            }

            $('#email').val(data.email);
            $('#telefono').val(data.telefono);
            $('#direccion').val(data.direccion);

            $('#btnGuardar').prop('disabled', true).removeClass('btn-info').addClass('btn-secondary').html('<i class="fas fa-ban"></i> YA REGISTRADO');
            $('.input-future').not('#documento, #tipo_documento').prop('readonly', true).addClass('bg-light');

            const shouldExpand = !isRUC && (data.email || data.telefono || data.direccion);
            toggleDetailsPanel(shouldExpand);
        };

        const handleFree = () => {
            resetFormState();
            if ($('#nombre').prop('readonly') || $('#razon_social').prop('readonly')) {
                $('.input-future').not('#documento, #tipo_documento').val('').prop('readonly', false).removeClass('bg-light');
                $('#sexo').val('M');
            }
        };

        const resetFormState = () => {
            $('#documento').removeClass('is-invalid');
            $('.alert-duplicate, #doc-error').remove();
            $('#btnGuardar').prop('disabled', false).removeClass('btn-secondary').addClass('btn-info').html('<i class="fas fa-save mr-1"></i> GUARDAR');
        };

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

        $('#tipo_documento').change(e => {
            if (e.originalEvent) $('#documento').val('');
            configureDocInput(e.target.value);
        });
        $('#documento').on('input', function() {
            this.value = this.value.replace(/\D/g, '');
            verifyDocument(this.value);
        });
        $('.toggle-details').click(() => toggleDetailsPanel($('#extra-fields').is(':hidden')));

        // ==========================================
        // MODALES
        // ==========================================
        window.openCreateModal = function() {
            if (!userPermissions.canCreate) return;
            $('#formCliente')[0].reset();
            resetFormState();
            $('.input-future').removeClass('is-invalid').prop('readonly', false).removeClass('bg-light');
            $('#cliente_id').val('');
            $('#modalTitulo').html('<span style="color: #00d2d3;">●</span> Nuevo Cliente');
            $('#tipo_documento').val('DNI').trigger('change');
            toggleDetailsPanel(false);
            $('#modalCliente').modal('show');
        };

        window.openEditModal = function(cliente) {
            if (!userPermissions.canEdit) {
                Swal.fire({
                    icon: 'error',
                    title: 'Sin Permisos',
                    text: 'No tienes autorización.'
                });
                return;
            }
            $('#formCliente')[0].reset();
            resetFormState();
            $('.input-future').removeClass('is-invalid').prop('readonly', false).removeClass('bg-light');

            $('#cliente_id').val(cliente.id);
            $('#modalTitulo').html('<span style="color: #ff9f43;">●</span> Editar Cliente');
            $('#tipo_documento').val(cliente.tipo_documento).trigger('change');
            $('#documento').val(cliente.documento);
            $('#sexo').val(cliente.sexo);

            if (cliente.tipo_documento === 'RUC') $('#razon_social').val(cliente.razon_social);
            else {
                $('#nombre').val(cliente.nombre);
                $('#apellidos').val(cliente.apellidos);
            }

            $('#email').val(cliente.email);
            $('#telefono').val(cliente.telefono);
            $('#direccion').val(cliente.direccion);

            toggleDetailsPanel(cliente.email || cliente.telefono || cliente.direccion);
            $('#modalCliente').modal('show');
        };

        $('#formCliente').submit(function(e) {
            e.preventDefault();
            const id = $('#cliente_id').val();
            if (id && !userPermissions.canEdit) return;
            if (!id && !userPermissions.canCreate) return;

            const btn = $('#btnGuardar');
            if (btn.prop('disabled')) return;
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
                    reloadTable();
                },
                error: (xhr) => {
                    if (xhr.status === 422) $.each(xhr.responseJSON.errors, (k, v) => $(`[name="${k}"]`).addClass('is-invalid'));
                    else if (xhr.status === 403) toastr.error('No autorizado.');
                    else alert('Error del servidor.');
                },
                complete: () => {
                    if (!$('#documento').hasClass('is-invalid'))
                        btn.prop('disabled', false).html('<i class="fas fa-save"></i> GUARDAR');
                }
            });
        });

        window.openConfigModal = function() {
            if (!userPermissions.canEdit) return;
            $('#modalConfigPuntos').modal('show');
        };

        $('#formConfig').submit(function(e) {
            e.preventDefault();
            if (!userPermissions.canEdit) return;
            let btn = $(this).find('button[type="submit"]');
            let originalText = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: configData.urlConfigUpdate,
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    let nuevoValor = parseFloat($('#conf_valor').val());
                    $('#lbl_puntos').text(100);
                    $('#lbl_moneda').text('S/ ' + (100 * nuevoValor).toFixed(2));
                    $('#modalConfigPuntos').modal('hide');
                    toastr.success('Reglas actualizadas');
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalText);
                }
            });
        });

        $('.close, [data-dismiss="modal"]').on('click', () => $('.modal').modal('hide'));
    });
</script>