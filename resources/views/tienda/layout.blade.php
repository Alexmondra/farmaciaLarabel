@php
    $empresa = cache()->remember('datos_empresa_config', 1440, function () {
        return \App\Models\Configuracion::first();
    });
    $nombreTienda = $empresa->empresa_razon_social ?? 'Farmacia Online';
    $logoUrl = ($empresa && $empresa->ruta_logo) ? asset('storage/' . $empresa->ruta_logo) : null;
    $sucursalesFooter = cache()->remember('sucursales_activas_footer', 1440, function () {
        return \App\Models\Sucursal::where('activo', true)->orderBy('nombre')->get(['id', 'nombre', 'direccion', 'telefono']);
    });
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tienda Virtual') - {{ $nombreTienda }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --store-green: #10b981;
            --store-green-dark: #059669;
            --store-green-soft: #f0fdfa;
            --store-red: #0ea5e9;
            --store-ink: #0f172a;
            --store-muted: #64748b;
        }

        body { background: #f8fafc; color: var(--store-ink); font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; -webkit-font-smoothing: antialiased; display: flex; flex-direction: column; min-height: 100vh; margin: 0; }
        .top-strip { background: #0f172a; color: #94a3b8; font-size: .82rem; font-weight: 500; }
        .store-header {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
            position: sticky;
            top: 0;
            z-index: 50;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .brand-mark {
            align-items: center;
            color: var(--store-ink);
            display: inline-flex;
            font-size: 1.35rem;
            font-weight: 800;
            gap: .65rem;
            letter-spacing: -.03em;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .brand-mark:hover {
            color: var(--store-green);
            transform: scale(1.02);
            opacity: 1;
        }
        .brand-icon {
            align-items: center;
            background: linear-gradient(135deg, var(--store-green) 0%, var(--store-green-dark) 100%);
            border-radius: 0.85rem;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
            color: white;
            display: inline-flex;
            height: 38px;
            justify-content: center;
            width: 38px;
            font-size: 1.25rem;
            font-weight: bold;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .brand-mark:hover .brand-icon {
            transform: rotate(10deg) scale(1.05);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
        }
        .brand-logo-img {
            height: 38px;
            width: 38px;
            object-fit: contain;
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .brand-mark:hover .brand-logo-img {
            transform: scale(1.05);
        }
        .header-link {
            color: var(--store-muted);
            font-weight: 600;
            text-decoration: none;
            position: relative;
            transition: color 0.3s ease;
            font-size: 0.95rem;
        }
        .header-link::after {
            content: '';
            position: absolute;
            width: 100%;
            transform: scaleX(0);
            height: 2px;
            bottom: -4px;
            left: 0;
            background-color: var(--store-green);
            transform-origin: bottom right;
            transition: transform 0.25s ease-out;
        }
        .header-link:hover, .header-link.active {
            color: var(--store-green-dark);
        }
        .header-link:hover::after, .header-link.active::after {
            transform: scaleX(1);
            transform-origin: bottom left;
            background-color: var(--store-green-dark);
        }
        .cart-pill {
            background: linear-gradient(135deg, var(--store-green) 0%, var(--store-green-dark) 100%);
            border-radius: 999px;
            color: white !important;
            font-weight: 700;
            padding: .5rem 1.25rem;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.2);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
        }
        .cart-pill:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.35);
        }
        .cart-pill:active {
            transform: scale(0.96);
        }
        .store-shell { margin-top: 2rem; flex-grow: 1; padding-bottom: 80px; }
        .store-card {
            border: 0;
            border-radius: 1.25rem;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.03), 0 8px 10px -6px rgba(15, 23, 42, 0.03);
            border: 1px solid rgba(226, 232, 240, 0.7);
        }
        .price { color: var(--store-green); font-weight: 800; }
        .btn-store {
            background: linear-gradient(135deg, var(--store-red) 0%, #0284c7 100%);
            border: 0;
            color: white;
            font-weight: 700;
            border-radius: 0.85rem;
            padding: 0.6rem 1.5rem;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.2);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-store:hover {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(2, 132, 199, 0.35);
        }
        .btn-store:active {
            transform: scale(0.96);
        }
        .btn-store-outline {
            border: 2px solid var(--store-green);
            background: transparent;
            color: var(--store-green-dark);
            font-weight: 700;
            border-radius: 0.85rem;
            padding: 0.6rem 1.5rem;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.05);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-store-outline:hover {
            background: var(--store-green);
            color: white;
            border-color: var(--store-green);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(16, 185, 129, 0.2);
        }
        .btn-store-outline:active {
            transform: scale(0.96);
        }
        .search-box {
            background: white;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.02), 0 8px 10px -6px rgba(15, 23, 42, 0.02);
            padding: 1.75rem;
        }
        .search-box-compact {
            background: white;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 1.25rem;
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.03);
            padding: 0.75rem 1.25rem;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        [x-cloak] {
            display: none !important;
        }
        .filter-label {
            color: var(--store-muted);
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .category-chip {
            background: white;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 999px;
            color: var(--store-ink);
            display: inline-flex;
            font-weight: 600;
            font-size: 0.9rem;
            padding: .5rem 1.1rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .category-chip:hover, .category-chip.active {
            background: var(--store-green);
            border-color: var(--store-green);
            color: white;
            box-shadow: 0 8px 16px -4px rgba(16, 185, 129, 0.35);
            transform: translateY(-2px);
        }
        .category-chip:active {
            transform: scale(0.96);
        }
        .branch-badge {
            background: var(--store-green-soft);
            border-radius: 999px;
            color: var(--store-green-dark);
            font-weight: 700;
            padding: .35rem .8rem;
            font-size: 0.85rem;
            border: 1px solid rgba(13, 148, 136, 0.1);
        }
        .muted-copy { color: var(--store-muted); }
        .quick-banner {
            background: linear-gradient(135deg, #fff 0%, #f0fdfa 100%);
            border-radius: 1.25rem;
            padding: 1.25rem;
            border: 1px solid rgba(13, 148, 136, 0.08);
        }
        .quick-banner strong { color: var(--store-green-dark); }
        .product-card {
            background: white;
            border: 1px solid rgba(241, 245, 249, 0.9);
            border-radius: 1.25rem;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.02);
        }
        .product-card:hover {
            box-shadow: 0 20px 30px -10px rgba(15, 23, 42, 0.08);
            transform: translateY(-6px);
            border-color: rgba(16, 185, 129, 0.2);
        }
        .product-media {
            align-items: center;
            background: #f8fafc;
            display: flex;
            height: 180px;
            justify-content: center;
            overflow: hidden;
            position: relative;
            text-decoration: none;
            border-bottom: 1px solid rgba(241, 245, 249, 0.5);
        }
        .product-media img {
            height: 100%;
            object-fit: contain;
            padding: 1.25rem;
            width: 100%;
            transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .product-card:hover .product-media img {
            transform: scale(1.06);
        }
        .product-gallery { display: grid; gap: .75rem; grid-template-columns: repeat(auto-fill, minmax(86px, 1fr)); }
        .product-gallery-item { align-items: center; background: #f7faf8; border: 1px solid #e7eee9; border-radius: .85rem; display: flex; height: 86px; justify-content: center; overflow: hidden; }
        .product-gallery-item img { height: 100%; object-fit: contain; padding: .45rem; width: 100%; }
        .product-placeholder {
            align-items: center;
            background: var(--store-green-soft);
            border-radius: 1.25rem;
            color: var(--store-green);
            display: flex;
            font-size: 2.25rem;
            font-weight: 700;
            height: 80px;
            justify-content: center;
            width: 80px;
            box-shadow: inset 0 2px 4px rgba(13, 148, 136, 0.05);
        }
        .deal-tag {
            background: #f43f5e;
            border-radius: 999px;
            color: white;
            font-size: .7rem;
            font-weight: 700;
            left: .75rem;
            padding: .3rem .7rem;
            position: absolute;
            top: .75rem;
            box-shadow: 0 4px 8px rgba(244, 63, 94, 0.2);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .product-info {
            display: flex;
            flex: 1;
            flex-direction: column;
            padding: 1.25rem;
        }
        .product-meta {
            color: var(--store-green);
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }
        .product-lab {
            color: var(--store-muted);
            font-size: .8rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .product-title {
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.4;
            margin: .25rem 0 .5rem;
            min-height: 2.8rem;
            color: var(--store-ink);
        }
        .product-description {
            color: var(--store-muted);
            flex: 1;
            font-size: .85rem;
            margin-bottom: 0.75rem;
            line-height: 1.5;
        }
        .product-branch {
            background: rgba(13, 148, 136, 0.06);
            border: 1px solid rgba(13, 148, 136, 0.1);
            border-radius: .5rem;
            color: var(--store-green-dark);
            display: inline-flex;
            font-weight: 600;
            font-size: 0.75rem;
            margin-top: .35rem;
            padding: .2rem .5rem;
            width: fit-content;
        }
        .product-bottom {
            align-items: center;
            display: flex;
            gap: .75rem;
            justify-content: space-between;
            margin-top: 1rem;
            border-top: 1px solid rgba(241, 245, 249, 0.8);
            padding-top: 0.75rem;
        }
        .product-bottom .price {
            font-size: 1.15rem;
            color: var(--store-ink);
            font-weight: 800;
        }
        .btn-add {
            background: var(--store-green);
            border: 0;
            border-radius: .75rem;
            color: white;
            font-size: .85rem;
            font-weight: 700;
            padding: .5rem 1rem;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.15);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-add:hover {
            background: var(--store-green-dark);
            box-shadow: 0 8px 16px -4px rgba(16, 185, 129, 0.4);
            transform: translateY(-2px) scale(1.05);
        }
        .btn-add:active {
            transform: scale(0.95);
        }
        .infinite-loader { color: var(--store-muted); display: none; font-weight: 700; padding: 1rem; text-align: center; }
        .infinite-loader.is-visible { display: block; }

        @media (max-width: 767.98px) {
            .brand-mark { font-size: 1.1rem; }
            .brand-icon { height: 36px; width: 36px; }
            .top-strip { display: none; }
            .store-header { position: sticky; top: 0; z-index: 50; }
            .store-header .container {
                flex-direction: row !important;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 0.5rem;
            }
            .store-header nav {
                width: 100%;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 0;
            }
            .product-media { height: 140px; }
            .product-title { font-size: .9rem; min-height: 2.4rem; }
            .product-description, .product-lab { display: none; }
            .product-info { padding: 1rem; }
            .product-bottom { gap: .55rem; }
        }

        .store-footer {
            background: rgba(15, 23, 42, 0.98);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #cbd5e1;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.85rem;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 45;
            transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
        }

        .store-footer a {
            color: #cbd5e1 !important;
            text-decoration: none;
            transition: color 0.2s ease-in-out;
        }
        .store-footer a:hover {
            color: #5eead4 !important;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="d-flex flex-column min-vh-100">
    <div class="top-strip py-2">
        <div class="container d-flex justify-content-between gap-3">
            <span>🚀 Compra online y recoge en tu sucursal preferida</span>
            <span>📞 Atención rápida por tienda virtual</span>
        </div>
    </div>

    <header class="store-header py-3">
        <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3">
            <a href="{{ route('tienda.index') }}" class="brand-mark">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $nombreTienda }}" class="brand-logo-img">
                @else
                    <span class="brand-icon">+</span>
                @endif
                <span>{{ $nombreTienda }}</span>
            </a>
            <nav class="d-flex align-items-center gap-3">
                <a href="{{ route('tienda.index') }}" class="header-link {{ request()->routeIs('tienda.index') || request()->routeIs('tienda.productos.show') ? 'active' : '' }}">Catalogo</a>
                <a href="{{ route('tienda.sucursales') }}" class="header-link {{ request()->routeIs('tienda.sucursales') ? 'active' : '' }}">Sucursales</a>
               <a href="#" class="header-link open-chat-widget d-none d-md-inline">Chat Asistente</a>
                @auth('tienda')
                    <div class="dropdown">
                        <a href="#" class="header-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Hola, {{ Str::words(auth('tienda')->user()->nombre_completo, 1, '') }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-xl mt-2">
                            <li><a href="{{ route('tienda.perfil') }}" class="dropdown-item py-2 px-3">Mi Perfil</a></li>
                            <li><a href="{{ route('tienda.mis-pedidos') }}" class="dropdown-item py-2 px-3">Mis Pedidos</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form method="POST" action="{{ route('tienda.logout') }}">
                                    @csrf
                                    <button class="dropdown-item py-2 px-3 text-danger">Cerrar sesión</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('tienda.login') }}" class="header-link">Iniciar sesión</a>
                @endauth
                <a href="{{ route('tienda.carrito.index') }}" class="cart-pill d-inline-flex align-items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.1rem; height: 1.1rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span>Carrito</span>
                    @if(count(session('tienda_carrito', [])) > 0)
                        <span class="badge bg-danger text-white rounded-full px-2 py-0.5" style="font-size: 0.72rem; min-width: 1.25rem; line-height: 1.25;">
                            {{ array_sum(session('tienda_carrito', [])) }}
                        </span>
                    @endif
                </a>
            </nav>
        </div>
    </header>

    <main class="container store-shell mb-5">
        @include('tienda.partials.alerts')
        @yield('content')
    </main>

    @if(session('confirmar_multi_sucursal'))
    <div class="modal fade" id="modalMultiSucursal" tabindex="-1" aria-hidden="true" data-producto-id="{{ session('confirmar_multi_sucursal') }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
                <div class="modal-header border-0 pb-2">
                    <h5 class="modal-title fw-bold" style="color: var(--store-ink);">Producto de otra sucursal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
                    <p>Este producto pertenece a <strong>{{ session('confirmar_multi_sucursal_nombre') }}</strong>, que es diferente a la sucursal de los productos que ya tienes en tu carrito.</p>
                    <p class="mb-0 text-muted small">Si continuas con productos de distintas sucursales, el tiempo de espera sera de al menos <strong>una semana</strong> mientras trasladamos todo a un solo punto de recojo.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-store" id="btnConfirmarMulti">Agregar de todas formas</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @stack('scripts')
    @if(session('confirmar_multi_sucursal'))
    <script>
        (function () {
            const modalEl = document.getElementById('modalMultiSucursal');
            if (!modalEl) return;
            const productoId = modalEl.dataset.productoId;

            function showModal() {
                modalEl.classList.add('show');
                modalEl.style.display = 'block';
                modalEl.setAttribute('aria-hidden', 'false');
                modalEl.setAttribute('aria-modal', 'true');
                document.body.classList.add('modal-open');
                if (!document.querySelector('.modal-backdrop')) {
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    document.body.appendChild(backdrop);
                }
            }

            function hideModal() {
                modalEl.classList.remove('show');
                modalEl.style.display = 'none';
                modalEl.setAttribute('aria-hidden', 'true');
                modalEl.removeAttribute('aria-modal');
                document.body.classList.remove('modal-open');
                document.querySelectorAll('.modal-backdrop').forEach(function (el) { el.remove(); });
            }

            showModal();

            document.getElementById('btnConfirmarMulti').addEventListener('click', function () {
                var forms = document.querySelectorAll('.form-agregar-carrito');
                for (var i = 0; i < forms.length; i++) {
                    if (forms[i].action.indexOf('/' + productoId) !== -1) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'confirmar_multi';
                        input.value = '1';
                        forms[i].appendChild(input);
                        forms[i].submit();
                        return;
                    }
                }
                hideModal();
            });

            modalEl.querySelector('.btn-close').addEventListener('click', hideModal);
            var dismissButtons = modalEl.querySelectorAll('[data-bs-dismiss="modal"]');
            for (var j = 0; j < dismissButtons.length; j++) {
                dismissButtons[j].addEventListener('click', hideModal);
            }
        })();
    </script>
    @endif

    <footer class="store-footer py-3">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
            <span class="small" style="font-size: 0.8rem;">Diseñado y desarrollado por <strong class="text-white">S1NT4X System</strong></span>
            <a href="{{ route('login') }}" class="small d-inline-flex align-items-center gap-1.5 font-semibold text-decoration-none" style="font-size: 0.8rem;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 0.95rem; height: 0.95rem; color: #5eead4;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <span>Acceso Personal</span>
            </a>
        </div>
    </footer>
    </div>

    <script>
        (function() {
            let lastScrollTop = 0;
            const footer = document.querySelector('.store-footer');
            
            window.addEventListener('scroll', function() {
                let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop <= 50) {
                    if (footer) {
                        footer.style.transform = 'translateY(0)';
                        footer.style.opacity = '1';
                        footer.style.pointerEvents = 'auto';
                    }
                    lastScrollTop = scrollTop;
                    return;
                }
                
                if (scrollTop > lastScrollTop) {
                    // Scrolling down - hide footer
                    if (footer) {
                        footer.style.transform = 'translateY(100%)';
                        footer.style.opacity = '0';
                        footer.style.pointerEvents = 'none';
                    }
                } else {
                    // Scrolling up - show footer
                    if (footer) {
                        footer.style.transform = 'translateY(0)';
                        footer.style.opacity = '1';
                        footer.style.pointerEvents = 'auto';
                    }
                }
                lastScrollTop = scrollTop;
            }, { passive: true });
        })();
    </script>

    @include('tienda.partials.chat-widget')
</body>
</html>
