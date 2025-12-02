<script>
    $(document).ready(function() {

        // ==========================================
        // 1. CONFIGURACIÓN Y VARIABLES
        // ==========================================
        const RUTA_LOOKUP_MEDICAMENTOS = "{{ route('ventas.lookup_medicamentos') }}";
        const RUTA_LOOKUP_LOTES = "{{ route('ventas.lookup_lotes') }}";
        const sucursalId = $('#sucursal_id').val();

        // Variables de estado
        let selectedIndex = -1;
        let resultCount = 0;
        let carrito = {};
        let medicamentoSeleccionado = null;
        let timeoutBusqueda = null;

        // ==========================================
        // 2. BUSCADOR DE MEDICAMENTOS (AJAX)
        // ==========================================
        window.buscarMedicamentos = function() {
            let q = $('#busqueda_medicamento').val().trim();
            let categoriaId = $('#filtro_categoria_id').val();

            // Si está vacío y sin categoría, limpiar
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

            // Reiniciar índices de navegación teclado
            selectedIndex = -1;
            resultCount = lista.length;

            if (!lista.length) {
                contenedor.html('<div class="list-group-item text-muted small py-2">Sin resultados</div>').addClass('active');
                return;
            }

            // Generar HTML
            let html = lista.map(m => `
                <button type="button"
                        class="list-group-item list-group-item-action resultado-medicamento py-1 px-2"
                        data-medicamento-id="${m.medicamento_id}"
                        data-nombre="${m.nombre}"
                        data-presentacion="${m.presentacion || ''}"
                        data-precio="${m.precio_venta}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
                            <strong>${m.nombre}</strong> <small class="text-muted">(${m.presentacion || ''})</small>
                        </div>
                        <div>
                            <span class="badge badge-light border">S/ ${parseFloat(m.precio_venta).toFixed(2)}</span>
                        </div>
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

        // --- EVENTOS DE TECLADO (FLECHAS) ---
        $('#busqueda_medicamento').on('keydown', function(e) {
            let $resultados = $('#resultados-medicamentos');
            if (!$resultados.hasClass('active') || resultCount === 0) return;

            // ABAJO (40)
            if (e.which === 40) {
                e.preventDefault();
                selectedIndex++;
                if (selectedIndex >= resultCount) selectedIndex = 0;
                highlightItem();
            }
            // ARRIBA (38)
            else if (e.which === 38) {
                e.preventDefault();
                selectedIndex--;
                if (selectedIndex < 0) selectedIndex = resultCount - 1;
                highlightItem();
            }
            // ENTER (13)
            else if (e.which === 13) {
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

        // ==========================================
        // 3. SELECCIÓN Y MODAL DE LOTES
        // ==========================================
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
                        // Precios
                        let precioBase = parseFloat(l.precio_venta);
                        let precioOferta = l.precio_oferta ? parseFloat(l.precio_oferta) : null;

                        let htmlPrecio = precioOferta ?
                            `<small style="text-decoration:line-through" class="text-muted">S/ ${precioBase.toFixed(2)}</small><br><span class="text-danger font-weight-bold">S/ ${precioOferta.toFixed(2)}</span>` :
                            `S/ ${precioBase.toFixed(2)}`;

                        // Precio final a cobrar (si hay oferta, se usa esa)
                        let precioFinal = precioOferta || precioBase;
                        let rowClass = precioOferta ? 'table-warning' : '';

                        tbody.append(`
                            <tr data-lote-id="${l.id}" class="${rowClass}">
                                <td class="align-middle small">${l.codigo_lote}</td>
                                <td class="align-middle small">${l.fecha_vencimiento || '-'}</td>
                                <td class="text-center font-weight-bold align-middle text-primary" style="font-size:1.1em">${l.stock_actual}</td>
                                
                                <td class="align-middle">
                                    <input type="number" class="form-control form-control-sm input-cant-lote text-center font-weight-bold" 
                                           min="1" max="${l.stock_actual}" value="1">
                                </td>
                                
                                <td class="align-middle text-right" style="line-height:1.1">${htmlPrecio}</td>
                                
                                <td class="align-middle text-center">
                                    <button type="button" class="btn btn-sm btn-success btn-agregar-lote" title="Agregar al carrito">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </td>

                                {{-- Datos ocultos para JS --}}
                                <td style="display:none;" class="data-precio">${precioFinal}</td>
                                <td style="display:none;" class="data-codigo-lote">${l.codigo_lote}</td>
                            </tr>
                        `);
                    });
                }
            });
        }

        // ==========================================
        // 4. LOGICA DEL CARRITO (CORE)
        // ==========================================

        // A. Agregar desde Modal (Botón Verde)
        $(document).on('click', '.btn-agregar-lote', function() {
            let fila = $(this).closest('tr');
            let loteId = fila.data('lote-id');
            let cant = parseInt(fila.find('.input-cant-lote').val()) || 0;
            let stock = parseInt(fila.find('.input-cant-lote').attr('max'));
            let precio = parseFloat(fila.find('.data-precio').text());

            // Validación inicial
            if (cant > stock) return toastr.error('La cantidad supera el stock disponible.');
            if (cant <= 0) return toastr.error('Cantidad inválida.');

            // Crear o Actualizar Item
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
                    stock_max: stock // Guardamos el stock máximo para validaciones futuras
                };
                carrito[loteId] = item;
            }

            renderCarrito();
            $('#modalLotes').modal('hide');
            $('#busqueda_medicamento').focus(); // Volver al buscador
        });

        // Enter en el modal
        $(document).on('keydown', '.input-cant-lote', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).closest('tr').find('.btn-agregar-lote').click();
            }
        });

        // B. Renderizar Tabla Carrito (Con Inputs Editables)
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
                        <td class="align-middle">
                            <span class="font-weight-bold text-dark">${i.nombre}</span><br>
                            <small class="text-muted">${i.presentacion} | Lote: ${i.codigo_lote}</small>
                        </td>
                        
                        <td class="align-middle">
                            <input type="number" class="form-control form-control-sm text-center input-edit-cant font-weight-bold" 
                                   value="${i.cantidad}" min="1" max="${i.stock_max}" style="width: 80px; margin:0 auto;">
                        </td>

                        <td class="align-middle">
                            <input type="number" class="form-control form-control-sm text-center input-edit-precio" 
                                   value="${i.precio_venta.toFixed(2)}" step="0.01" min="0" style="width: 100px; margin:0 auto;">
                        </td>

                        <td class="align-middle text-right font-weight-bold text-success td-subtotal">
                            S/ ${subtotal.toFixed(2)}
                        </td>

                        <td class="align-middle text-center">
                            <button type="button" class="btn btn-xs btn-outline-danger btn-eliminar-item" title="Quitar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });

            actualizarTotalGlobal(total);
        }

        // ==========================================
        // C. EVENTO: EDICIÓN EN LÍNEA (OPTIMIZADO)
        // ==========================================

        // Usamos 'input' en lugar de keyup para detectar el cambio inmediatamente al escribir
        $(document).on('input', '.input-edit-cant', function() {
            let input = $(this);
            let row = input.closest('tr');
            let loteId = row.data('lote-id');
            let item = carrito[loteId];

            let val = parseInt(input.val());

            // 1. VALIDACIÓN ESTRICTA DE STOCK
            // Si el valor es mayor al stock máximo, lo forzamos al máximo inmediatamente
            if (val > item.stock_max) {
                input.val(item.stock_max); // Sobreescribimos visualmente lo que el usuario intentó poner
                val = item.stock_max; // Ajustamos la variable lógica

                // Feedback visual (clase error de Bootstrap temporal)
                input.addClass('is-invalid');
                setTimeout(() => input.removeClass('is-invalid'), 1000);

                // Mensaje (Opcional, a veces es molesto si sale muchas veces, descomenta si lo quieres)
                // toastr.warning('El stock máximo es: ' + item.stock_max);
            }

            // 2. Validación de mínimos (evitar 0 o negativos, excepto si está borrando para escribir)
            if (val < 1 && input.val() !== '') {
                input.val(1);
                val = 1;
            }

            // 3. Actualizar Lógica solo si es un número válido
            if (!isNaN(val)) {
                carrito[loteId].cantidad = val;

                // Recalcular subtotal de la fila
                let subtotal = val * item.precio_venta;
                row.find('.td-subtotal').text('S/ ' + subtotal.toFixed(2));

                // Recalcular total global
                recalcularTotalDesdeMemoria();
            }
        });

        // Evento Blur: Si el usuario deja el campo vacío y hace clic afuera, poner 1
        $(document).on('blur', '.input-edit-cant', function() {
            if ($(this).val() === '' || isNaN($(this).val())) {
                $(this).val(1).trigger('input');
            }
        });

        // Evento para el PRECIO (Separado para no mezclar lógicas)
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
        // Evento Blur: Si deja vacío, volver a 1
        $(document).on('blur', '.input-edit-cant', function() {
            if ($(this).val() === '' || isNaN($(this).val())) {
                $(this).val(1).trigger('change');
            }
        });

        // D. Eliminar Item
        $(document).on('click', '.btn-eliminar-item', function() {
            delete carrito[$(this).closest('tr').data('lote-id')];
            renderCarrito();
        });

        // E. Funciones de Totales
        function recalcularTotalDesdeMemoria() {
            let total = 0;
            Object.values(carrito).forEach(i => total += (i.cantidad * i.precio_venta));
            actualizarTotalGlobal(total);
        }

        function actualizarTotalGlobal(total) {
            $('#total-venta').text(total.toFixed(2));
            $('#input-items-json').val(JSON.stringify(Object.values(carrito)));

            // Llamar a calculadora de vuelto (en create.blade.php)
            if (window.calcularVuelto) window.calcularVuelto(total);
        }

        // ==========================================
        // 5. CATEGORÍAS (Lógica Visual + Teclado)
        // ==========================================
        const categorias = window.listadoCategorias || [];

        // Variables para navegación con teclado
        let catSelectedIndex = -1;
        let catResultCount = 0;

        // A. EVENTO INPUT/FOCUS: Filtrar y Mostrar
        $('#busqueda_categoria').on('input focus', function() {
            let txt = $(this).val().toLowerCase();
            let cont = $('#resultados-categorias').empty();

            // Resetear navegación
            catSelectedIndex = -1;

            if (!categorias.length) return;

            // Filtrar
            let match = categorias.filter(c => c.nombre.toLowerCase().includes(txt));
            catResultCount = match.length; // Guardamos cuántos hay

            if (!match.length) {
                cont.hide();
                return;
            }

            // Renderizar botones
            match.forEach(c => {
                cont.append(`
                    <button type="button" 
                            class="list-group-item list-group-item-action py-1 px-2 item-categoria" 
                            data-id="${c.id}" 
                            data-nombre="${c.nombre}">
                        ${c.nombre}
                    </button>
                `);
            });

            cont.show();
        });

        // B. EVENTO TECLADO (FLECHAS Y ENTER)
        $('#busqueda_categoria').on('keydown', function(e) {
            let $lista = $('#resultados-categorias');
            if (!$lista.is(':visible') || catResultCount === 0) return;

            // FLECHA ABAJO (40)
            if (e.which === 40) {
                e.preventDefault();
                catSelectedIndex++;
                if (catSelectedIndex >= catResultCount) catSelectedIndex = 0; // Vuelve al inicio
                highlightCategoria();
            }
            // FLECHA ARRIBA (38)
            else if (e.which === 38) {
                e.preventDefault();
                catSelectedIndex--;
                if (catSelectedIndex < 0) catSelectedIndex = catResultCount - 1; // Vuelve al final
                highlightCategoria();
            }
            // ENTER (13)
            else if (e.which === 13) {
                e.preventDefault();
                if (catSelectedIndex > -1) {
                    // Simular clic en el elemento seleccionado
                    $lista.find('.item-categoria').eq(catSelectedIndex).click();
                }
            }
        });

        // Función para resaltar visualmente
        function highlightCategoria() {
            let items = $('#resultados-categorias').find('.item-categoria');
            items.removeClass('active-key'); // Quitamos color a todos

            if (catSelectedIndex > -1) {
                let actual = items.eq(catSelectedIndex);
                actual.addClass('active-key'); // Ponemos color al actual

                // Scroll automático si la lista es larga
                actual[0].scrollIntoView({
                    block: 'nearest'
                });
            }
        }

        // C. SELECCIÓN (CLICK O ENTER)
        $(document).on('click', '.item-categoria', function() {
            $('#filtro_categoria_id').val($(this).data('id'));
            $('#busqueda_categoria').val($(this).data('nombre'));

            $('#resultados-categorias').hide();
            $('#btn-limpiar-cat').show();

            // Foco al buscador de medicamentos para seguir vendiendo rápido
            $('#busqueda_medicamento').focus();

            buscarMedicamentos();
        });

        // D. BOTÓN LIMPIAR
        $('#btn-limpiar-cat').click(function() {
            $('#filtro_categoria_id').val('');
            $('#busqueda_categoria').val('').focus(); // Volver foco al input
            $(this).hide();
            buscarMedicamentos();
        });

        // E. CERRAR AL CLIC FUERA
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-container').length) cerrarResultados();
            if (!$(e.target).closest('.search-container-cat').length) $('#resultados-categorias').hide();
        });
    });
</script>