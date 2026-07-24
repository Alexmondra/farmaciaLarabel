@foreach ($productos as $producto)
    @php
        $precio = $producto->precioVenta();
        $categoriaNombre = $producto->medicamento->categoria->nombre ?? 'Medicamento';
        $descripcion = $producto->descripcion_web ?: Str::limit($producto->medicamento->descripcion ?? 'Producto disponible en tienda.', 82);
    @endphp
    <div class="col-6 col-lg-3 product-item mb-4">
        <article class="product-card h-100">
            <a href="{{ route('tienda.productos.show', $producto->slug) }}" class="product-media" aria-label="Ver {{ $producto->nombre }}">
                @if($producto->imagen_url)
                    <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" loading="lazy" decoding="async">
                @else
                    <span class="product-placeholder">+</span>
                @endif
                @if($producto->destacado)
                    <span class="deal-tag">Oferta</span>
                @endif
            </a>
            <div class="product-info flex flex-col h-100 justify-between">
                <div>
                    <div class="product-meta">{{ $categoriaNombre }}</div>
                    <h3 class="product-title hover:text-emerald-600 transition-colors">
                        <a href="{{ route('tienda.productos.show', $producto->slug) }}" class="text-decoration-none text-reset">
                            {{ $producto->nombre }}
                        </a>
                    </h3>
                    @if($producto->descripcion_web || $producto->medicamento->descripcion)
                        <p class="product-description">{{ $descripcion }}</p>
                    @endif
                    @if($producto->medicamento->laboratorio)
                        <div class="product-lab text-xs text-slate-400 mt-1">Lab: <span class="font-semibold text-slate-600">{{ $producto->medicamento->laboratorio }}</span></div>
                    @endif
                    <div class="product-branch mt-2">{{ $producto->sucursal->nombre ?? 'Sucursal' }}</div>
                </div>
                <div class="product-bottom mt-4">
                    <div class="price">
                        <span class="text-xs text-slate-400 font-medium">S/</span>
                        <span class="text-lg font-extrabold text-emerald-600">{{ number_format((float) $precio, 2) }}</span>
                    </div>
                    <form method="POST" action="{{ route('tienda.carrito.store', $producto) }}" class="form-agregar-carrito">
                        @csrf
                        <button class="btn-add d-inline-flex align-items-center gap-1" aria-label="Agregar {{ $producto->nombre }} al carrito">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 0.95rem; height: 0.95rem;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span class="d-none d-sm-inline">Agregar</span>
                        </button>
                    </form>
                </div>
            </div>
        </article>
    </div>
@endforeach
