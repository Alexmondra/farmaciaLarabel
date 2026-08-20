@extends('tienda.layout')

@section('title', 'Tienda Virtual')

@push('styles')
<style>
    .suggestions-dropdown {
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        background-color: #ffffff;
    }
    .suggestions-dropdown a {
        transition: background-color 0.15s ease-in-out;
    }
    .suggestions-dropdown a:hover {
        background-color: #f8fafc;
    }
    .form-range::-webkit-slider-thumb {
        background: var(--store-green, #10b981) !important;
    }
    .form-range::-moz-range-thumb {
        background: var(--store-green, #10b981) !important;
    }
</style>
@endpush

@section('content')
@php
    $categoriaActiva = request('categoria');
    $sucursalActiva = request('sucursal');
@endphp



<!-- Menú Lateral Colapsable de Filtros Avanzados (Sidebar) -->
<div class="offcanvas offcanvas-start border-0 shadow-lg" tabindex="-1" id="filterSidebar" aria-labelledby="filterSidebarLabel" style="width: 320px; background: #fafbfc; border-top-right-radius: 1rem; border-bottom-right-radius: 1rem;">
    <div class="offcanvas-header border-b border-slate-100 bg-white py-3">
        <h5 class="offcanvas-title fw-bold text-slate-800 d-flex align-items-center gap-2" id="filterSidebarLabel">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.25rem; height: 1.25rem;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c-1.2 0-2.4.4-3.4 1.2A6 6 0 005.8 8.6c.1 1.7.9 3.2 2.2 4.3v4.6a2 2 0 002 2h4a2 2 0 002-2v-4.6c1.3-1.1 2.1-2.6 2.2-4.3a6 6 0 00-2.8-4.4c-1-.8-2.2-1.2-3.4-1.2z"></path>
            </svg>
            <span class="text-sm tracking-wide">Filtros Avanzados</span>
        </h5>
        <button type="button" class="btn-close text-slate-600" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form method="GET" action="{{ route('tienda.index') }}" id="sidebarFilterForm">
            <!-- Preservar el query de busqueda actual si existe -->
            @if(request()->filled('q'))
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif

            <!-- Filtro de Rango de Precios -->
            <div class="mb-4 pb-4 border-b border-slate-100/80" x-data="{ maxPrice: {{ request('precio_max', 150) }} }">
                <h6 class="fw-bold text-slate-700 text-xs tracking-wider uppercase mb-3">Rango de Precios</h6>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-xs text-slate-400 font-semibold">Hasta:</span>
                    <span class="text-xs font-extrabold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-xl" x-text="'S/ ' + parseFloat(maxPrice).toFixed(2)"></span>
                </div>
                <input type="range" name="precio_max" min="1" max="250" step="1" x-model="maxPrice" class="form-range" style="accent-color: var(--store-green);">
                <div class="d-flex justify-content-between text-slate-400" style="font-size: 0.65rem;">
                    <span>S/ 1.00</span>
                    <span>S/ 250.00+</span>
                </div>
            </div>

            <!-- Filtro de Sucursales -->
            <div class="mb-4 pb-4 border-b border-slate-100/80">
                <h6 class="fw-bold text-slate-700 text-xs tracking-wider uppercase mb-2.5">Sucursales</h6>
                <div class="d-flex flex-column gap-2 overflow-y-auto max-h-[160px] pe-1">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="sucursal" id="sucursal_all" value="" {{ !request('sucursal') ? 'checked' : '' }}>
                        <label class="form-check-label text-xs font-semibold text-slate-600" for="sucursal_all">Todas las Sucursales</label>
                    </div>
                    @foreach ($sucursales as $suc)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="sucursal" id="sucursal_{{ $suc->id }}" value="{{ $suc->id }}" {{ (string)request('sucursal') === (string)$suc->id ? 'checked' : '' }}>
                            <label class="form-check-label text-xs text-slate-500" for="sucursal_{{ $suc->id }}">{{ $suc->nombre }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Filtro de Categorias (Por si quieren cambiarla en el sidebar) -->
            <div class="mb-4">
                <h6 class="fw-bold text-slate-700 text-xs tracking-wider uppercase mb-2.5">Categorías</h6>
                <div class="d-flex flex-column gap-2 overflow-y-auto max-h-[200px] pe-1">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="categoria" id="categoria_all" value="" {{ !request('categoria') ? 'checked' : '' }}>
                        <label class="form-check-label text-xs font-semibold text-slate-600" for="categoria_all">Todas las Categorías</label>
                    </div>
                    @foreach ($categorias as $cat)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="categoria" id="categoria_{{ $cat->id }}" value="{{ $cat->id }}" {{ (string)request('categoria') === (string)$cat->id ? 'checked' : '' }}>
                            <label class="form-check-label text-xs text-slate-500" for="categoria_{{ $cat->id }}">{{ $cat->nombre }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="d-grid gap-2 mt-4 pt-2">
                <button type="submit" class="btn btn-store py-2 rounded-xl font-bold text-xs tracking-wide shadow-sm flex items-center justify-center gap-1.5 active:scale-95 transition-all">
                    <span>Aplicar Filtros</span>
                </button>
                @if(request()->hasAny(['q', 'sucursal', 'categoria', 'precio_max']))
                    <a href="{{ route('tienda.index') }}" class="btn btn-outline-secondary py-2 border-slate-200 text-slate-600 rounded-xl font-bold text-xs active:scale-95 transition-all">
                        Limpiar Todos
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<section x-data="searchAutocomplete()" class="search-box-compact mb-4 relative" @click.away="closeSuggestions()">
    <form method="GET" action="{{ route('tienda.index') }}" class="m-0" @submit="submitForm($event)">
        <!-- Preservar filtros actuales si existen -->
        @if(request()->filled('sucursal'))
            <input type="hidden" name="sucursal" value="{{ request('sucursal') }}">
        @endif
        @if(request()->filled('categoria'))
            <input type="hidden" name="categoria" value="{{ request('categoria') }}">
        @endif
        @if(request()->filled('precio_max'))
            <input type="hidden" name="precio_max" value="{{ request('precio_max') }}">
        @endif

        <div class="d-flex align-items-center gap-2">
            <!-- Contenedor Buscador con autocompletado -->
            <div class="position-relative flex-grow-1">
                <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-slate-400">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.1rem; height: 1.1rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input type="search" 
                       name="q" 
                       x-model="query"
                       @input="fetchSuggestions()"
                       @keydown.down="focusNext()"
                       @keydown.up="focusPrev()"
                       @keydown.escape="closeSuggestions()"
                       autocomplete="off"
                       class="form-control border-slate-200 bg-slate-50/50 ps-5 pe-3 py-2.5 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all" 
                       placeholder="Buscar medicamento o laboratorio...">

                <!-- Indicador de Carga (Spinner) -->
                <div x-show="loading" x-cloak class="position-absolute top-50 end-0 translate-middle-y pe-3" style="display: none;">
                    <div class="spinner-border text-emerald-600" role="status" style="width: 1rem; height: 1rem; border-width: 0.15em;"></div>
                </div>

                <!-- Lista Desplegable de Recomendaciones -->
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     x-cloak 
                     class="suggestions-dropdown position-absolute w-full mt-2 bg-white border border-slate-100 rounded-2xl shadow-xl z-50 overflow-hidden" 
                     style="left: 0; right: 0; min-width: 280px; box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.12); display: none;">
                    
                    <div class="d-flex flex-column divide-y divide-slate-100">
                        <template x-for="(item, index) in suggestions" :key="item.id">
                            <a :href="item.url" 
                               :class="{'bg-slate-50': index === activeIndex}"
                               @mouseenter="activeIndex = index"
                               class="d-flex align-items-center gap-3 p-3 text-decoration-none text-reset hover:bg-slate-50 transition-colors">
                                
                                <div class="flex-shrink-0 bg-slate-50 border border-slate-100 rounded-xl overflow-hidden d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <img :src="item.imagen_url" alt="" class="h-100 w-100 object-contain p-1">
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <h4 class="text-xs font-bold text-slate-800 mb-0.5 text-truncate" x-text="item.nombre"></h4>
                                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                        <span class="text-[10px] text-slate-400" x-show="item.laboratorio" x-text="'Lab: ' + item.laboratorio"></span>
                                        <span class="text-[10px] text-slate-400" x-show="item.laboratorio">•</span>
                                        <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50/80 px-1.5 py-0.5 rounded" x-text="item.sucursal"></span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 text-end">
                                    <span class="text-xs text-slate-400 font-semibold">S/</span>
                                    <span class="text-sm font-extrabold text-emerald-600" x-text="item.precio"></span>
                                </div>
                            </a>
                        </template>

                        <div x-show="suggestions.length === 0" class="p-4 text-center text-slate-400 text-xs font-medium" style="display: none;">
                            No se encontraron recomendaciones para tu búsqueda.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón Filtros Avanzados -->
            <button type="button" 
                    data-bs-toggle="offcanvas" 
                    data-bs-target="#filterSidebar" 
                    class="btn btn-outline-secondary border-slate-200 text-slate-600 d-inline-flex align-items-center gap-1.5 px-3 py-2.5 rounded-xl text-sm font-semibold active:scale-95 transition-all">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.05rem; height: 1.05rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
                <span class="d-none d-sm-inline">Filtros</span>
                @if(request('sucursal') || request('categoria') || request('precio_max'))
                    <span class="position-relative d-flex h-2 w-2">
                        <span class="animate-ping position-absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                @endif
            </button>
        </div>
    </form>
    
    @if(request()->hasAny(['q', 'sucursal', 'categoria', 'precio_max']))
        <div class="mt-2.5 pt-2 d-flex justify-content-between align-items-center">
            <a href="{{ route('tienda.index') }}" class="small fw-bold text-decoration-none text-emerald-600 hover:text-emerald-700 transition-all flex items-center gap-1">
                <span>&times;</span> Limpiar todos los filtros activos
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
    @if ($productos->count() > 0)
        @include('tienda.partials.product-cards', ['productos' => $productos])
    @else
        <div class="col-12">
            <div class="store-card bg-white p-5 text-center">
                <h3 class="h4 fw-bold">No encontramos productos con esos filtros</h3>
                <p class="muted-copy mb-3">Prueba cambiando la sucursal, categoria o el texto de busqueda.</p>
                <a href="{{ route('tienda.index') }}" class="btn btn-store">Ver todo el catalogo</a>
            </div>
        </div>
    @endif
</div>

<!-- Grid de Skeleton Loaders para Carga Perezosa (Lazy Loading) -->
<div class="row g-3 d-none mt-2" id="product-loader">
    @for ($i = 0; $i < 4; $i++)
        <div class="col-6 col-lg-3 product-item mb-4">
            <div class="product-card h-100 d-flex flex-column bg-white border border-slate-100/80 rounded-2xl p-3 animate-pulse" style="min-height: 360px;">
                <div class="bg-slate-100 rounded-xl w-full mb-3" style="aspect-ratio: 1; min-height: 140px;"></div>
                <div class="flex-grow-1 d-flex flex-column justify-between">
                    <div>
                        <div class="bg-slate-100 rounded w-1/3 mb-2" style="height: 12px;"></div>
                        <div class="bg-slate-100 rounded w-5/6 mb-2" style="height: 16px;"></div>
                        <div class="bg-slate-100 rounded w-2/3 mb-2" style="height: 16px;"></div>
                        <div class="bg-slate-100 rounded w-1/2 mb-3" style="height: 12px;"></div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top border-slate-100 mt-3">
                        <div class="bg-slate-100 rounded w-1/3" style="height: 22px;"></div>
                        <div class="bg-slate-100 rounded-xl" style="height: 32px; width: 42px;"></div>
                    </div>
                </div>
            </div>
        </div>
    @endfor
</div>

<div id="product-sentinel" data-next-page="{{ $productos->nextPageUrl() }}"></div>

<noscript>
    <div class="mt-4">
        {{ $productos->links() }}
    </div>
</noscript>

@push('scripts')
<script>
    function searchAutocomplete() {
        return {
            query: '{{ request('q') }}',
            suggestions: [],
            open: false,
            loading: false,
            activeIndex: -1,
            async fetchSuggestions() {
                if (this.query.trim().length < 2) {
                    this.suggestions = [];
                    this.open = false;
                    return;
                }
                this.loading = true;
                try {
                    const response = await fetch(`{{ route('tienda.productos.sugerencias') }}?q=${encodeURIComponent(this.query)}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (response.ok) {
                        this.suggestions = await response.json();
                        this.open = this.suggestions.length > 0;
                    } else {
                        this.suggestions = [];
                        this.open = false;
                    }
                } catch (error) {
                    console.error(error);
                    this.suggestions = [];
                    this.open = false;
                } finally {
                    this.loading = false;
                    this.activeIndex = -1;
                }
            },
            closeSuggestions() {
                setTimeout(() => {
                    this.open = false;
                }, 200);
            },
            focusNext() {
                if (this.suggestions.length === 0) return;
                this.activeIndex = (this.activeIndex + 1) % this.suggestions.length;
            },
            focusPrev() {
                if (this.suggestions.length === 0) return;
                this.activeIndex = (this.activeIndex - 1 + this.suggestions.length) % this.suggestions.length;
            },
            submitForm(e) {
                if (this.open && this.activeIndex >= 0 && this.suggestions[this.activeIndex]) {
                    e.preventDefault();
                    window.location.href = this.suggestions[this.activeIndex].url;
                }
            }
        };
    }

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
            loader.classList.remove('d-none');

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
                loader.classList.add('d-none');
            }
        };

        const observer = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                loadMore();
            }
        }, { rootMargin: '150px 0px' });

        observer.observe(sentinel);
    });
</script>
@endpush
@endsection
