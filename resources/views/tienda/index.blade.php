@extends('tienda.layout')

@section('title', 'Tienda Virtual')

@section('content')
@php
    $categoriaActiva = request('categoria');
    $sucursalActiva = request('sucursal');
@endphp



<section class="search-box mb-4">
    <form method="GET" action="{{ route('tienda.index') }}" class="row g-3 align-items-end">
        <div class="col-lg-6">
            <label class="filter-label mb-2">Medicamento o laboratorio</label>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-lg" placeholder="Buscar medicamento, laboratorio o codigo">
        </div>
        <div class="col-md-6 col-lg-2">
            <label class="filter-label mb-2">Sucursal</label>
            <select name="sucursal" class="form-select form-select-lg">
                <option value="">Todas</option>
                @foreach ($sucursales as $sucursal)
                    <option value="{{ $sucursal->id }}" @selected((string) $sucursalActiva === (string) $sucursal->id)>{{ $sucursal->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 col-lg-2">
            <label class="filter-label mb-2">Categoria</label>
            <select name="categoria" class="form-select form-select-lg">
                <option value="">Todas</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}" @selected((string) $categoriaActiva === (string) $categoria->id)>{{ $categoria->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 d-grid">
            <button class="btn btn-store btn-lg">Buscar</button>
        </div>
    </form>
    @if(request()->hasAny(['q', 'sucursal', 'categoria']))
        <div class="mt-3">
            <a href="{{ route('tienda.index') }}" class="small fw-bold text-decoration-none text-secondary">Limpiar filtros</a>
        </div>
    @endif
</section>

<section class="mb-4">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('tienda.index', request()->except(['categoria', 'page'])) }}" class="category-chip {{ blank($categoriaActiva) ? 'active' : '' }}">Todas</a>
        @foreach ($categorias->take(10) as $categoria)
            <a href="{{ route('tienda.index', array_merge(request()->except(['page']), ['categoria' => $categoria->id])) }}" class="category-chip {{ (string) $categoriaActiva === (string) $categoria->id ? 'active' : '' }}">{{ $categoria->nombre }}</a>
        @endforeach
    </div>
</section>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="h4 fw-bold mb-1">Catalogo disponible</h2>
        <div class="muted-copy small">{{ $productos->total() }} resultado(s) encontrados</div>
    </div>
    @if($sucursalActiva && $sucursales->firstWhere('id', (int) $sucursalActiva))
        <span class="branch-badge">Sucursal: {{ $sucursales->firstWhere('id', (int) $sucursalActiva)->nombre }}</span>
    @endif
</div>

<div class="row g-3" id="product-grid">
    @forelse ($productos as $producto)
        @include('tienda.partials.product-cards', ['productos' => collect([$producto])])
    @empty
        <div class="col-12">
            <div class="store-card bg-white p-5 text-center">
                <h3 class="h4 fw-bold">No encontramos productos con esos filtros</h3>
                <p class="muted-copy mb-3">Prueba cambiando la sucursal, categoria o el texto de busqueda.</p>
                <a href="{{ route('tienda.index') }}" class="btn btn-store">Ver todo el catalogo</a>
            </div>
        </div>
    @endforelse
</div>

<div class="infinite-loader" id="product-loader">Cargando mas productos...</div>
<div id="product-sentinel" data-next-page="{{ $productos->nextPageUrl() }}"></div>

<noscript>
    <div class="mt-4">
        {{ $productos->links() }}
    </div>
</noscript>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const grid = document.getElementById('product-grid');
        const sentinel = document.getElementById('product-sentinel');
        const loader = document.getElementById('product-loader');

        if (!grid || !sentinel || !sentinel.dataset.nextPage) {
            return;
        }

        let loading = false;

        const loadMore = async () => {
            const nextPage = sentinel.dataset.nextPage;

            if (loading || !nextPage) {
                return;
            }

            loading = true;
            loader.classList.add('is-visible');

            try {
                const response = await fetch(nextPage, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('No se pudo cargar mas productos');
                }

                const data = await response.json();
                grid.insertAdjacentHTML('beforeend', data.html || '');
                sentinel.dataset.nextPage = data.next_page_url || '';

                if (!sentinel.dataset.nextPage) {
                    observer.disconnect();
                }
            } catch (error) {
                sentinel.dataset.nextPage = '';
                observer.disconnect();
            } finally {
                loading = false;
                loader.classList.remove('is-visible');
            }
        };

        const observer = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                loadMore();
            }
        }, { rootMargin: '500px 0px' });

        observer.observe(sentinel);
    });
</script>
@endpush
@endsection
