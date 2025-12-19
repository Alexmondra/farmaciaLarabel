<script>
    $(document).ready(function() {

        // ==========================================
        // 0. CONFIGURACIÓN Y VARIABLES GLOBALES
        // ==========================================
        const RUTA_LOOKUP_MEDICAMENTOS = "{{ route('ventas.lookup_medicamentos') }}";
        const RUTA_LOOKUP_LOTES = "{{ route('ventas.lookup_lotes') }}";
        const RUTA_CHECK_CLIENTE = "{{ route('clientes.check') }}"; // O checkDocumento, revisa tu ruta
        const sucursalId = $('#sucursal_id').val();

        // --- NUEVO: VALOR DEL PUNTO DESDE CONFIGURACIÓN ---
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
        window.buscarMedicamentos = function() {
            let q = $('#busqueda_medicamento').val().trim();
            let categoriaId = $('#filtro_categoria_id').val();
            if (q.length === 0 && !categoriaId) {
                cerrarResultados();
                return;
            }
            $.ajax({
                url: RUTA_LOOKUP_MEDICAMENTOS,
                method: 'GET',
                data: {
                    sucursal_id: sucursalId,
                    q: q,
                    categoria_id: categoriaId
                },
                success: function(data) {
                    renderResultadosMedicamentos(data);
                }
            });
        }

        function renderResultadosMedicamentos(lista) {
            let contenedor = $('#resultados-medicamentos');
            selectedIndex = -1;
            resultCount = lista.length;

            if (!lista.length) {
                contenedor.html('<div class="list-group-item text-muted small py-2">Sin resultados</div>').addClass('active');
                return;
            }

            let html = lista.map(m => `
                <button type="button" class="list-group-item list-group-item-action resultado-medicamento py-1 px-2" 
                        data-medicamento-id="${m.medicamento_id}" 
                        data-nombre="${m.nombre}" 
                        data-presentacion="${m.presentacion || ''}" 
                        data-precio="${m.precio_venta}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
                            <strong>${m.nombre}</strong> <small class="text-muted">(${m.presentacion || ''})</small>
                        </div>
                        <div><span class="badge badge-light border">S/ ${parseFloat(m.precio_venta).toFixed(2)}</span></div>
                    </div>
                </button>
            `).join('');
            contenedor.html(html).addClass('active');
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
                async: false,
                success: function(lotes) {
                    let tbody = $('#modal-lotes-tbody').empty();
                    if (!lotes.length) {
                        tbody.append('<tr><td colspan="6" class="text-center text-danger font-weight-bold">AGOTADO / SIN STOCK</td></tr>');
                        return;
                    }
                    lotes.forEach(l => {
                        let precioBase = parseFloat(l.precio_venta);
                        let precioOferta = l.precio_oferta ? parseFloat(l.precio_oferta) : null;
                        let htmlPrecio = precioOferta ?
                            `<small style="text-decoration:line-through" class="text-muted">S/ ${precioBase.toFixed(2)}</small><br><span class="text-danger font-weight-bold">S/ ${precioOferta.toFixed(2)}</span>` :
                            `S/ ${precioBase.toFixed(2)}`;
                        let precioFinal = precioOferta || precioBase;
                        let rowClass = precioOferta ? 'table-warning' : '';

                        tbody.append(`
                            <tr data-lote-id="${l.id}" class="${rowClass}">
                                <td class="align-middle small">${l.codigo_lote}</td>
                                <td class="align-middle small">${l.fecha_vencimiento || '-'}</td>
                                <td class="text-center font-weight-bold align-middle text-primary" style="font-size:1.1em">${l.stock_actual}</td>
                                <td class="align-middle"><input type="number" class="form-control form-control-sm input-cant-lote text-center font-weight-bold" min="1" max="${l.stock_actual}" value="1"></td>
                                <td class="align-middle text-right" style="line-height:1.1">${htmlPrecio}</td>
                                <td class="align-middle text-center"><button type="button" class="btn btn-sm btn-success btn-agregar-lote"><i class="fas fa-plus"></i></button></td>
                                <td style="display:none;" class="data-precio">${precioFinal}</td>
                                <td style="display:none;" class="data-codigo-lote">${l.codigo_lote}</td>
                            </tr>
                        `);
                    });
                }
            });
        }

        // ==========================================
        // 2. CARRITO Y TABLA
        // ==========================================
        $(document).on('click', '.btn-agregar-lote', function() {
            let fila = $(this).closest('tr');
            let loteId = fila.data('lote-id');
            let cant = parseInt(fila.find('.input-cant-lote').val()) || 0;
            let stock = parseInt(fila.find('.input-cant-lote').attr('max'));
            let precio = parseFloat(fila.find('.data-precio').text());

            if (cant > stock) return toastr.error('La cantidad supera el stock disponible.');
            if (cant <= 0) return toastr.error('Cantidad inválida.');

            if (carrito[loteId]) {
                let nuevaCant = carrito[loteId].cantidad + cant;
                if (nuevaCant > stock) return toastr.warning('Stock máximo alcanzado en el carrito.');
                carrito[loteId].cantidad = nuevaCant;
            } else {
                let item = {
                    lote_id: loteId,
                    nombre: medicamentoSeleccionado.nombre,
                    presentacion: medicamentoSeleccionado.presentacion,
                    codigo_lote: fila.find('.data-codigo-lote').text(),
                    cantidad: cant,
                    precio_venta: precio,
                    stock_max: stock
                };
                carrito[loteId] = item;
            }
            renderCarrito();
            $('#modalLotes').modal('hide');
            $('#busqueda_medicamento').focus();
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
                    <tr data-lote-id="${i.lote_id}">
                        <td class="align-middle"><span class="font-weight-bold text-dark">${i.nombre}</span><br><small class="text-muted">${i.presentacion} | Lote: ${i.codigo_lote}</small></td>
                        
                        <td class="align-middle text-center"> 
                            <input type="number" class="form-control form-control-sm input-edit-cant font-weight-bold" value="${i.cantidad}" min="1" max="${i.stock_max}">
                        </td>
                        
                        <td class="align-middle text-center"> 
                            <input type="number" 
                                class="form-control form-control-sm input-edit-precio" 
                                value="${i.precio_venta.toFixed(2)}" 
                                step="0.01" 
                                min="0">
                        </td>
                        
                        <td class="align-middle text-right font-weight-bold text-success td-subtotal">S/ ${subtotal.toFixed(2)}</td>
                        <td class="align-middle text-center"><button type="button" class="btn btn-xs btn-outline-danger btn-eliminar-item"><i class="fas fa-trash-alt"></i></button></td>
                    </tr>
                `);
            });
            actualizarTotalGlobal(total);
        }

        $(document).on('input', '.input-edit-cant', function() {
            let input = $(this);
            let row = input.closest('tr');
            let loteId = row.data('lote-id');
            let item = carrito[loteId];
            let val = parseInt(input.val());

            if (val > item.stock_max) {
                input.val(item.stock_max);
                val = item.stock_max;
                input.addClass('is-invalid');
                setTimeout(() => input.removeClass('is-invalid'), 1000);
            }
            if (val < 1 && input.val() !== '') {
                input.val(1);
                val = 1;
            }
            if (!isNaN(val)) {
                carrito[loteId].cantidad = val;
                let subtotal = val * item.precio_venta;
                row.find('.td-subtotal').text('S/ ' + subtotal.toFixed(2));
                recalcularTotalDesdeMemoria();
            }
        });

        $(document).on('input', '.input-edit-precio', function() {
            let row = $(this).closest('tr');
            let loteId = row.data('lote-id');
            let val = parseFloat($(this).val()) || 0;
            carrito[loteId].precio_venta = val;
            let cant = carrito[loteId].cantidad;
            let subtotal = cant * val;
            row.find('.td-subtotal').text('S/ ' + subtotal.toFixed(2));
            recalcularTotalDesdeMemoria();
        });

        $(document).on('click', '.btn-eliminar-item', function() {
            delete carrito[$(this).closest('tr').data('lote-id')];
            renderCarrito();
        });

        function recalcularTotalDesdeMemoria() {
            let total = 0;
            Object.values(carrito).forEach(i => total += (i.cantidad * i.precio_venta));
            actualizarTotalGlobal(total);
        }

        // ==========================================
        // 2.5 CALCULO TOTAL CON PUNTOS (MODIFICADO)
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
        // 3. LOGICA BUSCADOR CLIENTES (MODIFICADO)
        // ==========================================
        const $tipo = $('#tipo_comprobante');
        const $input = $('#busqueda_cliente');
        const $display = $('#nombre_cliente_display');
        const $hidden = $('#cliente_id_hidden');

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
                    // Tu controlador devuelve { exists: true/false, data: { ... puntos: X } }
                    if (res.exists) {
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
            let nombre = (data.tipo_documento === 'RUC') ? data.razon_social : `${data.nombre} ${data.apellidos}`;
            $display.val(nombre).removeClass('text-danger').addClass('text-primary font-weight-bold');
            $('#btn-crear-cliente').addClass('d-none');
            $('#btn-ver-cliente').removeClass('d-none');

            // --- LÓGICA DE PUNTOS (NUEVA) ---
            // Verifica si el objeto 'data' trae puntos y si son mayores a 0
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
        // 3.5 CALCULADORA DE PUNTOS (NUEVO)
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
        // 4. LOGICA COBRO, VUELTO Y REFERENCIA (CORREGIDO)
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

                // Tolerancia de 0.01 céntimos para evitar errores de decimales
                if (pagaCon >= (totalVenta - 0.01)) {
                    // SI ALCANZA EL DINERO -> ACTIVA
                    btn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-light');
                } else {
                    // NO ALCANZA -> BLOQUEA
                    btn.prop('disabled', true).removeClass('btn-light').addClass('btn-secondary');
                }
            } else {
                // TARJETA/YAPE/PLIN -> SIEMPRE ACTIVO (Referencia es opcional)
                btn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-light');
            }
        }

        // B. Evento: Cuando cambias entre Efectivo / Yape / Tarjeta
        $('#medio_pago').change(function() {
            let metodo = $(this).val();
            let totalFinal = obtenerTotalActual(); // Usamos función auxiliar

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

        // C. Evento: ¡ESTE ES EL QUE TE FALTABA! (Cuando escribes el dinero)
        $('#input-paga-con').on('input', function() {
            let totalFinal = obtenerTotalActual();

            // 1. Calculamos visualmente el vuelto
            calcularVuelto(totalFinal);

            // 2. Validamos si el botón debe prenderse
            validarBotonConfirmar(totalFinal);
        });

        // D. Función Visual: Calcular Vuelto (Texto Rojo/Verde)
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

        // E. Auxiliar para no repetir código de cálculo de total
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
        // 6. FUNCIONES DE MODALES (INCLUIDAS)
        // =======================================================

        // Modal Crear Cliente
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

        // ==========================================
        // 7. VALIDACIONES SUNAT (FACTURA Y BOLETA > 700)
        // ==========================================
        $('#form-venta').on('submit', function(e) {


            let total = obtenerTotalActual();
            let tipoComprobante = $('#tipo_comprobante').val();
            let clienteId = $('#cliente_id_hidden').val(); // Si está vacío es "Cliente General"

            // REGLA 1: FACTURAS SIEMPRE REQUIEREN CLIENTE
            if (tipoComprobante === 'FACTURA') {
                if (!clienteId || clienteId === '') {
                    e.preventDefault(); // Detiene el envío
                    toastr.error('ERROR: Para emitir una FACTURA es obligatorio seleccionar un cliente con RUC.', 'Falta Cliente');

                    // Efecto visual: Resaltar la caja del cliente y subir
                    $('.card-cliente-pos').addClass('border-danger shadow-lg');
                    $('html, body').animate({
                        scrollTop: $(".card-cliente-pos").offset().top - 100
                    }, 500);
                    setTimeout(() => $('.card-cliente-pos').removeClass('border-danger shadow-lg'), 2000);

                    return false;
                }
            }

            // REGLA 2: BOLETAS MAYORES A 700 SOLES
            if (tipoComprobante === 'BOLETA' && total >= 700) {
                if (!clienteId || clienteId === '') {
                    e.preventDefault(); // Detiene el envío
                    toastr.error('NORMA SUNAT: Las boletas de venta que superan los S/ 700 requieren identificar al cliente (DNI).', 'Monto Alto');

                    // Efecto visual
                    $('.card-cliente-pos').addClass('border-danger shadow-lg');
                    $('html, body').animate({
                        scrollTop: $(".card-cliente-pos").offset().top - 100
                    }, 500);
                    setTimeout(() => $('.card-cliente-pos').removeClass('border-danger shadow-lg'), 2000);

                    return false;
                }
            }
        });
    });
</script>