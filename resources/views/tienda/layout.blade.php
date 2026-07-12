<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Tienda Virtual')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --store-green: #008f5f;
            --store-green-dark: #006b48;
            --store-green-soft: #e8f8f1;
            --store-red: #e52f3f;
            --store-ink: #102033;
            --store-muted: #64748b;
        }

        body { background: #f5f7f6; color: var(--store-ink); font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .top-strip { background: var(--store-green-dark); color: #dff8ec; font-size: .875rem; }
        .store-header { background: var(--store-green); box-shadow: 0 10px 30px rgba(0, 107, 72, .14); color: white; position: sticky; top: 0; z-index: 20; }
        .brand-mark { align-items: center; color: white; display: inline-flex; font-size: 1.35rem; font-weight: 800; gap: .65rem; letter-spacing: -.03em; text-decoration: none; }
        .brand-icon { align-items: center; background: var(--store-red); border-radius: 1rem; display: inline-flex; height: 42px; justify-content: center; width: 42px; }
        .header-link { color: rgba(255, 255, 255, .88); font-weight: 600; text-decoration: none; }
        .header-link:hover { color: white; }
        .cart-pill { background: white; border-radius: 999px; color: var(--store-green-dark); font-weight: 800; padding: .6rem 1rem; text-decoration: none; }
        .store-shell { margin-top: 1.25rem; }
        .store-card { border: 0; border-radius: 1rem; box-shadow: 0 12px 28px rgba(15, 23, 42, .08); }
        .price { color: var(--store-red); font-weight: 800; }
        .btn-store { background: var(--store-red); border-color: var(--store-red); color: white; font-weight: 700; }
        .btn-store:hover { background: #c91f2f; border-color: #c91f2f; color: white; }
        .btn-store-outline { border-color: var(--store-green); color: var(--store-green-dark); font-weight: 700; }
        .btn-store-outline:hover { background: var(--store-green); color: white; }
        .search-box { background: white; border: 1px solid rgba(0, 143, 95, .12); border-radius: 1rem; box-shadow: 0 12px 28px rgba(15, 23, 42, .08); padding: 1rem; }
        .filter-label { color: var(--store-muted); font-size: .78rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .category-chip { background: white; border: 1px solid #d9e8df; border-radius: 999px; color: var(--store-green-dark); display: inline-flex; font-weight: 700; padding: .55rem .9rem; text-decoration: none; }
        .category-chip:hover, .category-chip.active { background: var(--store-green); border-color: var(--store-green); color: white; }
        .branch-badge { background: var(--store-green-soft); border-radius: 999px; color: var(--store-green-dark); font-weight: 800; padding: .4rem .7rem; }
        .muted-copy { color: var(--store-muted); }
        .quick-banner { background: linear-gradient(135deg, #fff 0%, #eafff4 100%); border-radius: 1rem; padding: 1rem; }
        .quick-banner strong { color: var(--store-green-dark); }
        .product-card { background: white; border: 1px solid #e7eee9; border-radius: 1rem; display: flex; flex-direction: column; overflow: hidden; transition: box-shadow .16s ease, transform .16s ease; }
        .product-card:hover { box-shadow: 0 18px 38px rgba(15, 23, 42, .11); transform: translateY(-2px); }
        .product-media { align-items: center; background: #f7faf8; display: flex; height: 170px; justify-content: center; overflow: hidden; position: relative; text-decoration: none; }
        .product-media img { height: 100%; object-fit: contain; padding: 1rem; width: 100%; }
        .product-gallery { display: grid; gap: .75rem; grid-template-columns: repeat(auto-fill, minmax(86px, 1fr)); }
        .product-gallery-item { align-items: center; background: #f7faf8; border: 1px solid #e7eee9; border-radius: .85rem; display: flex; height: 86px; justify-content: center; overflow: hidden; }
        .product-gallery-item img { height: 100%; object-fit: contain; padding: .45rem; width: 100%; }
        .product-placeholder { align-items: center; background: var(--store-green-soft); border-radius: 1rem; color: var(--store-green-dark); display: flex; font-size: 2rem; font-weight: 900; height: 80px; justify-content: center; width: 80px; }
        .deal-tag { background: var(--store-red); border-radius: 999px; color: white; font-size: .75rem; font-weight: 800; left: .75rem; padding: .28rem .6rem; position: absolute; top: .75rem; }
        .product-info { display: flex; flex: 1; flex-direction: column; padding: .95rem; }
        .product-meta, .product-lab, .product-branch { color: var(--store-muted); font-size: .78rem; }
        .product-title { font-size: .98rem; font-weight: 800; line-height: 1.25; margin: .25rem 0 .35rem; min-height: 2.45rem; }
        .product-description { color: var(--store-muted); flex: 1; font-size: .82rem; margin-bottom: .5rem; }
        .product-branch { background: var(--store-green-soft); border-radius: .6rem; color: var(--store-green-dark); display: inline-flex; font-weight: 700; margin-top: .35rem; padding: .25rem .45rem; width: fit-content; }
        .product-bottom { align-items: center; display: flex; gap: .75rem; justify-content: space-between; margin-top: .75rem; }
        .btn-add { background: var(--store-red); border: 0; border-radius: .65rem; color: white; font-size: .85rem; font-weight: 800; padding: .5rem .75rem; }
        .btn-add:hover { background: #c91f2f; }
        .infinite-loader { color: var(--store-muted); display: none; font-weight: 700; padding: 1rem; text-align: center; }
        .infinite-loader.is-visible { display: block; }

        @media (max-width: 767.98px) {
            .brand-mark { font-size: 1.1rem; }
            .brand-icon { height: 36px; width: 36px; }
            .top-strip { display: none; }
            .store-header { position: static; }
            .product-media { height: 132px; }
            .product-title { font-size: .88rem; min-height: 2.2rem; }
            .product-description, .product-lab { display: none; }
            .product-info { padding: .75rem; }
            .product-bottom { align-items: stretch; flex-direction: column; gap: .55rem; }
            .btn-add { width: 100%; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="top-strip py-2">
        <div class="container d-flex justify-content-between gap-3">
            <span>Compra online y recoge en tu sucursal preferida</span>
            <span>Atencion rapida por tienda virtual</span>
        </div>
    </div>

    <header class="store-header py-3">
        <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3">
            <a href="{{ route('tienda.index') }}" class="brand-mark">
                <span class="brand-icon">+</span>
                <span>Farmacia Online</span>
            </a>
            <nav class="d-flex align-items-center gap-3">
                <a href="{{ route('tienda.index') }}" class="header-link">Catalogo</a>
                @auth('tienda')
                    <div class="dropdown">
                        <a href="#" class="header-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Hola, {{ Str::words(auth('tienda')->user()->nombre_completo, 1, '') }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a href="{{ route('tienda.perfil') }}" class="dropdown-item">Mi Perfil</a></li>
                            <li><a href="{{ route('tienda.mis-pedidos') }}" class="dropdown-item">Mis Pedidos</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('tienda.logout') }}">
                                    @csrf
                                    <button class="dropdown-item">Cerrar sesion</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('tienda.login') }}" class="header-link">Iniciar sesion</a>
                    <a href="{{ route('tienda.register') }}" class="header-link">Registrarse</a>
                @endauth
                <a href="{{ route('tienda.carrito.index') }}" class="cart-pill">Carrito</a>
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
</body>
</html>
