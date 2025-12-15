<script>
    // ==========================================
    // VARIABLES GLOBALES
    // ==========================================
    var timerBusqueda;
    var currentFocus = -1; // Para navegar con flechas

    var itemsGuia = @json(old('items') ? json_decode(old('items')) : []);
    if (!Array.isArray(itemsGuia)) itemsGuia = [];

    // RUTAS (Blade las imprime aquí)
    const URL_BUSCAR_PROD = "{{ route('guias.lookup_medicamentos') }}";
    const URL_BUSCAR_VENTA = "{{ route('guias.buscar_venta') }}";
    const SUC_ID = "{{ $sucursalOrigen->id ?? '1' }}";

    // VALORES POR DEFECTO (Para restaurar origen)
    const DIR_DEFAULT = "{{ $sucursalOrigen->direccion }}";
    const UBI_DEFAULT = "{{ $sucursalOrigen->ubigeo }}";
    const COD_DEFAULT = "{{ $sucursalOrigen->codigo ?? '0000' }}";

    document.addEventListener('DOMContentLoaded', function() {

        if (itemsGuia.length > 0) {
            renderTabla();
        }
        // ============================================================
        // 1. LÓGICA DE BÚSQUEDA DE VENTA (IMPORTAR)
        // ============================================================
        $('#btnBuscarVenta').click(function() {
            let q = $('#txtBuscarVenta').val().trim();
            if (!q) return Swal.fire('Falta Serie', 'Ingrese serie y número (Ej: B001-23)', 'warning');

            Swal.fire({
                title: 'Buscando...',
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: URL_BUSCAR_VENTA,
                type: 'GET',
                data: {
                    q: q
                },
                success: function(response) {
                    Swal.close();

                    // A. LLENAR ITEMS EN LA TABLA
                    // Reemplazamos lo que había (o puedes hacer push si prefieres mezclar)
                    itemsGuia = response.items;
                    renderTabla();

                    // B. LLENAR DATOS DEL CLIENTE (DESTINO)
                    let cli = response.cliente;

                    $('#inputDestinatario').val(cli.nombre);
                    $('#inputDocDestinatario').val(cli.documento);
                    $('#inputDireccion').val(cli.direccion || '');

                    if (cli.ubigeo) $('#inputUbigeo').val(cli.ubigeo);

                    // C. LLENAR IDS OCULTOS
                    $('#inputClienteId').val(cli.id);
                    $('#inputVentaId').val(response.venta_id);

                    // Notificación
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Venta importada correctamente'
                    });
                },
                error: function(xhr) {
                    Swal.close();
                    let msg = xhr.responseJSON ? xhr.responseJSON.error : 'No se pudo buscar la venta';
                    Swal.fire('Error', msg, 'error');
                }
            });
        });

        // Permitir Enter en el input de Buscar Venta
        $('#txtBuscarVenta').keypress(function(e) {
            if (e.which == 13) {
                e.preventDefault();
                $('#btnBuscarVenta').click();
            }
        });


        // ============================================================
        // 2. BUSCADOR DE PRODUCTOS MANUAL (ESTILO POS + LOTES)
        // ============================================================

        // A. EVENTOS DE TECLADO (FLECHAS Y ENTER)
        $('#busqueda_medicamento').on('keydown', function(e) {
            let lista = $('#res-busqueda');
            let items = lista.find('a.item-res');

            if (e.key === 'ArrowDown') {
                currentFocus++;
                addActive(items);
                e.preventDefault();
            } else if (e.key === 'ArrowUp') {
                currentFocus--;
                addActive(items);
                e.preventDefault();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (currentFocus > -1) {
                    if (items[currentFocus]) items[currentFocus].click(); // Simular click
                }
            }
        });

        function addActive(items) {
            if (!items || items.length === 0) return false;
            items.removeClass('active'); // Limpiar anterior

            if (currentFocus >= items.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = (items.length - 1);

            let target = $(items[currentFocus]);
            target.addClass('active');
            target[0].scrollIntoView({
                block: 'nearest'
            });
        }

        // B. INPUT AJAX (BUSCAR)
        $('#busqueda_medicamento').on('input', function() {
            clearTimeout(timerBusqueda);
            let q = $(this).val().trim();
            let lista = $('#res-busqueda');

            currentFocus = -1;

            if (q.length < 1) {
                lista.hide();
                return;
            }

            timerBusqueda = setTimeout(() => {
                $.get(URL_BUSCAR_PROD, {
                    q: q,
                    sucursal_id: SUC_ID
                }, function(data) {
                    let html = '';
                    if (!data.length) {
                        html = '<div class="list-group-item small text-muted">No encontrado / Sin Stock</div>';
                    } else {
                        data.forEach(p => {
                            // Guardamos todo el objeto en data-json
                            let jsonStr = encodeURIComponent(JSON.stringify(p));

                            html += `
                            <a href="#" class="list-group-item list-group-item-action py-2 item-res"
                               data-json="${jsonStr}">
                               <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="text-navy">${p.nombre}</strong> 
                                        <small class="d-block text-muted">${p.presentacion || ''}</small>
                                    </div>
                                    <span class="badge badge-light border">${p.codigo}</span>
                               </div>
                            </a>`;
                        });
                    }
                    lista.html(html).show();
                });
            }, 300);
        });

        // C. CLICK EN RESULTADO -> ABRIR MODAL
        $(document).on('click', '.item-res', function(e) {
            e.preventDefault();

            let rawJson = $(this).data('json');
            let producto = JSON.parse(decodeURIComponent(rawJson));

            // Limpiar buscador
            $('#res-busqueda').hide();
            $('#busqueda_medicamento').val('');

            // ABRIR EL MODAL DE LOTES
            abrirModalLotes(producto);
        });

        // D. CONSTRUIR MODAL LOTES
        function abrirModalLotes(producto) {
            let lotes = producto.lotes || [];

            if (lotes.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin Stock',
                    text: 'No hay lotes disponibles.'
                });
                return;
            }

            let htmlTable = `
                <div class="text-left mb-2">
                    <h5 class="font-weight-bold text-navy mb-0">${producto.nombre}</h5>
                </div>
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-bordered text-center mb-0" style="font-size: 0.9rem;">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th>Lote</th>
                                <th>Vence</th>
                                <th>Stock</th>
                                <th width="100">Cant.</th>
                            </tr>
                        </thead>
                        <tbody>`;

            lotes.forEach(l => {
                htmlTable += `
                    <tr>
                        <td class="align-middle font-weight-bold">${l.codigo_lote}</td>
                        <td class="align-middle">${l.vencimiento}</td>
                        <td class="align-middle text-success font-weight-bold">${parseFloat(l.stock)}</td>
                        <td class="align-middle">
                            <input type="number" class="form-control form-control-sm text-center input-lote-qty font-weight-bold" 
                                min="0" max="${l.stock}" 
                                data-id="${l.id}" data-cod="${l.codigo_lote}" data-stock="${l.stock}"
                                placeholder="0" onfocus="this.select()">
                        </td>
                    </tr>`;
            });
            htmlTable += `</tbody></table></div>`;

            Swal.fire({
                html: htmlTable,
                width: '600px',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-plus"></i> Agregar',
                confirmButtonColor: '#20c997',
                focusConfirm: false,
                didOpen: () => {
                    let firstInput = document.querySelector('.input-lote-qty');
                    if (firstInput) firstInput.focus();
                    $('.input-lote-qty').on('keydown', function(e) {
                        if (e.key === 'Enter') Swal.clickConfirm();
                    });
                },
                preConfirm: () => {
                    let seleccionados = [];
                    let inputs = document.querySelectorAll('.input-lote-qty');
                    let totalQty = 0;
                    let error = null;

                    inputs.forEach(inp => {
                        let cant = parseFloat(inp.value);
                        let stock = parseFloat(inp.dataset.stock);
                        if (cant > 0) {
                            if (cant > stock) error = `El lote ${inp.dataset.cod} solo tiene ${stock} unidades.`;
                            seleccionados.push({
                                medicamento_id: producto.id,
                                codigo: producto.codigo,
                                descripcion: `${producto.nombre} - Lote: ${inp.dataset.cod}`,
                                lote_id: inp.dataset.id,
                                cantidad: cant
                            });
                            totalQty += cant;
                        }
                    });

                    if (error) {
                        Swal.showValidationMessage(error);
                        return false;
                    }
                    if (totalQty === 0) {
                        Swal.showValidationMessage('Ingrese al menos una cantidad.');
                        return false;
                    }

                    return seleccionados;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    result.value.forEach(item => agregarItemTabla(item));
                    setTimeout(() => $('#busqueda_medicamento').focus(), 300);
                }
            });
        }


        // ============================================================
        // 3. GESTIÓN DE LA TABLA (AGREGAR/ELIMINAR)
        // ============================================================
        function agregarItemTabla(item) {
            // Buscamos si ya existe ese producto CON ESE LOTE
            // Nota: Si viene de Venta, item.lote_id puede ser null, así que usamos codigo/medicamento_id
            let existente = itemsGuia.find(i =>
                (i.lote_id && i.lote_id == item.lote_id) ||
                (!i.lote_id && i.medicamento_id == item.medicamento_id)
            );

            if (existente) {
                existente.cantidad += parseFloat(item.cantidad);
            } else {
                itemsGuia.push(item);
            }
            renderTabla();
        }

        window.eliminarItem = function(index) {
            itemsGuia.splice(index, 1);
            renderTabla();
        }

        function renderTabla() {
            let tbody = $('#tablaItems tbody');
            let html = '';

            if (itemsGuia.length === 0) {
                $('#msg-vacio').show();
                tbody.empty();
                $('#lbl-conteo').text('0 Items');
            } else {
                $('#msg-vacio').hide();
                $('#lbl-conteo').text(itemsGuia.length + ' Items');

                itemsGuia.forEach((it, i) => {
                    // Asegurar que las descripciones no son nulas al renderizar
                    const descripcion = it.descripcion || 'Producto sin descripción';

                    html += `
                    <tr>
                        <td class="pl-3 py-2">
                            <span class="badge badge-light border mr-1">${it.codigo || 'S/C'}</span> 
                            <span class="text-navy font-weight-bold">${descripcion}</span>
                        </td>
                        <td class="text-center py-2">
                            <span class="font-weight-bold" style="font-size:1.1rem">${it.cantidad}</span>
                        </td>
                        <td class="py-2 text-right pr-3">
                            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="eliminarItem(${i})">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>`;
                });
                tbody.html(html);
            }
            $('#inputItemsJson').val(JSON.stringify(itemsGuia));
        }

        window.eliminarItem = function(index) {
            itemsGuia.splice(index, 1);
            renderTabla();
        }

        function renderTabla() {
            let tbody = $('#tablaItems tbody');
            let html = '';

            if (itemsGuia.length === 0) {
                $('#msg-vacio').show();
                tbody.empty();
                $('#lbl-conteo').text('0 Items');
            } else {
                $('#msg-vacio').hide();
                $('#lbl-conteo').text(itemsGuia.length + ' Items');

                itemsGuia.forEach((it, i) => {
                    html += `
                    <tr>
                        <td class="pl-3 py-2">
                            <span class="badge badge-light border mr-1">${it.codigo}</span> 
                            <span class="text-navy font-weight-bold">${it.descripcion}</span>
                        </td>
                        <td class="text-center py-2">
                            <span class="font-weight-bold" style="font-size:1.1rem">${it.cantidad}</span>
                        </td>
                        <td class="py-2 text-right pr-3">
                            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="eliminarItem(${i})">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>`;
                });
                tbody.html(html);
            }
            $('#inputItemsJson').val(JSON.stringify(itemsGuia));
        }

        // Clic fuera para cerrar buscador
        $(document).click(function(e) {
            if (!$(e.target).closest('#busqueda_medicamento, #res-busqueda').length) {
                $('#res-busqueda').hide();
            }
        });


        // ============================================================
        // 4. LÓGICA DE CAMPOS (ORIGEN / DESTINO / MOTIVO)
        // ============================================================

        // A. MOTIVO (Cambia entre Venta o Traslado)
        $('#selectMotivo').change(function() {
            let motivo = $(this).val();

            // 1. Manejo visual del input "Descripción Manual"
            if (motivo === '13') { // OTROS
                $('#divDescripcionMotivo').removeClass('d-none');
                $('#inputDescripcionMotivo').prop('required', true).focus();
            } else {
                $('#divDescripcionMotivo').addClass('d-none');
                $('#inputDescripcionMotivo').prop('required', false).val('');
            }

            // 2. Resetear UI de Paneles
            $('#panel-importar-venta').hide();
            $('#panel-sucursal-destino').addClass('d-none');
            $('#box-buscador-manual').removeClass('d-none');

            // 3. Lógica por tipo
            if (motivo === '01') { // VENTA
                $('#panel-importar-venta').show();
                $('#box-buscador-manual').addClass('d-none');
                bloquearCamposDestino(false);

                // Clientes siempre es 0000
                $('#inputCodLlegada').val('0000');

            } else if (motivo === '04') { // TRASLADO SUCURSAL
                $('#panel-sucursal-destino').removeClass('d-none');
                bloquearCamposDestino(true);
                $('#inputDestinatario').val('');

                // El código se llenará cuando seleccione la sucursal en el otro select

            } else { // OTROS
                bloquearCamposDestino(false);
                $('#inputCodLlegada').val('0000');
            }
        });

        // B. SUCURSAL DESTINO (Rellena automático + CÓDIGO)
        $('#selectSucursalDestino').change(function() {
            let opt = $(this).find(':selected');
            if (opt.val()) {
                let ruc = opt.data('ruc') || '';
                let razon = opt.data('razon') || 'MI EMPRESA';
                let dir = opt.data('dir') || '';
                let ubi = opt.data('ubi') || '';
                let cod = opt.data('codigo') || '0000';

                $('#inputDestinatario').val(razon + ' - ' + opt.text().trim());
                $('#inputDocDestinatario').val(ruc);
                $('#inputUbigeo').val(String(ubi).padStart(6, '0'));
                $('#inputDireccion').val(dir);

                // AQUÍ ASIGNAMOS EL CÓDIGO DE LLEGADA AUTOMÁTICO
                $('#inputCodLlegada').val(cod);

                $('#inputClienteId').val('');
            }
        });

        // C. PARTIDA DISTINTA (Origen)
        $('#checkPartidaDistinta').change(function() {
            let isChecked = $(this).is(':checked');
            let inputs = $('#inputDirPartida, #inputUbiPartida');

            if (isChecked) {
                // MODO EDICIÓN
                inputs.prop('readonly', false).removeClass('bg-light').addClass('bg-white');
                $('#inputCodPartida').val('0000');
                $('#inputDirPartida').focus();
            } else {
                // MODO DEFAULT (Restaurar)
                inputs.prop('readonly', true).addClass('bg-light').removeClass('bg-white');
                $('#inputDirPartida').val(DIR_DEFAULT);
                $('#inputUbiPartida').val(UBI_DEFAULT);
                $('#inputCodPartida').val(COD_DEFAULT);
            }
        });

        // FUNCIONES AUXILIARES
        function bloquearCamposDestino(bloquear) {
            let campos = $('#inputDestinatario, #inputDocDestinatario, #inputUbigeo, #inputDireccion');
            campos.prop('readonly', bloquear);
            if (bloquear) campos.addClass('bg-light');
            else campos.removeClass('bg-light');
        }

        // Trigger inicial
        $('#selectMotivo').trigger('change');

        // ============================================================
        // 5. CONTROL DE TRANSPORTE (MOSTRAR / OCULTAR)
        // ============================================================
        $('#selectModalidad').on('change', function() {
            let modalidad = $(this).val();
            if (modalidad === '01') {
                $('.campo-privado').addClass('d-none');
                $('.campo-publico').removeClass('d-none');

            } else {
                $('.campo-publico').addClass('d-none');
                $('.campo-privado').removeClass('d-none');
            }
        });
        $('#selectModalidad').trigger('change');

    });
</script>