<script>
    $(function() {
        const CSRF = '{{ csrf_token() }}';

        // Función de notificación unificada
        const notify = (icon, title) => {
            const Toast = Swal.mixin({
                toast: true,
                position: 'center',
                iconColor: 'white',
                customClass: {
                    popup: 'colored-toast'
                },
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            Toast.fire({
                icon: icon,
                title: title
            });
        };

        /* ============================================================
           I. SECCIÓN MEDICAMENTOS (FICHA TÉCNICA)
           ============================================================ */

        // Previsualización de Imagen
        $('#input_imagen').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    $('#img_preview').attr('src', e.target.result).removeClass('d-none').show();
                    $('#img_placeholder').hide();
                }
                reader.readAsDataURL(file);
            }
        });

        // Guardar Ficha Técnica (Update Rapido)
        $('#formEditRapido').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btnGuardarCambios');
            const formData = new FormData(this);
            formData.append('_method', 'PUT');

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

            $.ajax({
                url: "{{ route('inventario.medicamentos.updateRapido', $medicamento->id) }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function() {
                    notify('success', '¡Ficha técnica actualizada!');
                    setTimeout(() => location.reload(), 800);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> GUARDAR CAMBIOS');
                    notify('error', xhr.responseJSON?.message || 'Error al guardar');
                }
            });
        });

        /* ============================================================
           II. SECCIÓN SUCURSALES Y PRECIOS (DINÁMICO)
           ============================================================ */

        /**
         * 1. Abrir Modal Precio Maestro
         * Captura los datos de la sucursal específica del loop
         */
        window.abrirModalPrecio = function(id, nombre, pUnit, pBlis, pCaja, uBlis, uEnv, sucursalId) {
            // Guardamos los IDs en los campos ocultos
            $('#medIdHidden').val(id);
            $('#edit_sucursal_id').val(sucursalId); // ID dinámico de la sucursal

            $('#lblNombreMedicamento').text(nombre);
            $('#inputPrecioUnidad').val(pUnit);
            $('#inputPrecioBlister').val(pBlis);
            $('#inputPrecioCaja').val(pCaja);

            // Mostrar campos según configuración
            (uBlis > 1) ? $('#divPrecioBlister').show(): $('#divPrecioBlister').hide();
            (uEnv > 1) ? $('#divPrecioCaja').show(): $('#divPrecioCaja').hide();

            $('#modalPrecio').modal('show');
        };

        /**
         * 2. Guardar Precio Maestro
         * Usa el ID de sucursal capturado en el paso anterior
         */
        window.guardarPrecio = function(e) {
            e.preventDefault();
            const medId = $('#medIdHidden').val();
            const sucId = $('#edit_sucursal_id').val(); // Leemos el ID dinámico

            if (!sucId) {
                notify('error', 'No se detectó la sucursal');
                return;
            }

            $.ajax({
                // Construcción de URL sin usar variables de Blade que fallen en null
                url: "/inventario/medicamentos/" + medId + "/sucursales/" + sucId,
                type: "PUT",
                data: {
                    _token: CSRF,
                    precio: $('#inputPrecioUnidad').val(),
                    precio_blister: $('#inputPrecioBlister').val(),
                    precio_caja: $('#inputPrecioCaja').val()
                },
                success: function() {
                    $('#modalPrecio').modal('hide');
                    notify('success', 'Precios actualizados');
                    setTimeout(() => location.reload(), 800);
                },
                error: function() {
                    notify('error', 'Error al actualizar precios');
                }
            });
        };

        /* ============================================================
           III. GESTIÓN DE LOTES (Vencimiento, Oferta, Ubicación)
           ============================================================ */

        // Vencimiento
        $(document).on('click', '.js-edit-vencimiento', function() {
            $('#vencimiento_url').val($(this).data('url'));
            $('#fecha_vencimiento_input').val($(this).data('current'));
            $('#modalEditarVencimiento').modal('show');
        });

        $('#btnGuardarVencimiento').click(function() {
            $.ajax({
                url: $('#vencimiento_url').val(),
                method: 'POST',
                data: {
                    _token: CSRF,
                    _method: 'PUT',
                    fecha_vencimiento: $('#fecha_vencimiento_input').val()
                },
                success: function() {
                    notify('success', 'Vencimiento actualizado');
                    setTimeout(() => location.reload(), 800);
                }
            });
        });

        // Oferta
        $(document).on('click', '.js-edit-oferta', function() {
            $('#oferta_url').val($(this).data('url'));
            $('#oferta_input').val($(this).data('oferta'));
            $('#modalEditarOferta').modal('show');
        });

        $('#btnGuardarOferta').click(function() {
            $.ajax({
                url: $('#oferta_url').val(),
                method: 'POST',
                data: {
                    _token: CSRF,
                    _method: 'PUT',
                    precio_oferta: $('#oferta_input').val()
                },
                success: function() {
                    notify('success', 'Oferta actualizada');
                    setTimeout(() => location.reload(), 800);
                }
            });
        });

        // Ubicación
        $(document).on('click', '.js-edit-ubicacion', function() {
            $('#ubicacion_url').val($(this).data('url'));
            $('#ubicacion_input').val($(this).data('current'));
            $('#modalEditarUbicacion').modal('show');
        });

        $('#btnGuardarUbicacion').click(function() {
            $.ajax({
                url: $('#ubicacion_url').val(),
                method: 'POST',
                data: {
                    _token: CSRF,
                    _method: 'PUT',
                    ubicacion: $('#ubicacion_input').val()
                },
                success: function() {
                    notify('success', 'Ubicación actualizada');
                    setTimeout(() => location.reload(), 800);
                }
            });
        });

        // Expandir/Contraer campos adicionales
        $('#btnExpandirEdicion').click(function() {
            $('#seccionMasDatos').slideToggle('fast');
        });



        /* lo de categorias para select2 y tambein para crear  */
        /* ============================================================
           SECCIÓN CATEGORÍAS (SELECT2)
           ============================================================ */

        $(document).ready(function() {
            $('.select2-categoria').select2({
                theme: 'bootstrap4',
                placeholder: "-- Buscar Categoría --",
                allowClear: true,
                width: '100%',
                dropdownParent: $('#formEditRapido')
            });
        });


    });
</script>