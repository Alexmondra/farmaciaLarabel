@section('js')
{{-- LIBRERIAS --}}
<script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // === CONFIGURACIÓN GLOBAL ===
    const RUTA_LOOKUP = "{{ route('inventario.medicamentos.lookup') }}";
    let itemIndex = 0;
    let timeoutBusqueda = null;
    let sufijoAleatorio = "";

    // Variables globales para conectar la tabla con los modales
    window.btnAccionActivo = null;
    window.inputSearchActivo = null;

    $(document).ready(function() {
        bsCustomFileInput.init();

        // Agregar la primera fila vacía al cargar
        agregarFilaItem();

        // 1. PROVEEDORES (Tu código existente)
        $('.select2-proveedor').select2({
            theme: 'bootstrap-5',
            placeholder: "-- Buscar Proveedor --",
            allowClear: true,
            width: '100%'
        });

        // ... (Mantén aquí tu lógica de botones de proveedor si la tienes) ...

        // ============================================================
        // 2. LÓGICA DE LA TABLA DE ITEMS (Buscador, Precios, Cálculos)
        // ============================================================

        // A. Cálculos al cambiar inputs (Cantidad, Precio, Unidad)
        $(document).on('input change', '.input-cantidad-visual, .input-precio-visual, .select-unidad-compra', function() {
            recalcularFila($(this).closest('tr'));
        });

        // B. Escribir en el Buscador
        $(document).on('input', '.input-medicamento-search', function() {
            let $input = $(this);
            let q = $input.val().trim();
            let $row = $input.closest('tr');

            // Reiniciamos índice de flechas
            $row.data('search-index', -1);

            clearTimeout(timeoutBusqueda);

            if (q.length === 0) {
                $row.find('.search-results').removeClass('active').empty();
                limpiarFila($row);
                return;
            }

            timeoutBusqueda = setTimeout(() => {
                $.get(RUTA_LOOKUP, {
                    q: q
                }, (data) => {
                    renderResultados(data.results, $row.find('.search-results'));
                });
            }, 200); // 200ms para que sea rápido
        });

        // C. Navegación con Teclado (Flechas y Enter) en el Buscador
        // ======================================================================
        // LOGICA DE TECLADO MEJORADA (SOLUCIÓN FLECHAS)
        // ======================================================================

        // 1. Manejo de Flechas y Enter en el Buscador
        $(document).on('keydown', '.input-medicamento-search', function(e) {
            let $input = $(this);
            let $row = $input.closest('tr');
            let $results = $row.find('.search-results');
            let $items = $results.find('.search-item');

            if (!$results.hasClass('active') || $items.length === 0) return;

            // CORRECCIÓN AQUÍ: Validamos explícitamente si es undefined
            let currentIndex = $row.data('search-index');
            if (typeof currentIndex === 'undefined') currentIndex = -1;

            // FLECHA ABAJO (40)
            if (e.which === 40) {
                e.preventDefault();
                currentIndex++;
                if (currentIndex >= $items.length) currentIndex = 0; // Vuelve al inicio
                highlightItem($items, currentIndex);
                $row.data('search-index', currentIndex);
            }
            // FLECHA ARRIBA (38)
            else if (e.which === 38) {
                e.preventDefault();
                currentIndex--;
                if (currentIndex < 0) currentIndex = $items.length - 1; // Va al final
                highlightItem($items, currentIndex);
                $row.data('search-index', currentIndex);
            }
            // ENTER (13)
            else if (e.which === 13) {
                e.preventDefault();
                if (currentIndex >= 0 && currentIndex < $items.length) {
                    $items.eq(currentIndex).click();
                } else {
                    // Si da enter sin seleccionar nada, selecciona el primero por defecto
                    $items.first().click();
                }
            }
            // ESCAPE (27)
            else if (e.which === 27) {
                $results.removeClass('active').empty();
                $row.data('search-index', -1);
            }
        });

        // Función para resaltar (Asegúrate de tenerla así)
        function highlightItem($items, index) {
            $items.removeClass('active-keyboard bg-light text-primary');
            if (index >= 0) {
                let $sel = $items.eq(index);
                $sel.addClass('active-keyboard bg-light text-primary');
                // Scroll automático para que siga la selección
                $sel[0].scrollIntoView({
                    block: 'nearest'
                });
            }
        }

        // 2. Función auxiliar para pintar (Agregada/Corregida)
        function pintarSeleccion($items, index, $row) {
            // Guardamos el índice en la fila para recordarlo
            $row.data('search-index', index);

            // Visual
            $items.removeClass('active-keyboard');
            let $selected = $items.eq(index);
            $selected.addClass('active-keyboard');

            // Scroll automático suave si la lista es larga
            $selected[0].scrollIntoView({
                block: 'nearest'
            });
        }

        // 3. Resetear índice al escribir (Para que empiece de nuevo)
        $(document).on('input', '.input-medicamento-search', function() {
            $(this).closest('tr').data('search-index', -1);
        });
        // D. Cerrar resultados al hacer clic fuera
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-container').length) {
                $('.search-results').removeClass('active').empty();
            }
        });

        // E. Eliminar Fila
        $(document).on('click', '.btn-eliminar-fila', function() {
            if ($('.fila-item').length > 1) {
                $(this).closest('tr').remove();
                recalcularTotalGeneral();
            } else {
                limpiarFila($(this).closest('tr'));
            }
        });

        // ============================================================
        // 3. LÓGICA DE MODALES (Crear/Editar)
        // ============================================================

        // Auto-código al escribir nombre (Nuevo)
        $('#formNuevoMedicamentoRapid input[name="nombre"]').on('input', function() {
            let texto = $(this).val().toUpperCase();
            let limpio = texto.replace(/[^A-Z0-9]/g, '');
            let prefijo = limpio.substring(0, 6) || "NEW";
            $('#crear_codigo').val(prefijo + '-' + Math.floor(Math.random() * 900 + 100));
        });

        // Abrir Modal (Botón Ojito/Más)
        $(document).on('click', '.btn-accion-med', function() {
            window.btnAccionActivo = $(this);
            let info = $(this).data('info');

            if (info) {
                // EDITAR: Llenar campos
                $('#edit_med_id').val(info.id);
                $('#edit_med_nombre').val(info.nombre);
                $('#edit_med_codigo').val(info.codigo);
                $('#edit_med_digemid').val(info.codigo_digemid);
                $('#edit_med_barra').val(info.codigo_barra);
                $('#edit_med_barra_blister').val(info.codigo_barra_blister);
                $('#edit_med_reg').val(info.registro_sanitario);
                $('#edit_med_forma').val(info.forma_farmaceutica);
                $('#edit_med_lab').val(info.laboratorio);
                $('#edit_med_pres').val(info.presentacion);
                $('#edit_med_conc').val(info.concentracion);
                $('#edit_med_unidades').val(info.unidades_por_envase);
                $('#edit_med_unidades_blister').val(info.unidades_por_blister);
                $('#edit_med_desc').val(info.descripcion);
                $('#edit_med_cat').val(info.categoria_id);
                $('#edit_med_igv').prop('checked', (info.afecto_igv == 1 || info.afecto_igv === true));

                // Imagen
                if (info.imagen_url) {
                    $('#img_med_foto_edit').attr('src', info.imagen_url).show();
                    $('#div_med_placeholder_edit').hide();
                } else {
                    $('#img_med_foto_edit').hide();
                    $('#div_med_placeholder_edit').show();
                }
                $('#modalVerMedicamento').modal('show');
            } else {
                // CREAR
                window.inputSearchActivo = $(this).closest('.input-group').find('.input-medicamento-search');
                $('#formNuevoMedicamentoRapid')[0].reset();
                $('#crear_med_igv').prop('checked', true);
                $('#crear_codigo').val('NEW-' + Math.floor(Math.random() * 900 + 100));
                $('#modalCrearMedicamento').modal('show');
                setTimeout(() => {
                    $('input[name="nombre"]').focus();
                }, 500);
            }
        });

        // Guardar Nuevo (Ajax)
        $('#formNuevoMedicamentoRapid').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('inventario.medicamentos.storeRapido') }}",
                method: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#modalCrearMedicamento').modal('hide');
                    if (window.inputSearchActivo) {
                        seleccionarItem({
                            full_data: res.data
                        }, window.inputSearchActivo[0]);
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Creado',
                        timer: 1000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
                }
            });
        });

        // Guardar Edición (Ajax)
        $('#formEditarMedicamento').on('submit', function(e) {
            e.preventDefault();
            let id = $('#edit_med_id').val();
            let formData = new FormData(this);
            formData.append('_method', 'PUT');

            $.ajax({
                url: "/inventario/medicamentos/" + id + "/update-rapido",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#modalVerMedicamento').modal('hide');
                    if (window.btnAccionActivo) {
                        let row = window.btnAccionActivo.closest('tr');
                        seleccionarItem({
                            full_data: res.data
                        }, row.find('.input-medicamento-search')[0]);
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        timer: 1000,
                        showConfirmButton: false
                    });
                }
            });
        });

        // Validación final Formulario
        $('#form-compra').on('submit', function(e) {
            if ($('.fila-item').length === 0) {
                e.preventDefault();
                Swal.fire('Error', 'Debe agregar al menos un producto.', 'error');
                return;
            }
            // Validar que se haya seleccionado medicamento
            let error = false;
            $('.input-medicamento-id').each(function() {
                if (!$(this).val()) error = true;
            });
            if (error) {
                e.preventDefault();
                Swal.fire('Atención', 'Complete la información de los productos seleccionados.', 'warning');
            }
        });
    });

    // ============================================================
    // 4. FUNCIONES GLOBALES (LÓGICA DEL NEGOCIO)
    // ============================================================

    // Renderiza la lista desplegable
    function renderResultados(data, $div) {
        $div.empty();
        if (!data.length) return $div.append('<div class="p-2 small text-muted">Sin resultados</div>');

        data.forEach(item => {
            let i = item.full_data;
            // Mostramos presentación para diferenciar
            let presentacion = i.presentacion ? `(${i.presentacion})` : '';
            $div.append(`
                <div class="search-item" onclick='seleccionarItem(${JSON.stringify(item)}, this)'>
                    <div class="d-flex justify-content-between">
                        <strong>${i.nombre} <small>${presentacion}</small></strong>
                        <span class="text-success font-weight-bold">Stock: ${i.stock_actual || 0}</span>
                    </div>
                    <small class="text-muted">${i.laboratorio || 'Generico'}</small>
                </div>
            `);
        });
        $div.addClass('active');
    }

    // Resaltar item con flechas
    function highlightItem($items, index) {
        $items.removeClass('bg-light text-primary'); // Limpiar estilo
        if (index >= 0) {
            let $sel = $items.eq(index);
            $sel.addClass('bg-light text-primary'); // Estilo seleccionado
            $sel[0].scrollIntoView({
                block: 'nearest'
            });
        }
    }

    // --- FUNCIÓN PRINCIPAL: SELECCIONAR MEDICAMENTO ---
    window.seleccionarItem = function(item, divOrElement) {
        let $row = $(divOrElement).closest('tr');
        let med = item.full_data;

        // 1. LLENAR DATOS Y OCULTAR LISTA
        $row.find('.input-medicamento-search').val(med.nombre);
        $row.find('.input-medicamento-id').val(med.id);

        // ¡AQUÍ ESTÁ LA SOLUCIÓN! Desaparecemos la lista inmediatamente
        $row.find('.search-results').removeClass('active').empty();

        // 2. AUTO-RELLENADO DE PRECIOS EXISTENTES
        let pvUnidad = parseFloat(med.precio_venta || 0);
        $row.find('.input-pv-unidad').val(pvUnidad > 0 ? pvUnidad.toFixed(2) : '');

        let pvBlister = parseFloat(med.precio_blister || 0);
        $row.find('.input-pv-blister').val(pvBlister > 0 ? pvBlister.toFixed(2) : '');

        let pvCaja = parseFloat(med.precio_caja || 0);
        $row.find('.input-pv-caja').val(pvCaja > 0 ? pvCaja.toFixed(2) : '');

        // 3. INTELIGENCIA DE CAMPOS (Bloquear lo que no sirve)
        configurarInputsPrecios($row, med);

        // 4. LOGÍSTICA (Selector de unidades)
        regenerarSelectorUnidades($row, med);

        // 5. Actualizar botón del modal (Ojito)
        actualizarBotonAccion($row, med);

        // 6. Foco a cantidad
        $row.find('.input-cantidad-visual').focus().select();
        recalcularFila($row);
    };

    // Bloquea/Desbloquea inputs según si tiene blister/caja
    function configurarInputsPrecios($row, med) {
        let factorBlister = parseInt(med.unidades_por_blister) || 0;
        let factorCaja = parseInt(med.unidades_por_envase) || 1;

        // BLÍSTER: Si es <= 1, no tiene sentido poner precio de blister
        let $inputBlister = $row.find('.input-pv-blister');
        if (factorBlister > 1) {
            $inputBlister.prop('readonly', false).attr('placeholder', '0.00').removeClass('bg-light');
        } else {
            $inputBlister.prop('readonly', true).val('').attr('placeholder', '-').addClass('bg-light');
        }

        // CAJA: Si la caja trae 1 unidad, el precio caja es igual al unitario (redundante)
        let $inputCaja = $row.find('.input-pv-caja');
        if (factorCaja > 1) {
            $inputCaja.prop('readonly', false).attr('placeholder', '0.00').removeClass('bg-light');
        } else {
            $inputCaja.prop('readonly', true).val('').attr('placeholder', '-').addClass('bg-light');
        }
    }

    // Genera el <select> solo con las opciones válidas
    function regenerarSelectorUnidades($row, med) {
        let $select = $row.find('.select-unidad-compra');
        $select.empty();

        // Unidad siempre existe
        $select.append('<option value="UNI" data-factor="1">UNIDAD</option>');

        // Caja solo si trae más de 1
        let factorCaja = parseInt(med.unidades_por_envase);
        if (factorCaja > 1) {
            $select.append(`<option value="CAJA" data-factor="${factorCaja}" selected>CAJA (x${factorCaja})</option>`);
        }

        // Blíster solo si trae más de 1
        let factorBlister = parseInt(med.unidades_por_blister);
        if (factorBlister > 1) {
            $select.append(`<option value="BLIS" data-factor="${factorBlister}">BLÍSTER (x${factorBlister})</option>`);
        }
    }

    window.recalcularFila = function(row) {
        row = $(row);

        // Entradas
        let cantidadVisual = parseFloat(row.find('.input-cantidad-visual').val()) || 0;
        let precioCompraVisual = parseFloat(row.find('.input-precio-visual').val()) || 0;
        let $option = row.find('.select-unidad-compra option:selected');
        let factor = parseInt($option.data('factor')) || 1;

        // Cálculos
        let totalUnidadesReales = cantidadVisual * factor;
        let costoUnitarioReal = (factor > 0) ? (precioCompraVisual / factor) : 0;
        let subtotalDinero = cantidadVisual * precioCompraVisual;

        // Salidas Visuales
        row.find('.lbl-info-conversion').text(`Entran: ${totalUnidadesReales} un.`);
        row.find('.subtotal-fila').text('S/ ' + subtotalDinero.toFixed(2));

        // Salidas Ocultas (BD)
        row.find('.input-cantidad-hidden').val(totalUnidadesReales);
        row.find('.input-precio-hidden').val(costoUnitarioReal.toFixed(4));

        recalcularTotalGeneral();
    }

    window.agregarFilaItem = function() {
        itemIndex++;
        let h = `
        <tr class="fila-item">
            <td class="align-middle text-center"><span class="badge bg-light text-dark border indice-fila">${$('.fila-item').length + 1}</span></td>
            <td>
                {{-- BUSCADOR --}}
                <div class="mb-2 search-container">
                    <span class="label-mini">MEDICAMENTO</span>
                    <div class="input-group">
                         <input type="text" class="form-control input-medicamento-search" placeholder="Escriba para buscar..." autocomplete="off">
                        <input type="hidden" name="items[${itemIndex}][medicamento_id]" class="input-medicamento-id">
                        <input type="hidden" name="items[${itemIndex}][cantidad_recibida]" class="input-cantidad-hidden" value="1">
                        <input type="hidden" name="items[${itemIndex}][precio_compra_unitario]" class="input-precio-hidden" value="0">
                        <button type="button" class="btn btn-outline-primary btn-addon-right btn-accion-med" title="Nuevo"><i class="fas fa-plus"></i></button>
                    </div>
                    <div class="search-results"></div>
                </div>
                {{-- LOTE --}}
                <div class="row g-2">
                    <div class="col-6"><span class="label-mini">LOTE</span><input type="text" name="items[${itemIndex}][codigo_lote]" class="input-modern text-uppercase input-lote"></div>
                    <div class="col-6"><span class="label-mini">VENCIMIENTO</span><input type="date" name="items[${itemIndex}][fecha_vencimiento]" class="input-modern input-fechaVenci"></div>
                </div>
            </td>
            
            {{-- LOGÍSTICA --}}
            <td>
                <div class="mb-2">
                    <span class="label-mini">CANTIDAD (A COMPRAR)</span>
                    <div class="d-flex">
                        <input type="number" class="input-modern text-center fw-bold input-cantidad-visual mr-1" value="1" style="width: 70px;">
                        <select name="items[${itemIndex}][unidad_compra_visual]" class="input-modern select-unidad-compra font-weight-bold" style="font-size: 0.85rem;">
                            <option value="UNI" data-factor="1">UNIDAD</option>
                        </select>
                    </div>
                    <div class="text-right mt-1">
                        <small class="text-muted font-italic lbl-info-conversion" style="font-size: 0.75rem;">Entran: 1 un.</small>
                    </div>
                </div>
                <div><span class="label-mini">UBICACIÓN</span><input type="text" name="items[${itemIndex}][ubicacion]" class="input-modern input-ubicacion"></div>
            </td>

            {{-- PRECIOS --}}
            <td>
                <div class="row g-2 mb-2 bg-light p-2 rounded mx-0">
                    <div class="col-6">
                        <span class="label-mini text-primary fw-bold">COSTO (X EMPAQUE)</span>
                        <input type="number" step="0.01" class="input-modern input-precio-visual text-primary fw-bold" value="0">
                    </div>
                    <div class="col-6">
                        <span class="label-mini text-danger fw-bold">OFERTA (UNIDAD)</span>
                        <input type="number" step="0.01" name="items[${itemIndex}][precio_oferta]" class="input-modern text-danger fw-bold input-oferta" placeholder="0.00">
                    </div>
                </div>

                <div class="row g-1">
                    <div class="col-4">
                        <span class="label-mini text-success">P.V. UNIDAD</span>
                        <input type="number" step="0.01" name="items[${itemIndex}][precio_venta]" class="input-modern input-pv-unidad" placeholder="0.00">
                    </div>
                    <div class="col-4">
                        <span class="label-mini text-info">P.V. BLÍSTER</span>
                        <input type="number" step="0.01" name="items[${itemIndex}][precio_venta_blister]" class="input-modern input-pv-blister bg-light" placeholder="-" readonly>
                    </div>
                    <div class="col-4">
                        <span class="label-mini text-dark">P.V. CAJA</span>
                        <input type="number" step="0.01" name="items[${itemIndex}][precio_venta_caja]" class="input-modern input-pv-caja bg-light" placeholder="-" readonly>
                    </div>
                </div>
            </td>

            <td class="text-end">
                <div class="subtotal-fila fw-bold" style="font-size: 1.1rem;">S/ 0.00</div>
                <button type="button" class="btn btn-outline-danger btn-sm border-0 btn-eliminar-fila mt-2"><i class="fas fa-trash-alt"></i></button>
            </td>
        </tr>`;

        $('#items-table-body').append(h);

        let $newRow = $('.fila-item').last();
        // Intentar dar foco solo si ya cargó el DOM
        setTimeout(() => $newRow.find('.input-medicamento-search').focus(), 50);
    }

    function actualizarBotonAccion($row, info) {
        let btn = $row.find('.btn-accion-med');
        let icon = btn.find('i');
        if (info) {
            btn.removeClass('btn-outline-primary').addClass('btn-outline-info').attr('title', 'Ver/Editar').data('info', info);
            icon.removeClass('fa-plus').addClass('fa-eye');
        } else {
            btn.removeClass('btn-outline-info').addClass('btn-outline-primary').attr('title', 'Nuevo').data('info', null);
            icon.removeClass('fa-eye').addClass('fa-plus');
        }
    }

    function limpiarFila($row) {
        $row.find('input').not('.btn-accion-med').val('');
        $row.find('.input-cantidad-visual').val(1);
        $row.find('.select-unidad-compra').html('<option value="UNI" data-factor="1">UNIDAD</option>');
        $row.find('.lbl-info-conversion').text('Entran: 1 un.');
        $row.find('.input-pv-blister, .input-pv-caja').prop('readonly', true).addClass('bg-light').val('');
        actualizarBotonAccion($row, null);
    }

    function recalcularTotalGeneral() {
        let t = 0;
        $('.subtotal-fila').each((i, el) => t += parseFloat($(el).text().replace('S/', '')) || 0);
        $('#total-general-fijo').text('S/ ' + t.toFixed(2));
        $('.indice-fila').each((i, el) => $(el).text(i + 1));
    }

    window.previewImage = function(input, imgSelector, placeholderSelector) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $(imgSelector).attr('src', e.target.result).show();
                $(placeholderSelector).hide();
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection