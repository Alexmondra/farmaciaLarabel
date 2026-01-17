<div class="table-responsive p-0">
    <table class="table table-hover mb-0">
        <thead class="thead-dark-adaptive">
            <tr>
                <th>Comprobante</th>
                <th class="d-none d-md-table-cell">Cliente</th>
                <th class="d-none d-lg-table-cell">Emisión</th>
                <th class="text-right">Total</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody id="tabla-ventas">
            @forelse($ventas as $venta)
            <tr class="fila-venta {{ $venta->estado == 'ANULADO' ? 'venta-anulada' : '' }}"
                data-url="{{ route('ventas.show', $venta->id) }}"
                style="cursor: pointer;">

                <td class="align-middle">
                    <div class="d-flex flex-column">
                        <span class="font-weight-bold {{ $venta->estado == 'ANULADO' ? 'text-secondary' : 'text-primary' }}">
                            {{ $venta->tipo_comprobante }}
                        </span>
                        <small class="text-bold text-nowrap">{{ $venta->serie }}-{{ $venta->numero }}</small>
                        {{-- En móvil mostramos el cliente debajo del nro --}}
                        <small class="d-md-none text-muted">{{ Str::limit($venta->cliente->nombre ?? 'Público General', 15) }}</small>
                    </div>
                </td>

                <td class="align-middle d-none d-md-table-cell small">
                    {{ Str::limit($venta->cliente->nombre ?? 'Público General', 25) }}
                </td>

                <td class="d-none d-lg-table-cell align-middle small">
                    {{ $venta->fecha_emision->format('d/m/Y H:i') }}
                </td>

                <td class="align-middle text-right font-weight-bold">
                    <span class="text-nowrap">S/ {{ number_format($venta->total_neto, 2) }}</span>
                </td>

                <td class="align-middle text-center">
                    @if($venta->estado == 'ANULADO')
                    <span class="badge badge-danger badge-pill px-2">ANULADO</span>
                    @else
                    <span class="badge badge-success badge-pill px-2">EMITIDA</span>
                    @endif
                </td>

                <td class="align-middle text-center">
                    <div class="btn-group shadow-sm">
                        <a href="{{ route('ventas.show', $venta->id) }}"
                            class="btn btn-sm btn-outline-info"
                            title="Ver Detalle">
                            <i class="fas fa-eye"></i>
                        </a>

                        @if($venta->estado !== 'ANULADO')
                        <button type="button"
                            onclick="event.stopPropagation(); confirmarAnulacion('{{ $venta->id }}', '{{ $venta->serie }}-{{ $venta->numero }}')"
                            class="btn btn-sm btn-outline-danger ml-1"
                            title="Anular Venta">
                            <i class="fas fa-ban"></i>
                        </button>
                        <form id="form-anular-{{ $venta->id }}" action="{{ route('ventas.anular', $venta->id) }}" method="POST" style="display:none;">
                            @csrf
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-search fa-3x mb-3 opacity-50"></i><br>
                        No se encontraron ventas.
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="card-footer bg-transparent py-3 d-flex justify-content-center">
    {!! $ventas->appends(request()->query())->links('pagination::bootstrap-4') !!}
</div>