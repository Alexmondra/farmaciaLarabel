@extends('tienda.layout')

@section('title', $producto->nombre)

@push('styles')
<style>
    .detail-gallery-wrap { position: relative; background: #f7faf8; border-radius: 1rem; }

    .detail-main-img { background: #f7faf8; border-radius: 1rem; cursor: crosshair; overflow: hidden; position: relative; aspect-ratio: 1 / 1; }
    .detail-main-img img { display: block; height: 100%; object-fit: contain; pointer-events: none; transition: transform .18s ease, opacity .18s ease; width: 100%; }
    .detail-main-img.is-zoomed img { transform: scale(2.3); }

    .detail-thumbs { display: flex; flex-wrap: wrap; gap: .65rem; margin-top: .85rem; }
    .detail-thumb { background: #f7faf8; border: 2px solid transparent; border-radius: .75rem; cursor: pointer; flex: 0 0 68px; height: 68px; overflow: hidden; padding: .3rem; transition: border-color .16s ease, box-shadow .16s ease; }
    .detail-thumb:hover { border-color: var(--store-green); box-shadow: 0 4px 14px rgba(0, 107, 72, .12); }
    .detail-thumb.is-active { border-color: var(--store-green); box-shadow: 0 0 0 2px var(--store-green-soft); }
    .detail-thumb img { display: block; height: 100%; object-fit: contain; width: 100%; }

    .detail-zoom-hint { align-items: center; background: rgba(15, 23, 42, .72); border-radius: .5rem; bottom: .75rem; color: white; display: flex; font-size: .75rem; font-weight: 600; gap: .35rem; opacity: 0; padding: .35rem .65rem; pointer-events: none; position: absolute; right: .75rem; transition: opacity .18s ease; }
    .detail-main-img:hover .detail-zoom-hint { opacity: 1; }

    @media (max-width: 767.98px) {
        .detail-main-img { aspect-ratio: auto; height: 280px; }
        .detail-thumb { flex: 0 0 56px; height: 56px; }
    }
</style>
@endpush

@section('content')
@php
    $galeria = $producto->galeria_imagenes;
    $imagenPrincipal = $galeria[0]['url'] ?? $producto->imagen_url;
@endphp
<div class="row g-4">
    <div class="col-lg-7">
        <div class="store-card bg-white p-4">
            <div class="detail-gallery-wrap">
                <div class="detail-main-img" id="detailMain">
                    @if($imagenPrincipal)
                        <img src="{{ $imagenPrincipal }}" alt="{{ $producto->nombre }}" id="detailMainImg" loading="eager" decoding="async">
                    @else
                        <span class="product-placeholder">+</span>
                    @endif
                    <span class="detail-zoom-hint"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>Pasa el mouse para ampliar</span>
                </div>
                @if(count($galeria) > 1)
                    <div class="detail-thumbs" id="detailThumbs">
                        @foreach($galeria as $index => $imagen)
                            <div class="detail-thumb @if($index === 0) is-active @endif" data-src="{{ $imagen['url'] }}">
                                <img src="{{ $imagen['url'] }}" alt="{{ $imagen['alt'] }}" loading="lazy" decoding="async">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <span class="badge bg-success mt-4 mb-3">{{ $producto->sucursal->nombre ?? 'Sucursal' }}</span>
            <h1 class="h3 fw-bold">{{ $producto->nombre }}</h1>
            @if($producto->descripcion_web || $producto->medicamento->descripcion)
                <p class="text-muted">{{ $producto->descripcion_web ?: $producto->medicamento->descripcion }}</p>
            @endif
            <dl class="row small">
                @if($producto->medicamento->laboratorio)
                    <dt class="col-sm-4">Laboratorio</dt>
                    <dd class="col-sm-8">{{ $producto->medicamento->laboratorio }}</dd>
                @endif
                @if($producto->medicamento->presentacion)
                    <dt class="col-sm-4">Presentacion</dt>
                    <dd class="col-sm-8">{{ $producto->medicamento->presentacion }}</dd>
                @endif
                <dt class="col-sm-4">Stock referencial</dt>
                <dd class="col-sm-8">{{ $stockDisponible === null ? 'Disponible' : $stockDisponible }}</dd>
            </dl>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="store-card bg-white p-4">
            @php
                $precio = $producto->precioVenta();
                $maxCantidad = $stockDisponible === null ? 99 : min(99, max(1, $stockDisponible));
            @endphp
            <div class="price display-6 mb-3">S/ {{ number_format((float) $precio, 2) }}</div>
            <form method="POST" action="{{ route('tienda.carrito.store', $producto) }}" class="form-agregar-carrito">
                @csrf
                <label class="form-label">Cantidad</label>
                <input type="number" name="cantidad" value="1" min="1" max="{{ $maxCantidad }}" class="form-control mb-3">
                <button class="btn btn-store btn-lg w-100">Agregar al carrito</button>
            </form>
        </div>
    </div>
</div>

@if($relacionados->isNotEmpty())
    <div class="mt-5">
        <h2 class="h4 fw-bold mb-4">Productos relacionados</h2>
        <div class="row g-3">
            @foreach($relacionados as $relacionado)
                <div class="col-6 col-lg-3">
                    <article class="product-card h-100">
                        <a href="{{ route('tienda.productos.show', $relacionado->slug) }}" class="product-media">
                            @if($relacionado->imagen_url)
                                <img src="{{ $relacionado->imagen_url }}" alt="{{ $relacionado->nombre }}" loading="lazy" decoding="async">
                            @else
                                <span class="product-placeholder">+</span>
                            @endif
                            @if($relacionado->destacado)
                                <span class="deal-tag">Oferta</span>
                            @endif
                        </a>
                        <div class="product-info">
                            <div class="product-meta">{{ $relacionado->medicamento->categoria->nombre ?? 'Medicamento' }}</div>
                            <h3 class="product-title">{{ $relacionado->nombre }}</h3>
                            <div class="product-branch">{{ $relacionado->sucursal->nombre ?? 'Sucursal' }}</div>
                            <div class="product-bottom">
                                <div class="price">S/ {{ number_format((float) $relacionado->precioVenta(), 2) }}</div>
                                <form method="POST" action="{{ route('tienda.carrito.store', $relacionado) }}" class="form-agregar-carrito">
                                    @csrf
                                    <button class="btn-add">Agregar</button>
                                </form>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
@endif

@if($masProductos->isNotEmpty())
    <div class="mt-5">
        <h2 class="h4 fw-bold mb-4">Mas productos</h2>
        <div class="row g-3">
            @foreach($masProductos as $item)
                <div class="col-6 col-lg-3">
                    <article class="product-card h-100">
                        <a href="{{ route('tienda.productos.show', $item->slug) }}" class="product-media">
                            @if($item->imagen_url)
                                <img src="{{ $item->imagen_url }}" alt="{{ $item->nombre }}" loading="lazy" decoding="async">
                            @else
                                <span class="product-placeholder">+</span>
                            @endif
                            @if($item->destacado)
                                <span class="deal-tag">Oferta</span>
                            @endif
                        </a>
                        <div class="product-info">
                            <div class="product-meta">{{ $item->medicamento->categoria->nombre ?? 'Medicamento' }}</div>
                            <h3 class="product-title">{{ $item->nombre }}</h3>
                            <div class="product-branch">{{ $item->sucursal->nombre ?? 'Sucursal' }}</div>
                            <div class="product-bottom">
                                <div class="price">S/ {{ number_format((float) $item->precioVenta(), 2) }}</div>
                                <form method="POST" action="{{ route('tienda.carrito.store', $item) }}" class="form-agregar-carrito">
                                    @csrf
                                    <button class="btn-add">Agregar</button>
                                </form>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    const main = document.getElementById('detailMain');
    const mainImg = document.getElementById('detailMainImg');
    const thumbs = document.getElementById('detailThumbs');

    if (!main || !mainImg) return;

    main.addEventListener('mousemove', function (e) {
        const rect = main.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width * 100;
        const y = (e.clientY - rect.top) / rect.height * 100;
        main.style.setProperty('--zx', x + '%');
        main.style.setProperty('--zy', y + '%');
        mainImg.style.transformOrigin = x + '% ' + y + '%';
        main.classList.add('is-zoomed');
    });

    main.addEventListener('mouseleave', function () {
        main.classList.remove('is-zoomed');
    });

    if (thumbs) {
        thumbs.addEventListener('click', function (e) {
            const thumb = e.target.closest('.detail-thumb');
            if (!thumb) return;
            const src = thumb.dataset.src;
            if (!src) return;

            mainImg.style.opacity = '0.15';
            setTimeout(function () {
                mainImg.src = src;
                mainImg.style.opacity = '1';
            }, 140);

            thumbs.querySelectorAll('.detail-thumb').forEach(t => t.classList.remove('is-active'));
            thumb.classList.add('is-active');
        });
    }
})();
</script>
@endpush
