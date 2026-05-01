<div class="card-body p-0 table-responsive">
    <table class="table table-hover w-100 mb-0">
        <thead class="text-muted small bg-light text-uppercase">
            <tr>
                <th class="pl-4" style="width: 50px;">#</th>
                <th>Fecha Emisión</th>
                <th>Comprobante</th>
                <th>N.Crédito</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Estado</th>
                <th class="text-center pr-4">Acción</th>
            </tr>
        </thead>
        <tbody style="color: var(--text-main);">
            @forelse($ventas as $index => $venta)
            <tr style="border-bottom: 1px solid var(--border-color);">

                {{-- 1. CONTADOR SIMPLE --}}
                <td data-label="#" class="pl-4 align-middle font-weight-bold text-muted">
                    {{ ($ventas->currentPage() - 1) * $ventas->perPage() + $loop->iteration }}
                </td>

                {{-- 2. FECHA BIEN FORMATEADA --}}
                <td data-label="Fecha" class="align-middle">
                    <span class="d-block font-weight-bold">
                        {{ $venta->fecha_emision->format('d/m/Y') }}
                    </span>
                    <small class="text-muted">
                        <i class="far fa-clock mr-1"></i>{{ $venta->fecha_emision->format('h:i A') }}
                    </small>
                </td>

                {{-- Comprobante Original --}}
                <td data-label="Documento" class="align-middle">
                    <span class="badge badge-light border">{{ $venta->tipo_comprobante }}</span>
                    <span class="d-block small text-muted mt-1">{{ $venta->serie }}-{{ $venta->numero }}</span>
                </td>

                {{-- Nota de Crédito (Serie de la anulación) --}}
                <td data-label="N.Crédito" class="align-middle">
                    @php
                        $nc = $venta->notasCredito->first();
                    @endphp
                    @if($nc)
                        <span class="badge badge-danger">NC</span>
                        <span class="d-block small font-weight-bold mt-1 text-danger">{{ $nc->serie }}-{{ $nc->numero }}</span>
                    @else
                        <span class="text-muted small">-</span>
                    @endif
                </td>

                {{-- Cliente --}}
                <td data-label="Cliente" class="align-middle">
                    <span class="font-weight-bold text-dark">
                        {{ Str::limit($venta->cliente->nombre_completo ?? 'Público General', 20) }}
                    </span>
                    <br>
                    <small class="text-muted">{{ $venta->cliente->documento ?? '-' }}</small>
                </td>

                {{-- Total --}}
                <td data-label="Monto" class="align-middle">
                    <span class="text-success font-weight-bold" style="font-size: 1.1rem;">
                        S/ {{ number_format($venta->total_neto, 2) }}
                    </span>
                </td>

                {{-- Estado --}}
                <td data-label="Estado" class="align-middle">
                    @if($venta->estado == 'ANULADO')
                    <span class="badge-modern badge-void">ANULADO</span>
                    @else
                    <span class="badge-modern badge-paid">COMPLETADO</span>
                    @endif
                </td>

                {{-- Botones --}}
                <td class="text-center pr-4">
                    <div class="btn-group">
                        <a href="{{ route('reportes.venta.pdf', $venta->id) }}" target="_blank"
                            class="btn btn-sm btn-light text-danger ml-1"
                            title="PDF" style="border-radius: 50%;">
                            <i class="fas fa-file-pdf"></i>
                        </a>

                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        <i class="far fa-folder-open fa-3x text-muted mb-3 opacity-50"></i>
                        <h6 class="text-muted">No se encontraron ventas.</h6>
                        <small class="text-muted">Intenta cambiar los filtros de búsqueda.</small>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- PAGINACIÓN AJAX --}}
<div class="card-footer bg-transparent border-top-0 d-flex justify-content-end" id="pagination-container">
    {{ $ventas->links() }}
</div>
