@extends('adminlte::page')

@section('title', 'Pedido ' . $pedido->codigo)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="text-dark font-weight-bold">Pedido {{ $pedido->codigo }}</h1>
    <a href="{{ route('tienda.admin.pedidos.index') }}" class="btn btn-default btn-sm shadow-xs rounded-lg font-weight-medium border">
        <i class="fas fa-arrow-left mr-1"></i> Regresar al listado
    </a>
</div>
@endsection

@section('content')
@include('tienda.partials.alerts')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Detalle</div>
            <div class="card-body table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pedido->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->descripcion }}</td>
                                <td>{{ $detalle->cantidad }}</td>
                                <td>S/ {{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                                <td>S/ {{ number_format((float) $detalle->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <strong>Total: S/ {{ number_format((float) $pedido->total, 2) }}</strong>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-xl overflow-hidden">
            <div class="card-header bg-light border-0 py-3">
                <h5 class="card-title mb-0 font-weight-bold text-dark" style="font-size: 1.1rem;">
                    <i class="fas fa-user-circle mr-2 text-primary"></i>Cliente y Estado
                </h5>
            </div>
            <div class="card-body p-4 text-secondary">
                <div class="mb-3 pb-3 border-bottom">
                    <p class="mb-1 text-xs text-muted font-weight-bold uppercase" style="letter-spacing: 0.05em;">Cliente</p>
                    <span class="font-weight-bold text-dark d-block" style="font-size: 1.05rem;">{{ $pedido->cliente_nombre }}</span>
                    <span class="text-muted d-block" style="font-size: 0.9rem;"><i class="far fa-id-card mr-1 text-xs"></i>Doc: {{ $pedido->cliente_documento }}</span>
                </div>
                <div class="mb-3 pb-3 border-bottom row">
                    <div class="col-6">
                        <p class="mb-1 text-xs text-muted font-weight-bold uppercase" style="letter-spacing: 0.05em;">Teléfono</p>
                        <span class="text-dark font-weight-medium">{{ $pedido->cliente_telefono ?: '-' }}</span>
                    </div>
                    <div class="col-6">
                        <p class="mb-1 text-xs text-muted font-weight-bold uppercase" style="letter-spacing: 0.05em;">Método Pago</p>
                        <span class="badge bg-success-light text-success px-2 py-1 font-weight-bold rounded">{{ $pedido->metodo_pago }}</span>
                    </div>
                </div>
                <div class="mb-3 pb-3 border-bottom">
                    <p class="mb-1 text-xs text-muted font-weight-bold uppercase" style="letter-spacing: 0.05em;">Email</p>
                    <span class="text-dark font-weight-medium text-break" style="font-size: 0.9rem;">{{ $pedido->cliente_email ?: '-' }}</span>
                </div>
                <div class="mb-3 pb-3 border-bottom">
                    <p class="mb-1 text-xs text-muted font-weight-bold uppercase" style="letter-spacing: 0.05em;">Sucursal de Recojo</p>
                    <span class="text-dark font-weight-medium"><i class="fas fa-hospital-alt mr-1 text-xs text-primary"></i>{{ $pedido->sucursal->nombre ?? '-' }}</span>
                </div>
                
                <div class="mb-4 pb-3 border-bottom row">
                    <div class="col-6">
                        <p class="mb-1 text-xs text-muted font-weight-bold uppercase" style="letter-spacing: 0.05em;">Recojo Programado</p>
                        <span class="text-dark font-weight-bold" style="font-size: 0.9rem;">
                            <i class="far fa-calendar-alt mr-1 text-primary"></i>
                            {{ $pedido->fecha_recojo ? $pedido->fecha_recojo->format('d/m/Y') : 'Hoy' }}
                        </span>
                    </div>
                    @if($pedido->entregado_at)
                    <div class="col-6">
                        <p class="mb-1 text-xs text-muted font-weight-bold uppercase" style="letter-spacing: 0.05em;">Fecha de Entrega</p>
                        <span class="text-success font-weight-bold" style="font-size: 0.85rem;">
                            <i class="fas fa-check-circle mr-1"></i>
                            {{ $pedido->entregado_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    @endif
                </div>

                <form id="estado-form" method="POST" action="{{ route('tienda.admin.pedidos.estado', $pedido) }}">
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <label class="font-weight-bold text-dark mb-2">Estado del Pedido</label>
                        <div class="position-relative d-flex align-items-center">
                            <select name="estado" id="select-estado" class="form-control font-weight-bold rounded-lg py-2" 
                                    style="height: auto; border-color: #cbd5e1; transition: all 0.3s;"
                                    data-prev="{{ $pedido->estado }}"
                                    @disabled(in_array($pedido->estado, ['ENTREGADO', 'CANCELADO', 'CONVERTIDO_A_VENTA']))>
                                @foreach ([
                                    'PENDIENTE' => 'Pendiente',
                                    'CONFIRMADO' => 'Confirmado',
                                    'PREPARANDO' => 'Preparando',
                                    'LISTO' => 'Listo para Recojo',
                                    'ENTREGADO' => 'Entregado',
                                    'CANCELADO' => 'Cancelado',
                                    'CONVERTIDO_A_VENTA' => 'Facturado/Venta'
                                ] as $valor => $label)
                                    <option value="{{ $valor }}" @selected($pedido->estado === $valor)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="spinner-border spinner-border-sm text-primary ml-2 d-none" id="estado-spinner" role="status"></span>
                        </div>
                    </div>
                    @if(!in_array($pedido->estado, ['ENTREGADO', 'CANCELADO', 'CONVERTIDO_A_VENTA']))
                        <button type="submit" id="btn-actualizar" class="btn btn-primary btn-block rounded-lg py-2 shadow-xs font-weight-bold transition-all">
                            <i class="fas fa-save mr-1"></i> Guardar Cambios
                        </button>
                    @endif
                    
                    @if($pedido->estado !== 'ENTREGADO' && $pedido->estado !== 'CANCELADO')
                        <button type="button" id="btn-entregar-pedido-rapido-show" class="btn btn-success btn-block rounded-lg py-2 mt-2 shadow-xs font-weight-bold transition-all">
                            <i class="fas fa-box mr-1"></i> Entregar Pedido
                        </button>
                    @endif

                    @if($pedido->venta_id)
                        <button type="button" class="btn btn-info btn-block rounded-lg py-2 mt-2 shadow-xs font-weight-bold btn-reimprimir-comprobante-show"
                                data-print-ticket-url="{{ route('ventas.print_ticket', $pedido->venta_id) }}"
                                data-print-a4-url="{{ route('ventas.print_a4', $pedido->venta_id) }}">
                            <i class="fas fa-print mr-1"></i> Imprimir Comprobante
                        </button>
                    @endif
                </form>
            </div>
        </div>
        
        <!-- Scripts y estilos locales para la interactividad AJAX -->
        <style>
            .rounded-xl { border-radius: 0.75rem !important; }
            .rounded-lg { border-radius: 0.5rem !important; }
            .shadow-xs { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
            .font-weight-medium { font-weight: 500; }
            .text-xs { font-size: 0.75rem; }
            .bg-success-light { background-color: #d1fae5 !important; color: #065f46 !important; }
            
            #select-estado:focus {
                border-color: #10b981 !important;
                box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
                outline: none !important;
            }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('estado-form');
            const select = document.getElementById('select-estado');
            const spinner = document.getElementById('estado-spinner');
            const btn = document.getElementById('btn-actualizar');

            function pintarSelect(val) {
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
                if (colores[val]) {
                    select.style.backgroundColor = colores[val].bg;
                    select.style.color = colores[val].text;
                }
            }

            pintarSelect(select.value);
            select.addEventListener('change', () => pintarSelect(select.value));

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                select.disabled = true;
                btn.disabled = true;
                spinner.classList.remove('d-none');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Actualizando...';

                try {
                    const response = await fetch(form.action, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ estado: select.value })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        select.dataset.prev = select.value;
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error(data.message || 'Error al actualizar el estado.');
                    }
                } catch (err) {
                    console.error(err);
                    select.value = select.dataset.prev;
                    pintarSelect(select.value);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.message || 'No se pudo guardar el nuevo estado.'
                    });
                } finally {
                    select.disabled = false;
                    btn.disabled = false;
                    spinner.classList.add('d-none');
                    btn.innerHTML = '<i class="fas fa-save mr-1"></i> Guardar Cambios';
                }
            });
        });
        </script>
        
        <!-- Modal Entrega Pedido y Facturación (Específico para este pedido) -->
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
                    <form id="form-entregar-facturar" method="POST" action="{{ route('tienda.admin.pedidos.entregar_facturar', $pedido) }}">
                        @csrf
                        <div class="modal-body p-4">
                            <!-- Datos del pedido -->
                            <div class="alert alert-light border rounded-lg p-3 mb-3" style="font-size: 0.95rem;">
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <span class="text-xs text-muted d-block">CÓDIGO PEDIDO</span>
                                        <strong class="text-dark">{{ $pedido->codigo }}</strong>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <span class="text-xs text-muted d-block">TOTAL A COBRAR</span>
                                        <strong class="text-teal" style="font-size: 1.15rem;">S/ <span>{{ number_format((float) $pedido->total, 2) }}</span></strong>
                                    </div>
                                    <div class="col-12">
                                        <span class="text-xs text-muted d-block">CLIENTE</span>
                                        <span class="text-dark font-weight-medium">{{ $pedido->cliente_nombre }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tipo de Comprobante -->
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark text-xs uppercase" style="letter-spacing: 0.05em;">Tipo de Comprobante</label>
                                <select name="tipo_comprobante" id="modal-tipo-comprobante" class="form-control rounded-lg border-gray font-weight-medium" required>
                                    <option value="TICKET">Ticket / Nota de Venta</option>
                                    <option value="BOLETA" selected>Boleta de Venta</option>
                                    <option value="FACTURA">Factura</option>
                                </select>
                            </div>

                            <!-- Campos de Factura -->
                            <div id="modal-factura-fields" class="d-none border rounded-lg p-3 mb-3 bg-light">
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold text-dark text-xs uppercase" style="letter-spacing: 0.05em;">RUC</label>
                                    <div class="input-group">
                                        <input type="text" name="cliente_ruc" id="modal-cliente-ruc" class="form-control rounded-lg border-gray" placeholder="Ingrese el RUC de 11 dígitos" value="{{ $pedido->cliente_tipo_documento === 'RUC' ? $pedido->cliente_documento : '' }}" maxlength="11">
                                        <div class="input-group-append">
                                            <button type="button" id="btn-buscar-ruc" class="btn btn-outline-secondary rounded-right-lg"><i class="fas fa-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold text-dark text-xs uppercase" style="letter-spacing: 0.05em;">Razón Social</label>
                                    <input type="text" name="cliente_razon_social" id="modal-cliente-razon-social" class="form-control rounded-lg border-gray" placeholder="Razón social de la empresa" value="{{ $pedido->cliente_tipo_documento === 'RUC' ? $pedido->cliente_nombre : '' }}">
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

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const totalPedido = parseFloat("{{ $pedido->total }}");
            
            // Formulario de estado principal
            const formEstado = document.getElementById('estado-form');
            const selectEstado = document.getElementById('select-estado');
            
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

            // Interceptar el envío del formulario de actualización de estado
            formEstado.addEventListener('submit', (e) => {
                const nuevoEstado = selectEstado.value;
                const estadoPago = "{{ $pedido->estado_pago }}";
                
                if (nuevoEstado === 'ENTREGADO') {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Si ya está pagado online
                    if (estadoPago === 'PAGADO' || estadoPago === 'COMPLETADO') {
                        modalMedioPago.value = 'TARJETA';
                        modalMedioPago.disabled = true;
                        
                        // Agregar input oculto para medio_pago si no existe (ya que select disabled no envía valor)
                        let tempInput = document.getElementById('temp-hidden-medio-pago');
                        if (!tempInput) {
                            tempInput = document.createElement('input');
                            tempInput.type = 'hidden';
                            tempInput.name = 'medio_pago';
                            tempInput.id = 'temp-hidden-medio-pago';
                            formEntregarFacturar.appendChild(tempInput);
                        }
                        tempInput.value = 'TARJETA';
                    } else {
                        modalMedioPago.disabled = false;
                        const tempInput = document.getElementById('temp-hidden-medio-pago');
                        if (tempInput) tempInput.remove();
                    }

                    // Abrir modal
                    $('#modalEntregaPedido').modal('show');
                    
                    // Disparar eventos iniciales del modal
                    modalTipoComprobante.dispatchEvent(new Event('change'));
                    modalMedioPago.dispatchEvent(new Event('change'));
                    
                    setTimeout(() => {
                        if (!modalMedioPago.disabled && modalMedioPago.value === 'EFECTIVO') {
                            modalPagaCon.focus();
                        }
                    }, 500);
                }
            });

            // Control de visibilidad del tipo de comprobante
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

            // Control de visibilidad del medio de pago
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

            // Calcular vuelto en efectivo
            function calcularVuelto() {
                const pagaConVal = parseFloat(modalPagaCon.value) || 0;
                if (modalMedioPago.value !== 'EFECTIVO') return;

                if (pagaConVal < totalPedido) {
                    modalVuelto.textContent = '0.00';
                    modalVueltoError.classList.remove('d-none');
                    btnModalConfirmar.disabled = true;
                } else {
                    const vuelto = pagaConVal - totalPedido;
                    modalVuelto.textContent = vuelto.toFixed(2);
                    modalVueltoError.classList.add('d-none');
                    btnModalConfirmar.disabled = false;
                }
            }

            modalPagaCon.addEventListener('input', calcularVuelto);

            // Búsqueda de RUC en SUNAT
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
                        Swal.fire('No encontrado', 'No se pudo resolver la Razón Social.', 'info');
                    }
                } catch (err) {
                    console.error(err);
                    Swal.fire('Error', 'No se pudo consultar el RUC.', 'error');
                } finally {
                    btnBuscarRuc.disabled = false;
                    btnBuscarRuc.innerHTML = '<i class="fas fa-search"></i>';
                }
            });

            // Enviar formulario de cobro y entrega vía AJAX
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
                        $('#modalEntregaPedido').modal('hide');
                        
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
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Error al procesar la entrega.');
                    }
                } catch (err) {
                    console.error(err);
                    Swal.fire('Error', err.message || 'No se pudo registrar la entrega.', 'error');
                    btnModalConfirmar.disabled = false;
                    btnModalConfirmar.innerHTML = '<i class="fas fa-check mr-1"></i> Confirmar Entrega';
                }
            });

            // Función para procesar entrega de pedidos ya pagados en la vista Detalle
            async function ejecutarEntregaPrepagadaShow() {
                const url = formEstado.action;
                const nuevoEstado = 'ENTREGADO';
                const spinner = document.getElementById('estado-spinner');
                const btnActualizar = document.getElementById('btn-actualizar');
                const btnEntregarRapido = document.getElementById('btn-entregar-pedido-rapido-show');
                const ventaId = "{{ $pedido->venta_id }}";

                try {
                    if (spinner) spinner.classList.remove('d-none');
                    if (btnActualizar) btnActualizar.disabled = true;
                    if (btnEntregarRapido) btnEntregarRapido.disabled = true;

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
                        selectEstado.dataset.prev = nuevoEstado;
                        selectEstado.value = nuevoEstado;
                        
                        Swal.fire({
                            icon: 'success',
                            title: '¡Pedido Entregado!',
                            text: 'El pedido ha sido marcado como entregado.',
                            showCancelButton: true,
                            confirmButtonText: '<i class="fas fa-print"></i> Imprimir Ticket',
                            cancelButtonText: '<i class="fas fa-file-pdf"></i> Imprimir A4',
                            confirmButtonColor: '#0ea5e9',
                            cancelButtonColor: '#10b981',
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed && ventaId) {
                                window.open(`{{ url('ventas') }}/${ventaId}/print-ticket?imprimir=si`, '_blank');
                            } else if (result.dismiss === Swal.DismissReason.cancel && ventaId) {
                                window.open(`{{ url('ventas') }}/${ventaId}/print-a4?imprimir=si`, '_blank');
                            }
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Error al actualizar el estado del pedido.');
                    }
                } catch (err) {
                    console.error(err);
                    Swal.fire('Error', err.message || 'No se pudo registrar la entrega.', 'error');
                } finally {
                    if (spinner) spinner.classList.add('d-none');
                    if (btnActualizar) btnActualizar.disabled = false;
                    if (btnEntregarRapido) btnEntregarRapido.disabled = false;
                }
            }

            // Click del botón "Entregar Pedido" rápido en show.blade.php
            const btnEntregarRapidoShow = document.getElementById('btn-entregar-pedido-rapido-show');
            if (btnEntregarRapidoShow) {
                btnEntregarRapidoShow.addEventListener('click', () => {
                    selectEstado.value = 'ENTREGADO';
                    // Gatilla el submit de formEstado, lo cual dispara la interceptación de arriba
                    formEstado.dispatchEvent(new Event('submit', { cancelable: true }));
                });
            }

            // Click del botón de reimpresión de comprobante en show.blade.php
            const btnReimprimirShow = document.querySelector('.btn-reimprimir-comprobante-show');
            if (btnReimprimirShow) {
                btnReimprimirShow.addEventListener('click', () => {
                    const ticketUrl = btnReimprimirShow.dataset.printTicketUrl;
                    const a4Url = btnReimprimirShow.dataset.printA4Url;
                    const codigo = "{{ $pedido->codigo }}";

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
            }
        });
        </script>
    </div>
        <div class="card">
            <div class="card-header">QR de recojo</div>
            <div class="card-body text-center">
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(160)->generate(route('tienda.pedidos.recojo', $pedido->qr_token)) !!}
            </div>
        </div>
    </div>
</div>
@endsection
