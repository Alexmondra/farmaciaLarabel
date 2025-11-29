@section('js')
{{-- LIBRERIAS --}}
<script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // === CONFIGURACIÓN GLOBAL ===
    const RUTA_LOOKUP = "{{ route('inventario.medicamentos.lookup') }}";
    let timeoutBusqueda = null;
    let itemIndex = 0;
    let searchIndex = -1; // Para navegación con flechas

    // Orden de Tabulación (Visual)
    const ordenTab = [
        '.input-medicamento-search', '.input-cantidad', '.input-precio',
        '.input-precio-venta', '.input-lote', '.input-fechaVenci',
        '.input-ubicacion', '.input-oferta'
    ];

    $(document).ready(function() {
        bsCustomFileInput.init();

        // 1. PROVEEDORES
        $('.select2-proveedor').select2({
            theme: 'bootstrap-5',
            placeholder: "-- Buscar Proveedor --",
            allowClear: true,
            width: '100%'
        });

        $('#proveedor_id').on('change', function() {
            const val = $(this).val();
            const btn = $('#btn-accion-proveedor');
            const icon = $('#icon-accion-proveedor');

            if (val) {
                btn.removeClass('btn-outline-primary').addClass('btn-outline-info').attr('title', 'Ver datos');
                icon.removeClass('fa-plus').addClass('fa-eye');
            } else {
                btn.removeClass('btn-outline-info').addClass('btn-outline-primary').attr('title', 'Agregar Nuevo');
                icon.removeClass('fa-eye').addClass('fa-plus');
            }
        }).trigger('change');

        $('#btn-accion-proveedor').click(function() {
            const opt = $('#proveedor_id').find(':selected');
            if ($('#proveedor_id').val()) {
                $('#view_ruc').text('RUC: ' + opt.data('ruc'));
                $('#view_razon_social').text(opt.text());
                $('#view_telefono').text(opt.data('telefono'));
                $('#view_email').text(opt.data('email'));
                $('#view_direccion').text(opt.data('direccion'));
                $('#modalVerProveedor').modal('show');
            } else {
                $('#modalCrearProveedor').modal('show');
            }
        });

        // 2. EVENTOS DE TABLA (Delegación: Funciona para filas actuales y futuras)

        // A. Cálculos automáticos (Cantidad o Precio Compra)
        $(document).on('input', '.input-cantidad, .input-precio', function() {
            let fila = $(this).closest('tr');
            let cant = parseFloat(fila.find('.input-cantidad').val()) || 0;
            let prec = parseFloat(fila.find('.input-precio').val()) || 0;
            fila.find('.subtotal-fila').text('S/ ' + (cant * prec).toFixed(2));
            recalcularTotalGeneral();
        });

        // B. Buscador: Escribir (Input)
        $(document).on('input', '.input-medicamento-search', function() {
            let $input = $(this);
            let q = $input.val().trim();
            searchIndex = -1; // Reset flechas

            clearTimeout(timeoutBusqueda);
            if (q.length === 0) {
                $input.closest('.search-container').find('.search-results').removeClass('active').empty();
                limpiarFila($input.closest('tr'));
                return;
            }

            timeoutBusqueda = setTimeout(() => {
                $.get(RUTA_LOOKUP, {
                    q: q
                }, (data) => renderResultados(data.results, $input.closest('.search-container').find('.search-results')));
            }, 250);
        });

        // C. Buscador: Click fuera para cerrar
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-container').length) $('.search-results').removeClass('active');
        });

        // D. Botón Eliminar Fila
        $(document).on('click', '.btn-eliminar-fila', function() {
            $(this).closest('tr').remove();
            recalcularTotalGeneral();
        });

        // E. Botón Acción Medicamento (Ojo / Más)
        $(document).on('click', '.btn-accion-med', function() {
            let info = $(this).data('info');

            if (info) {
                // === MODO VER DETALLE ===

                // 1. Campos de Texto (Mapeo Manual ID vs Dato)
                $('#lbl_med_nombre').text(info.nombre || '--');
                $('#lbl_med_lab').text(info.laboratorio || '--'); // Corregido
                $('#lbl_med_codigo').text(info.codigo || '--');
                $('#lbl_med_cat').text(info.categoria || 'Sin Categoría'); // Corregido
                $('#lbl_med_desc').text(info.descripcion || '--');
                $('#lbl_med_pres').text(info.presentacion || '--'); // Corregido
                $('#lbl_med_conc').text(info.concentracion || '--'); // Corregido
                $('#lbl_med_reg').text(info.registro_sanitario || '--');

                // 2. Campos Especiales
                $('#lbl_med_barra').text(info.codigo_barra || 'Sin Código');
                $('#lbl_med_unidades').text(info.unidades_por_envase || 1);
                $('#lbl_med_stock').text(info.stock_actual);
                $('#lbl_med_precio').text('S/ ' + parseFloat(info.precio_venta || 0).toFixed(2));

                // 3. Imagen
                let img = info.imagen_url;
                if (img) {
                    $('#img_med_foto').attr('src', img).show();
                    $('#div_med_placeholder').hide();
                } else {
                    $('#img_med_foto').hide();
                    $('#div_med_placeholder').show();
                }

                $('#modalVerMedicamento').modal('show');

            } else {
                // === MODO CREAR NUEVO ===
                let group = $(this).closest('.input-group');
                window.inputSearchActivo = group.find('.input-medicamento-search');
                window.inputIdActivo = group.find('.input-medicamento-id');

                $('#formNuevoMedicamentoRapid')[0].reset();
                $('#modalCrearMedicamento').modal('show');
            }
        });

        // 3. GUARDAR MEDICAMENTO RÁPIDO
        $('#formNuevoMedicamentoRapid').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#btnGuardarMedRapido');
            btn.prop('disabled', true).text('Guardando...');

            $.ajax({
                url: "{{ route('inventario.medicamentos.storeRapido') }}",
                method: "POST",
                data: new FormData(this), // Esto ya maneja la imagen automáticamente
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#modalCrearMedicamento').modal('hide');
                    if (window.inputSearchActivo) {
                        window.inputSearchActivo.val(res.data.nombre);
                        window.inputIdActivo.val(res.data.id);
                        actualizarBotonAccion(window.inputSearchActivo.closest('tr'), res.data);
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Creado',
                        text: 'Medicamento guardado con éxito',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    // MANEJO DE ERRORES DE VALIDACIÓN (DUPLICADOS)
                    let errores = xhr.responseJSON.errors;
                    let mensaje = 'Error al guardar.';

                    if (errores) {
                        mensaje = '<ul style="text-align: left;">';
                        $.each(errores, function(key, val) {
                            mensaje += `<li>${val[0]}</li>`; // Ej: "El nombre ya existe"
                        });
                        mensaje += '</ul>';
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'No se pudo crear',
                        html: mensaje // Usamos HTML para mostrar la lista
                    });
                },
                complete: () => btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar')
            });
        });

        // 4. ENVÍO DE FORMULARIO COMPRA (Validación)
        $('#form-compra').on('submit', function(e) {
            e.preventDefault();
            let errores = [];

            // Validaciones básicas
            if (!$('#proveedor_id').val()) errores.push("Falta Proveedor.");
            if (!$('input[name="numero_factura_proveedor"]').val()) errores.push("Falta N° Comprobante.");
            if ($('.fila-item').length === 0) errores.push("La compra está vacía.");

            let warningPrecio = false;
            $('.fila-item').each(function(i) {
                let fila = $(this);
                let n = i + 1;
                let med = fila.find('.input-medicamento-search').val();

                if (!fila.find('.input-medicamento-id').val()) errores.push(`Item ${n}: Seleccione medicamento.`);
                if (!fila.find('.input-lote').val()) errores.push(`Item ${n} (${med}): Falta Lote.`);
                if (!fila.find('.input-fechaVenci').val()) errores.push(`Item ${n} (${med}): Falta Vencimiento.`);

                let pCompra = parseFloat(fila.find('.input-precio').val()) || 0;
                let pVenta = parseFloat(fila.find('.input-precio-venta').val()) || 0;
                if (pVenta <= pCompra && pVenta > 0) warningPrecio = true;
            });

            if (errores.length > 0) return Swal.fire({
                icon: 'error',
                title: 'Atención',
                html: errores.join('<br>')
            });

            let submitForm = () => {
                Swal.fire({
                    title: 'Guardando...',
                    didOpen: () => Swal.showLoading()
                });
                this.submit();
            };

            if (warningPrecio) {
                Swal.fire({
                    icon: 'warning',
                    title: '¿Precios bajos?',
                    text: 'Hay productos con precio venta menor o igual al costo.',
                    showCancelButton: true,
                    confirmButtonText: 'Guardar igual'
                }).then((r) => {
                    if (r.isConfirmed) submitForm();
                });
            } else {
                submitForm();
            }
        });
    });

    // ======================================================================
    // LOGICA DE TECLADO UNIFICADA (Navigation Controller)
    // ======================================================================
    $(document).on('keydown', function(e) {
        // F2: AGREGAR FILA
        if (e.which === 113) {
            e.preventDefault();
            agregarFilaItem();
        }
    });

    $(document).on('keydown', '.fila-item input', function(e) {
        let key = e.which;
        let $input = $(this);
        let $tr = $input.closest('tr');

        // --- 1. BUSCADOR: FLECHAS Y ENTER ---
        if ($input.hasClass('input-medicamento-search')) {
            let $results = $tr.find('.search-results');
            let $items = $results.find('.search-item');

            if ($results.hasClass('active') && $items.length > 0) {
                if (key === 40) { // Abajo
                    e.preventDefault();
                    searchIndex = (searchIndex + 1) % $items.length;
                    pintarSeleccion($items);
                    return;
                }
                if (key === 38) { // Arriba
                    e.preventDefault();
                    searchIndex = (searchIndex - 1 + $items.length) % $items.length;
                    pintarSeleccion($items);
                    return;
                }
                if (key === 13) { // Enter
                    e.preventDefault();
                    if (searchIndex > -1) $items.eq(searchIndex).click();
                    else $items.first().click();
                    return;
                }
            }
        }

        // --- 2. ENTER EN OTROS CAMPOS (Crear Fila) ---
        if (key === 13 && ($input.hasClass('input-precio-venta') || $input.hasClass('input-cantidad'))) {
            e.preventDefault();
            if ($input.hasClass('input-precio-venta')) agregarFilaItem();
        }

        // --- 3. TABULACIÓN VISUAL (Tab Manager) ---
        if (key === 9) { // Tab
            e.preventDefault();
            let currentClass = ordenTab.find(cls => $input.is(cls));
            let idx = ordenTab.indexOf(currentClass);

            if (idx !== -1 && idx < ordenTab.length - 1) {
                $tr.find(ordenTab[idx + 1]).focus().select();
            } else {
                let $nextRow = $tr.next('.fila-item');
                if ($nextRow.length) $nextRow.find(ordenTab[0]).focus();
                else agregarFilaItem();
            }
        }

        // --- 4. ESCAPE (Borrar fila o cerrar lista) ---
        if (key === 27) { // Esc
            e.preventDefault();
            let $res = $tr.find('.search-results');
            if ($res.hasClass('active')) {
                $res.removeClass('active');
            } else {
                if ($('.fila-item').length > 1) {
                    $tr.css('background', '#ffe6e6').fadeOut(200, function() {
                        $(this).remove();
                        recalcularTotalGeneral();
                        $('.fila-item').last().find('.input-cantidad').focus(); // Foco a la anterior
                    });
                } else {
                    limpiarFila($tr);
                    $tr.find('.input-medicamento-search').val('').focus();
                }
            }
        }
    });

    // ======================================================================
    // FUNCIONES AUXILIARES
    // ======================================================================

    function renderResultados(data, $div) {
        $div.empty();
        if (!data.length) return $div.append('<div class="p-2 small text-muted">Sin resultados</div>');

        data.forEach(item => {
            let i = item.full_data;
            let precioHtml = i.precio_venta > 0 ? `<span class="search-item-price">S/ ${parseFloat(i.precio_venta).toFixed(2)}</span>` : '<span class="search-item-price text-muted">Nuevo</span>';
            $div.append(`
                <div class="search-item" onclick='seleccionarItem(${JSON.stringify(item)}, this)'>
                    <div><strong>${i.nombre}</strong> <small>(${i.presentacion})</small></div>
                    <small class="text-muted">Stock: ${i.stock_actual}</small> ${precioHtml}
                </div>
            `);
        });
        $div.addClass('active');
    }

    function pintarSeleccion($items) {
        $items.removeClass('active-keyboard').eq(searchIndex).addClass('active-keyboard')[0].scrollIntoView({
            block: 'nearest'
        });
    }

    // Función Global para seleccionar (llamada desde el HTML generado)
    window.seleccionarItem = function(item, div) {
        let $row = $(div).closest('tr');
        $row.find('.input-medicamento-search').val(item.full_data.nombre);
        $row.find('.input-medicamento-id').val(item.id);
        $row.find('.input-precio-venta').val(parseFloat(item.full_data.precio_venta || 0).toFixed(2));

        actualizarBotonAccion($row, item.full_data);
        $(div).parent().removeClass('active').empty();

        // Auto-foco al siguiente campo (Cantidad)
        $row.find('.input-cantidad').focus().select();
    };

    function actualizarBotonAccion($row, info) {
        let btn = $row.find('.btn-accion-med');
        let icon = btn.find('i');
        if (info) {
            btn.removeClass('btn-outline-primary').addClass('btn-outline-info').attr('title', 'Ver Detalle').data('info', info);
            icon.removeClass('fa-plus').addClass('fa-eye');
        } else {
            btn.removeClass('btn-outline-info').addClass('btn-outline-primary').attr('title', 'Nuevo').data('info', null);
            icon.removeClass('fa-eye').addClass('fa-plus');
        }
    }

    function limpiarFila($row) {
        $row.find('.input-medicamento-id').val('');
        $row.find('.input-precio-venta').val('0.00');
        actualizarBotonAccion($row, null);
    }

    function agregarFilaItem() {
        itemIndex++;
        let h = `
        <tr class="fila-item">
            <td class="align-middle text-center"><span class="badge bg-light text-dark border">${$('.fila-item').length + 1}</span></td>
            <td>
                <div class="mb-2 search-container">
                    <span class="label-mini">MEDICAMENTO</span>
                    <div class="input-group">
                        <input type="text" class="form-control input-medicamento-search" placeholder="Buscar..." autocomplete="off">
                        <input type="hidden" name="items[${itemIndex}][medicamento_id]" class="input-medicamento-id">
                        <button type="button" class="btn btn-outline-primary btn-addon-right btn-accion-med"><i class="fas fa-plus"></i></button>
                    </div>
                    <div class="search-results"></div>
                </div>
                <div class="row g-2">
                    <div class="col-6"><span class="label-mini">LOTE</span><input type="text" name="items[${itemIndex}][codigo_lote]" class="input-modern text-uppercase input-lote"></div>
                    <div class="col-6"><span class="label-mini">VENCIMIENTO</span><input type="date" name="items[${itemIndex}][fecha_vencimiento]" class="input-modern input-fechaVenci"></div>
                </div>
            </td>
            <td>
                <div class="mb-2"><span class="label-mini">CANTIDAD</span><input type="number" name="items[${itemIndex}][cantidad_recibida]" class="input-modern text-center fw-bold input-cantidad" value="1"></div>
                <div><span class="label-mini">UBICACIÓN</span><input type="text" name="items[${itemIndex}][ubicacion]" class="input-modern input-ubicacion"></div>
            </td>
            <td>
                <div class="row g-2 mb-2">
                    <div class="col-6"><span class="label-mini">P. COMPRA</span><input type="number" name="items[${itemIndex}][precio_compra_unitario]" class="input-modern input-precio" value="0"></div>
                    <div class="col-6"><span class="label-mini">P. VENTA</span><input type="number" name="items[${itemIndex}][precio_venta]" class="input-modern input-precio-venta" value="0"></div>
                </div>
                <div><span class="label-mini">P. OFERTA</span><input type="number" name="items[${itemIndex}][precio_oferta]" class="input-modern input-oferta" placeholder="0.00"></div>
            </td>
            <td class="text-end">
                <div class="subtotal-fila fw-bold">S/ 0.00</div>
                <button type="button" class="btn btn-outline-danger btn-sm border-0 btn-eliminar-fila"><i class="fas fa-trash-alt"></i></button>
            </td>
        </tr>`;

        $('#items-table-body').append(h);

        // Auto-scroll y Foco
        let $newInput = $('.fila-item').last().find('.input-medicamento-search');
        setTimeout(() => $newInput.focus()[0].scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        }), 50);
    }

    function recalcularTotalGeneral() {
        let t = 0;
        $('.subtotal-fila').each((i, el) => t += parseFloat($(el).text().replace('S/', '')) || 0);
        $('#total-general-fijo').text('S/ ' + t.toFixed(2));

        // Re-enumerar filas
        $('.fila-item').each((i, row) => $(row).find('.badge').text(i + 1));
    }

    // ======================================================================
    // LOGICA PARA CREAR PROVEEDOR DESDE MODAL (AJAX)
    // ======================================================================
    $('#formNuevoProveedor').on('submit', function(e) {
        e.preventDefault(); // Evita que se recargue la página

        let $form = $(this);
        let $btn = $form.find('button[type="submit"]');
        let data = $form.serialize(); // Empaqueta los datos

        // Bloquear botón
        $btn.prop('disabled', true).text('Guardando...');

        $.ajax({
            // Usamos la ruta ESTÁNDAR de store
            url: "{{ route('inventario.proveedores.store') }}",
            method: "POST",
            data: data,
            // Importante para que Laravel sepa que es Ajax
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // 1. Cerrar Modal
                $('#modalCrearProveedor').modal('hide');
                $form[0].reset(); // Limpiar inputs

                // 2. Agregar el nuevo proveedor al Select2
                let prov = response.data;
                let nuevoOption = new Option(
                    prov.ruc + ' - ' + prov.razon_social, // Texto visible
                    prov.id, // Valor (ID)
                    true, // Selected (propiedad)
                    true // Selected (visual)
                );

                // Guardar datos extra (data-attributes) para el botón del ojito
                $(nuevoOption).attr({
                    'data-ruc': prov.ruc,
                    'data-telefono': prov.telefono || '',
                    'data-direccion': prov.direccion || '',
                    'data-email': prov.email || ''
                });

                // Añadir y disparar cambio para que se active el botón "Ver datos"
                $('#proveedor_id').append(nuevoOption).trigger('change');

                // 3. Mensaje de éxito
                Swal.fire({
                    icon: 'success',
                    title: '¡Listo!',
                    text: 'Proveedor agregado y seleccionado.',
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                // Manejo de errores de validación (ej. RUC duplicado)
                let errors = xhr.responseJSON.errors;
                let msg = 'Ocurrió un error al guardar.';

                if (errors) {
                    msg = '';
                    $.each(errors, function(key, val) {
                        msg += val[0] + '\n';
                    });
                }

                Swal.fire('Error', msg, 'error');
            },
            complete: function() {
                // Desbloquear botón
                $btn.prop('disabled', false).text('Guardar Proveedor');
            }
        });
    });
</script>
@endsection