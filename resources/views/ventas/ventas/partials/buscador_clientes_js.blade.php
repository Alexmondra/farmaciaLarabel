<script>
    $(document).ready(function() {

        /* ==========================================
           1. CONFIGURACIÓN Y CONSTANTES
           ========================================== */
        const RUTA_CHECK_CLIENTE = "{{ route('clientes.check') }}";

        // Elementos DOM (Cacheamos para mayor velocidad)
        const $tipoComp = $('#tipo_comprobante');
        const $inputDoc = $('#busqueda_cliente');
        const $labelDoc = $('#label-documento');
        const $displayNombre = $('#nombre_cliente_display');
        const $loader = $('#loader-cliente');

        // Botones de acción
        const $btnBuscar = $('#btn-buscar-cliente'); // Lupa (opcional)
        const $btnCrear = $('#btn-crear-cliente'); // Botón +
        const $btnVer = $('#btn-ver-cliente'); // Botón Ojo
        const $inputHidden = $('#cliente_id_hidden'); // ID para el formulario

        let clienteSeleccionadoID = null;

        /* ==========================================
           2. EVENTO: CAMBIO DE TIPO (BOLETA/FACTURA)
           ========================================== */
        $tipoComp.on('change', function() {
            let tipo = $(this).val();
            let isFactura = (tipo === 'FACTURA');
            let maxLen = isFactura ? 11 : 8;

            // Ajustes visuales y restricciones
            $labelDoc.text(isFactura ? 'NÚMERO (RUC)' : 'NÚMERO (DNI)');
            $inputDoc.attr('placeholder', `Ingrese ${maxLen} dígitos`);
            $inputDoc.attr('maxlength', maxLen);

            // Limpieza total al cambiar
            $inputDoc.val('').removeClass('border-primary is-invalid').focus();
            resetClienteUI();
            $displayNombre.val('--- Cliente General ---');
        });

        /* ==========================================
           3. LÓGICA DE INPUT (BÚSQUEDA AUTOMÁTICA)
           ========================================== */
        $inputDoc.on('input', function() {
            // A. Sanitización: Permitir SOLO números
            this.value = this.value.replace(/\D/g, '');

            let valor = $(this).val();
            let tipo = $tipoComp.val();
            let requerido = (tipo === 'FACTURA') ? 11 : 8;

            // B. Si longitud es correcta -> DISPARAR BÚSQUEDA
            if (valor.length === requerido) {
                buscarClienteAutomatico(valor);
            } else {
                // Si el usuario borra dígitos, reseteamos la UI para no confundir
                if (clienteSeleccionadoID !== null || $btnCrear.is(':visible')) {
                    resetClienteUI();
                    $displayNombre.val('Escribiendo...');
                }
            }
        });

        // Opción: Buscar al presionar Enter (por si acaso)
        $inputDoc.on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                // Forzamos el evento input para validar longitud
                $inputDoc.trigger('input');
            }
        });

        /* ==========================================
           4. FUNCIÓN AJAX PRINCIPAL
           ========================================== */
        function buscarClienteAutomatico(doc) {
            // UI Loading (Mostrar reloj)
            $loader.removeClass('d-none');
            $inputDoc.addClass('border-primary');

            $.get(RUTA_CHECK_CLIENTE, {
                    doc: doc
                })
                .done(function(res) {
                    if (res.exists) {
                        // CASO A: CLIENTE ENCONTRADO
                        seleccionarCliente(res.data);
                        if (typeof toastr !== 'undefined') toastr.success('Cliente seleccionado correctamente.');
                    } else {
                        // CASO B: NO EXISTE
                        habilitarCreacion();
                        if (typeof toastr !== 'undefined') toastr.info('Cliente no existe. Puede registrarlo.');
                    }
                })
                .fail(function() {
                    alert('Error de conexión al buscar cliente.');
                })
                .always(function() {
                    // Finalizar Loading (Ocultar reloj)
                    $loader.addClass('d-none');
                    $inputDoc.removeClass('border-primary');
                });
        }

        /* ==========================================
           5. FUNCIONES DE ESTADO DE INTERFAZ
           ========================================== */

        // Estado: Cliente Seleccionado (Éxito)
        function seleccionarCliente(data) {
            clienteSeleccionadoID = data.id;
            $inputHidden.val(data.id);

            let nombre = (data.tipo_documento === 'RUC') ? data.razon_social : `${data.nombre} ${data.apellidos}`;

            $displayNombre.val(nombre).removeClass('text-danger not-found').addClass('text-primary font-weight-bold');

            // Gestión de botones
            $btnCrear.addClass('d-none');
            $btnVer.removeClass('d-none'); // Mostrar Ojito
        }

        // Estado: Cliente No Encontrado (Habilitar Registro)
        function habilitarCreacion() {
            resetClienteUI();
            $displayNombre.val('NO REGISTRADO (Click en + para crear)').addClass('text-danger not-found');

            // Gestión de botones
            $btnCrear.removeClass('d-none'); // Mostrar +
            $btnVer.addClass('d-none');
        }

        // Estado: Reset (Limpio)
        function resetClienteUI() {
            clienteSeleccionadoID = null;
            $inputHidden.val('');
            $btnCrear.addClass('d-none');
            $btnVer.addClass('d-none');
            $displayNombre.removeClass('text-danger text-primary not-found font-weight-bold');
        }

        /* ==========================================
           6. CONEXIÓN CON MODALES (GLOBALES)
           ========================================== */

        // Botón + (Crear Nuevo)
        $btnCrear.click(function() {
            let docActual = $inputDoc.val();

            // Verificamos si la función del modal existe
            if (typeof openCreateModal === 'function') {
                openCreateModal();

                // Pre-llenado inteligente con un pequeño delay para asegurar carga del modal
                setTimeout(() => {
                    let tipo = $tipoComp.val() === 'FACTURA' ? 'RUC' : 'DNI';
                    $('#tipo_documento').val(tipo).trigger('change');
                    $('#documento').val(docActual);

                    // Disparar la validación interna del modal para que busque si está libre
                    if (typeof verifyDocument === 'function') verifyDocument(docActual);
                }, 200);
            } else {
                console.error('Error: La función openCreateModal no está definida.');
            }
        });

        // Botón Ojito (Ver Historial)
        $btnVer.click(function() {
            if (clienteSeleccionadoID && typeof openShowModal === 'function') {
                openShowModal(clienteSeleccionadoID);
            }
        });

        /* ==========================================
           7. CALLBACK MÁGICO (POST-GUARDADO)
           ========================================== */
        // Esta función es llamada automáticamente por el modal "Create" cuando guarda con éxito.
        // En lugar de recargar una tabla, aquí "recargamos" el input para seleccionarlo.
        window.reloadTable = function() {
            let docNuevo = $('#documento').val(); // Obtenemos el DNI del modal antes de cerrarse/limpiarse
            if (docNuevo) {
                $inputDoc.val(docNuevo);
                buscarClienteAutomatico(docNuevo); // Auto-seleccionar al recién nacido
            }
        };

        // Disparar cambio inicial para setear placeholders
        $tipoComp.trigger('change');
    });
</script>