<script>
    $(document).ready(function() {

        // ==========================================
        // 0. CONFIGURACIÓN Y VARIABLES GLOBALES
        // ==========================================
        const RUTA_LOOKUP_MEDICAMENTOS = "{{ route('ventas.lookup_medicamentos') }}";
        const RUTA_LOOKUP_LOTES = "{{ route('ventas.lookup_lotes') }}";
        const RUTA_CHECK_CLIENTE = "{{ route('clientes.check') }}";
        const sucursalId = $('#sucursal_id').val();

        // --- VALOR DEL PUNTO DESDE CONFIGURACIÓN ---
        // Si no existe la variable, usamos 0.02 por defecto
        let valorPuntoActual = parseFloat("{{ $config->valor_punto_canje ?? 0.02 }}");

        // Variables de estado
        let selectedIndex = -1;
        let resultCount = 0;
        let carrito = {};
        let medicamentoSeleccionado = null;
        let timeoutBusqueda = null;

        // ==========================================
        // 1. BUSCADOR MEDICAMENTOS & LOTES
        // ==========================================
        let peticionActual = null;

        window.buscarMedicamentos = function() {
            let q = $('#busqueda_medicamento').val().trim();
            let categoriaId = $('#filtro_categoria_id').val();

            if (q.length === 0 && !categoriaId) {
                cerrarResultados();
                return;
            }
            if (peticionActual) {
                peticionActual.abort();
            }

            peticionActual = $.ajax({
                url: RUTA_LOOKUP_MEDICAMENTOS,
                method: 'GET',
                data: {
                    sucursal_id: sucursalId,
                    q: q,
                    categoria_id: categoriaId
                },
                success: function(data) {
                    renderResultadosMedicamentos(data);
                },
                error: function(xhr) {
                    if (xhr.statusText !== 'abort') {
                        console.error("Error en búsqueda:", xhr);
                    }
                },
                complete: function() {
                    peticionActual = null;
                }
            });
        }

        function renderResultadosMedicamentos(lista) {
            let contenedor = $('#resultados-medicamentos');
            let term = $('#busqueda_medicamento').val().trim(); // Capturamos lo que escribió
            // Verificamos si es un código numérico largo (escáner)
            let esCodigoBarra = (/^\d+$/.test(term) && term.length >= 5);

            // Si hay un único resultado exacto y está asignado a la sucursal
            if (esCodigoBarra && lista.length === 1 && lista[0].asignado === true) {
                let m = lista[0];

                cerrarResultados();
                $('#busqueda_medicamento').val('');

                medicamentoSeleccionado = {
                    medicamento_id: m.medicamento_id,
                    nombre: m.nombre,
                    presentacion: m.presentacion,
                    precio_venta: parseFloat(m.precio_venta)
                };
                $('#modal-medicamento-nombre').text(medicamentoSeleccionado.nombre);
                $('#modal-medicamento-presentacion').text(medicamentoSeleccionado.presentacion);

                // Cargamos lotes y abrimos el modal de inmediato
                cargarLotesMedicamento(m.medicamento_id);
                $('#modalLotes').modal('show');

                return;
            }
            selectedIndex = -1;
            resultCount = lista.length;

            // --- CAMBIO PRINCIPAL AQUÍ ---
            if (!lista.length) {
                // Si no hay resultados, mostramos el botón de CREAR RÁPIDO
                let htmlNoResult = `
                    <div class="list-group-item text-center py-3">
                        <p class="text-muted mb-2"><i class="fas fa-search"></i> No se encontró "<b>${term}</b>"</p>
                        <button type="button" class="btn btn-outline-primary font-weight-bold shadow-sm" 
                                onclick="abrirModalCrearRapido('${term}')">
                            <i class="fas fa-plus-circle mr-1"></i> REGISTRAR NUEVO
                        </button>
                    </div>
                `;
                contenedor.html(htmlNoResult).addClass('active');
                return;
            }
            // -----------------------------

            let html = lista.map(m => {
                let extraHtml = '';
                let claseExtra = '';

                if (m.asignado === false) {
                    claseExtra = 'bg-light';
                    extraHtml = `
                    <div class="mt-1" style="line-height: 1.2;">
                        <div class="text-danger font-weight-bold" style="font-size: 0.75rem;">
                            <i class="fas fa-exclamation-circle"></i> NO ASIGNADO A SUCURSAL
                        </div>
                        <div class="text-muted border-top mt-1 pt-1" style="font-size: 0.7rem; font-style: italic; width: 100%;">
                            <i class="fas fa-arrow-right mr-1"></i> Se asignará automáticamente a esta sucursal al registrar.
                        </div>
                    </div>`;
                }

                let precioHtml = '';
                if (m.es_precio_sugerido) {
                    precioHtml = `<span class="badge badge-info border" title="Precio Sugerido de otra tienda">S/ ${parseFloat(m.precio_venta).toFixed(2)} <i class="fas fa-info-circle small"></i></span>`;
                } else {
                    precioHtml = `<span class="badge badge-light border">S/ ${parseFloat(m.precio_venta).toFixed(2)}</span>`;
                }

                return `
                <button type="button" class="list-group-item list-group-item-action resultado-medicamento py-2 px-2 ${claseExtra}" 
                        data-medicamento-id="${m.medicamento_id}" 
                        data-nombre="${m.nombre}" 
                        data-presentacion="${m.presentacion || ''}" 
                        data-precio="${m.precio_venta}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis; max-width: 70%;">
                            <strong>${m.nombre}</strong> <small class="text-muted">(${m.presentacion || ''})</small>
                            ${extraHtml} 
                        </div>
                        <div>
                            ${precioHtml}
                        </div>
                    </div>
                </button>
            `
            }).join('');

            contenedor.html(html).addClass('active');
        }

        window.cerrarResultados = function() {
            $('#resultados-medicamentos').removeClass('active').empty();
            selectedIndex = -1;
            resultCount = 0;
        }

        function cerrarResultados() {
            $('#resultados-medicamentos').removeClass('active').empty();
            selectedIndex = -1;
            resultCount = 0;
        }

        $('#busqueda_medicamento').on('keydown', function(e) {
            let $resultados = $('#resultados-medicamentos');
            if (!$resultados.hasClass('active') || resultCount === 0) return;
            if (e.which === 40) { // Abajo
                e.preventDefault();
                selectedIndex++;
                if (selectedIndex >= resultCount) selectedIndex = 0;
                highlightItem();
            } else if (e.which === 38) { // Arriba
                e.preventDefault();
                selectedIndex--;
                if (selectedIndex < 0) selectedIndex = resultCount - 1;
                highlightItem();
            } else if (e.which === 13) { // Enter
                e.preventDefault();
                if (selectedIndex > -1) {
                    $resultados.find('.resultado-medicamento').eq(selectedIndex).click();
                }
            }
        });

        $('#busqueda_medicamento').on('input', function() {
            clearTimeout(timeoutBusqueda);
            timeoutBusqueda = setTimeout(() => buscarMedicamentos(), 250);
        });

        function highlightItem() {
            let items = $('#resultados-medicamentos').find('.resultado-medicamento');
            items.removeClass('active-key');
            if (selectedIndex > -1) {
                let activeItem = items.eq(selectedIndex);
                activeItem.addClass('active-key');
                activeItem[0].scrollIntoView({
                    block: 'nearest'
                });
            }
        }

        $(document).on('click', '.resultado-medicamento', function() {
            let btn = $(this);
            cerrarResultados();
            $('#busqueda_medicamento').val('');
            medicamentoSeleccionado = {
                medicamento_id: btn.data('medicamento-id'),
                nombre: btn.data('nombre'),
                presentacion: btn.data('presentacion'),
                precio_venta: parseFloat(btn.data('precio'))
            };
            $('#modal-medicamento-nombre').text(medicamentoSeleccionado.nombre);
            $('#modal-medicamento-presentacion').text(medicamentoSeleccionado.presentacion);
            cargarLotesMedicamento(medicamentoSeleccionado.medicamento_id);
            $('#modalLotes').modal('show');
        });

        $('#modalLotes').on('shown.bs.modal', function() {
            let primerInput = $('#modal-lotes-tbody').find('.input-cant-lote').first();
            if (primerInput.length) primerInput.focus().select();
        });

        function cargarLotesMedicamento(id) {
            $.ajax({
                url: RUTA_LOOKUP_LOTES,
                method: 'GET',
                data: {
                    medicamento_id: id,
                    sucursal_id: sucursalId
                },
                success: function(lotes) {
                    let tbody = $('#modal-lotes-tbody').empty();
                    if (!lotes.length) {
                        tbody.append('<tr><td colspan="7" class="text-center text-danger">SIN STOCK DISPONIBLE</td></tr>');
                        return;
                    }

                    lotes.forEach(l => {
                        let tieneOfertaUnidad = l.precio_oferta > 0;
                        let precioUnidadFinal = tieneOfertaUnidad ? l.precio_oferta : l.precios.unidad;

                        // A. Guardamos el HTML del precio de la unidad en un data-attribute para restaurarlo luego
                        let htmlPrecioUnidad = tieneOfertaUnidad ?
                            `<div class="d-flex flex-column align-items-end" style="line-height: 1.2;">
                        <small class="text-muted" style="text-decoration: line-through; font-size: 0.7rem; opacity: 0.7;">S/ ${l.precios.unidad.toFixed(2)}</small>
                        <span style="color: #ff7e67; font-size: 1rem; font-weight: 800;">S/ ${precioUnidadFinal.toFixed(2)}</span>
                    </div>` :
                            `<span style="color: #28a745;">S/ ${l.precios.unidad.toFixed(2)}</span>`;

                        let options = `<option value="UNIDAD" data-precio="${precioUnidadFinal}" data-factor="1" data-html-precio='${htmlPrecioUnidad}'>UNIDAD</option>`;

                        if (l.factores.blister > 1 && l.precios.blister > 0) {
                            options += `<option value="BLISTER" data-precio="${l.precios.blister}" data-factor="${l.factores.blister}" data-html-precio='<span style="color: #28a745;">S/ ${l.precios.blister.toFixed(2)}</span>'>BLISTER (x${l.factores.blister})</option>`;
                        }

                        if (l.factores.caja > 1 && l.precios.caja > 0) {
                            options += `<option value="CAJA" data-precio="${l.precios.caja}" data-factor="${l.factores.caja}" data-html-precio='<span style="color: #28a745;">S/ ${l.precios.caja.toFixed(2)}</span>'>CAJA (x${l.factores.caja})</option>`;
                        }

                        let badgeOferta = tieneOfertaUnidad ?
                            `<br><span class="badge" style="font-size: 0.6rem; background-color: #fd7e14; color: white; border: 1px solid #ffc107;">
                        <i class="fas fa-bolt"></i> OFERTA UNID.
                    </span>` : '';

                        let styleFila = tieneOfertaUnidad ? 'style="background-color: rgba(255, 193, 7, 0.05); border-left: 3px solid #fd7e14;"' : '';

                        tbody.append(`
                    <tr data-lote-id="${l.id}" data-stock="${l.stock_actual}" ${styleFila}>
                        <td class="align-middle small font-weight-bold">${l.codigo_lote} ${badgeOferta}</td>
                        <td class="align-middle small text-nowrap">${l.fecha_vencimiento || '-'}</td>
                        <td class="align-middle text-center small text-info"><i class="fas fa-map-marker-alt mr-1"></i>${l.ubicacion || '-'}</td>
                        <td class="text-center font-weight-bold align-middle text-primary" style="font-size: 1rem;">${l.stock_actual}</td>
                        <td class="align-middle">
                            <select class="form-control form-control-sm select-presentacion font-weight-bold" style="font-size: 0.85rem; background-color: transparent;">
                                ${options}
                            </select>
                        </td>
                        <td class="align-middle">
                            <input type="number" class="form-control form-control-sm input-cant-lote text-center font-weight-bold" min="1" value="1" style="background-color: transparent;">
                        </td>
                        <td class="align-middle text-right font-weight-bold cell-precio">
                            ${htmlPrecioUnidad}
                        </td>
                        <td class="align-middle text-center">
                            <button type="button" class="btn btn-sm ${tieneOfertaUnidad ? 'btn-warning' : 'btn-success'} btn-agregar-lote">
                                <i class="fas fa-plus"></i>
                            </button>
                        </td>
                    </tr>
                `);
                    });
                }
            });
        }

        // NUEVO EVENTO CHANGE: Recupera el HTML guardado en la opción seleccionada
        $(document).on('change', '.select-presentacion', function() {
            let row = $(this).closest('tr');
            let option = $(this).find(':selected');
            let htmlPredefinido = option.data('html-precio'); // Recuperamos el diseño completo

            row.find('.cell-precio').html(htmlPredefinido);
        });

        // ==========================================
        // 2. CARRITO Y TABLA
        // ==========================================
        $(document).on('click', '.btn-agregar-lote', function() {
            let row = $(this).closest('tr');
            let loteId = row.data('lote-id');
            let stockReal = parseInt(row.data('stock')); // Stock en unidades

            // Datos de la selección
            let select = row.find('.select-presentacion option:selected');
            let tipo = select.val(); // UNIDAD, BLISTER, CAJA
            let precio = parseFloat(select.data('precio'));
            let factor = parseInt(select.data('factor')); // Por cuántas unidades multiplicamos

            let cantidad = parseInt(row.find('.input-cant-lote').val()) || 0;

            if (cantidad <= 0) return toastr.error('Cantidad inválida.');


            let totalRequerido = cantidad * factor;
            if (totalRequerido > stockReal) {
                Swal.fire({
                    icon: 'warning', // Icono Amarillo
                    title: 'VENDIENDO FUERA DE STOCK',
                    html: `Stock: <b>${stockReal}</b> | Solicitado: <b>${totalRequerido}</b>`,
                    position: 'center',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                });
            }

            let uniqueId = loteId + '-' + tipo;

            if (carrito[uniqueId]) {
                carrito[uniqueId].cantidad += cantidad;
            } else {
                carrito[uniqueId] = {
                    unique_id: uniqueId,
                    unidad_medida: tipo,

                    lote_id: loteId,
                    nombre: medicamentoSeleccionado.nombre,
                    presentacion: `${medicamentoSeleccionado.presentacion} [${tipo}]`,
                    codigo_lote: row.find('.data-codigo-lote').text(),

                    cantidad: cantidad,
                    precio_venta: precio,

                    stock_max: Math.floor(stockReal / factor),
                    factor: factor
                };
            }

            renderCarrito();
            $('#modalLotes').modal('hide');
            $('#busqueda_medicamento').val('').focus();
        });

        $(document).on('keydown', '.input-cant-lote', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).closest('tr').find('.btn-agregar-lote').click();
            }
        });

        function renderCarrito() {
            let tbody = $('#carrito-tbody').empty();
            let total = 0;
            let items = Object.values(carrito);

            if (items.length === 0) {
                tbody.html('<tr id="carrito-vacio"><td colspan="5" class="text-center text-muted py-5"><div class="opacity-50"><i class="fas fa-shopping-basket fa-3x mb-3"></i><br>Carrito vacío</div></td></tr>');
                actualizarTotalGlobal(0);
                return;
            }

            items.forEach(i => {
                let subtotal = i.cantidad * i.precio_venta;
                total += subtotal;

                tbody.append(`
                    <tr data-unique-id="${i.unique_id || (i.lote_id + '-' + i.unidad_medida)}">
                        <td class="align-middle">
                            <span class="font-weight-bold text-dark">${i.nombre}</span><br>
                            <small class="text-muted">${i.presentacion_visual || i.presentacion} | Lote: ${i.codigo_lote}</small>
                        </td>
                        
                        <td class="align-middle text-center"> 
                            <div class="d-flex align-items-center justify-content-center">
                                
                                <input type="number" 
                                    class="form-control form-control-sm input-edit-cant font-weight-bold" 
                                    value="${i.cantidad}" 
                                    min="1"
                                    style="width: 60px;"> <button type="button" 
                                    class="btn btn-xs btn-warning ml-1 btn-warning-stock" 
                                    style="display: none; border-radius: 50%; width: 22px; height: 22px; padding: 0;"
                                    data-stock="${i.stock_max}">
                                    <i class="fas fa-exclamation" style="font-size: 0.7rem;"></i>
                                </button>

                            </div>
                        </td>
                        
                        <td class="align-middle text-center"> 
                            <input type="number" 
                                class="form-control form-control-sm input-edit-precio" 
                                value="${i.precio_venta.toFixed(2)}" 
                                step="0.01" 
                                min="0">
                        </td>
                        
                        <td class="align-middle text-right font-weight-bold text-success td-subtotal">S/ ${subtotal.toFixed(2)}</td>
                        <td class="align-middle text-center">
                            <button type="button" class="btn btn-xs btn-outline-danger btn-eliminar-item">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
            actualizarTotalGlobal(total);
        }

        // --- Helpers Edición Carrito ---
        function normalizarNumero(val) {
            if (val === null || val === undefined) return 0;
            return parseFloat(String(val).replace(',', '.')) || 0;
        }

        function refrescarSubtotalFila($row, item) {
            const subtotal = item.cantidad * item.precio_venta;
            $row.find('.td-subtotal').text('S/ ' + subtotal.toFixed(2));
        }

        $(document).on('input blur change', '.input-edit-cant', function(e) {
            const $row = $(this).closest('tr');
            const uniqueId = $row.data('unique-id');
            const item = carrito[uniqueId];
            if (!item) return;

            let val = $(this).val();
            if (e.type === 'blur' || e.type === 'change') {
                if (val === '' || isNaN(val) || parseInt(val) < 1) {
                    val = 1;
                    $(this).val(1);
                }
            }
            let cant = parseInt(val);
            if (cant < 0) cant = 1;
            if (isNaN(cant)) cant = 1;

            let $btnWarning = $row.find('.btn-warning-stock');

            if (cant > item.stock_max) {
                $(this).addClass('is-invalid');
                $btnWarning.show();
            } else {
                $(this).removeClass('is-invalid');
                $btnWarning.hide();
            }

            item.cantidad = cant;
            refrescarSubtotalFila($row, item);
            recalcularTotalDesdeMemoria();
        });

        $(document).on('click', '.btn-warning-stock', function() {
            let stockReal = $(this).data('stock');

            Swal.fire({
                icon: 'warning',
                title: 'Stock Insuficiente',
                text: `Estás sobregirando el stock actual. (Tienes ${stockReal} disponibles)`,
                confirmButtonColor: '#f39c12',
                confirmButtonText: 'Entendido'
            });
        });

        $(document).on('input blur change', '.input-edit-precio', function(e) {
            const $row = $(this).closest('tr');
            const uniqueId = $row.data('unique-id');
            const item = carrito[uniqueId];
            if (!item) return;

            let val = $(this).val();

            if (e.type === 'blur' || e.type === 'change') {
                if (val === '' || isNaN(val) || parseFloat(val) < 0) {
                    val = 0;
                    $(this).val('0.00');
                } else {
                    $(this).val(parseFloat(val).toFixed(2));
                }
            }

            let precio = normalizarNumero(val);
            if (precio < 0) {
                precio = 0;
                $(this).val(0);
            }

            item.precio_venta = precio;
            refrescarSubtotalFila($row, item);
            recalcularTotalDesdeMemoria();
        });

        $(document).on('click', '.btn-eliminar-item', function() {
            let row = $(this).closest('tr');
            let uniqueId = row.data('unique-id');
            let item = carrito[uniqueId];

            Swal.fire({
                title: '¿Quitar del carrito?',
                text: `Se eliminará: ${item.nombre} [${item.presentacion}]`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, quitar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    delete carrito[uniqueId];
                    renderCarrito();

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Eliminado'
                    });
                }
            });
        });

        function recalcularTotalDesdeMemoria() {
            let total = 0;
            Object.values(carrito).forEach(i => total += (i.cantidad * i.precio_venta));
            actualizarTotalGlobal(total);
        }

        // ==========================================
        // 2.5 CALCULO TOTAL CON PUNTOS
        // ==========================================
        function actualizarTotalGlobal(total) {
            // 1. Obtener descuento activo (si hay)
            let descuento = parseFloat($('#descuento-aplicado-soles').val()) || 0;

            // Seguridad: Si el descuento es mayor al total, lo ajustamos
            if (descuento > total) {
                descuento = total;
            }

            let totalFinal = total - descuento;

            // 2. Pintar en el cuadro verde
            if (descuento > 0 && total > 0) {
                $('#total-venta').html(`
                    <span class="original-price-strike">S/ ${total.toFixed(2)}</span><br>
                    ${totalFinal.toFixed(2)}
                `);
            } else {
                $('#total-venta').text(total.toFixed(2));
            }

            $('#input-items-json').val(JSON.stringify(Object.values(carrito)));

            // Recalcular vuelto con el NUEVO total
            if (window.calcularVuelto) window.calcularVuelto(totalFinal);
            if (typeof validarBotonConfirmar === 'function') validarBotonConfirmar(totalFinal);
        }

        // ==========================================
        // 3. LOGICA BUSCADOR CLIENTES
        // ==========================================
        const $tipo = $('#tipo_comprobante');
        const $input = $('#busqueda_cliente');
        const $display = $('#nombre_cliente_display');
        const $hidden = $('#cliente_id_hidden');

        // Sincronizar cliente_id para el backend
        function syncClienteIdBackend(val) {
            const $form = $('#form-venta');
            if (!$form.length) return;

            let $field = $form.find('input[name="cliente_id"]');
            if (!$field.length) {
                $field = $('<input>', {
                    type: 'hidden',
                    name: 'cliente_id'
                }).appendTo($form);
            }
            $field.val(val || '');
        }

        $tipo.on('change', function() {
            let isFactura = ($(this).val() === 'FACTURA');
            let max = isFactura ? 11 : 8;
            $('#label-documento').text(isFactura ? 'RUC' : 'DNI');
            $input.attr({
                placeholder: max + ' dígitos',
                maxlength: max
            }).val('').removeClass('is-invalid border-primary').focus();
            resetCliente();
        });

        $input.on('input', function() {
            this.value = this.value.replace(/\D/g, '');
            let val = $(this).val();
            let req = ($tipo.val() === 'FACTURA') ? 11 : 8;
            if (val.length === req) buscarCliente(val);
            else if ($hidden.val()) resetCliente();
        });

        function buscarCliente(doc) {
            $('#loader-cliente').removeClass('d-none');
            $input.addClass('border-primary');
            $.get(RUTA_CHECK_CLIENTE, {
                    doc: doc
                })
                .done(res => {
                    if (res.exists) {
                        // Si el backend devuelve configuración de puntos, actualizar
                        if (res.config && res.config.valor_punto) {
                            valorPuntoActual = parseFloat(res.config.valor_punto);
                        }
                        selectCliente(res.data);
                    } else {
                        showCreateOption();
                    }
                })
                .always(() => {
                    $('#loader-cliente').addClass('d-none');
                    $input.removeClass('border-primary');
                });
        }

        function selectCliente(data) {
            $hidden.val(data.id);
            syncClienteIdBackend(data.id);
            let nombre = (data.tipo_documento === 'RUC') ? data.razon_social : `${data.nombre} ${data.apellidos}`;
            $display.val(nombre).removeClass('text-danger').addClass('text-primary font-weight-bold');
            $('#btn-crear-cliente').addClass('d-none');
            $('#btn-ver-cliente').removeClass('d-none');

            // --- LÓGICA DE PUNTOS ---
            if (data.puntos && data.puntos > 0) {
                $('#panel-canje-puntos').slideDown();
                $('#lbl-puntos-total').text(data.puntos);

                // Configurar input
                $('#input-puntos-usar').val(0).attr('max', data.puntos);
                $('#lbl-equivalencia-dinero').text('0.00');

                // Resetear estado del botón
                $('#descuento-aplicado-soles').val(0);
                $('#btn-aplicar-puntos').removeClass('btn-info').addClass('btn-outline-info').html('<i class="fas fa-tag mr-1"></i> APLICAR DESCUENTO');
            } else {
                $('#panel-canje-puntos').slideUp();
            }
            recalcularTotalDesdeMemoria();
        }

        function showCreateOption() {
            resetCliente();
            $display.val('NO REGISTRADO (Crear Nuevo)').addClass('text-danger');
            $('#btn-crear-cliente').removeClass('d-none');
        }

        function resetCliente() {
            $hidden.val('');
            syncClienteIdBackend('');
            $display.val('--- Cliente General ---').removeClass('text-primary text-danger font-weight-bold');
            $('#btn-crear-cliente, #btn-ver-cliente').addClass('d-none');

            // --- OCULTAR PANEL PUNTOS ---
            $('#panel-canje-puntos').slideUp();
            $('#descuento-aplicado-soles').val(0);
            recalcularTotalDesdeMemoria();
        }

        $('#btn-crear-cliente').click(function() {
            if (window.openCreateModal) {
                window.openCreateModal();
                setTimeout(() => {
                    let doc = $input.val();
                    let tipo = $tipo.val() === 'FACTURA' ? 'RUC' : 'DNI';
                    $('#tipo_documento').val(tipo).trigger('change');
                    $('#documento').val(doc).trigger('input');
                }, 200);
            }
        });

        $('#btn-ver-cliente').click(function() {
            let id = $hidden.val();
            if (id && window.openShowModal) window.openShowModal(id);
        });

        window.reloadTable = function() {
            let nuevoDoc = $('#documento').val();
            if (nuevoDoc) {
                $input.val(nuevoDoc);
                buscarCliente(nuevoDoc);
            }
        };
        $tipo.trigger('change');

        // ==========================================
        // 3.5 CALCULADORA DE PUNTOS
        // ==========================================

        // A. Al escribir puntos, calcular dinero automáticamente
        $('#input-puntos-usar').on('input', function() {
            let max = parseInt($(this).attr('max'));
            let val = parseInt($(this).val());

            if (isNaN(val) || val < 0) val = 0;

            if (val > max) {
                val = max;
                $(this).val(max);
            }

            // USAMOS LA VARIABLE DINÁMICA 'valorPuntoActual'
            let dinero = (val * valorPuntoActual).toFixed(2);

            $('#lbl-equivalencia-dinero').text(dinero);
        });

        // B. Botón "Aplicar Descuento"
        $('#btn-aplicar-puntos').click(function() {
            let dinero = parseFloat($('#lbl-equivalencia-dinero').text());
            let puntos = $('#input-puntos-usar').val();

            if (dinero > 0) {
                // Guardamos el descuento en el input oculto
                $('#descuento-aplicado-soles').val(dinero);

                // Efecto visual botón
                $(this).removeClass('btn-outline-info').addClass('btn-info').html(`<i class="fas fa-check mr-1"></i> DESCUENTO DE S/ ${dinero} APLICADO`);

                if (typeof toastr !== 'undefined') toastr.success(`Descuento de S/ ${dinero} aplicado.`);
            } else {
                $('#descuento-aplicado-soles').val(0);
                $(this).removeClass('btn-info').addClass('btn-outline-info').html('<i class="fas fa-tag mr-1"></i> APLICAR DESCUENTO');
            }

            // Recalculamos el total global para que se tache el precio
            recalcularTotalDesdeMemoria();
        });


        // ==========================================
        // 4. LOGICA COBRO, VUELTO Y REFERENCIA
        // ==========================================

        // A. Función para validar si el botón se activa o no
        function validarBotonConfirmar(totalVenta) {
            let medio = $('#medio_pago').val();
            let btn = $('#btn-confirmar-venta');

            // 1. Si no hay venta, bloqueado siempre
            if (totalVenta <= 0) {
                btn.prop('disabled', true).removeClass('btn-light').addClass('btn-secondary');
                return;
            }

            // 2. Reglas según medio de pago
            if (medio === 'EFECTIVO') {
                let pagaCon = parseFloat($('#input-paga-con').val()) || 0;

                // Tolerancia de 0.01 céntimos
                if (pagaCon >= (totalVenta - 0.01)) {
                    btn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-light');
                } else {
                    btn.prop('disabled', true).removeClass('btn-light').addClass('btn-secondary');
                }
            } else {
                // TARJETA/YAPE/PLIN -> SIEMPRE ACTIVO
                btn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-light');
            }
        }

        // B. Evento: Cuando cambias entre Efectivo / Yape / Tarjeta
        $('#medio_pago').change(function() {
            let metodo = $(this).val();
            let totalFinal = obtenerTotalActual();

            if (metodo === 'EFECTIVO') {
                $('#bloque-calculadora').slideDown();
                $('#bloque-referencia').slideUp();
                $('#input-paga-con').focus();
                $('#referencia_pago').val('');
            } else {
                $('#bloque-calculadora').slideUp();
                $('#bloque-referencia').slideDown();
                $('#referencia_pago').focus();
                $('#input-paga-con').val('');
                $('#txt-vuelto').text('0.00');
            }

            validarBotonConfirmar(totalFinal);
        });

        // C. Cuando escribes el dinero
        $('#input-paga-con').on('input', function() {
            let totalFinal = obtenerTotalActual();
            calcularVuelto(totalFinal);
            validarBotonConfirmar(totalFinal);
        });

        // D. Función Visual: Calcular Vuelto
        window.calcularVuelto = function(totalVenta) {
            if ($('#medio_pago').val() !== 'EFECTIVO') return;

            let pagaCon = parseFloat($('#input-paga-con').val()) || 0;
            let vuelto = pagaCon - totalVenta;
            let elVuelto = $('#txt-vuelto');

            if (vuelto < -0.01) {
                elVuelto.text('Falta dinero');
                elVuelto.parent().removeClass('text-success').addClass('text-danger');
            } else {
                elVuelto.text(vuelto.toFixed(2));
                elVuelto.parent().removeClass('text-danger').addClass('text-success');
            }
        };

        // E. Auxiliar para obtener total actual
        function obtenerTotalActual() {
            let totalCarrito = 0;
            Object.values(carrito).forEach(i => totalCarrito += (i.cantidad * i.precio_venta));
            let descuento = parseFloat($('#descuento-aplicado-soles').val()) || 0;
            if (descuento > totalCarrito) descuento = totalCarrito;
            return totalCarrito - descuento;
        }

        // ==========================================
        // 5. CATEGORIAS TECLADO
        // ==========================================
        const categorias = window.listadoCategorias || [];
        let catSelectedIndex = -1;
        let catResultCount = 0;

        $('#busqueda_categoria').on('input focus', function() {
            let txt = $(this).val().toLowerCase();
            let cont = $('#resultados-categorias').empty();
            catSelectedIndex = -1;
            if (!categorias.length) return;
            let match = categorias.filter(c => c.nombre.toLowerCase().includes(txt));
            catResultCount = match.length;
            if (!match.length) {
                cont.hide();
                return;
            }
            match.forEach(c => {
                cont.append(`<button type="button" class="list-group-item list-group-item-action py-1 px-2 item-categoria" data-id="${c.id}" data-nombre="${c.nombre}">${c.nombre}</button>`);
            });
            cont.show();
        });

        $('#busqueda_categoria').on('keydown', function(e) {
            let $lista = $('#resultados-categorias');
            if (!$lista.is(':visible') || catResultCount === 0) return;
            if (e.which === 40) {
                e.preventDefault();
                catSelectedIndex++;
                if (catSelectedIndex >= catResultCount) catSelectedIndex = 0;
                highlightCategoria();
            } else if (e.which === 38) {
                e.preventDefault();
                catSelectedIndex--;
                if (catSelectedIndex < 0) catSelectedIndex = catResultCount - 1;
                highlightCategoria();
            } else if (e.which === 13) {
                e.preventDefault();
                if (catSelectedIndex > -1) {
                    $lista.find('.item-categoria').eq(catSelectedIndex).click();
                }
            }
        });

        function highlightCategoria() {
            let items = $('#resultados-categorias').find('.item-categoria');
            items.removeClass('active-key');
            if (catSelectedIndex > -1) {
                let actual = items.eq(catSelectedIndex);
                actual.addClass('active-key');
                actual[0].scrollIntoView({
                    block: 'nearest'
                });
            }
        }

        $(document).on('click', '.item-categoria', function() {
            $('#filtro_categoria_id').val($(this).data('id'));
            $('#busqueda_categoria').val($(this).data('nombre'));
            $('#resultados-categorias').hide();
            $('#btn-limpiar-cat').show();
            $('#busqueda_medicamento').focus();
            buscarMedicamentos();
        });

        $('#btn-limpiar-cat').click(function() {
            $('#filtro_categoria_id').val('');
            $('#busqueda_categoria').val('').focus();
            $(this).hide();
            buscarMedicamentos();
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-container').length) cerrarResultados();
            if (!$(e.target).closest('.search-container-cat').length) $('#resultados-categorias').hide();
        });

        // =======================================================
        // 6. FUNCIONES DE MODALES (CLIENTE)
        // =======================================================

        window.openCreateModal = function() {
            $('#formCliente')[0].reset();
            resetFormState();
            $('.input-future').removeClass('is-invalid bg-light').prop('readonly', false);
            $('#cliente_id').val('');
            $('#modalTitulo').html('<span style="color: #00d2d3;">●</span> Nuevo Cliente');
            toggleDetailsPanel(false);
            $('#modalCliente').modal('show');
        }

        window.verifyDocument = function(doc) {
            const tipo = $('#tipo_documento').val();
            const requiredLen = (tipo === 'RUC') ? 11 : 8;

            if (doc.length === requiredLen) {
                $('#documento').addClass('is-loading');
                $.get("{{ route('clientes.check') }}", {
                        doc: doc
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
            let msg = `<div id="doc-error" class="text-danger small font-weight-bold mt-1"><i class="fas fa-exclamation-circle"></i> Registrado como: ${nombre}</div>`;
            $('#doc-error').length ? $('#doc-error').html(msg) : $('#documento').parent().after(msg);

            if (isRUC) $('#razon_social').val(data.razon_social);
            else {
                $('#nombre').val(data.nombre);
                $('#apellidos').val(data.apellidos);
            }
            $('#email').val(data.email);
            $('#telefono').val(data.telefono);
            $('#direccion').val(data.direccion);

            $('#btnGuardar').prop('disabled', true).addClass('btn-secondary').removeClass('btn-info').html('<i class="fas fa-ban"></i> YA REGISTRADO');
            $('.input-future').not('#documento, #tipo_documento').prop('readonly', true).addClass('bg-light');
            toggleDetailsPanel(data.email || data.telefono || data.direccion);
        };

        const handleFree = () => {
            resetFormState();
            if ($('#nombre').prop('readonly') || $('#razon_social').prop('readonly')) {
                $('.input-future').not('#documento, #tipo_documento').val('').prop('readonly', false).removeClass('bg-light');
            }
        };

        const resetFormState = () => {
            $('#documento').removeClass('is-invalid');
            $('#doc-error').remove();
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

        $('#tipo_documento').change(function() {
            $('#documento').val('');
            let isRUC = $(this).val() === 'RUC';
            $('#documento').attr({
                maxlength: isRUC ? 11 : 8,
                minlength: isRUC ? 11 : 8,
                placeholder: isRUC ? 'RUC (11)' : 'DNI (8)'
            });
            $('.bloque-dni').toggleClass('d-none', isRUC);
            $('.bloque-ruc').toggleClass('d-none', !isRUC);
            resetFormState();
        });

        $('#documento').on('input', function() {
            this.value = this.value.replace(/\D/g, '');
            verifyDocument(this.value);
        });

        $('.toggle-details').click(() => toggleDetailsPanel($('#extra-fields').is(':hidden')));

        // Submit Crear Cliente
        $('#formCliente').submit(function(e) {
            e.preventDefault();
            const btn = $('#btnGuardar');
            if (btn.prop('disabled')) return;
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
            $.ajax({
                url: '/clientes',
                method: 'POST',
                data: $(this).serialize(),
                success: (res) => {
                    $('#modalCliente').modal('hide');
                    if (typeof toastr !== 'undefined') toastr.success(res.message);
                    let docNuevo = $('#documento').val();
                    if (docNuevo && window.reloadTable) window.reloadTable();
                },
                error: (xhr) => {
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, (k, v) => $(`[name="${k}"]`).addClass('is-invalid'));
                        if (typeof toastr !== 'undefined') toastr.error('Revise los campos.');
                    } else if (typeof toastr !== 'undefined') toastr.error('Error servidor.');
                },
                complete: () => {
                    if (!$('#documento').hasClass('is-invalid'))
                        btn.prop('disabled', false).html('<i class="fas fa-save"></i> GUARDAR');
                }
            });
        });

        $('.close, [data-dismiss="modal"]').on('click', () => $('.modal').modal('hide'));

        // =========================================================
        // 7. VALIDACIONES SUNAT (FACTURA Y BOLETA > 700) - PROTEGIDO
        // =========================================================
        let formularioEnviado = false;

        $('#form-venta').on('submit', function(e) {
            const btnConfirmar = $('#btn-confirmar-venta');

            // 2. Bloqueo inmediato contra doble clic o Enter repetido
            if (formularioEnviado || btnConfirmar.hasClass('procesando')) {
                e.preventDefault();
                return false;
            }

            let total = obtenerTotalActual();
            let tipoComprobante = $('#tipo_comprobante').val();
            let clienteId = $('#cliente_id_hidden').val();

            // Sincronizar ID antes de validar
            syncClienteIdBackend(clienteId);

            // ---------------------------------------------------------
            // REGLA 1: FACTURAS SIEMPRE REQUIEREN CLIENTE CON RUC
            // ---------------------------------------------------------
            if (tipoComprobante === 'FACTURA') {
                if (!clienteId || clienteId === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Falta Cliente (RUC)',
                        html: `<p style="font-size: 1.1em; color: #555;">Para emitir una <b>FACTURA</b>, es obligatorio seleccionar una empresa con RUC válido.</p>`,
                        confirmButtonText: '<i class="fas fa-search mr-1"></i> Buscar Cliente',
                        confirmButtonColor: '#17a2b8',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) irASeccionCliente();
                    });
                    return false;
                }
            }

            // ---------------------------------------------------------
            // REGLA 2: BOLETAS MAYORES A S/ 700 (NORMA SUNAT)
            // ---------------------------------------------------------
            if (tipoComprobante === 'BOLETA' && total >= 700) {
                if (!clienteId || clienteId === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'info',
                        title: 'Identificación Requerida',
                        html: `
                            <div class="text-left mt-2">
                                <p style="font-size: 1.2em; color: #3dc1d3; font-weight: bold; margin-bottom: 10px;">El monto de la venta es S/ ${total.toFixed(2)}.</p>
                                <div style="background-color: #fff3cd; border-left: 5px solid #ffc107; padding: 15px; border-radius: 4px;">
                                    <h6 style="color: #856404; font-weight: bold; margin-bottom: 5px;"><i class="fas fa-exclamation-circle mr-1"></i> Normativa SUNAT:</h6>
                                    <small style="color: #856404;">Las boletas de venta que superan los <b>S/ 700.00</b> requieren identificar al cliente con su <b>DNI</b>.</small>
                                </div>
                            </div>`,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-user-check mr-1"></i> Ingresar Cliente',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        reverseButtons: true,
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) irASeccionCliente();
                    });
                    return false;
                }
            }

            formularioEnviado = true;
            btnConfirmar.addClass('procesando')
                .prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> PROCESANDO...');

            return true;
        });

        function irASeccionCliente() {
            $('html, body').animate({
                scrollTop: $(".card-cliente-pos").offset().top - 100
            }, 600);

            // Efecto visual (borde rojo parpadeante)
            $('.card-cliente-pos').addClass('border-danger shadow-lg');
            $('#busqueda_cliente').focus().addClass('is-invalid');

            setTimeout(() => {
                $('.card-cliente-pos').removeClass('border-danger shadow-lg');
                $('#busqueda_cliente').removeClass('is-invalid');
            }, 2500);
        }

        // ==========================================================
        // 9. LÓGICA DE PEDIDOS ONLINE (MÓDULO QR & LOOKUP)
        // ==========================================================
        let currentPedidoOnline = null;

        // Apertura del Modal y Enfoque Automático
        $('#btn-modal-pedido-online').click(function() {
            $('#modalPedidoOnline').modal('show');
        });

        $('#modalPedidoOnline').on('shown.bs.modal', function () {
            $('#input-scan-pedido').val('').focus();
            resetModalScanInfo();
        });

        function resetModalScanInfo() {
            currentPedidoOnline = null;
            $('#scan-pedido-warning').addClass('d-none');
            $('#container-reimprimir-directo').addClass('d-none');
            $('.btn-reimprimir-pedido').addClass('d-none');
            $('#scan-pedido-details').addClass('d-none');
            $('#scan-pedido-spinner').addClass('d-none');
            $('#btn-cargar-carrito-pos').addClass('d-none');
            $('#btn-procesar-pedido-online').addClass('d-none');
            $('#ped-det-items-tbody').empty();
            $('#ped-fact-ruc').val('');
            $('#ped-fact-razon-social').val('');
            $('#container-ped-factura-datos').addClass('d-none');
            $('#ped-det-fecha-entrega-container').addClass('d-none');
            $('#ped-det-fecha-entrega').text('');
        }

        function buscarPedidoOnline() {
            let term = $('#input-scan-pedido').val().trim();
            if (!term) return;

            resetModalScanInfo();
            $('#scan-pedido-spinner').removeClass('d-none');

            $.ajax({
                url: "{{ route('tienda.admin.pedidos.buscar_ajax') }}",
                method: 'GET',
                data: { q: term },
                success: function(res) {
                    $('#scan-pedido-spinner').addClass('d-none');
                    if (res.success) {
                        currentPedidoOnline = res;
                        renderPedidoDetails(res);
                    } else {
                        mostrarErrorScan(res.message || 'Pedido no encontrado.');
                    }
                },
                error: function(xhr) {
                    $('#scan-pedido-spinner').addClass('d-none');
                    let msg = 'Error al buscar el pedido.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    mostrarErrorScan(msg);
                }
            });
        }

        function mostrarErrorScan(msg) {
            $('#scan-pedido-warning').removeClass('d-none');
            $('#scan-pedido-warning .warning-message').text(msg);
        }

        $('#btn-cerrar-warning').click(function() {
            $('#scan-pedido-warning').addClass('d-none');
        });

        $('#input-scan-pedido').keypress(function(e) {
            if (e.which === 13) {
                e.preventDefault();
                buscarPedidoOnline();
            }
        });

        $('#btn-buscar-pedido-scan').click(function() {
            buscarPedidoOnline();
        });

        function renderPedidoDetails(data) {
            let p = data.pedido;
            let items = data.detalles;

            // Rellenar tarjetas cliente y sucursal
            $('#ped-det-cliente-nombre').text(p.cliente_nombre);
            $('#ped-det-cliente-doc').text(p.cliente_documento || 'No especificado');
            $('#ped-det-sucursal').text(p.sucursal);
            $('#ped-det-fecha-recojo').text(p.fecha_recojo || 'No especificada');

            // Badges de estado de pedido y pago
            let $estPedido = $('#ped-det-estado');
            $estPedido.text(p.estado).removeClass().addClass('badge px-2.5 py-1 text-white');
            if (p.estado === 'PENDIENTE') $estPedido.addClass('bg-warning');
            else if (p.estado === 'CONFIRMADO') $estPedido.addClass('bg-info');
            else if (p.estado === 'ENTREGADO') $estPedido.addClass('bg-success');
            else $estPedido.addClass('bg-secondary');

            let $estPago = $('#ped-det-pago-estado');
            $estPago.text(p.estado_pago).removeClass().addClass('badge px-2.5 py-1 text-white');
            if (p.estado_pago === 'PAGADO' || p.estado_pago === 'COMPLETADO') $estPago.addClass('bg-success');
            else $estPago.addClass('bg-danger');

            $('#ped-det-pago-metodo').text(p.metodo_pago);

            // Renderizar productos
            let tbody = $('#ped-det-items-tbody').empty();
            items.forEach(i => {
                tbody.append(`
                    <tr>
                        <td class="pl-3 py-2">
                            <strong>${i.nombre}</strong><br>
                            <small class="text-muted">${i.presentacion}</small>
                        </td>
                        <td class="text-center align-middle">${i.cantidad}</td>
                        <td class="text-right align-middle">S/ ${i.precio_unitario.toFixed(2)}</td>
                        <td class="text-right pr-3 align-middle font-weight-bold">S/ ${i.subtotal.toFixed(2)}</td>
                    </tr>
                `);
            });

            $('#ped-det-total').text(p.total.toFixed(2));

            // Mostrar fecha de entrega si ya fue entregado
            if (p.estado === 'ENTREGADO' && p.entregado_at) {
                $('#ped-det-fecha-entrega').text(p.entregado_at);
                $('#ped-det-fecha-entrega-container').removeClass('d-none');
            } else {
                $('#ped-det-fecha-entrega-container').addClass('d-none');
            }

            // Advertir si ya fue entregado o cancelado
            if (data.warning) {
                mostrarErrorScan(data.warning);
                $('#panel-facturacion-directa').addClass('d-none');
                
                // Si el pedido ya tiene venta_id, habilitar reimpresión
                if (p.venta_id) {
                    $('#container-reimprimir-directo').removeClass('d-none');
                    $('#btn-reimprimir-pedido-footer').removeClass('d-none');
                }
            } else {
                $('#panel-facturacion-directa').removeClass('d-none');
                
                // Si el pago ya fue completado/pagado, configurar visualización en línea
                if (p.estado_pago === 'PAGADO' || p.estado_pago === 'COMPLETADO') {
                    $('#ped-fact-pago-online-badge').removeClass('d-none');
                    $('#ped-fact-pago-caja-select').addClass('d-none');
                    $('#container-ped-paga-con').addClass('d-none');
                } else {
                    $('#ped-fact-pago-online-badge').addClass('d-none');
                    $('#ped-fact-pago-caja-select').removeClass('d-none');
                    $('#container-ped-paga-con').removeClass('d-none');
                }
                
                $('#ped-fact-paga-con').val(p.total.toFixed(2));

                // Configurar si es factura
                if (p.cliente_documento && p.cliente_documento.length === 11) {
                    $('#ped-fact-tipo-comprobante').val('FACTURA').trigger('change');
                    $('#ped-fact-ruc').val(p.cliente_documento);
                    $('#ped-fact-razon-social').val(p.cliente_nombre);
                } else {
                    $('#ped-fact-tipo-comprobante').val('BOLETA').trigger('change');
                    $('#ped-fact-ruc').val('');
                    $('#ped-fact-razon-social').val('');
                }

                $('#btn-procesar-pedido-online').removeClass('d-none');
            }

            // Mostrar opción de cargar en el POS sólo si no está entregado ni cancelado (por seguridad)
            if (p.estado !== 'ENTREGADO' && p.estado !== 'CANCELADO') {
                $('#btn-cargar-carrito-pos').removeClass('d-none');
            } else {
                $('#btn-cargar-carrito-pos').addClass('d-none');
            }
            $('#scan-pedido-details').removeClass('d-none');
        }

        // Lógica de Facturación del Modal
        $('#ped-fact-tipo-comprobante').change(function() {
            let isFactura = ($(this).val() === 'FACTURA');
            if (isFactura) {
                $('#container-ped-factura-datos').removeClass('d-none');
            } else {
                $('#container-ped-factura-datos').addClass('d-none');
            }
        });

        $('#ped-fact-medio-pago').change(function() {
            let esEfectivo = ($(this).val() === 'EFECTIVO');
            if (esEfectivo) {
                $('#container-ped-paga-con').removeClass('d-none');
            } else {
                $('#container-ped-paga-con').addClass('d-none');
            }
        });

        $('#btn-procesar-pedido-online').click(function() {
            if (!currentPedidoOnline) return;

            let p = currentPedidoOnline.pedido;

            let yaPagado = (p.estado_pago === 'PAGADO' || p.estado_pago === 'COMPLETADO');

            // Tomar siempre los valores seleccionados/ingresados en el modal del formulario
            let tipoComprobante = $('#ped-fact-tipo-comprobante').val();
            let medioPago = yaPagado ? 'TARJETA' : $('#ped-fact-medio-pago').val();
            let pagaCon = yaPagado ? p.total : ($('#ped-fact-paga-con').val() || p.total);
            let ruc = $('#ped-fact-ruc').val();
            let razonSocial = $('#ped-fact-razon-social').val();

            if (tipoComprobante === 'FACTURA') {
                if (!ruc || ruc.length !== 11 || !/^\d+$/.test(ruc)) {
                    return toastr.error('Debe ingresar un número de RUC válido de 11 dígitos.');
                }
                if (!razonSocial || razonSocial.trim().length === 0) {
                    return toastr.error('Debe ingresar la Razón Social.');
                }
            }

            Swal.fire({
                title: '¿Confirmar entrega y facturación?',
                text: yaPagado 
                    ? `Se registrará la entrega del pedido ${p.codigo} (Ya Pagado Online) por S/ ${p.total.toFixed(2)}.`
                    : `Se generará una ${tipoComprobante} para el pedido ${p.codigo} por S/ ${p.total.toFixed(2)}.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check mr-1"></i> Sí, entregar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando entrega...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    let url = "{{ route('tienda.admin.pedidos.entregar_facturar', ':id') }}".replace(':id', p.id);

                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            tipo_comprobante: tipoComprobante,
                            medio_pago: medioPago,
                            paga_con: pagaCon,
                            cliente_ruc: ruc,
                            cliente_razon_social: razonSocial
                        },
                        success: function(res) {
                            Swal.close();
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Éxito!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    if (res.print_ticket_url && res.print_a4_url) {
                                        abrirSelectorImpresion(res.print_ticket_url, res.print_a4_url);
                                    }
                                });

                                $('#modalPedidoOnline').modal('hide');
                                resetCliente();
                                carrito = {};
                                renderCarrito();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: res.message || 'Error al procesar la entrega.'
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.close();
                            let msg = 'Error al procesar la entrega del pedido.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: msg
                            });
                        }
                    });
                }
            });
        });

        // Acción de Cargar al Carrito del POS
        $('#btn-cargar-carrito-pos').click(async function() {
            if (!currentPedidoOnline) return;

            let p = currentPedidoOnline.pedido;
            let items = currentPedidoOnline.detalles;

            Swal.fire({
                title: 'Cargando pedido en el POS...',
                text: 'Buscando lotes y asignando stock...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                // Limpiar carrito actual del POS
                carrito = {};

                for (const item of items) {
                    let lotes = await $.get(RUTA_LOOKUP_LOTES, {
                        medicamento_id: item.medicamento_id,
                        sucursal_id: sucursalId
                    });

                    let cantidadRequerida = item.cantidad;

                    // Asignación PEPS
                    for (const lote of lotes) {
                        if (cantidadRequerida <= 0) break;

                        let stockLote = parseInt(lote.stock_actual);
                        if (stockLote <= 0) continue;

                        let cantidadATomar = Math.min(stockLote, cantidadRequerida);
                        let uniqueId = lote.id + '-UNIDAD';

                        carrito[uniqueId] = {
                            unique_id: uniqueId,
                            unidad_medida: 'UNIDAD',
                            lote_id: lote.id,
                            nombre: item.nombre,
                            presentacion: item.presentacion ? `${item.presentacion} [UNIDAD]` : '[UNIDAD]',
                            codigo_lote: lote.codigo_lote,
                            cantidad: cantidadATomar,
                            precio_venta: item.precio_unitario, // Precio de la compra online
                            stock_max: stockLote,
                            factor: 1
                        };

                        cantidadRequerida -= cantidadATomar;
                    }

                    // Forzar saldo en el primer lote si falta stock
                    if (cantidadRequerida > 0 && lotes.length > 0) {
                        let primerLote = lotes[0];
                        let uniqueId = primerLote.id + '-UNIDAD';

                        if (carrito[uniqueId]) {
                            carrito[uniqueId].cantidad += cantidadRequerida;
                        } else {
                            carrito[uniqueId] = {
                                unique_id: uniqueId,
                                unidad_medida: 'UNIDAD',
                                lote_id: primerLote.id,
                                nombre: item.nombre,
                                presentacion: item.presentacion ? `${item.presentacion} [UNIDAD]` : '[UNIDAD]',
                                codigo_lote: primerLote.codigo_lote,
                                cantidad: cantidadRequerida,
                                precio_venta: item.precio_unitario,
                                stock_max: primerLote.stock_actual,
                                factor: 1
                            };
                        }
                    }
                }

                // Renderizar cambios
                renderCarrito();

                // Sincronizar Cliente
                if (p.cliente_documento) {
                    let isFactura = (p.cliente_documento.length === 11);
                    $tipo.val(isFactura ? 'FACTURA' : 'BOLETA').trigger('change');
                    $input.val(p.cliente_documento);
                    buscarCliente(p.cliente_documento);
                } else {
                    resetCliente();
                }

                Swal.close();
                $('#modalPedidoOnline').modal('hide');
                toastr.success(`Pedido ${p.codigo} cargado en el carrito con éxito.`);

            } catch (err) {
                console.error("Error al cargar carrito:", err);
                Swal.close();
                toastr.error('Ocurrió un error al cargar los productos en el POS.');
            }
        });

        // Selector de Impresión Ticket vs A4 (Mediante Swal con Deny/Confirm)
        function abrirSelectorImpresion(printTicketUrl, printA4Url) {
            Swal.fire({
                title: '¿Cómo desea imprimir el comprobante?',
                text: 'Seleccione el formato en el que desea imprimir o guardar el comprobante de pago:',
                icon: 'question',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: '<i class="fas fa-receipt mr-1"></i> Formato Ticket',
                denyButtonText: '<i class="fas fa-file-pdf mr-1"></i> Formato A4',
                cancelButtonText: 'Cerrar',
                confirmButtonColor: '#17a2b8', // Info
                denyButtonColor: '#6c757d', // Gray
                cancelButtonColor: '#343a40', // Dark
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open(printTicketUrl + '?imprimir=si', '_blank');
                } else if (result.isDenied) {
                    window.open(printA4Url + '?imprimir=si', '_blank');
                }
            });
        }

        // Clic en Reimprimir desde el modal
        $(document).on('click', '.btn-reimprimir-pedido', function() {
            if (currentPedidoOnline && currentPedidoOnline.pedido && currentPedidoOnline.pedido.print_ticket_url) {
                let p = currentPedidoOnline.pedido;
                abrirSelectorImpresion(p.print_ticket_url, p.print_a4_url);
            } else {
                toastr.error('No hay URLs de impresión disponibles para este pedido.');
            }
        });
    });
</script>