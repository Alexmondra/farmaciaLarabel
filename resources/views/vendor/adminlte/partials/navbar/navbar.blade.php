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
        @if(Auth::user())
        @if(config('adminlte.usermenu_enabled'))
        @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
        @else
        @include('adminlte::partials.navbar.menu-item-logout-link')
        @endif
        @endif

        {{-- Right sidebar toggler link --}}
        @if($layoutHelper->isRightSidebarEnabled())
        @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>

</nav>