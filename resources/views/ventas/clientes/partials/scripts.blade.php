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

        // ==========================================
        // LÓGICA DE PERFILES MÉDICOS (CRUD AJAX)
        // ==========================================
        let currentPMClienteId = null;
        let currentPMMedicalProfiles = [];

        window.openMedicalProfilesModal = function(clienteId, clienteNombre) {
            currentPMClienteId = clienteId;
            $('#pm_cliente_nombre').text(clienteNombre);
            $('#pm_cliente_id').val(clienteId);
            
            if (!userPermissions.canEdit) {
                $('#pm_list_actions .btn-add-pm').addClass('d-none');
            } else {
                $('#pm_list_actions .btn-add-pm').removeClass('d-none');
            }

            hidePMForm();
            loadMedicalProfiles(clienteId);
            $('#modalPerfilesMedicos').modal('show');
        };

        window.closePMModal = function() {
            $('#modalPerfilesMedicos').modal('hide');
            reloadTable();
        };

        window.loadMedicalProfiles = function(clienteId) {
            const tbody = $('#pm_table_body');
            tbody.html('<tr><td colspan="4" class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-success"></i><p class="mt-2 text-muted small">Cargando perfiles...</p></td></tr>');

            $.get(`/clientes/${clienteId}/perfiles-medicos`, function(response) {
                if (!response.success) {
                    tbody.html('<tr><td colspan="4" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle"></i> Error al cargar datos.</td></tr>');
                    return;
                }

                currentPMMedicalProfiles = response.data || [];
                renderPMTable();
            }).fail(function() {
                tbody.html('<tr><td colspan="4" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle"></i> Error de conexión con el servidor.</td></tr>');
            });
        };

        function renderPMTable() {
            const tbody = $('#pm_table_body');
            tbody.empty();

            if (currentPMMedicalProfiles.length === 0) {
                tbody.html('<tr><td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-notes-medical fa-3x mb-2 text-muted opacity-25"></i><p class="mb-0 font-weight-bold">No hay perfiles médicos registrados</p><small>Haz clic en "Nuevo Perfil" para agregar el primero.</small></td></tr>');
                return;
            }

            currentPMMedicalProfiles.forEach((pm, idx) => {
                const fingerprint = pm.device_fingerprint ? `<span class="badge badge-info px-2 py-1 rounded-pill"><i class="fas fa-mobile-alt mr-1"></i> ${pm.device_fingerprint}</span>` : '<span class="text-muted small">Manual</span>';
                const fecha = new Date(pm.created_at).toLocaleDateString('es-PE') + ' ' + new Date(pm.created_at).toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
                
                let actionsHtml = '';
                if (userPermissions.canEdit) {
                    actionsHtml = `
                        <button class="btn btn-sm btn-outline-primary rounded-circle mr-1" onclick="showPMForm(true, ${idx})" title="Editar">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger rounded-circle" onclick="deletePM(${pm.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                } else {
                    actionsHtml = `<span class="text-muted small"><i class="fas fa-lock"></i> Sin permisos</span>`;
                }

                tbody.append(`
                    <tr>
                        <td class="text-wrap align-middle" style="max-width: 300px; white-space: normal;">
                            <span class="text-dark font-weight-medium">${pm.antecedentes}</span>
                        </td>
                        <td class="align-middle">${fingerprint}</td>
                        <td class="align-middle text-center text-muted small">${fecha}</td>
                        <td class="align-middle text-right">${actionsHtml}</td>
                    </tr>
                `);
            });
        }

        window.showPMForm = function(editMode = false, idx = null) {
            if (!userPermissions.canEdit) return;

            $('#pm_form')[0].reset();
            $('#pm_id').val('');
            
            if (editMode && idx !== null) {
                const pm = currentPMMedicalProfiles[idx];
                $('#pm_id').val(pm.id);
                $('#pm_antecedentes').val(pm.antecedentes);
                $('#pm_device_fingerprint').val(pm.device_fingerprint || '');
                $('#pm_form_title').html('<i class="fas fa-edit mr-1"></i> Editar Perfil Médico');
            } else {
                $('#pm_form_title').html('<i class="fas fa-plus mr-1"></i> Registrar Perfil Médico');
            }

            $('#pm_list_container').addClass('d-none');
            $('#pm_list_actions').addClass('d-none');
            $('#pm_form_container').removeClass('d-none');
        };

        window.hidePMForm = function() {
            $('#pm_form_container').addClass('d-none');
            $('#pm_list_container').removeClass('d-none');
            $('#pm_list_actions').removeClass('d-none');
        };

        $('#pm_form').submit(function(e) {
            e.preventDefault();
            if (!userPermissions.canEdit) return;

            const btn = $('#btnSavePM');
            const id = $('#pm_id').val();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

            const url = id ? `/clientes/perfiles-medicos/${id}` : `/clientes/${currentPMClienteId}/perfiles-medicos`;
            const method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _method: method,
                    _token: '{{ csrf_token() }}',
                    antecedentes: $('#pm_antecedentes').val(),
                    device_fingerprint: $('#pm_device_fingerprint').val()
                },
                success: function(res) {
                    if (res.success) {
                        if (typeof toastr !== 'undefined') toastr.success(res.message);
                        hidePMForm();
                        loadMedicalProfiles(currentPMClienteId);
                    } else {
                        alert(res.message || 'Error al guardar.');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errMsg = '';
                        $.each(errors, function(key, val) {
                            errMsg += val.join('\n') + '\n';
                        });
                        alert('Errores de validación:\n' + errMsg);
                    } else {
                        alert('Error al procesar la solicitud.');
                    }
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Guardar Cambios');
                }
            });
        });

        window.deletePM = function(perfilId) {
            if (!userPermissions.canEdit) return;

            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará el perfil médico de forma permanente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/clientes/perfiles-medicos/${perfilId}`,
                        method: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.success) {
                                if (typeof toastr !== 'undefined') toastr.success(res.message);
                                loadMedicalProfiles(currentPMClienteId);
                            } else {
                                Swal.fire('Error', res.message || 'No se pudo eliminar.', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Error de conexión.', 'error');
                        }
                    });
                }
            });
        };

        $('.close, [data-dismiss="modal"]').on('click', () => $('.modal').modal('hide'));
    });
</script>