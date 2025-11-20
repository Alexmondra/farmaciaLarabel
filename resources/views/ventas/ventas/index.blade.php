@extends('adminlte::page')

@section('title', 'Ventas de la Sesión')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Ventas de la Sesión</h1>
        
        @if($cajaAbierta)
            {{-- Si la caja está abierta, mostramos botón "Nueva Venta" --}}
            <a href="{{ route('ventas.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Venta
            </a>
        @else
            {{-- Si no hay caja, mostramos botón "Abrir Caja" --}}
            <button class="btn btn-success" data-toggle="modal" data-target="#modalAbrirCaja">
                <i class="fas fa-play"></i> Abrir Caja
            </button>
        @endif
    </div>
@stop

@section('content')

    {{-- Alertas de éxito o error (para el modal) --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Error:</strong> Por favor, revise los campos del formulario.
        </div>
    @endif


    {{-- ESTA ES LA LÓGICA PRINCIPAL DE LA VISTA --}}
    @if($cajaAbierta)
        
        {{-- ESTADO 1: CAJA ABIERTA --}}
        
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> 
            Estás operando en la <strong>Caja #{{ $cajaAbierta->id }}</strong> 
            (Sucursal: <strong>{{ $cajaAbierta->sucursal->nombre ?? 'N/A' }}</strong>).
            Abierta con S/ {{ number_format($cajaAbierta->saldo_inicial, 2) }}.
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ventas Registradas en esta Sesión</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Comprobante</th>
                            <th>Número</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Vendedor</th>
                            <th>Medio Pago</th>
                            <th style="text-align: right;">Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ventas as $venta)
                            <tr>
                                <td>{{ $venta->tipo_comprobante }}</td>
                                <td>{{ $venta->serie }}-{{ $venta->numero }}</td>
                                <td>{{ $venta->fecha_emision->format('d/m/Y H:i') }}</td>
                                <td>{{ $venta->cliente->nombre ?? 'Varios' }} {{ $venta->cliente->apellidos ?? '' }}</td>
                                <td>{{ $venta->usuario->name ?? 'N/A' }}</td>
                                <td>{{ $venta->medio_pago }}</td>
                                <td style="text-align: right;">S/ {{ number_format($venta->total_neto, 2) }}</td>
                                <td>
                                    <a href="{{ route('ventas.show', $venta->id) }}" class="btn btn-sm btn-info" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    Aún no se han registrado ventas en esta sesión de caja.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($ventas->hasPages())
                <div class="card-footer">
                    {{ $ventas->links() }}
                </div>
            @endif
        </div>

    @else

        {{-- ESTADO 2: CAJA CERRADA O NO EXISTE --}}
        
        <div class="callout callout-warning">
            <h5><i class="fas fa-exclamation-triangle"></i> No hay una caja abierta</h5>
            <p>
                Para registrar o ver las ventas del día, primero necesitas 
                abrir una sesión de caja en la sucursal seleccionada.
            </p>
            <button class="btn btn-success" data-toggle="modal" data-target="#modalAbrirCaja">
                <i class="fas fa-play"></i> Abrir Caja Ahora
            </button>
        </div>

    @endif


    {{-- 
      INCLUIMOS EL MODAL DE APERTURA
      (Siempre debe estar, por si el usuario necesita abrirla)
    --}}
    @include('ventas.cajas._modal_apertura', [
        'sucursalesParaApertura' => $sucursalesParaApertura
    ])

    {{-- El 'div' para la lógica de JS (a prueba de formateadores) --}}
    <div id="js-page-data" data-abrir-modal="{{ $errors->any() ? 'true' : 'false' }}"></div>

@stop

@push('js')
<script>
    $(document).ready(function() {
        
        // Lógica para reabrir el modal de APERTURA si falla la validación
        var abrir = $('#js-page-data').data('abrir-modal');
        if (abrir === 'true' || abrir === true) {
            $('#modalAbrirCaja').modal('show');
        }

    });
</script>
@endpush