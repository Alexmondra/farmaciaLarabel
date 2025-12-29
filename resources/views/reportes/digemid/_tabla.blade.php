<div class="d-flex flex-column h-100">

    <div class="flex-grow-1" style="overflow-y: auto;">
        <table class="table table-hover table-striped text-nowrap mb-0 align-middle">
            <thead>
                <tr>
                    {{-- Columnas Dinámicas --}}
                    @foreach($colsSeleccionadas as $key)
                    <th class="border-bottom text-uppercase text-secondary small fw-bold"
                        style="position: sticky; top: 0; z-index: 10; background-color: var(--bs-card-bg);">
                        {{ $columnasDisponibles[$key] ?? $key }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($resultados as $row)
                <tr>
                    {{-- Datos --}}
                    @foreach($colsSeleccionadas as $key)
                    <td>
                        @switch($key)
                        @case('cod_establecimiento')
                        {{-- NUEVO: Código de Sucursal --}}
                        <span class="badge bg-light text-dark border">
                            {{ $row->sucursal_cod_digemid ?? 'S/N' }}
                        </span>
                        @break

                        @case('codigo_digemid')
                        <code class="text-primary fw-bold">{{ $row->medicamento->codigo_digemid ?? '--' }}</code>
                        @break

                        @case('precio_venta')
                        <span class="fw-bold">S/ {{ number_format($row->precio_venta, 2) }}</span>
                        @break

                        @case('stock_computado')
                        <span class="badge {{ $row->stock_computado > 0 ? 'bg-info text-dark' : 'bg-danger' }}">
                            {{ $row->stock_computado }}
                        </span>
                        @break

                        @case('estado')
                        @if($row->activo)
                        <span class="badge bg-success">Activo</span>
                        @else
                        <span class="badge bg-secondary">Inactivo</span>
                        @endif
                        @break

                        @default
                        {{ $row->medicamento->$key ?? $row->$key ?? '--' }}
                        @endswitch
                    </td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($colsSeleccionadas) + 1 }}" class="text-center py-5">
                        <div class="text-muted opacity-75">
                            <i class="fas fa-inbox fa-3x mb-3"></i><br>
                            <span class="h6">No se encontraron datos</span>
                            <p class="small">Verifica los filtros o el stock disponible.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="card-footer py-2 flex-shrink-0 border-top" style="background-color: var(--bs-card-bg);">
        <div id="pagination-links" class="d-flex justify-content-end mb-0">
            {{ $resultados->links() }}
        </div>
    </div>
</div>