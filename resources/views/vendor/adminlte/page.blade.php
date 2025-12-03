@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')
@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

{{-- ============================================================ --}}
{{-- 1. ESTILOS (CSS) - Ubicados en el Head para evitar parpadeos --}}
{{-- ============================================================ --}}
@section('adminlte_css')
@stack('css')
@yield('css')

<style>
    /* --- A. NOTIFICACIONES (SweetAlert2 Personalizado) --- */
    .colored-toast.swal2-icon-success {
        background-color: #20c997 !important;
    }

    /* Verde Farmacia */
    .colored-toast.swal2-icon-error {
        background-color: #dc3545 !important;
    }

    /* Rojo Error */
    .colored-toast.swal2-icon-warning {
        background-color: #f39c12 !important;
    }

    /* Naranja Alerta */
    .colored-toast .swal2-title,
    .colored-toast .swal2-close,
    .colored-toast .swal2-html-container {
        color: white !important;
    }

    /* --- B. ESTILO TECH MINIMALISTA DEL MENÚ --- */
    .main-sidebar {
        font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
        font-size: 0.9rem;
    }

    /* Cabeceras de sección */
    .nav-header {
        font-size: 0.65rem !important;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: #6c757d !important;
        margin-top: 15px;
        margin-bottom: 5px;
        font-weight: 700;
        opacity: 0.7;
    }

    /* Botones del menú (Compactos y Modernos) */
    .nav-sidebar .nav-item>.nav-link {
        padding: 8px 12px !important;
        margin: 2px 10px !important;
        border-radius: 6px !important;
        /* Borde Tech suave */
        font-weight: 500;
        transition: all 0.2s ease-in-out;
        color: #c2c7d0;
    }

    /* Texto en modo claro */
    .sidebar-light-primary .nav-sidebar .nav-item>.nav-link {
        color: #343a40;
    }

    /* Estado ACTIVO (Verde Farmacia) */
    .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active,
    .sidebar-light-primary .nav-sidebar>.nav-item>.nav-link.active,
    .nav-sidebar .nav-item.menu-open>.nav-link {
        background-color: #20c997 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(32, 201, 151, 0.3) !important;
    }

    /* Hover */
    .nav-sidebar .nav-item>.nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1) !important;
        color: #fff !important;
    }

    .sidebar-light-primary .nav-sidebar .nav-item>.nav-link:hover {
        background-color: rgba(0, 0, 0, 0.05) !important;
        color: #000 !important;
    }

    /* Submenús */
    .nav-treeview>.nav-item>.nav-link {
        font-size: 0.85rem;
        padding-left: 35px !important;
        padding-top: 6px !important;
        padding-bottom: 6px !important;
    }

    .nav-treeview>.nav-item>.nav-link.active {
        background-color: rgba(32, 201, 151, 0.15) !important;
        color: #20c997 !important;
        box-shadow: none !important;
        font-weight: 600;
    }

    /* Íconos y Logo */
    .nav-sidebar .nav-link>i {
        font-size: 0.95rem;
        margin-right: 0.4rem;
        vertical-align: middle;
    }

    .brand-link {
        border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
        font-size: 1.1rem !important;
    }
</style>
@stop

{{-- ============================================================ --}}
{{-- 2. CLASES Y DATA DEL BODY --}}
{{-- ============================================================ --}}
@section('classes_body', $layoutHelper->makeBodyClasses())
@section('body_data', $layoutHelper->makeBodyData())

{{-- ============================================================ --}}
{{-- 3. ESTRUCTURA HTML (BODY) --}}
{{-- ============================================================ --}}
@section('body')
<div class="wrapper">
    {{-- Preloader --}}
    @if($preloaderHelper->isPreloaderEnabled())
    @include('adminlte::partials.common.preloader')
    @endif

    {{-- Navbar --}}
    @if($layoutHelper->isLayoutTopnavEnabled())
    @include('adminlte::partials.navbar.navbar-layout-topnav')
    @else
    @include('adminlte::partials.navbar.navbar')
    @endif

    {{-- Sidebar Izquierdo --}}
    @if(!$layoutHelper->isLayoutTopnavEnabled())
    @include('adminlte::partials.sidebar.left-sidebar')
    @endif

    {{-- Contenido Principal --}}
    @empty($iFrameEnabled)
    @include('adminlte::partials.cwrapper.cwrapper-default')
    @else
    @include('adminlte::partials.cwrapper.cwrapper-iframe')
    @endempty

    {{-- Footer --}}
    @hasSection('footer')
    @include('adminlte::partials.footer.footer')
    @endif

    {{-- Sidebar Derecho --}}
    @if($layoutHelper->isRightSidebarEnabled())
    @include('adminlte::partials.sidebar.right-sidebar')
    @endif
</div>
@stop

{{-- ============================================================ --}}
{{-- 4. SCRIPTS (JS) --}}
{{-- ============================================================ --}}
@section('adminlte_js')

{{-- Contenedor oculto para pasar datos de PHP a JS --}}
<div id="global-flash-data" style="display: none;"
    data-success="{{ session('success') }}"
    data-error="{{ session('error') }}"
    data-any-error="{{ $errors->any() ? 'true' : 'false' }}">
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        // --- A. LÓGICA DE NOTIFICACIONES (SWEETALERT2) ---
        const flashData = document.getElementById('global-flash-data');

        if (flashData && typeof Swal !== 'undefined') {
            const msgSuccess = flashData.dataset.success;
            const msgError = flashData.dataset.error;
            const hayErrores = flashData.dataset.anyError === 'true';

            const Toast = Swal.mixin({
                toast: true,
                position: 'center',
                iconColor: 'white',
                customClass: {
                    popup: 'colored-toast'
                },
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });

            if (msgSuccess) Toast.fire({
                icon: 'success',
                title: msgSuccess
            });
            if (msgError) Toast.fire({
                icon: 'error',
                title: msgError
            });
            if (hayErrores) Toast.fire({
                icon: 'warning',
                title: 'Revise el formulario.'
            });
        }

        // --- B. SINCRONIZACIÓN AUTOMÁTICA MODO OSCURO/CLARO ---
        const body = document.querySelector('body');
        const sidebar = document.querySelector('.main-sidebar');

        const syncTheme = () => {
            if (body.classList.contains('dark-mode')) {
                sidebar.classList.remove('sidebar-light-primary');
                sidebar.classList.add('sidebar-dark-primary');
            } else {
                sidebar.classList.remove('sidebar-dark-primary');
                sidebar.classList.add('sidebar-light-primary');
            }
        };

        // 1. Ejecutar al inicio
        syncTheme();

        // 2. Observador para cambios en tiempo real (MutationObserver)
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class') syncTheme();
            });
        });

        observer.observe(body, {
            attributes: true
        });
    });
</script>

@stack('js')
@yield('js')
@stop