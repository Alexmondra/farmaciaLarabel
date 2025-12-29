{{-- resources/views/inventario/medicamentos/_index_tabla.blade.php --}}

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="bg-light">
            <tr>
                <th width="5%" class="text-center d-none d-sm-table-cell"><i class="fas fa-image text-muted"></i></th>
                <th width="40%">Producto</th>
                <th width="15%" class="d-none d-sm-table-cell">Categoría</th>
                <th width="30%">Stock y Precio</th>
                <th width="10%" class="text-right">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($medicamentos as $m)
            <tr>
                {{-- FOTO (Ocultar en XS) --}}
                <td class="align-middle text-center d-none d-sm-table-cell">
                    @if($m->imagen_url)
                    <img src="{{ $m->imagen_url }}" class="img-circle elevation-1" style="width: 40px; height: 40px; object-fit: cover;">
                    @else
                    <span class="avatar-letter bg-light text-muted rounded-circle d-flex justify-content-center align-items-center mx-auto" style="width: 40px; height: 40px;">
                        <i class="fas fa-medkit"></i>
                    </span>
                    @endif
                </td>

                {{-- DATOS --}}
                <td class="align-middle">
                    <h6 class="mb-0 font-weight-bold text-dark">{{ $m->nombre }}</h6>
                    <small class="text-muted">
                        <i class="fas fa-barcode mr-1"></i> {{ $m->codigo_barra ?? $m->codigo }}
                        @if($m->laboratorio)
                        <span class="mx-1">|</span> {{ $m->laboratorio }}
                        @endif
                    </small>
                </td>

                {{-- CATEGORÍA (Ocultar en XS) --}}
                <td class="align-middle d-none d-sm-table-cell">
                    <span class="badge badge-light border">{{ $m->categoria->nombre ?? 'General' }}</span>
                </td>

                {{-- STOCK Y PRECIO --}}
                <td class="align-middle">
                    @if($sucursalSeleccionada)
                    {{-- MODO SUCURSAL: Mostramos Stock Real, Minimo y Precio --}}

                    @php
                    // Obtenemos los datos del pivot (tabla intermedia) de forma segura
                    $pivot = $m->sucursales->find($sucursalSeleccionada->id)->pivot ?? null;
                    $stockMin = $pivot ? $pivot->stock_minimo : 0;
                    // Usamos tu accessor existente para el precio o el pivot directo
                    $precio = $m->precio_v ?? ($pivot ? $pivot->precio_venta : 0);
                    @endphp

                    <div class="d-flex flex-column">

                        {{-- 1. Stock Real --}}
                        <div class="mb-1">
                            <span class="font-weight-bold {{ $m->stock_unico <= $stockMin ? 'text-danger animate__animated animate__pulse' : 'text-success' }}" style="font-size: 1.1rem;">
                                {{ $m->stock_unico }} un.
                            </span>
                        </div>

                        {{-- 2. Stock Mínimo (NUEVO) --}}
                        <div class="d-flex align-items-center mb-1 text-muted" style="font-size: 0.85rem;">
                            <i class="fas fa-arrow-down mr-1 text-secondary" style="font-size: 0.7rem;"></i> Min:
                            <strong class="mx-1" id="min-display-{{ $m->id }}">{{ $stockMin }}</strong>

                            @can('medicamentos.editar')
                            <a href="#"
                                class="text-secondary ml-1"
                                onclick="abrirModalStockMin({{ $m->id }}, '{{ addslashes($m->nombre) }}', {{ $stockMin }}); return false;">
                                <i class="fas fa-pencil-alt" style="font-size: 0.7rem;"></i>
                            </a>
                            @endcan
                        </div>

                        {{-- 3. PRECIOS (Bloque Mejorado) --}}
                        <div class="mt-1">
                            @if($precio)
                            {{-- Precio Unitario --}}
                            <div class="d-flex align-items-center justify-content-between" style="max-width: 140px;">
                                <span class="text-primary font-weight-bold small" id="price-display-{{ $m->id }}">
                                    S/ {{ number_format($precio, 2) }}
                                    <span class="text-muted" style="font-size: 0.7em;">(Unid)</span>
                                </span>

                                @can('medicamentos.editar')
                                {{-- BOTÓN DE EDICIÓN: Ahora pasa todos los parámetros --}}
                                <button type="button"
                                    class="btn btn-xs btn-light border rounded-circle ml-2"
                                    style="width: 22px; height: 22px;"
                                    onclick="abrirModalPrecio(
                                            {{ $m->id }}, 
                                            '{{ addslashes($m->nombre) }}', 
                                            '{{ $precio }}', 
                                            '{{ $pivot->precio_blister ?? 0 }}', 
                                            '{{ $pivot->precio_caja ?? 0 }}',
                                            {{ $m->unidades_por_blister ?? 0 }},
                                            {{ $m->unidades_por_envase ?? 1 }}
                                        )"
                                    title="Configurar Precios">
                                    <i class="fas fa-pencil-alt text-secondary" style="font-size: 0.7rem;"></i>
                                </button>
                                @endcan
                            </div>

                            {{-- Precios Secundarios (Solo visualización pequeña) --}}
                            @if( ($pivot->precio_blister ?? 0) > 0 || ($pivot->precio_caja ?? 0) > 0 )
                            <div class="mt-1 pl-1 border-left border-secondary" style="font-size: 0.75rem; line-height: 1.1;">
                                @if(($pivot->precio_blister ?? 0) > 0)
                                <div class="text-muted">Blís: <strong>S/ {{ number_format($pivot->precio_blister, 2) }}</strong></div>
                                @endif
                                @if(($pivot->precio_caja ?? 0) > 0)
                                <div class="text-muted">Caja: <strong>S/ {{ number_format($pivot->precio_caja, 2) }}</strong></div>
                                @endif
                            </div>
                            @endif

                            @else
                            {{-- Sin Precio --}}
                            <div class="d-flex align-items-center">
                                <small class="text-danger mr-2">Sin asignar</small>
                                @can('medicamentos.editar')
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger rounded-circle"
                                    onclick="abrirModalPrecio({{ $m->id }}, '{{ addslashes($m->nombre) }}', 0, 0, 0, {{ $m->unidades_por_blister ?? 0 }}, {{ $m->unidades_por_envase ?? 1 }})">
                                    <i class="fas fa-plus"></i>
                                </button>
                                @endcan
                            </div>
                            @endif
                        </div>
                    </div>

                    @else
                    {{-- MODO GLOBAL (Sin cambios, muestra resumen) --}}
                    @php $lista = $m->stock_por_sucursal ?? collect(); @endphp
                    @if($lista->isEmpty())
                    <span class="badge badge-danger">Sin Stock</span>
                    @else
                    @foreach($lista->take(2) as $item)
                    <div style="font-size: 0.85rem;">
                        <span class="text-muted">{{ Str::limit($item['sucursal_name'], 10) }}:</span>
                        <strong>{{ $item['stock'] }}</strong>
                    </div>
                    @endforeach
                    @if($lista->count() > 2)
                    <small class="text-muted">+ {{ $lista->count() - 2 }} más...</small>
                    @endif
                    @endif
                    @endif
                </td>
                {{-- ACCIONES --}}
                <td class="align-middle text-right">
                    <div class="btn-group">
                        <a href="{{ route('inventario.medicamentos.show', $m->id) }}" class="btn btn-sm btn-outline-info" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                        {{-- PERMISO ELIMINAR --}}
                        @if($sucursalSeleccionada)
                        @can('medicamentos.eliminar')
                        <button type="button" class="btn btn-sm btn-outline-danger"
                            title="Quitar de esta sucursal"
                            onclick="if(confirm('¿Quitar de {{ $sucursalSeleccionada->nombre }}?')) document.getElementById('delete-form-{{ $m->id }}').submit();">
                            <i class="fas fa-trash"></i>
                        </button>
                        <form id="delete-form-{{ $m->id }}" action="{{ route('inventario.medicamento_sucursal.destroy', ['medicamento' => $m->id, 'sucursal' => $sucursalSeleccionada->id]) }}" method="POST" style="display: none;">
                            @csrf @method('DELETE')
                        </form>
                        @endcan
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-search fa-3x mb-3 opacity-50"></i>
                        <h5>No encontramos medicamentos...</h5>
                        <p class="mb-0">Intenta con otro término.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- PAGINACIÓN --}}
@if ($medicamentos->hasPages())
<div class="card-footer bg-white border-top-0 d-flex justify-content-center">
    {{ $medicamentos->appends(['q' => $q ?? ''])->links() }}
</div>
@endif