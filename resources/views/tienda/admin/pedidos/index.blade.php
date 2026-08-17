@extends('adminlte::page')

@section('title', 'Pedidos online')

@section('content_header')
<h1>Pedidos online</h1>
@endsection

@section('content')
@include('tienda.partials.alerts')
<div class="card shadow-sm border-0 rounded-xl overflow-hidden">
    <div class="card-body p-4">
        <!-- Formulario de filtros dinámicos -->
        <form id="filters-form" method="GET" class="row mb-4">
            <div class="col-md-6 mb-2 mb-md-0">
                <div class="input-group shadow-xs rounded-lg overflow-hidden border">
                    <div class="input-group-prepend border-0">
                        <span class="input-group-text bg-white border-0 text-muted pl-3 pr-2">
                            <i class="fas fa-search" id="search-icon"></i>
                        </span>
                    </div>
                    <input type="search" name="q" id="search-input" value="{{ request('q') }}" 
                           class="form-control border-0 pl-2 text-secondary font-weight-medium" 
                           placeholder="Buscar por código, documento o cliente..." autocomplete="off">
                </div>
            </div>
            <div class="col-md-6">
                <div class="shadow-xs rounded-lg overflow-hidden border">
                    <select name="estado" id="estado-select" class="form-control border-0 font-weight-medium text-secondary">
                        <option value="">Todos los estados</option>
                        @foreach ([
                            'PENDIENTE' => 'Pendiente',
                            'CONFIRMADO' => 'Confirmado',
                            'PREPARANDO' => 'Preparando',
                            'LISTO' => 'Listo para Recojo',
                            'ENTREGADO' => 'Entregado',
                            'CANCELADO' => 'Cancelado',
                        ] as $valor => $label)
                            <option value="{{ $valor }}" @selected(request('estado') === $valor)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <div class="position-relative">
            <!-- Tabla de pedidos -->
            <div id="pedidos-table-container" class="transition-all duration-300">
                @include('tienda.admin.pedidos.partials.table')
            </div>
        </div>
    </div>
</div>

<!-- Modal Entrega Pedido y Facturación -->
<div class="modal fade" id="modalEntregaPedido" tabindex="-1" role="dialog" aria-labelledby="modalEntregaPedidoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-xl border-0 shadow-lg">
            <div class="modal-header border-0 bg-light py-3">
                <h5 class="modal-title font-weight-bold text-dark" id="modalEntregaPedidoLabel">
                    <i class="fas fa-cash-register text-teal mr-2"></i> Confirmar Entrega y Cobro
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-entregar-facturar" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    <!-- Datos del pedido -->
                    <div class="alert alert-light border rounded-lg p-3 mb-3" style="font-size: 0.95rem;">
                        <div class="row">
                            <div class="col-6 mb-2">
                                <span class="text-xs text-muted d-block">CÓDIGO PEDIDO</span>
                                <strong class="text-dark" id="modal-pedido-codigo">-</strong>
                            </div>
                            <div class="col-6 mb-2">
                                <span class="text-xs text-muted d-block">TOTAL A COBRAR</span>
                                <strong class="text-teal" style="font-size: 1.15rem;">S/ <span id="modal-pedido-total">0.00</span></strong>
                            </div>
                            <div class="col-12">
                                <span class="text-xs text-muted d-block">CLIENTE</span>
                                <span class="text-dark font-weight-medium" id="modal-pedido-cliente">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tipo de Comprobante -->
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark text-xs uppercase" style="letter-spacing: 0.05em;">Tipo de Comprobante</label>
                        <select name="tipo_comprobante" id="modal-tipo-comprobante" class="form-control rounded-lg border-gray font-weight-medium" required>
                            <option value="BOLETA" selected>Boleta de Venta</option>
                            <option value="FACTURA">Factura</option>
                        </select>
                    </div>

                    <!-- Campos de Factura (Ocultos por defecto) -->
                    <div id="modal-factura-fields" class="d-none border rounded-lg p-3 mb-3 bg-light">
                        <div class="form-group mb-2">
                            <label class="font-weight-bold text-dark text-xs uppercase" style="letter-spacing: 0.05em;">RUC</label>
                            <div class="input-group">
                                <input type="text" name="cliente_ruc" id="modal-cliente-ruc" class="form-control rounded-lg border-gray" placeholder="Ingrese el RUC de 11 dígitos" maxlength="11">
                                <div class="input-group-append">
                                    <button type="button" id="btn-buscar-ruc" class="btn btn-outline-secondary rounded-right-lg"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark text-xs uppercase" style="letter-spacing: 0.05em;">Razón Social</label>
                            <input type="text" name="cliente_razon_social" id="modal-cliente-razon-social" class="form-control rounded-lg border-gray" placeholder="Razón social de la empresa">
                        </div>
                    </div>

                    <!-- Medio de Pago -->
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark text-xs uppercase" style="letter-spacing: 0.05em;">Medio de Pago</label>
                        <select name="medio_pago" id="modal-medio-pago" class="form-control rounded-lg border-gray font-weight-medium" required>
                            <option value="EFECTIVO" selected>Efectivo</option>
                            <option value="TARJETA">Tarjeta (Débito/Crédito)</option>
                            <option value="YAPE">Yape</option>
                            <option value="PLIN">Plin</option>
                        </select>
                    </div>

                    <!-- Control de Efectivo y Vuelto -->
                    <div id="modal-efectivo-fields" class="border rounded-lg p-3 mb-3 bg-light">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <label class="font-weight-bold text-dark text-xs uppercase mb-1" style="letter-spacing: 0.05em;">Paga con</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text border-right-0 bg-white">S/</span></div>
                                    <input type="number" step="0.01" min="0" name="paga_con" id="modal-paga-con" class="form-control border-left-0 rounded-right-lg font-weight-bold" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-6 text-right">
                                <span class="font-weight-bold text-dark text-xs uppercase d-block mb-1" style="letter-spacing: 0.05em;">Vuelto</span>
                                <strong class="text-dark" style="font-size: 1.3rem;">S/ <span id="modal-vuelto">0.00</span></strong>
                            </div>
                        </div>
                        <div id="modal-vuelto-error" class="text-danger small font-weight-bold mt-2 d-none">
                            <i class="fas fa-exclamation-triangle mr-1"></i> El monto recibido es menor al total del pedido.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-secondary rounded-lg font-weight-bold px-4" data-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn-modal-confirmar" class="btn btn-primary rounded-lg font-weight-bold px-4 shadow-xs">
                        <i class="fas fa-check mr-1"></i> Confirmar Entrega
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .rounded-xl { border-radius: 0.75rem !important; }
    .rounded-lg { border-radius: 0.5rem !important; }
    .shadow-xs { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
    .font-weight-medium { font-weight: 500; }
    .text-xs { font-size: 0.75rem; }
    
    /* Estilos clínicos suaves */
    .bg-success-light { background-color: #d1fae5 !important; }
    .bg-warning-light { background-color: #fef3c7 !important; }
    
    /* Efecto de foco en inputs y selects */
    .input-group:focus-within, select:focus {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
        transition: all 0.3s ease-in-out;
    }
    
    #search-input:focus, #estado-select:focus {
        outline: none !important;
        box-shadow: none !important;
    }
    
    /* Estilos para selectores de estado en la tabla según valor */
    .select-estado-pedido {
        padding-right: 1.5rem !important;
        cursor: pointer;
    }
    .select-estado-pedido[value="PENDIENTE"] { background-color: #f1f5f9; color: #475569; }
    .select-estado-pedido[value="CONFIRMADO"] { background-color: #dbeafe; color: #1d4ed8; }
    .select-estado-pedido[value="PREPARANDO"] { background-color: #eff6ff; color: #1e40af; }
    .select-estado-pedido[value="LISTO"] { background-color: #d1fae5; color: #065f46; }
    .select-estado-pedido[value="ENTREGADO"] { background-color: #10b981; color: #fff; }
    .select-estado-pedido[value="CANCELADO"] { background-color: #fee2e2; color: #991b1b; }
    .select-estado-pedido[value="CONVERTIDO_A_VENTA"] { background-color: #ccfbf1; color: #0f766e; }

    /* Botón minimalista */
    .btn-white {
        background-color: #fff;
        border: 1px solid #e2e8f0;
        color: #4a5568;
        transition: all 0.2s ease-in-out;
    }
    .btn-white:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1;
        color: #1a202c;
    }
    
    /* Transiciones */
    .transition-all {
        transition: all 0.3s ease-in-out;
    }
    .duration-300 {
        transition-duration: 300ms;
    }
</style>
@endpush

@push('js')
<script>
// Función global para actualizar el estado del pedido con AJAX o modal de entrega
async function actualizarEstadoPedido(select) {
    const url = select.dataset.url;
    const nuevoEstado = select.value;
    const previoEstado = select.dataset.prevVal;
    
    // Si intenta marcar como ENTREGADO, abrir modal de facturación (siempre)
    if (nuevoEstado === 'ENTREGADO') {
        // Revertir temporalmente el select
        select.value = previoEstado;
        
        const row = select.closest('tr');
        const pedidoId = row.dataset.pedidoId;
        const codigo = row.querySelector('td.font-mono').textContent.trim();
        const total = parseFloat(select.dataset.pedidoTotal);
        const clienteNombre = row.querySelector('.font-weight-bold.text-dark').textContent.trim();
        const clienteDoc = select.dataset.clienteDoc;
        const tipoDoc = select.dataset.clienteTipoDoc;
        const estadoPago = select.dataset.estadoPago;
        
        abrirModalEntrega(pedidoId, codigo, total, clienteNombre, clienteDoc, tipoDoc, estadoPago);
        return;
    }
    
    // Obtener elementos de carga locales en la fila
    const spinner = select.nextElementSibling;
    
    // Deshabilitar y mostrar loader
    select.disabled = true;
    if (spinner) spinner.classList.remove('d-none');

    try {
        const response = await fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ estado: nuevoEstado })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Actualizar valor de referencia y estilo visual
            select.dataset.prevVal = nuevoEstado;
            
            // Cambiar color de fondo dinámicamente según el estado
            actualizarEstiloSelect(select, nuevoEstado);
            
            // Toast de éxito
            Swal.fire({
                icon: 'success',
                title: 'Estado Actualizado',
                text: data.message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        } else {
            throw new Error(data.message || 'Error al actualizar el estado');
        }
    } catch (error) {
        console.error('Error:', error);
        // Revertir valor
        select.value = previoEstado;
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'No se pudo actualizar el estado del pedido.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000
        });
    } finally {
        // Habilitar y ocultar loader
        select.disabled = false;
        if (spinner) spinner.classList.add('d-none');
    }
}

// Abre el modal de confirmación de entrega y cobro
let totalPedidoGlobal = 0;
function abrirModalEntrega(pedidoId, codigo, total, clienteNombre, clienteDoc, tipoDoc, estadoPago) {
    totalPedidoGlobal = parseFloat(total);
    
    // Setear datos informativos en el modal
    document.getElementById('modal-pedido-codigo').textContent = codigo;
    document.getElementById('modal-pedido-total').textContent = totalPedidoGlobal.toFixed(2);
    document.getElementById('modal-pedido-cliente').textContent = clienteNombre;
    
    // Setear action del formulario
    const form = document.getElementById('form-entregar-facturar');
    form.action = `/ventas/pedidos-online/${pedidoId}/entregar-facturar`;
    
    // Configurar inputs del modal por defecto
    document.getElementById('modal-tipo-comprobante').value = 'BOLETA';
    document.getElementById('modal-medio-pago').value = 'EFECTIVO';
    document.getElementById('modal-paga-con').value = '';
    document.getElementById('modal-vuelto').textContent = '0.00';
    document.getElementById('modal-vuelto-error').classList.add('d-none');
    document.getElementById('btn-modal-confirmar').disabled = false;
    
    // Configurar medio de pago según el estado de pago del pedido
    const medioPagoSelect = document.getElementById('modal-medio-pago');
    if (estadoPago === 'PAGADO' || estadoPago === 'COMPLETADO') {
        medioPagoSelect.value = 'TARJETA';
        medioPagoSelect.disabled = true;
        
        // Agregar input oculto para medio_pago si no existe (ya que select disabled no envía valor)
        let tempInput = document.getElementById('temp-hidden-medio-pago');
        if (!tempInput) {
            tempInput = document.createElement('input');
            tempInput.type = 'hidden';
            tempInput.name = 'medio_pago';
            tempInput.id = 'temp-hidden-medio-pago';
            form.appendChild(tempInput);
        }
        tempInput.value = 'TARJETA';
    } else {
        medioPagoSelect.disabled = false;
        const tempInput = document.getElementById('temp-hidden-medio-pago');
        if (tempInput) tempInput.remove();
    }
    
    // Autocompletar RUC si aplica
    const rucInput = document.getElementById('modal-cliente-ruc');
    const razonSocialInput = document.getElementById('modal-cliente-razon-social');
    rucInput.value = '';
    razonSocialInput.value = '';
    
    if (tipoDoc === 'RUC' && clienteDoc) {
        rucInput.value = clienteDoc;
        razonSocialInput.value = clienteNombre;
    }
    
    // Disparar cambio de selectores para mostrar/ocultar secciones
    document.getElementById('modal-tipo-comprobante').dispatchEvent(new Event('change'));
    document.getElementById('modal-medio-pago').dispatchEvent(new Event('change'));
    
    // Abrir modal
    $('#modalEntregaPedido').modal('show');
    
    // Enfocar campo de pago si es efectivo
    setTimeout(() => {
        const pagaConInput = document.getElementById('modal-paga-con');
        if (document.getElementById('modal-medio-pago').value === 'EFECTIVO') {
            pagaConInput.focus();
        }
    }, 500);
}

// Actualiza los estilos visuales del select según su valor
function actualizarEstiloSelect(select, valor) {
    // Remover colores previos
    select.style.backgroundColor = '';
    select.style.color = '';

    const colores = {
        'PENDIENTE': { bg: '#f1f5f9', text: '#475569' },
        'CONFIRMADO': { bg: '#dbeafe', text: '#1d4ed8' },
        'PREPARANDO': { bg: '#eff6ff', text: '#1e40af' },
        'LISTO': { bg: '#d1fae5', text: '#065f46' },
        'ENTREGADO': { bg: '#10b981', text: '#ffffff' },
        'CANCELADO': { bg: '#fee2e2', text: '#991b1b' },
        'CONVERTIDO_A_VENTA': { bg: '#ccfbf1', text: '#0f766e' }
    };

    if (colores[valor]) {
        select.style.backgroundColor = colores[valor].bg;
        select.style.color = colores[valor].text;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-input');
    const estadoSelect = document.getElementById('estado-select');
    const tableContainer = document.getElementById('pedidos-table-container');
    
    let timer = null;
    let currentQuery = searchInput.value;
    let currentEstado = estadoSelect.value;

    // Elementos del Modal
    const modalTipoComprobante = document.getElementById('modal-tipo-comprobante');
    const modalFacturaFields = document.getElementById('modal-factura-fields');
    const modalMedioPago = document.getElementById('modal-medio-pago');
    const modalEfectivoFields = document.getElementById('modal-efectivo-fields');
    const modalPagaCon = document.getElementById('modal-paga-con');
    const modalVuelto = document.getElementById('modal-vuelto');
    const modalVueltoError = document.getElementById('modal-vuelto-error');
    const btnModalConfirmar = document.getElementById('btn-modal-confirmar');
    const btnBuscarRuc = document.getElementById('btn-buscar-ruc');
    const formEntregarFacturar = document.getElementById('form-entregar-facturar');

    // Escuchar tipo de comprobante para mostrar campos de Factura
    modalTipoComprobante.addEventListener('change', () => {
        if (modalTipoComprobante.value === 'FACTURA') {
            modalFacturaFields.classList.remove('d-none');
            document.getElementById('modal-cliente-ruc').required = true;
            document.getElementById('modal-cliente-razon-social').required = true;
        } else {
            modalFacturaFields.classList.add('d-none');
            document.getElementById('modal-cliente-ruc').required = false;
            document.getElementById('modal-cliente-razon-social').required = false;
        }
    });

    // Escuchar medio de pago para mostrar campos de Efectivo
    modalMedioPago.addEventListener('change', () => {
        if (modalMedioPago.value === 'EFECTIVO') {
            modalEfectivoFields.classList.remove('d-none');
            modalPagaCon.required = true;
            calcularVuelto();
        } else {
            modalEfectivoFields.classList.add('d-none');
            modalPagaCon.required = false;
            btnModalConfirmar.disabled = false;
            modalVueltoError.classList.add('d-none');
        }
    });

    // Calcular Vuelto en tiempo real
    function calcularVuelto() {
        const pagaConVal = parseFloat(modalPagaCon.value) || 0;
        if (modalMedioPago.value !== 'EFECTIVO') return;

        if (pagaConVal < totalPedidoGlobal) {
            modalVuelto.textContent = '0.00';
            modalVueltoError.classList.remove('d-none');
            btnModalConfirmar.disabled = true;
        } else {
            const vuelto = pagaConVal - totalPedidoGlobal;
            modalVuelto.textContent = vuelto.toFixed(2);
            modalVueltoError.classList.add('d-none');
            btnModalConfirmar.disabled = false;
        }
    }

    modalPagaCon.addEventListener('input', calcularVuelto);

    // Búsqueda de RUC en la base de datos / SUNAT
    btnBuscarRuc.addEventListener('click', async () => {
        const ruc = document.getElementById('modal-cliente-ruc').value.trim();
        if (ruc.length !== 11) {
            Swal.fire('Atención', 'Ingrese un RUC válido de 11 dígitos.', 'warning');
            return;
        }

        btnBuscarRuc.disabled = true;
        btnBuscarRuc.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        try {
            const response = await fetch(`/clientes/check-documento?doc=${ruc}`);
            if (!response.ok) throw new Error('Error al consultar RUC');
            
            const data = await response.json();
            if (data && (data.nombre || data.razon_social)) {
                document.getElementById('modal-cliente-razon-social').value = data.nombre || data.razon_social;
                Swal.fire({
                    icon: 'success',
                    title: 'RUC Encontrado',
                    text: data.nombre || data.razon_social,
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('No encontrado', 'No se pudo resolver la Razón Social de este RUC.', 'info');
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Error', 'No se pudo consultar el RUC en el servidor.', 'error');
        } finally {
            btnBuscarRuc.disabled = false;
            btnBuscarRuc.innerHTML = '<i class="fas fa-search"></i>';
        }
    });

    // Envío del formulario de Entrega y Facturación vía AJAX
    formEntregarFacturar.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        btnModalConfirmar.disabled = true;
        btnModalConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

        try {
            const formData = new FormData(formEntregarFacturar);
            const response = await fetch(formEntregarFacturar.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Cerrar modal
                $('#modalEntregaPedido').modal('hide');
                
                // Mostrar alerta de éxito SweetAlert2 con opciones de impresión
                Swal.fire({
                    icon: 'success',
                    title: '¡Pedido Entregado!',
                    text: data.message,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-print"></i> Imprimir Ticket',
                    cancelButtonText: '<i class="fas fa-file-pdf"></i> Imprimir A4',
                    confirmButtonColor: '#0ea5e9',
                    cancelButtonColor: '#10b981',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open(data.print_ticket_url + '?imprimir=si', '_blank');
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        window.open(data.print_a4_url + '?imprimir=si', '_blank');
                    }
                    // Recargar el listado de forma asíncrona
                    fetchPedidos();
                });

            } else {
                throw new Error(data.message || 'Ocurrió un error al procesar la entrega.');
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Error', err.message || 'No se pudo registrar la entrega.', 'error');
            btnModalConfirmar.disabled = false;
            btnModalConfirmar.innerHTML = '<i class="fas fa-check mr-1"></i> Confirmar Entrega';
        }
    });

    // Inicializar estilos de todos los selectores de estados de la tabla
    document.querySelectorAll('.select-estado-pedido').forEach(select => {
        actualizarEstiloSelect(select, select.value);
    });

    // Función para obtener los pedidos vía AJAX
    async function fetchPedidos() {
        const query = searchInput.value.trim();
        const estado = estadoSelect.value;

        // Reducir la opacidad para indicar carga sutilmente
        tableContainer.style.opacity = '0.5';

        try {
            // Construir URL final de forma segura con base absoluta
            const url = new URL(window.location.origin + window.location.pathname);
            if (query) url.searchParams.set('q', query);
            if (estado) url.searchParams.set('estado', estado);

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) throw new Error('Error en la red');
            
            const data = await response.json();
            
            // Actualizar el DOM
            tableContainer.innerHTML = data.html;
            
            // Re-inicializar estilos de los selectores de estados en el nuevo HTML
            document.querySelectorAll('.select-estado-pedido').forEach(select => {
                actualizarEstiloSelect(select, select.value);
            });

            // Actualizar la URL sin recargar
            window.history.pushState({}, '', url.toString());
            
        } catch (error) {
            console.error('Error cargando pedidos:', error);
            mostrarNotificacionError('Ocurrió un error al cargar la lista de pedidos.');
        } finally {
            // Restaurar la opacidad
            tableContainer.style.opacity = '1';
        }
    }

    function mostrarNotificacionError(mensaje) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000
            });
        } else if (typeof toastr !== 'undefined') {
            toastr.error(mensaje);
        } else {
            alert(mensaje);
        }
    }

    // Escuchar búsqueda por teclado con debounce
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        if (query === currentQuery) return;
        currentQuery = query;

        clearTimeout(timer);
        timer = setTimeout(fetchPedidos, 250);
    });

    // Escuchar filtrado por estado
    estadoSelect.addEventListener('change', (e) => {
        const estado = e.target.value;
        if (estado === currentEstado) return;
        currentEstado = estado;
        fetchPedidos();
    });

    // Paginación dinámica AJAX usando delegación de eventos
    tableContainer.addEventListener('click', (e) => {
        const pageLink = e.target.closest('#pagination-links a');
        if (!pageLink) return;

        e.preventDefault();
        // Resolver URL de forma segura contra la base
        const url = new URL(pageLink.getAttribute('href'), window.location.origin);
        
        // Mantener filtros actuales en la URL
        const query = searchInput.value.trim();
        const estado = estadoSelect.value;
        if (query) url.searchParams.set('q', query);
        if (estado) url.searchParams.set('estado', estado);

        // Fetch de la página correspondiente
        (async () => {
            tableContainer.style.opacity = '0.5';
            try {
                const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                
                if (!response.ok) throw new Error('Error en la respuesta');
                
                const data = await response.json();
                tableContainer.innerHTML = data.html;
                document.querySelectorAll('.select-estado-pedido').forEach(select => {
                    actualizarEstiloSelect(select, select.value);
                });
                window.history.pushState({}, '', url.toString());
                document.querySelector('.card').scrollIntoView({ behavior: 'smooth' });
            } catch (err) {
                console.error(err);
                mostrarNotificacionError('No se pudo cargar la página solicitada.');
            } finally {
                tableContainer.style.opacity = '1';
            }
        })();
    });

    // Función para procesar entrega de pedidos ya pagados
    async function ejecutarEntregaPrepagada(select, codigo, ventaId) {
        const url = select.dataset.url;
        const nuevoEstado = 'ENTREGADO';
        
        try {
            const spinner = select.nextElementSibling;
            if (spinner) spinner.classList.remove('d-none');
            select.disabled = true;

            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ estado: nuevoEstado })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                select.dataset.prevVal = nuevoEstado;
                actualizarEstiloSelect(select, nuevoEstado);
                
                // Mostrar SweetAlert2 final de impresión
                Swal.fire({
                    icon: 'success',
                    title: '¡Pedido Entregado!',
                    text: 'El pedido ' + codigo + ' ha sido marcado como entregado.',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-print"></i> Imprimir Ticket',
                    cancelButtonText: '<i class="fas fa-file-pdf"></i> Imprimir A4',
                    confirmButtonColor: '#0ea5e9',
                    cancelButtonColor: '#10b981',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open(`{{ url('ventas') }}/${ventaId}/print-ticket?imprimir=si`, '_blank');
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        window.open(`{{ url('ventas') }}/${ventaId}/print-a4?imprimir=si`, '_blank');
                    }
                    fetchPedidos();
                });
            } else {
                throw new Error(data.message || 'Error al actualizar el estado del pedido.');
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Error', err.message || 'No se pudo registrar la entrega.', 'error');
            select.value = select.dataset.prevVal;
        } finally {
            select.disabled = false;
            const spinner = select.nextElementSibling;
            if (spinner) spinner.classList.add('d-none');
        }
    }

    // Delegación de eventos para botón "Entregar" directo en la tabla
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-entregar-pedido-rapido');
        if (!btn) return;

        const pedidoId = btn.dataset.pedidoId;
        const codigo = btn.dataset.codigo;
        const total = parseFloat(btn.dataset.total);
        const clienteNombre = btn.dataset.clienteNombre;
        const clienteDoc = btn.dataset.clienteDoc;
        const tipoDoc = btn.dataset.clienteTipoDoc;
        const estadoPago = btn.dataset.estadoPago;

        abrirModalEntrega(pedidoId, codigo, total, clienteNombre, clienteDoc, tipoDoc, estadoPago);
    });

    // Delegación de eventos para botón "Comprobante" / reimpresión
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-reimprimir-comprobante');
        if (!btn) return;

        const ticketUrl = btn.dataset.printTicketUrl;
        const a4Url = btn.dataset.printA4Url;
        const codigo = btn.dataset.codigo;

        Swal.fire({
            icon: 'info',
            title: 'Imprimir Comprobante',
            text: 'Selecciona el formato para reimprimir el comprobante del pedido ' + codigo + ':',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-print"></i> Imprimir Ticket',
            cancelButtonText: '<i class="fas fa-file-pdf"></i> Imprimir A4',
            confirmButtonColor: '#0ea5e9',
            cancelButtonColor: '#10b981',
            allowOutsideClick: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.open(ticketUrl + '?imprimir=si', '_blank');
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                window.open(a4Url + '?imprimir=si', '_blank');
            }
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
