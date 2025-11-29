<script>
    $(document).ready(function() {

        const RUTA_LOOKUP_MEDICAMENTOS = "{{ route('ventas.lookup_medicamentos') }}";
        const RUTA_LOOKUP_LOTES = "{{ route('ventas.lookup_lotes') }}";
        const RUTA_BUSCAR_CLIENTE = "{{ route('ventas.buscar_cliente') ?? '/ruta-temporal-cliente' }}";
        const sucursalId = $('#sucursal_id').val();

        // Obtenemos las categorías que definimos en el HTML
        const categoriasData = window.listadoCategorias || [];

        let carrito = {};
        let medicamentoSeleccionado = null;
        let timeoutBusqueda = null;

        // ==========================================
        // 1. LOGICA DE CLIENTE (Tu código existente)
        // ==========================================
        $('#tipo_comprobante').on('change', function() {
            let tipo = $(this).val();
            let input = $('#busqueda_cliente');
            let label = $('#label-documento');
            $('#cliente_id_hidden').val('');
            $('#busqueda_cliente').val('');
            if (tipo === 'FACTURA') {
                label.text('RUC');
                input.attr('placeholder', '11 Dígitos');
            } else {
                label.text('DNI');
                input.attr('placeholder', '8 Dígitos');
            }
        });
        // (Aquí iría tu lógica AJAX de cliente si la tienes...)


        // ==========================================
        // 2. LOGICA DE CATEGORÍAS (LO NUEVO)
        // ==========================================

        // A. Filtrar categorías mientras escribes
        $('#busqueda_categoria').on('input focus', function() {
            let texto = $(this).val().toLowerCase();
            let contenedor = $('#resultados-categorias');
            contenedor.empty();

            // Si no hay categorías cargadas, salir
            if (categoriasData.length === 0) return;

            // Filtrar el array en JS (es instantáneo)
            let filtrados = categoriasData.filter(c => c.nombre.toLowerCase().includes(texto));

            if (filtrados.length === 0) {
                contenedor.hide();
                return;
            }

            filtrados.forEach(c => {
                contenedor.append(`
                    <button type="button" class="list-group-item list-group-item-action py-1 px-2 item-categoria" 
                            data-id="${c.id}" data-nombre="${c.nombre}">
                        ${c.nombre}
                    </button>
                `);
            });
            contenedor.show();
        });

        // B. Seleccionar una categoría
        $(document).on('click', '.item-categoria', function() {
            let id = $(this).data('id');
            let nombre = $(this).data('nombre');

            // 1. Poner el nombre en el input y guardar el ID oculto
            $('#filtro_categoria_id').val(id);
            $('#busqueda_categoria').val(nombre);

            // 2. Esconder lista y mostrar botón de limpiar
            $('#resultados-categorias').hide();
            $('#btn-limpiar-cat').show();

            // 3. !!! DISPARAR LA BÚSQUEDA DE MEDICAMENTOS INMEDIATAMENTE !!!
            // Esto llenará la lista de la derecha con los medicamentos de esa categoría
            buscarMedicamentos();
        });

        // C. Limpiar categoría (Botón X)
        $('#btn-limpiar-cat').on('click', function() {
            $('#filtro_categoria_id').val('');
            $('#busqueda_categoria').val('');
            $(this).hide();
            // Volver a buscar (limpiará la lista de medicamentos o mostrará vacío)
            buscarMedicamentos();
        });

        // D. Cerrar lista si clic fuera
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-container-cat').length) {
                $('#resultados-categorias').hide();
            }
        });


        // ==========================================
        // 3. BÚSQUEDA MEDICAMENTOS (CONECTADA)
        // ==========================================

        // Hacemos la función global para poder llamarla desde el onclick del botón lupa si queremos
        window.buscarMedicamentos = function() {
            let q = $('#busqueda_medicamento').val().trim();
            let categoriaId = $('#filtro_categoria_id').val();

            // Si NO hay texto escrito Y NO hay categoría seleccionada -> Limpiar
            if (q.length === 0 && !categoriaId) {
                $('#resultados-medicamentos').removeClass('active').empty();
                return;
            }

            // AJAX al servidor
            $.ajax({
                url: RUTA_LOOKUP_MEDICAMENTOS,
                method: 'GET',
                data: {
                    sucursal_id: sucursalId,
                    q: q,
                    categoria_id: categoriaId // Enviamos el ID seleccionado
                },
                success: function(data) {
                    renderResultadosMedicamentos(data);
                }
            });
        }

        function renderResultadosMedicamentos(lista) {
            let contenedor = $('#resultados-medicamentos');

            if (!lista.length) {
                contenedor.html('<div class="list-group-item text-muted small py-2">Sin resultados</div>');
                contenedor.addClass('active');
                return;
            }

            // Usamos un array para acumular el HTML (es más rápido que concatenar strings grandes)
            let htmlAcumulado = [];

            lista.forEach(function(m) {
                let presentacion = m.presentacion ? m.presentacion : '';
                // Usamos Template Literals
                htmlAcumulado.push(`
            <button type="button"
                    class="list-group-item list-group-item-action resultado-medicamento py-1 px-2"
                    data-medicamento-id="${m.medicamento_id}"
                    data-nombre="${m.nombre}"
                    data-codigo="${m.codigo ?? ''}"
                    data-presentacion="${presentacion}"
                    data-precio="${m.precio_venta}">
                <div class="d-flex justify-content-between align-items-center">
                    <div style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
                        <strong>${m.nombre}</strong> <span class="text-muted text-xs">(${presentacion})</span>
                    </div>
                    <div>
                        <span class="badge badge-light border">S/ ${parseFloat(m.precio_venta).toFixed(2)}</span>
                    </div>
                </div>
            </button>
        `);
            });

            contenedor.html(htmlAcumulado.join(''));
            contenedor.addClass('active');
        }

        // Evento Input Medicamentos (Retardo para no saturar)
        $('#busqueda_medicamento').on('input', function() {
            clearTimeout(timeoutBusqueda);
            timeoutBusqueda = setTimeout(function() {
                buscarMedicamentos();
            }, 250);
        });

        // ==========================================
        // 4. MODAL LOTES Y CARRITO (TU CÓDIGO ANTERIOR)
        // ==========================================

        // Solo asegúrate de que estas funciones están aquí (abre modal, focus, enter, agregar carrito)
        // ... (Pega aquí el bloque del modal lotes y carrito que ya tenías y funcionaba bien) ...

        // --- RESUMEN DE TU CÓDIGO DEL MODAL (LO MANTENEMOS) ---
        $(document).on('click', '.resultado-medicamento', function() {
            let btn = $(this);
            $('#resultados-medicamentos').removeClass('active');
            // Opcional: Limpiar inputs tras seleccionar
            // $('#busqueda_medicamento').val(''); 

            medicamentoSeleccionado = {
                medicamento_id: btn.data('medicamento-id'),
                nombre: btn.data('nombre'),
                codigo: btn.data('codigo'),
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
            if (primerInput.length) {
                primerInput.focus();
                primerInput.select();
            }
        });

        function cargarLotesMedicamento(medicamentoId) {
            $.ajax({
                url: RUTA_LOOKUP_LOTES,
                method: 'GET',
                data: {
                    medicamento_id: medicamentoId,
                    sucursal_id: sucursalId
                },
                async: false,
                success: function(lotes) {
                    let tbody = $('#modal-lotes-tbody');
                    tbody.empty();
                    if (!lotes.length) {
                        tbody.append('<tr><td colspan="6" class="text-center text-danger">Sin Stock</td></tr>');
                        return;
                    }
                    lotes.forEach(function(l) {
                        // 1. PREPARAMOS LOS PRECIOS
                        let precioNormal = l.precio_venta ? parseFloat(l.precio_venta) : 0;
                        let precioOferta = l.precio_oferta ? parseFloat(l.precio_oferta) : null;

                        // 2. LÓGICA: ¿Cuál precio vamos a cobrar?
                        // Si existe oferta y es menor al normal (opcional), usamos la oferta.
                        let precioFinal = precioNormal;
                        let htmlPrecio = `S/ ${precioNormal.toFixed(2)}`;

                        // Si hay oferta, cambiamos el HTML y el precio final
                        if (precioOferta !== null && precioOferta > 0) {
                            precioFinal = precioOferta;
                            htmlPrecio = `
            <small class="text-muted" style="text-decoration: line-through;">S/ ${precioNormal.toFixed(2)}</small>
            <br>
            <span class="text-danger font-weight-bold" title="Precio de Oferta">
                <i class="fas fa-tag"></i> S/ ${precioOferta.toFixed(2)}
            </span>
        `;
                        }

                        let stock = l.stock_actual;
                        let fecha = l.fecha_vencimiento ? l.fecha_vencimiento : '-';

                        // 3. RENDERIZAMOS LA FILA
                        // Fíjate que en 'data-precio' guardamos el precioFinal (sea oferta o normal)
                        // Así el botón "Agregar" jala el correcto automáticamente.

                        let rowClass = (precioOferta !== null) ? 'table-warning' : ''; // Opcional: Pinta amarillo suave si tiene oferta

                        tbody.append(`
        <tr data-lote-id="${l.id}" class="${rowClass}">
            <td class="text-xs align-middle">${l.codigo_lote}</td>
            <td class="text-xs align-middle">${fecha}</td>
            <td class="text-center font-weight-bold align-middle">${stock}</td>
            
            <td class="align-middle">
                <input type="number" class="form-control form-control-sm input-cant-lote text-center" min="1" max="${stock}" value="1">
            </td>
            
            <td class="align-middle text-right" style="line-height: 1.1;">
                ${htmlPrecio}
            </td>
            
            <td class="align-middle text-center">
                <button type="button" class="btn btn-sm btn-success btn-agregar-lote">
                    <i class="fas fa-plus"></i>
                </button>
            </td>

            <td style="display:none;" class="data-precio">${precioFinal}</td> 
            <td style="display:none;" class="data-codigo-lote">${l.codigo_lote}</td>
        </tr>
     `);
                    });
                }
            });
        }

        // Enter en input cantidad del modal
        $(document).on('keydown', '.input-cant-lote', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).closest('tr').find('.btn-agregar-lote').click();
            }
        });

        // Agregar al carrito (Click botón verde)
        $(document).on('click', '.btn-agregar-lote', function() {
            // ... (Tu lógica existente para agregar al objeto carrito y actualizar tabla) ...
            // ...
            let fila = $(this).closest('tr');
            let loteId = fila.data('lote-id');
            let cant = parseInt(fila.find('.input-cant-lote').val());
            // (Validaciones...)

            let item = {
                lote_id: loteId,
                medicamento_id: medicamentoSeleccionado.medicamento_id,
                nombre: medicamentoSeleccionado.nombre,
                codigo_lote: fila.find('.data-codigo-lote').text(),
                cantidad: cant,
                precio_venta: parseFloat(fila.find('.data-precio').text()),
                presentacion: medicamentoSeleccionado.presentacion
            };

            agregarAlCarrito(item);
            $('#modalLotes').modal('hide');
        });

        function agregarAlCarrito(item) {
            if (carrito[item.lote_id]) {
                carrito[item.lote_id].cantidad += item.cantidad;
            } else {
                carrito[item.lote_id] = item;
            }
            renderCarrito();
        }

        function renderCarrito() {
            let tbody = $('#carrito-tbody');
            tbody.empty();

            let total = 0;
            let itemsArray = Object.values(carrito);

            // 1. Si está vacío, mostrar mensaje bonito
            if (itemsArray.length === 0) {
                tbody.html(`
                    <tr id="carrito-vacio">
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-shopping-basket fa-3x mb-3 text-gray-300"></i><br>
                            El carrito está vacío.<br>
                            <small>Busque productos arriba para comenzar.</small>
                        </td>
                    </tr>
                `);
                $('#total-venta').text('0.00');
                $('#input-items-json').val('[]');
                return;
            }

            // 2. Si hay items, dibujar filas
            itemsArray.forEach(item => {
                let precio = parseFloat(item.precio_venta);
                let subtotal = item.cantidad * precio;
                total += subtotal;

                // Ajuste de presentación (si es null, pon vacío)
                let presentacion = item.presentacion ? item.presentacion : '';

                // AQUI ESTÁ LA MAGIA VISUAL:
                // - Usamos 'align-middle' para centrar verticalmente.
                // - 'text-right' para dineros.
                // - Agregamos data-lote-id al TR para que funcione el borrar.
                tbody.append(`
                    <tr data-lote-id="${item.lote_id}">
                        <td class="align-middle">
                            <span class="font-weight-bold text-dark">${item.nombre}</span><br>
                            <small class="text-muted">${presentacion}</small>
                        </td>

                        <td class="align-middle text-center">
                            <span class="font-weight-bold" style="font-size: 1.1rem;">${item.cantidad}</span>
                        </td>
                        <td class="align-middle text-right">
                            S/ ${precio.toFixed(2)}
                        </td>
                        <td class="align-middle text-right font-weight-bold text-success">
                            S/ ${subtotal.toFixed(2)}
                        </td>
                        <td class="align-middle text-center">
                            <button type="button" class="btn btn-xs btn-outline-danger btn-eliminar-item" title="Quitar del carrito">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });

            // 3. Actualizar Totales
            $('#total-venta').text(total.toFixed(2));
            $('#input-items-json').val(JSON.stringify(itemsArray));
        }

        // ==========================================
        // ELIMINAR ITEM (BOTÓN ROJO)
        // ==========================================
        $(document).on('click', '.btn-eliminar-item', function() {
            // Buscamos el ID en el TR padre
            let fila = $(this).closest('tr');
            let loteId = fila.data('lote-id');

            if (loteId) {
                delete carrito[loteId]; // Borrar del objeto JS
                renderCarrito(); // Re-dibujar la tabla
            }
        });

    });


    // ==========================================
    // 1. LOGICA DE CLIENTE (SIMPLIFICADA)
    // ==========================================

    // Ya no forzamos un valor por defecto al inicio.
    // Dejamos los inputs limpios para que el usuario decida.

    $('#btn-buscar-cliente').on('click', function() {
        let doc = $('#busqueda_cliente').val().trim();

        if (doc.length < 8) {
            alert("Ingrese un documento válido para buscar.");
            return;
        }

        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: "{{ route('ventas.buscar_cliente') }}",
            method: 'GET',
            data: {
                documento: doc
            },
            success: function(resp) {
                if (resp.success) {
                    // Cliente encontrado
                    $('#cliente_id_hidden').val(resp.cliente.id);
                    $('#nombre_cliente_display').val(resp.cliente.nombre_completo || resp.cliente.razon_social);
                    $('#busqueda_cliente').val('');
                } else {
                    // Cliente no encontrado
                    if (confirm('Cliente no existe. ¿Desea registrarlo ahora?')) {
                        $('#modalNuevoCliente').modal('show');
                    } else {
                        // Si dice que no, limpiamos para que sea venta anónima
                        $('#cliente_id_hidden').val('');
                        $('#nombre_cliente_display').val('--- Venta sin Cliente ---');
                    }
                }
            },
            error: function() {
                alert('Error al buscar cliente.');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-search"></i>');
            }
        });
    });
</script>