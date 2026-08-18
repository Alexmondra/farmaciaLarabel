<div class="table-responsive">
    <table class="table table-hover align-middle rounded-xl overflow-hidden" style="border-collapse: separate; border-spacing: 0;">
        <thead class="bg-light text-muted uppercase font-weight-bold" style="font-size: 0.85rem; letter-spacing: 0.05em;">
            <tr>
                <th class="border-0 px-4 py-3">Producto</th>
                <th class="border-0 px-4 py-3">Sucursal</th>
                <th class="border-0 px-4 py-3">Precio Web</th>
                <th class="border-0 px-4 py-3">Stock</th>
                <th class="border-0 px-4 py-3 text-center">Visible</th>
                <th class="border-0 px-4 py-3 text-center">Destacado</th>
                <th class="border-0 px-4 py-3 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y text-secondary">
            @forelse ($productos as $producto)
                <tr class="transition-all duration-300 hover:bg-light" style="font-size: 0.95rem;">
                    <td class="px-4 py-3 align-middle">
                        <div class="d-flex align-items-center">
                            @if($producto->imagenesVisibles->isNotEmpty())
                                <img src="{{ asset('storage/' . $producto->imagenesVisibles->first()->imagen_path) }}" 
                                     alt="{{ $producto->nombre }}" 
                                     class="rounded-lg mr-3 shadow-sm border" 
                                     style="width: 48px; height: 48px; object-fit: cover; transition: transform 0.2s;"
                                     onmouseover="this.style.transform='scale(1.1)'"
                                     onmouseout="this.style.transform='scale(1)'">
                            @elseif($producto->medicamento->imagen_path)
                                <img src="{{ asset('storage/' . $producto->medicamento->imagen_path) }}" 
                                     alt="{{ $producto->nombre }}" 
                                     class="rounded-lg mr-3 shadow-sm border" 
                                     style="width: 48px; height: 48px; object-fit: cover; transition: transform 0.2s;"
                                     onmouseover="this.style.transform='scale(1.1)'"
                                     onmouseout="this.style.transform='scale(1)'">
                            @else
                                <div class="rounded-lg mr-3 bg-light border d-flex align-items-center justify-content-center text-muted shadow-sm" 
                                     style="width: 48px; height: 48px; font-size: 1.2rem;">
                                    <i class="fas fa-pills"></i>
                                </div>
                            @endif
                            <div>
                                <span class="font-weight-bold text-dark d-block" style="letter-spacing: -0.01em;">{{ $producto->nombre }}</span>
                                <span class="text-xs text-muted font-mono" style="font-size: 0.8rem;">Cód: {{ $producto->medicamento->codigo ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 align-middle font-weight-medium">{{ $producto->sucursal->nombre ?? '-' }}</td>
                    <td class="px-4 py-3 align-middle">
                        @if($producto->precio_web)
                            <span class="font-weight-bold text-dark">S/ {{ number_format((float) $producto->precio_web, 2) }}</span>
                        @else
                            <span class="text-muted" style="font-size: 0.9rem;">
                                <i class="fas fa-store mr-1 text-xs"></i>Precio Sucursal (S/ {{ number_format((float) $producto->precioOriginal(), 2) }})
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 align-middle">
                        @if($producto->stock_modo === 'sin_control')
                            <span class="badge badge-pill badge-light border text-muted px-2 py-1">Sin control</span>
                        @elseif($producto->stock_modo === 'stock_manual')
                            <span class="badge badge-pill badge-warning border text-dark px-2 py-1">Manual: {{ $producto->stock_web ?? 0 }}</span>
                        @else
                            <span class="badge badge-pill badge-info border text-white px-2 py-1">Sucursal: {{ $producto->stockDisponible() }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 align-middle text-center">
                        <span class="badge rounded-xl px-3 py-1 font-weight-bold {{ $producto->visible ? 'bg-success-light text-success border border-success' : 'bg-light text-muted border' }}" 
                              style="{{ $producto->visible ? 'background-color: #d1fae5; color: #065f46; border-color: #a7f3d0;' : '' }}">
                            {{ $producto->visible ? 'Visible' : 'Oculto' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 align-middle text-center">
                        @if($producto->destacado)
                            <span class="text-warning" title="Destacado" style="font-size: 1.1rem;"><i class="fas fa-star"></i></span>
                        @else
                            <span class="text-muted" title="No destacado" style="font-size: 1.1rem;"><i class="far fa-star"></i></span>
                        @endif
                    </td>
                 
                    <td class="px-4 py-3 align-middle text-right border-0">
                        <div class="btn-group rounded-lg overflow-hidden shadow-sm" role="group">
                            <a href="{{ route('tienda.productos.show', $producto->slug) }}" target="_blank" 
                               class="btn btn-sm btn-white border-right text-info font-weight-medium px-3" title="Ver en tienda">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <a href="{{ route('tienda.admin.productos.edit', $producto) }}" 
                               class="btn btn-sm btn-white border-right text-primary font-weight-medium px-3" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('tienda.admin.productos.destroy', $producto) }}" 
                                  class="d-inline" onsubmit="return confirm('¿Retirar producto de la tienda?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-white text-danger font-weight-medium px-3" title="Retirar">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <div class="d-flex flex-column align-items-center">
                            <i class="fas fa-box-open mb-3 text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                            <span class="font-weight-medium">No se encontraron productos en la tienda.</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-3" id="pagination-links">
    <div class="text-muted" style="font-size: 0.9rem;">
        Mostrando {{ $productos->firstItem() ?? 0 }} al {{ $productos->lastItem() ?? 0 }} de {{ $productos->total() }} productos
    </div>
    <div>
        {{ $productos->links() }}
    </div>
</div>
