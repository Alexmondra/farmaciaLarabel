<!-- Modal para buscar/escanear Pedidos Online -->
<div class="modal fade" id="modalPedidoOnline" tabindex="-1" role="dialog" aria-labelledby="modalPedidoOnlineLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            
            <!-- Cabecera del Modal con Fondo Médico Clínico -->
            <div class="modal-header bg-gradient-info text-white py-3 border-0" style="background: linear-gradient(135deg, #14b8a6, #0891b2);">
                <h5 class="modal-title font-weight-bold" id="modalPedidoOnlineLabel">
                    <i class="fas fa-qrcode mr-2"></i> Atender Pedido Online
                </h5>
                <button type="button" class="close text-white opacity-80 hover:opacity-100 transition-all duration-200" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                </button>
            </div>
            
            <div class="modal-body p-4 bg-light">
                
                <!-- Buscador / Escáner de QR -->
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 15px;">
                    <div class="card-body p-3">
                        <label class="font-weight-bold text-slate-700 mb-2">
                            <i class="fas fa-barcode mr-1 text-success"></i> Escanee el código QR o ingrese el código de pedido
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0" style="border-radius: 10px 0 0 10px; border-color: #ced4da;">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                            </div>
                            <input type="text" id="input-scan-pedido" class="form-control border-left-0 font-weight-bold text-primary pl-1" placeholder="Ej. PED-00001 o pegue la URL del QR..." autocomplete="off" style="border-radius: 0 10px 10px 0; border-color: #ced4da; height: 45px; font-size: 1.1rem;">
                            <div class="input-group-append ml-2">
                                <button type="button" class="btn btn-success font-weight-bold px-4" id="btn-buscar-pedido-scan" style="border-radius: 10px; height: 45px;">
                                    Buscar
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted mt-2">
                            <i class="fas fa-info-circle mr-1"></i> Si utiliza una lectora de hardware, simplemente abra el modal y escanee el código QR del cliente.
                        </small>
                    </div>
                </div>

                <!-- Contenedor de Alertas / Advertencias -->
                <div id="scan-pedido-warning" class="alert alert-warning alert-dismissible fade show shadow-sm border-0 d-none" role="alert" style="border-radius: 12px;">
                    <strong class="warning-title"><i class="fas fa-exclamation-triangle mr-2"></i> Advertencia:</strong>
                    <span class="warning-message"></span>
                    <div id="container-reimprimir-directo" class="mt-2 d-none">
                        <button type="button" class="btn btn-sm btn-primary font-weight-bold btn-reimprimir-pedido">
                            <i class="fas fa-print mr-1"></i> Reimprimir Comprobante
                        </button>
                    </div>
                    <button type="button" class="close" id="btn-cerrar-warning" style="top: 0; right: 0; padding: 0.75rem 1.25rem;">
                        <span>&times;</span>
                    </button>
                </div>

                <!-- Spinner de Carga -->
                <div id="scan-pedido-spinner" class="text-center py-5 d-none">
                    <div class="spinner-border text-info" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="text-muted mt-3 font-weight-bold">Buscando pedido online...</p>
                </div>

                <!-- Contenedor Principal de Información del Pedido (Oculto al inicio) -->
                <div id="scan-pedido-details" class="d-none">
                    
                    <!-- Fila de Tarjetas Informativas (Cliente y Pago) -->
                    <div class="row">
                        <!-- Tarjeta Cliente -->
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                                <div class="card-body p-3">
                                    <h6 class="font-weight-bold text-slate-800 border-bottom pb-2 mb-2">
                                        <i class="fas fa-user-tag text-info mr-1"></i> Información del Cliente
                                    </h6>
                                    <p class="mb-1 text-dark"><strong>Nombre:</strong> <span id="ped-det-cliente-nombre"></span></p>
                                    <p class="mb-1 text-dark"><strong>Documento:</strong> <span id="ped-det-cliente-doc"></span></p>
                                    <p class="mb-1 text-dark"><strong>Sucursal:</strong> <span id="ped-det-sucursal" class="badge badge-secondary px-2 py-1"></span></p>
                                    <p class="mb-1 text-dark"><strong>Fecha Recojo:</strong> <span id="ped-det-fecha-recojo" class="badge badge-info px-2.5 py-1"></span></p>
                                    <p class="mb-0 text-dark d-none" id="ped-det-fecha-entrega-container"><strong>Fecha Entrega:</strong> <span id="ped-det-fecha-entrega" class="badge badge-success px-2.5 py-1"></span></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tarjeta Pago y Estado -->
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                                <div class="card-body p-3">
                                    <h6 class="font-weight-bold text-slate-800 border-bottom pb-2 mb-2">
                                        <i class="fas fa-receipt text-info mr-1"></i> Estado del Pedido
                                    </h6>
                                    <p class="mb-1 text-dark"><strong>Estado Pedido:</strong> <span id="ped-det-estado" class="badge px-2.5 py-1 text-white"></span></p>
                                    <p class="mb-1 text-dark"><strong>Estado Pago:</strong> <span id="ped-det-pago-estado" class="badge px-2.5 py-1 text-white"></span></p>
                                    <p class="mb-0 text-dark"><strong>Método Pago:</strong> <span id="ped-det-pago-metodo" class="font-weight-bold text-primary"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grilla de Productos -->
                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white border-0 py-2">
                            <h6 class="card-title font-weight-bold text-dark mb-0" style="font-size: 0.95rem;">
                                <i class="fas fa-box-open mr-1 text-warning"></i> Detalle de Productos
                            </h6>
                        </div>
                        <div class="table-responsive" style="max-height: 180px; overflow-y: auto;">
                            <table class="table table-sm table-striped table-hover mb-0" style="font-size: 0.9rem;">
                                <thead class="bg-light text-slate-700">
                                    <tr>
                                        <th class="pl-3 py-2">Medicamento</th>
                                        <th class="text-center py-2">Cant.</th>
                                        <th class="text-right py-2">P. Unit</th>
                                        <th class="text-right pr-3 py-2">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="ped-det-items-tbody">
                                    <!-- Items dinámicos -->
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white border-0 py-2 text-right">
                            <h5 class="font-weight-bold text-emerald-600 mb-0 pr-2">
                                Total Pedido: S/ <span id="ped-det-total">0.00</span>
                            </h5>
                        </div>
                    </div>

                    <!-- Panel de Facturación y Acción Directa (Visible sólo si no está entregado) -->
                    <div id="panel-facturacion-directa" class="card border-info shadow-sm mb-0" style="border-radius: 12px; border-width: 1px;">
                        <div class="card-body p-3">
                            <h6 class="font-weight-bold text-info mb-3">
                                <i class="fas fa-file-invoice-dollar mr-1"></i> Comprobante y Cobro en Caja
                            </h6>
                            <form id="form-atender-pedido-directo" onsubmit="return false;">
                                <div class="form-row">
                                    <!-- Tipo Comprobante -->
                                    <div class="col-md-4 form-group">
                                        <label class="small font-weight-bold text-muted">TIPO COMPROBANTE</label>
                                        <select id="ped-fact-tipo-comprobante" class="form-control form-control-sm font-weight-bold text-dark">
                                            <option value="BOLETA">BOLETA DE VENTA</option>
                                            <option value="FACTURA">FACTURA</option>
                                            <option value="TICKET">TICKET BOLETA</option>
                                        </select>
                                    </div>

                                    <!-- Medio de Pago -->
                                    <div class="col-md-4 form-group">
                                        <label class="small font-weight-bold text-muted">MEDIO DE PAGO</label>
                                        <div id="ped-fact-pago-online-badge" class="d-none">
                                            <span class="badge badge-success px-3 py-2 w-100 text-center font-weight-bold" style="font-size: 0.85rem; height: 31px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-check-circle mr-1"></i> YA PAGADO ONLINE
                                            </span>
                                            <input type="hidden" id="ped-fact-medio-pago-hidden" value="TARJETA">
                                        </div>
                                        <div id="ped-fact-pago-caja-select">
                                            <select id="ped-fact-medio-pago" class="form-control form-control-sm font-weight-bold text-primary">
                                                <option value="EFECTIVO">EFECTIVO</option>
                                                <option value="TARJETA">TARJETA DE CRÉDITO/DÉBITO</option>
                                                <option value="YAPE">YAPE</option>
                                                <option value="PLIN">PLIN</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Paga Con (Sólo visible si es efectivo y no pagado) -->
                                    <div class="col-md-4 form-group" id="container-ped-paga-con">
                                        <label class="small font-weight-bold text-muted">PAGA CON (S/)</label>
                                        <input type="number" id="ped-fact-paga-con" class="form-control form-control-sm text-center font-weight-bold" step="0.1" min="0" placeholder="0.00">
                                    </div>
                                </div>

                                <!-- Datos Factura (Ocultos por defecto) -->
                                <div class="form-row d-none" id="container-ped-factura-datos">
                                    <div class="col-md-4 form-group mb-0">
                                        <label class="small font-weight-bold text-muted">RUC (11 dígitos)</label>
                                        <input type="text" id="ped-fact-ruc" class="form-control form-control-sm font-weight-bold" placeholder="Número de RUC" maxlength="11">
                                    </div>
                                    <div class="col-md-8 form-group mb-0">
                                        <label class="small font-weight-bold text-muted">RAZÓN SOCIAL</label>
                                        <input type="text" id="ped-fact-razon-social" class="form-control form-control-sm" placeholder="Razón Social de la empresa">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>

            </div>
            
            <!-- Botones del Pie del Modal -->
            <div class="modal-footer bg-white border-top-0 d-flex justify-content-between p-3">
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal" style="border-radius: 10px;">
                    Cerrar
                </button>
                
                <div class="d-flex" style="gap: 8px;">
                    <!-- Opción B: Cargar en el POS -->
                    <button type="button" class="btn btn-outline-primary font-weight-bold d-none" id="btn-cargar-carrito-pos" style="border-radius: 10px;">
                        <i class="fas fa-shopping-cart mr-1"></i> Cargar en POS
                    </button>
                    <!-- Opción A: Procesar y Facturar Directamente -->
                    <button type="button" class="btn btn-success font-weight-bold d-none" id="btn-procesar-pedido-online" style="border-radius: 10px; background-color: #10b981; border-color: #10b981;">
                        <i class="fas fa-check mr-1"></i> Confirmar Entrega
                    </button>
                    <!-- Opción Reimprimir (Si ya está entregado) -->
                    <button type="button" class="btn btn-primary font-weight-bold d-none btn-reimprimir-pedido" id="btn-reimprimir-pedido-footer" style="border-radius: 10px; background-color: #0284c7; border-color: #0284c7;">
                        <i class="fas fa-print mr-1"></i> Reimprimir Comprobante
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
