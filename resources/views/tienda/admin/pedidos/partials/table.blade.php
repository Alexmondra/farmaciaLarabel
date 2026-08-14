<div class="table-responsive border-0">
    <table class="table table-hover align-middle rounded-xl overflow-hidden mb-0" style="border-collapse: separate; border-spacing: 0;">
        <thead class="bg-light text-muted uppercase font-weight-bold" style="font-size: 0.85rem; letter-spacing: 0.05em;">
            <tr>
                <th class="border-0 px-4 py-3">Pedido / Sucursal</th>
                <th class="border-0 px-4 py-3">Cliente</th>
                <th class="border-0 px-4 py-3 text-center">Cobro / Pago</th>
                <th class="border-0 px-4 py-3">Estado</th>
                <th class="border-0 px-4 py-3 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y text-secondary">
            @forelse ($pedidos as $pedido)
                <tr class="transition-all duration-300 hover:bg-light" style="font-size: 0.95rem;" data-pedido-id="{{ $pedido->id }}">
                    <!-- 1. Pedido / Sucursal -->
                    <td class="px-4 py-3 align-middle font-weight-bold text-dark">
                        <span class="font-mono d-block" style="font-size: 1.05rem;">{{ $pedido->codigo }}</span>
                        <small class="text-muted d-block font-weight-normal mt-1" style="font-size: 0.85rem;">
                            <i class="fas fa-hospital-alt mr-1 text-primary"></i>{{ $pedido->sucursal->nombre ?? '-' }}
                        </small>
                    </td>
                    
                    <!-- 2. Cliente -->
                    <td class="px-4 py-3 align-middle">
                        <div>
                            <span class="font-weight-bold text-dark d-block">{{ $pedido->cliente_nombre }}</span>
                            <span class="text-xs text-muted d-block mt-0.5" style="font-size: 0.8rem;">Doc: {{ $pedido->cliente_documento }}</span>
                        </div>
                    </td>
                    
                    <!-- 3. Cobro / Pago -->
                    <td class="px-4 py-3 align-middle text-center">
                        <span class="font-weight-bold text-dark d-block" style="font-size: 1.05rem;">S/ {{ number_format((float) $pedido->total, 2) }}</span>
                        <span class="badge rounded-xl px-2.5 py-1 font-weight-bold d-inline-block mt-1
                            {{ in_array($pedido->estado_pago, ['PAGADO', 'COMPLETADO']) ? 'bg-success-light text-success border border-success' : 'bg-warning-light text-warning border border-warning' }}"
                            style="
                                @if(in_array($pedido->estado_pago, ['PAGADO', 'COMPLETADO']))
                                    background-color: #d1fae5; color: #065f46; border-color: #a7f3d0;
                                @else
                                    background-color: #fef3c7; color: #92400e; border-color: #fde68a;
                                @endif
                                font-size: 0.78rem;
                            ">
                            {{ in_array($pedido->estado_pago, ['PAGADO', 'COMPLETADO']) ? 'PAGADO' : 'PENDIENTE' }}
                        </span>
                    </td>
                    
                    <!-- 4. Estado -->
                    <td class="px-4 py-3 align-middle" style="min-width: 150px;">
                        <div class="d-flex flex-column flex-md-row align-items-md-center">
                            <!-- Select dinámico para actualizar estado con AJAX -->
                            <select class="form-control form-control-sm select-estado-pedido font-weight-medium rounded-lg" 
                                    data-url="{{ route('tienda.admin.pedidos.estado', $pedido) }}"
                                    data-prev-val="{{ $pedido->estado }}"
                                    data-estado-pago="{{ $pedido->estado_pago }}"
                                    data-pedido-total="{{ $pedido->total }}"
                                    data-cliente-doc="{{ $pedido->cliente_documento }}"
                                    data-cliente-tipo-doc="{{ $pedido->cliente_tipo_documento }}"
                                    data-venta-id="{{ $pedido->venta_id }}"
                                    style="border-color: #e2e8f0; height: auto; padding: 0.25rem 0.5rem; width: 100%; min-width: 110px; transition: all 0.2s;"
                                    @disabled(in_array($pedido->estado, ['ENTREGADO', 'CANCELADO', 'CONVERTIDO_A_VENTA']))
                                    onchange="actualizarEstadoPedido(this)">
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
                            <span class="spinner-border spinner-border-sm text-primary ml-md-2 mt-1 mt-md-0 d-none state-change-spinner" role="status"></span>
                            
                            @if($pedido->estado !== 'ENTREGADO' && $pedido->estado !== 'CANCELADO')
                                <button type="button" 
                                        class="btn btn-sm btn-success mt-2 mt-md-0 ml-md-2 px-3 font-weight-bold rounded-lg btn-entregar-pedido-rapido"
                                        style="white-space: nowrap; height: 31px; display: inline-flex; align-items: center;"
                                        data-pedido-id="{{ $pedido->id }}"
                                        data-codigo="{{ $pedido->codigo }}"
                                        data-total="{{ $pedido->total }}"
                                        data-cliente-nombre="{{ $pedido->cliente_nombre }}"
                                        data-cliente-doc="{{ $pedido->cliente_documento }}"
                                        data-cliente-tipo-doc="{{ $pedido->cliente_tipo_documento }}"
                                        data-estado-pago="{{ $pedido->estado_pago }}"
                                        data-url-estado="{{ route('tienda.admin.pedidos.estado', $pedido) }}"
                                        data-url-facturar="{{ route('tienda.admin.pedidos.entregar_facturar', $pedido) }}">
                                    <i class="fas fa-box mr-1"></i> Entregar
                                </button>
                            @endif
                        </div>
                    </td>
                    
                    <!-- 5. Acciones -->
                    <td class="px-4 py-3 align-middle text-right text-nowrap">
                        <a href="{{ route('tienda.admin.pedidos.show', $pedido) }}" 
                           class="btn btn-sm btn-white text-primary font-weight-bold px-3 shadow-xs rounded-lg border"
                           title="Ver detalle del pedido">
                            <i class="fas fa-eye"></i> <span class="d-none d-lg-inline ml-1">Detalle</span>
                        </a>
                        @if($pedido->venta_id)
                            <button type="button" 
                                    class="btn btn-sm btn-white text-success border font-weight-bold px-3 shadow-xs rounded-lg ml-1 btn-reimprimir-comprobante"
                                    title="Imprimir comprobante"
                                    data-print-ticket-url="{{ route('ventas.print_ticket', $pedido->venta_id) }}"
                                    data-print-a4-url="{{ route('ventas.print_a4', $pedido->venta_id) }}"
                                    data-codigo="{{ $pedido->codigo }}">
                                <i class="fas fa-print"></i> <span class="d-none d-lg-inline ml-1">Comprobante</span>
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-5 border-0">
                        <div class="d-flex flex-column align-items-center">
                            <i class="fas fa-receipt mb-3 text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                            <span class="font-weight-medium">No se encontraron pedidos online.</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-3" id="pagination-links">
    <div class="text-muted" style="font-size: 0.9rem;">
        Mostrando {{ $pedidos->firstItem() ?? 0 }} al {{ $pedidos->lastItem() ?? 0 }} de {{ $pedidos->total() }} pedidos
    </div>
    <div>
        {{ $pedidos->links() }}
    </div>
</div>
