@extends('tienda.layout')

@section('title', 'Tienda Virtual')

@section('content')
@php
    $categoriaActiva = request('categoria');
    $sucursalActiva = request('sucursal');
@endphp



<section class="search-box mb-5">
    <form method="GET" action="{{ route('tienda.index') }}" class="row g-3 align-items-end">
        <div class="col-lg-5">
            <label class="filter-label mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 0.95rem; height: 0.95rem; display: inline;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <span>Medicamento o laboratorio</span>
            </label>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-lg border-slate-200 bg-slate-50/50 px-4 py-2.5 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all" placeholder="Buscar medicamento, laboratorio o código...">
        </div>
        <div class="col-md-6 col-lg-2.5">
            <label class="filter-label mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 0.95rem; height: 0.95rem; display: inline;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>Sucursal</span>
            </label>
            <select name="sucursal" class="form-select form-select-lg border-slate-200 bg-slate-50/50 px-4 py-2.5 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                <option value="">Todas</option>
                @foreach ($sucursales as $sucursal)
                    <option value="{{ $sucursal->id }}" @selected((string) $sucursalActiva === (string) $sucursal->id)>{{ $sucursal->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 col-lg-2.5">
            <label class="filter-label mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 0.95rem; height: 0.95rem; display: inline;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>Categoría</span>
            </label>
            <select name="categoria" class="form-select form-select-lg border-slate-200 bg-slate-50/50 px-4 py-2.5 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                <option value="">Todas</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}" @selected((string) $categoriaActiva === (string) $categoria->id)>{{ $categoria->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 d-grid">
            <button class="btn btn-store btn-lg w-full py-2.5 rounded-xl text-sm font-semibold tracking-wide shadow-md transition-all active:scale-95">Buscar</button>
        </div>
    </form>
    @if(request()->hasAny(['q', 'sucursal', 'categoria']))
        <div class="mt-3 flex justify-between items-center">
            <a href="{{ route('tienda.index') }}" class="small fw-bold text-decoration-none text-emerald-600 hover:text-emerald-700 transition-all flex items-center gap-1">
                <span>&times;</span> Limpiar filtros
            </a>
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
