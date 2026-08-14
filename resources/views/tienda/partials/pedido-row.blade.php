<tr>
    <td><strong>{{ $pedido->codigo }}</strong></td>
    <td>{{ $pedido->created_at->format('d/m/Y') }}</td>
    <td>{{ $pedido->sucursal->nombre ?? '-' }}</td>
    <td class="price">S/ {{ number_format((float) $pedido->total, 2) }}</td>
    <td>
        <span class="badge bg-{{ $pedido->metodo_pago === 'PAGO_ONLINE' ? 'info' : 'secondary' }}">
            {{ $pedido->metodo_pago === 'PAGO_ONLINE' ? 'Online' : 'Al recoger' }}
        </span>
    </td>
    <td>
        <span class="badge bg-{{ in_array($pedido->estado_pago, ['COMPLETADO', 'PAGADO']) ? 'success' : 'warning' }}">
            {{ in_array($pedido->estado_pago, ['COMPLETADO', 'PAGADO']) ? 'Pagado' : 'Pendiente' }}
        </span>
    </td>
    <td>
        Recojo en tienda
        @if($pedido->fecha_recojo)
            <br><small class="text-muted">{{ $pedido->fecha_recojo->format('d/m/Y H:i') }}</small>
        @endif
    </td>
    <td>
        <span class="badge bg-{{ $pedido->estado === 'PENDIENTE' ? 'warning' : ($pedido->estado === 'CONFIRMADO' ? 'success' : 'secondary') }}">
            {{ $pedido->estado }}
        </span>
    </td>
    <td class="text-nowrap">
        <a href="{{ route('tienda.pedidos.show', $pedido->codigo) }}" class="btn btn-sm btn-store-outline mr-1">Ver</a>
        @if($pedido->venta_id)
            <button type="button" class="btn btn-sm btn-store-outline btn-ver-venta" data-venta-id="{{ $pedido->venta_id }}">
                Comprobante
            </button>
        @endif
    </td>
</tr>
