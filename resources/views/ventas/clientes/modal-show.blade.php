<div class="modal fade" id="modalShowCliente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0" style="border-radius: 15px; overflow: hidden;">

            <div class="modal-header text-white" style="background: linear-gradient(135deg, #00d2d3 0%, #00a8ff 100%); border:0;">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-id-card-alt mr-2"></i> Expediente del Cliente
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-0">
                <div class="row no-gutters">

                    {{-- PERFIL (col-12 en móvil, col-md-4 en desktop) --}}
                    <div class="col-12 col-md-4 bg-light text-center p-4 border-right profile-sidebar">
                        <div class="mb-3">
                            <div id="show_avatar" class="d-flex align-items-center justify-content-center mx-auto shadow-sm"
                                style="width: 100px; height: 100px; border-radius: 50%; background: #e0f7fa; color: #00bcd4; font-size: 2.5rem; font-weight: bold; border: 4px solid #fff;">
                            </div>
                        </div>

                        <h4 id="show_nombre" class="font-weight-bold text-dark mb-1 text-uppercase">--</h4>
                        <span id="show_tipo_doc" class="badge badge-info px-3 py-1 mb-3">--</span>

                        <div class="card bg-white border-0 shadow-sm mt-3 p-3 text-left">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.65rem;">Saldo Disponible</small>
                                <span id="show_equivalencia" class="badge badge-success px-2 py-1 shadow-sm" style="font-size: 0.85rem;">
                                    S/ 0.00
                                </span>
                            </div>

                            <div class="d-flex align-items-center mt-2">
                                <i class="fas fa-star text-warning mr-2 fa-lg"></i>
                                <h3 id="show_puntos" class="mb-0 font-weight-bold text-dark" style="font-size: 1.8rem;">0</h3>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-outline-secondary btn-block btn-sm" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>

                    {{-- TABS (col-12 en móvil, col-md-8 en desktop) --}}
                    <div class="col-12 col-md-8 bg-white p-0">
                        <ul class="nav nav-tabs nav-justified" id="clienteTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active py-3 font-weight-bold" id="info-tab" data-toggle="tab" href="#info" role="tab">Información</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link py-3 font-weight-bold" id="history-tab" data-toggle="tab" href="#history" role="tab">Historial</a>
                            </li>
                        </ul>

                        <div class="tab-content p-4">
                            <div class="tab-pane fade show active" id="info">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small font-weight-bold">Email</label>
                                        <p id="show_email" class="font-weight-bold text-dark mb-0">--</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small font-weight-bold">Teléfono</label>
                                        <p id="show_telefono" class="font-weight-bold text-dark mb-0">--</p>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="text-muted small font-weight-bold">Dirección</label>
                                        <p id="show_direccion" class="font-weight-bold text-dark mb-0">--</p>
                                    </div>
                                    <div class="col-md-6 mb-3" id="block-sexo">
                                        <label class="text-muted small font-weight-bold">Sexo</label>
                                        <p id="show_sexo" class="font-weight-bold text-dark mb-0">--</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small font-weight-bold">Registrado el</label>
                                        <p id="show_registro" class="font-weight-bold text-dark mb-0">--</p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="history">
                                <div id="history-container" class="text-center py-4"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ... (Estilos Dark Mode existentes) ... */

    .dark-mode .nav-tabs .nav-link:not(.active) {
        color: #adb5bd;
        /* Hace que el tab inactivo sea legible en dark mode */
        background-color: #3f474e;
        border-color: #56606a;
    }

    .dark-mode .nav-tabs {
        border-bottom-color: #56606a;
    }

    @media (max-width: 767.98px) {

        /* Fuerza padding reducido en móvil para el contenido */
        .tab-content.p-4 {
            padding: 1.5rem !important;
        }

        .profile-sidebar {
            border-right: none !important;
            border-bottom: 1px solid #e9ecef;
            /* Separador cuando se apila */
        }

        .dark-mode .profile-sidebar {
            border-bottom-color: #56606a !important;
        }

        .modal-body .row .col-md-6,
        .modal-body .row .col-md-12 {
            margin-bottom: 0.75rem !important;
        }
    }
</style>
<script>
    let currentVentas = [];

    function openShowModal(id) {
        // 1. Definimos la URL ÚNICA (Cache Busting)
        // Usamos Date.now() para que sea siempre diferente y obligue a ir a la BD
        let urlFresca = "{{ url('clientes') }}/" + id + "?nocache=" + Date.now();

        // Limpieza UI
        $('#show_avatar').html('<i class="fas fa-spinner fa-spin"></i>');
        $('#show_nombre').text('Cargando...');
        $('#show_equivalencia').html('...');
        $('#history-container').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-info"></i></div>');

        $('#modalShowCliente').modal('show');

        // 2. AJAX (CORREGIDO: Usamos la variable urlFresca)
        $.get(urlFresca, function(response) {

            if (!response.success) {
                alert('No se pudo cargar la información.');
                $('#modalShowCliente').modal('hide');
                return;
            }

            const c = response.data;
            const conf = response.config;

            // Guardamos las ventas
            currentVentas = c.ventas || [];

            // --- DEPURACIÓN (Solo para verificar en consola F12) ---
            console.log("Datos frescos recibidos:", currentVentas);

            // --- A. LÓGICA DE PERFIL ---
            const isRUC = (c.tipo_documento === 'RUC');
            const nombre = isRUC ? c.razon_social : `${c.nombre} ${c.apellidos}`;

            if (isRUC) {
                $('#show_avatar').css({
                    background: '#fff3e0',
                    color: '#ff9800'
                }).html('<i class="fas fa-building"></i>');
                $('#block-sexo').addClass('d-none');
            } else {
                $('#show_avatar').css({
                    background: '#e0f7fa',
                    color: '#00bcd4'
                }).text((nombre || '?').charAt(0));
                $('#block-sexo').removeClass('d-none');
                $('#show_sexo').text(c.sexo === 'M' ? 'Masculino' : 'Femenino');
            }

            $('#show_nombre').text(nombre || 'SIN DATOS');
            $('#show_tipo_doc').text(`${c.tipo_documento}: ${c.documento}`);
            $('#show_registro').text(new Date(c.created_at).toLocaleDateString('es-PE'));

            // --- B. CONTACTO ---
            const setText = (sel, val) => $(sel).text((val && val !== '--') ? val : 'No registrado');
            setText('#show_email', c.email);
            setText('#show_telefono', c.telefono);
            setText('#show_direccion', c.direccion);

            // --- C. PUNTOS ---
            let puntos = c.puntos || 0;
            $('#show_puntos').text(puntos);
            let valorUnitario = parseFloat(conf.valor_punto);
            let dinero = (puntos * valorUnitario).toFixed(2);
            $('#show_equivalencia').html(`<i class="fas fa-money-bill-wave mr-1"></i> S/ ${dinero}`);

            // --- D. HISTORIAL ---
            const rows = currentVentas.map((v, index) => `
                <tr>
                    <td class="text-muted small align-middle">${new Date(v.created_at).toLocaleDateString('es-PE')}</td>
                    <td class="font-weight-bold align-middle">S/ ${parseFloat(v.total_neto || v.total).toFixed(2)}</td>
                    <td class="align-middle">
                        <button class="btn btn-sm btn-outline-info" onclick="verDetalleVenta(${index})" title="Ver productos">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>`).join('');

            const tableHtml = rows ?
                `<div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-hover table-sm text-center mb-0">
                    <thead class="bg-light text-muted small sticky-top">
                        <tr><th>FECHA</th><th>TOTAL</th><th>DETALLE</th></tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table></div>` :
                `<div class="text-center py-4"><i class="fas fa-shopping-basket fa-2x text-muted opacity-25 mb-2"></i><p class="text-muted small">Sin historial reciente</p></div>`;

            $('#history-container').html(tableHtml);

        }).fail(function() {
            alert('Error de conexión.');
            $('#modalShowCliente').modal('hide');
        });
    }

    // --- FUNCIÓN DETALLE (SIN CAMBIOS, AHORA FUNCIONARÁ) ---
    function verDetalleVenta(index) {
        const venta = currentVentas[index];
        // Ahora sí funcionará porque el controlador envía 'detalle_ventas'
        const detalles = venta.detalle_ventas || [];

        if (detalles.length === 0) {
            Swal.fire('Info', 'No hay detalles registrados para esta venta (o error de carga).', 'info');
            return;
        }

        let htmlDetalles = `
            <table class="table table-sm text-left" style="font-size: 0.9rem;">
                <thead class="bg-light">
                    <tr>
                        <th>Prod.</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-right">Importe</th>
                    </tr>
                </thead>
                <tbody>
        `;

        detalles.forEach(d => {
            const nombreProd = d.medicamento ? d.medicamento.nombre : '(Producto Eliminado)';
            htmlDetalles += `
                <tr>
                    <td>${nombreProd}</td>
                    <td class="text-center">${d.cantidad}</td>
                    <td class="text-right">S/ ${parseFloat(d.subtotal_bruto || d.subtotal).toFixed(2)}</td>
                </tr>
            `;
        });

        htmlDetalles += `</tbody></table>`;

        Swal.fire({
            title: `Venta del ${new Date(venta.created_at).toLocaleDateString('es-PE')}`,
            html: htmlDetalles,
            width: '600px',
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#6c5ce7'
        });
    }
</script>