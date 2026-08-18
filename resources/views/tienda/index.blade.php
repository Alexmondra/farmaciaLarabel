@extends('tienda.layout')

@section('title', 'Tienda Virtual')

@section('content')
@php
    $categoriaActiva = request('categoria');
    $sucursalActiva = request('sucursal');
@endphp



<section x-data="{ openFilters: false, sucursal: '{{ $sucursalActiva }}', categoria: '{{ $categoriaActiva }}' }" class="search-box-compact mb-4">
    <form method="GET" action="{{ route('tienda.index') }}" class="m-0">
        <div class="row g-2 align-items-center">
            <!-- Buscador Principal -->
            <div class="col-12 col-md-4 col-lg-5">
                <div class="position-relative">
                    <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-slate-400">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.1rem; height: 1.1rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control border-slate-200 bg-slate-50/50 ps-5 pe-3 py-2 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all" placeholder="Buscar medicamento o laboratorio...">
                </div>
            </div>

            <!-- Filtros en Escritorio / Botón en Móvil -->
            <div class="col-12 col-md-8 col-lg-7">
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-md-end">
                    <!-- Botón para alternar filtros en móvil -->
                    <button type="button" @click="openFilters = !openFilters" class="btn btn-outline-secondary border-slate-200 text-slate-600 btn-sm rounded-xl px-3 py-2 d-md-none text-sm d-inline-flex align-items-center gap-1.5 transition-all">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 1rem; height: 1rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                        <span>Filtros</span>
                        @if(request('sucursal') || request('categoria'))
                            <span class="position-relative d-flex h-2 w-2">
                                <span class="animate-ping position-absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                        @endif
                    </button>

                    <!-- Filtros selectores (visibles en MD o pantallas más grandes) -->
                    <div class="d-none d-md-flex align-items-center gap-2">
                        <!-- Sucursal Select -->
                        <div style="min-width: 160px;">
                            <select name="sucursal" x-model="sucursal" @change="$el.form.submit()" class="form-select border-slate-200 bg-slate-50/50 px-3 py-2 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                                <option value="">Todas las Sucursales</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Categoria Select -->
                        <div style="min-width: 160px;">
                            <select name="categoria" x-model="categoria" @change="$el.form.submit()" class="form-select border-slate-200 bg-slate-50/50 px-3 py-2 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                                <option value="">Todas las Categorías</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Botón de Búsqueda -->
                        <button class="btn btn-store py-2 px-4 rounded-xl text-xs font-semibold tracking-wide shadow-sm transition-all active:scale-95 animate-none">Buscar</button>
                    </div>

                    <!-- Botón buscar principal para móviles (siempre visible) -->
                    <button class="btn btn-store py-2 px-4 rounded-xl text-xs font-semibold shadow-sm d-md-none flex-grow-1 active:scale-95 animate-none">Buscar</button>
                </div>
            </div>
        </div>

        <!-- Contenedor Colapsable de Filtros para Móviles -->
        <div x-show="openFilters" x-collapse x-cloak class="mt-3 border-t border-slate-100 pt-3 d-md-none">
            <div class="row g-2">
                <div class="col-6">
                    <label class="filter-label mb-1.5 block">Sucursal</label>
                    <select x-model="sucursal" @change="$el.form.submit()" class="form-select border-slate-200 bg-slate-50/50 px-3 py-2 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                        <option value="">Todas</option>
                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="filter-label mb-1.5 block">Categoría</label>
                    <select x-model="categoria" @change="$el.form.submit()" class="form-select border-slate-200 bg-slate-50/50 px-3 py-2 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                        <option value="">Todas</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </form>

    @if(request()->hasAny(['q', 'sucursal', 'categoria']))
        <div class="mt-2 pt-2 border-t border-slate-50 d-flex justify-content-between align-items-center">
            <a href="{{ route('tienda.index') }}" class="small fw-bold text-decoration-none text-emerald-600 hover:text-emerald-700 transition-all flex items-center gap-1">
                <span>&times;</span> Limpiar filtros
            </a>
        </div>
    @endif
</section>

<section class="mb-4 overflow-x-auto scrollbar-hide py-1">
    <div class="d-flex flex-nowrap align-items-center gap-2" style="scrollbar-width: none; -ms-overflow-style: none;">
        <a href="{{ route('tienda.index', request()->except(['categoria', 'page'])) }}" class="category-chip flex-shrink-0 {{ blank($categoriaActiva) ? 'active' : '' }}">Todas</a>
        @foreach ($categorias as $categoria)
            <a href="{{ route('tienda.index', array_merge(request()->except(['page']), ['categoria' => $categoria->id])) }}" class="category-chip flex-shrink-0 {{ (string) $categoriaActiva === (string) $categoria->id ? 'active' : '' }}">{{ $categoria->nombre }}</a>
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
        // Lógica de Scroll-Up Reveal para Filtros (Cabecera siempre fija)
        let lastScrollTop = 0;
        const header = document.querySelector('.store-header');
        const searchBox = document.querySelector('.search-box-compact');
        
        if (searchBox && header) {
            searchBox.style.position = 'sticky';
            searchBox.style.top = `${header.offsetHeight || 74}px`;
            searchBox.style.zIndex = '40';
            searchBox.style.transition = 'transform 0.3s ease-in-out, opacity 0.2s ease-in-out, top 0.3s ease-in-out, box-shadow 0.3s ease-in-out';
            
            window.addEventListener('resize', () => {
                searchBox.style.top = `${header.offsetHeight || 74}px`;
            });
        }

        window.addEventListener('scroll', function() {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop <= 50) {
                if (searchBox) {
                    searchBox.style.transform = 'translateY(0)';
                    searchBox.style.opacity = '1';
                    searchBox.style.pointerEvents = 'auto';
                    searchBox.style.boxShadow = 'none';
                }
                lastScrollTop = scrollTop;
                return;
            }
            
            if (scrollTop > lastScrollTop) {
                // Scrolling down - hide only the filter bar (sliding under the header)
                if (searchBox) {
                    searchBox.style.transform = 'translateY(-120px)';
                    searchBox.style.opacity = '0';
                    searchBox.style.pointerEvents = 'none';
                }
            } else {
                // Scrolling up - show filter bar right below header
                if (searchBox) {
                    searchBox.style.transform = 'translateY(0)';
                    searchBox.style.opacity = '1';
                    searchBox.style.pointerEvents = 'auto';
                    searchBox.style.top = `${header.offsetHeight || 74}px`;
                    searchBox.style.boxShadow = '0 10px 25px -5px rgba(15, 23, 42, 0.08)';
                }
            }
            lastScrollTop = scrollTop;
        }, { passive: true });

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
