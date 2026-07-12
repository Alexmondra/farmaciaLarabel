@foreach ($productos as $producto)
    @php
        $precio = $producto->precioVenta();
        $categoriaNombre = $producto->medicamento->categoria->nombre ?? 'Medicamento';
        $descripcion = $producto->descripcion_web ?: Str::limit($producto->medicamento->descripcion ?? 'Producto disponible en tienda.', 82);
    @endphp
    <div class="col-6 col-lg-3 product-item">
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
            <div class="product-info">
                <div class="product-meta">{{ $categoriaNombre }}</div>
                <h3 class="product-title">{{ $producto->nombre }}</h3>
                @if($producto->descripcion_web || $producto->medicamento->descripcion)
                    <p class="product-description">{{ $descripcion }}</p>
                @endif
                @if($producto->medicamento->laboratorio)
                    <div class="product-lab">{{ $producto->medicamento->laboratorio }}</div>
                @endif
                <div class="product-branch">{{ $producto->sucursal->nombre ?? 'Sucursal' }}</div>
                <div class="product-bottom">
                    <div class="price">S/ {{ number_format((float) $precio, 2) }}</div>
                    <form method="POST" action="{{ route('tienda.carrito.store', $producto) }}" class="form-agregar-carrito">
                        @csrf
                        <button class="btn-add" aria-label="Agregar {{ $producto->nombre }} al carrito">Agregar</button>
                    </form>
                </div>
            </div>
        </article>
    </div>
@endforeach
