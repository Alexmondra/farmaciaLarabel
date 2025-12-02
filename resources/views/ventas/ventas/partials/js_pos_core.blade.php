<script>
    $(document).ready(function() {

        // ==========================================
        // 0. CONFIGURACIÓN Y VARIABLES GLOBALES
        // ==========================================
        const RUTA_LOOKUP_MEDICAMENTOS = "{{ route('ventas.lookup_medicamentos') }}";
        const RUTA_LOOKUP_LOTES = "{{ route('ventas.lookup_lotes') }}";
        const RUTA_CHECK_CLIENTE = "{{ route('clientes.check') }}";
        const sucursalId = $('#sucursal_id').val();

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
            if (e.which === 40) {
                e.preventDefault();
                selectedIndex++;
                if (selectedIndex >= resultCount) selectedIndex = 0;
                highlightItem();
            } else if (e.which === 38) {
                e.preventDefault();
                selectedIndex--;
                if (selectedIndex < 0) selectedIndex = resultCount - 1;
                highlightItem();
            } else if (e.which === 13) {
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
                        <td class="align-middle"><input type="number" class="form-control form-control-sm text-center input-edit-cant font-weight-bold" value="${i.cantidad}" min="1" max="${i.stock_max}" style="width: 80px; margin:0 auto;"></td>
                        <td class="align-middle"><input type="number" class="form-control form-control-sm text-center input-edit-precio" value="${i.precio_venta.toFixed(2)}" step="0.01" min="0" style="width: 100px; margin:0 auto;"></td>
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

        function actualizarTotalGlobal(total) {
            $('#total-venta').text(total.toFixed(2));
            $('#input-items-json').val(JSON.stringify(Object.values(carrito)));
            if (window.calcularVuelto) window.calcularVuelto(total);
        }

        // ==========================================
        // 3. LOGICA BUSCADOR CLIENTES (Panel)
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
                    if (res.exists) selectCliente(res.data);
                    else showCreateOption();
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
        }

        $('#btn-crear-cliente').click(function() {
            if (window.openCreateModal) {
                window.openCreateModal();
                // Pre-llenar datos en el modal
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
        // 4. LOGICA COBRO
        // ==========================================
        $('#medio_pago').change(function() {
            let metodo = $(this).val();
            if (metodo === 'EFECTIVO') {
                $('#bloque-calculadora').slideDown();
                $('#input-paga-con').focus();
            } else {
                $('#bloque-calculadora').slideUp();
                $('#input-paga-con').val('');
                $('#txt-vuelto').text('0.00');
            }
        });

        $('#input-paga-con').on('input', function() {
            let totalTexto = $('#total-venta').text();
            let total = parseFloat(totalTexto) || 0;
            calcularVuelto(total);
        });

        window.calcularVuelto = function(totalVenta) {
            if ($('#medio_pago').val() !== 'EFECTIVO') return;
            let pagaCon = parseFloat($('#input-paga-con').val()) || 0;
            let vuelto = pagaCon - totalVenta;
            let elVuelto = $('#txt-vuelto');
            if (vuelto < 0) {
                elVuelto.text('Falta dinero');
                elVuelto.parent().removeClass('text-success').addClass('text-danger');
            } else {
                elVuelto.text(vuelto.toFixed(2));
                elVuelto.parent().removeClass('text-danger').addClass('text-success');
            }
        };

        $('#form-venta').on('submit', function(e) {
            let items = $('#input-items-json').val();
            if (items === '[]' || items === '') {
                e.preventDefault();
                toastr.error('Carrito vacío.');
                return;
            }
            let tipo = $('#tipo_comprobante').val();
            let clienteId = $('#cliente_id_hidden').val();
            if (tipo === 'FACTURA' && !clienteId) {
                e.preventDefault();
                toastr.error('Falta RUC para Factura.');
                return;
            }
            if ($('#medio_pago').val() === 'EFECTIVO') {
                let total = parseFloat($('#total-venta').text());
                let pagaCon = parseFloat($('#input-paga-con').val()) || 0;
                if ($('#input-paga-con').val().length > 0 && pagaCon < total) {
                    e.preventDefault();
                    toastr.error('El monto de pago es insuficiente.');
                    $('#input-paga-con').focus().addClass('is-invalid');
                    return;
                }
            }
        });

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
        // 6. FUNCIONES DE MODALES (AQUÍ ESTABA EL FALTANTE)
        // =======================================================

        // --- A. MODAL CREAR CLIENTE ---
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

        // Listeners internos del Modal Create
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

        // SUBMIT DEL MODAL
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

        // Close helpers
        $('.close, [data-dismiss="modal"]').on('click', () => $('.modal').modal('hide'));
    });
</script>