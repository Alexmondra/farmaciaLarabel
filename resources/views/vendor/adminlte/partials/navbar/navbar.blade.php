@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')
    </ul>

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">


        {{-- 🔽 Selector de sucursal --}}
        @if(Auth::check())
        @php
        $user = Auth::user();
        $esAdmin = method_exists($user, 'hasRole') ? $user->hasRole('Administrador') : false;
        $sucursalActualId = session('sucursal_id');

        // Texto del placeholder cuando no hay sucursal seleccionada
        $placeholder = $sucursalActualId
        ? 'Todas mis sucursales'
        : 'Seleccionar sucursal';
        @endphp


        <li class="nav-item">
            <form method="GET" action="{{ route('cambiar.sucursal.desdeSelect') }}" class="form-inline">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-hospital"></i>
                        </span>
                    </div>

                    <select name="sucursal_id" class="form-control" onchange="this.form.submit()">
                        {{-- Opción para NO tener sucursal seleccionada (ver todas las permitidas) --}}
                        <option value="" {{ $sucursalActualId ? '' : 'selected' }}>
                            -- {{ $placeholder }} --
                        </option>

                        @if($esAdmin)
                        @foreach(\App\Models\Sucursal::orderBy('nombre')->get() as $s)
                        <option value="{{ $s->id }}"
                            {{ (int)$sucursalActualId === $s->id ? 'selected' : '' }}>
                            {{ $s->nombre }}
                        </option>
                        @endforeach
                        @else
                        @foreach($user->sucursales as $s)
                        <option value="{{ $s->id }}"
                            {{ (int)$sucursalActualId === $s->id ? 'selected' : '' }}>
                            {{ $s->nombre }}
                        </option>
                        @endforeach
                        @endif
                    </select>

                </div>
            </form>
        </li>
        @endif
        {{-- 🔼 Fin selector de sucursal --}}



        {{-- Custom right links --}}
        @yield('content_top_nav_right')

        {{-- Configured right links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- User menu link --}}
        {{-- User menu link (PERSONALIZADO) --}}
        {{-- User menu link (DISEÑO VERTICAL Y COMPACTO) --}}
        @if(Auth::check())
        @php
        $user = Auth::user();
        $imgUrl = $user->imagen_perfil
        ? route('seguridad.usuarios.imagen', $user->id) . '?t=' . time()
        : 'https://api.dicebear.com/9.x/lorelei/svg?seed=' . urlencode($user->name);
        @endphp

        <li class="nav-item dropdown user-menu">
            {{-- 1. BARRA SUPERIOR: Solo Foto --}}
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                <img src="{{ $imgUrl }}" class="user-image img-circle elevation-2" alt="User Image" style="width: 30px; height: 30px; object-fit: cover;">
            </a>

            {{-- 2. MENÚ DESPLEGABLE: Vertical y Elegante --}}
            <ul class="dropdown-menu dropdown-menu-right shadow border-0" style="min-width: 180px; border-radius: 10px;">

                {{-- Pequeño saludo (Opcional, para que sepa qué cuenta es) --}}
                <li class="px-3 py-2 text-center text-muted border-bottom" style="font-size: 0.85rem; background-color: #f8f9fa;">
                    Hola, <strong>{{ Str::limit($user->name, 15) }}</strong>
                </li>

                {{-- Opción 1: Mi Perfil --}}
                <li>
                    <a href="{{ route('perfil.editar') }}" class="dropdown-item py-2 text-dark">
                        <i class="fas fa-user-cog text-primary mr-2" style="width: 20px; text-align: center;"></i>
                        Mi Perfil
                    </a>
                </li>

                {{-- Opción 2: Salir (En Rojo suave) --}}
                <li>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="dropdown-item py-2 text-danger">
                        <i class="fas fa-sign-out-alt mr-2" style="width: 20px; text-align: center;"></i>
                        Cerrar Sesión
                    </a>
                </li>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </ul>
        </li>
        @endif

        {{-- Right sidebar toggler link --}}
        @if($layoutHelper->isRightSidebarEnabled())
        @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>

</nav>