@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')
@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

@section('adminlte_css')
@stack('css')
@yield('css')
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
<div class="wrapper">

    {{-- Preloader Animation (fullscreen mode) --}}
    @if($preloaderHelper->isPreloaderEnabled())
    @include('adminlte::partials.common.preloader')
    @endif

    {{-- Top Navbar --}}
    @if($layoutHelper->isLayoutTopnavEnabled())
    @include('adminlte::partials.navbar.navbar-layout-topnav')
    @else
    @include('adminlte::partials.navbar.navbar')
    @endif

    {{-- Left Main Sidebar --}}
    @if(!$layoutHelper->isLayoutTopnavEnabled())
    @include('adminlte::partials.sidebar.left-sidebar')
    @endif

    {{-- Content Wrapper --}}
    @empty($iFrameEnabled)
    @include('adminlte::partials.cwrapper.cwrapper-default')
    @else
    @include('adminlte::partials.cwrapper.cwrapper-iframe')
    @endempty

    {{-- Footer --}}
    @hasSection('footer')
    @include('adminlte::partials.footer.footer')
    @endif

    {{-- Right Control Sidebar --}}
    @if($layoutHelper->isRightSidebarEnabled())
    @include('adminlte::partials.sidebar.right-sidebar')
    @endif

</div>
@stop

@section('adminlte_js')

{{-- ============================================================ --}}
{{-- INICIO: Lógica Global de Notificaciones (Estilo Farmacia)    --}}
{{-- ============================================================ --}}

{{-- ESTILOS CSS PARA QUE SE VEA MODERNO --}}
<style>
    .colored-toast.swal2-icon-success {
        background-color: #20c997 !important;
        /* Verde Farmacia */
    }

    .colored-toast.swal2-icon-error {
        background-color: #dc3545 !important;
        /* Rojo Error */
    }

    .colored-toast.swal2-icon-warning {
        background-color: #f39c12 !important;
        /* Naranja Alerta */
    }

    .colored-toast .swal2-title {
        color: white !important;
        /* Texto Blanco */
    }

    .colored-toast .swal2-close {
        color: white !important;
    }

    .colored-toast .swal2-html-container {
        color: white !important;
    }
</style>

{{-- DIV OCULTO DE DATOS --}}
<div id="global-flash-data" style="display: none;"
    data-success="{{ session('success') }}"
    data-error="{{ session('error') }}"
    data-any-error="{{ $errors->any() ? 'true' : 'false' }}">
</div>

{{-- SCRIPT INTELIGENTE --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const flashData = document.getElementById('global-flash-data');
        if (!flashData) return;

        const msgSuccess = flashData.dataset.success;
        const msgError = flashData.dataset.error;
        const hayErrores = flashData.dataset.anyError === 'true';

        if (typeof Swal !== 'undefined') {

            // Configuración Base
            const Toast = Swal.mixin({
                toast: true,
                position: 'center', // <--- AQUÍ ESTABA EL DETALLE (Ahora dice center)
                iconColor: 'white',
                customClass: {
                    popup: 'colored-toast'
                },
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });

            // CASO 1: ÉXITO
            if (msgSuccess) {
                Toast.fire({
                    icon: 'success',
                    title: msgSuccess
                });
            }

            // CASO 2: ERROR
            if (msgError) {
                Toast.fire({
                    icon: 'error',
                    title: msgError
                });
            }

            // CASO 3: VALIDACIÓN
            if (hayErrores) {
                Toast.fire({
                    icon: 'warning',
                    title: 'Por favor, revise el formulario.'
                });
            }
        }
    });
</script>
{{-- FIN: Lógica Global --}}

@stack('js')
@yield('js')
@stop