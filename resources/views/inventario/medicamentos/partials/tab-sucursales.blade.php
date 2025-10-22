<div class="d-flex justify-content-between align-items-center mb-3">
    <h5><i class="fas fa-store"></i> Gestión por Sucursales</h5>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarSucursal">
        <i class="fas fa-plus"></i> Agregar a Sucursales
    </button>
</div>

@if($sucursalesMedicamento->count() > 0)
<div class="table-responsive">
    <table class="table table-hover">
        <thead class="thead-light">
            <tr>
                <th>Sucursal</th>
                <th>Precio Compra</th>
                <th>Precio Venta</th>
                <th>Stock Actual</th>
                <th>Stock Mínimo</th>
                <th>Ubicación</th>
                <th>Última Actualización</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sucursalesMedicamento as $sucursal)
            @php
                $pivot = $sucursal->pivot;
                $stockLotes = $medicamento->lotes()->where('sucursal_id', $sucursal->id)->sum('cantidad_actual');
                $isStockBajo = $stockLotes <= $pivot->stock_minimo;
            @endphp
            <tr class="{{ $isStockBajo ? 'table-warning' : '' }}">
                <td>
                    <strong>{{ $sucursal->nombre }}</strong><br>
                    <small class="text-muted">{{ $sucursal->direccion }}</small>
                </td>
                <td>
                    <span class="text-primary">S/ {{ number_format($pivot->precio_compra, 2) }}</span>
                </td>
                <td>
                    <span class="text-success">S/ {{ number_format($pivot->precio_venta, 2) }}</span>
                </td>
                <td>
                    <span class="badge badge-{{ $isStockBajo ? 'warning' : 'success' }} badge-lg">
                        {{ $stockLotes }}
                    </span>
                    @if($isStockBajo)
                    <br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Stock bajo</small>
                    @endif
                </td>
                <td>
                    <span class="badge badge-info">{{ $pivot->stock_minimo }}</span>
                </td>
                <td>
                    @if($pivot->ubicacion)
                        <i class="fas fa-map-marker-alt"></i> {{ $pivot->ubicacion }}
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    <small class="text-muted">
                        {{ $pivot->updated_at->format('d/m/Y H:i') }}
                        @if($pivot->updated_by)
                        <br>por {{ \App\Models\User::find($pivot->updated_by)->name ?? 'Usuario' }}
                        @endif
                    </small>
                </td>
                <td>
                    <div class="btn-group-vertical btn-group-sm" role="group">
                        <button type="button" class="btn btn-warning btn-sm" 
                                onclick="editarSucursal({{ $sucursal->id }}, '{{ $sucursal->nombre }}', {{ $pivot->precio_compra }}, {{ $pivot->precio_venta }}, {{ $pivot->stock_minimo }}, '{{ $pivot->ubicacion }}')"
                                title="Editar configuración">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-info btn-sm" 
                                onclick="agregarLote({{ $sucursal->id }}, '{{ $sucursal->nombre }}')"
                                title="Agregar lote">
                            <i class="fas fa-plus"></i>
                        </button>
                        <form action="{{ route('inventario.medicamentos.sucursales.destroy', [$medicamento, $sucursal]) }}" 
                              method="POST" class="d-inline" 
                              onsubmit="return confirm('¿Quitar medicamento de esta sucursal?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" title="Quitar de sucursal">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="text-center py-5">
    <i class="fas fa-store fa-3x text-muted mb-3"></i>
    <h5 class="text-muted">No está disponible en ninguna sucursal</h5>
    <p class="text-muted">Agrega este medicamento a las sucursales para gestionar precios y stock</p>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarSucursal">
        <i class="fas fa-plus"></i> Agregar a Sucursales
    </button>
</div>
@endif

