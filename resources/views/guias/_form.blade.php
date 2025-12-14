{{-- resources/views/guias/_form.blade.php --}}

{{-- 1. Estilos --}}
@include('guias.partials.css')

{{-- 2. Cabecera (Serie y Título) --}}
@include('guias.partials.header')

<div class="row">
    {{-- COLUMNA IZQUIERDA --}}
    <div class="col-lg-4 d-flex flex-column">
        @include('guias.partials.configuracion')
        @include('guias.partials.partida')
        @include('guias.partials.destino')
    </div>

    {{-- COLUMNA DERECHA --}}
    <div class="col-lg-8 d-flex flex-column">
        {{-- Aquí va la tabla y el buscador manual --}}
        @include('guias.partials.items')

        {{-- Aquí va chofer y vehículo --}}
        @include('guias.partials.transporte')
    </div>
</div>

@include('guias.partials.scripts')