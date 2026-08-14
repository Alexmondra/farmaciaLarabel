<tr>
    <td>
        <strong>{{ $venta->tipo_comprobante }}</strong>
        @if($venta->serie && $venta->numero)
            <br><small class="text-muted">{{ $venta->serie }}-{{ $venta->numero }}</small>
        @endif
    </td>
    <td>{{ $venta->fecha_emision ? $venta->fecha_emision->format('d/m/Y') : '-' }}</td>
    <td>{{ $venta->sucursal->nombre ?? '-' }}</td>
    <td class="price">S/ {{ number_format((float) $venta->total_neto, 2) }}</td>
    <td>
        <button type="button" class="btn btn-sm btn-store-outline btn-ver-venta" data-venta-id="{{ $venta->id }}">
            Ver detalle
        </button>
    </td>
</tr>
