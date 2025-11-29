{{-- resources/views/inventario/medicamentos/_index_tabla.blade.php --}}

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="bg-light">
            <tr>
                <th width="5%" class="text-center"><i class="fas fa-image text-muted"></i></th>
                <th width="35%">Producto</th>
                <th width="20%">Categoría</th>
                <th width="20%">Stock y Precio</th>
                <th width="10%" class="text-right">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($medicamentos as $m)
            <tr>
                {{-- FOTO --}}
                <td class="align-middle text-center">
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

                {{-- CATEGORÍA --}}
                <td class="align-middle">
                    <span class="badge badge-light border">{{ $m->categoria->nombre ?? 'General' }}</span>
                </td>

                {{-- STOCK Y PRECIO --}}
                <td class="align-middle">
                    @if($sucursalSeleccionada)
                    {{-- MODO SUCURSAL --}}
                    <div class="d-flex flex-column">
                        <span class="font-weight-bold {{ $m->stock_unico <= 5 ? 'text-danger' : 'text-success' }}">
                            {{ $m->stock_unico }} un.
                        </span>

                        <div class="d-flex align-items-center">
                            @if($m->precio_v)
                            <small class="text-dark font-weight-bold mr-2" id="price-display-{{ $m->id }}">
                                S/ {{ number_format($m->precio_v, 2) }}
                            </small>
                            @else
                            <small class="text-muted mr-2" id="price-display-{{ $m->id }}">Sin precio</small>
                            @endif

                            {{-- Botón Lápiz (Llama a la función JS del padre) --}}
                            <button type="button"
                                class="btn btn-xs btn-outline-secondary rounded-circle"
                                onclick="abrirModalPrecio({{ $m->id }}, '{{ addslashes($m->nombre) }}', '{{ $m->precio_v ?? 0 }}')"
                                title="Cambiar Precio">
                                <i class="fas fa-pencil-alt" style="font-size: 0.7rem;"></i>
                            </button>
                        </div>
                    </div>
                    @else
                    {{-- MODO GLOBAL --}}
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

                        @if($sucursalSeleccionada)
                        <button type="button" class="btn btn-sm btn-outline-danger"
                            onclick="if(confirm('¿Quitar de {{ $sucursalSeleccionada->nombre }}?')) document.getElementById('delete-form-{{ $m->id }}').submit();">
                            <i class="fas fa-trash"></i>
                        </button>
                        <form id="delete-form-{{ $m->id }}" action="{{ route('inventario.medicamento_sucursal.destroy', ['medicamento' => $m->id, 'sucursal' => $sucursalSeleccionada->id]) }}" method="POST" style="display: none;">
                            @csrf @method('DELETE')
                        </form>
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
    {{ $medicamentos->appends(['q' => $q])->links() }}
</div>
@endif