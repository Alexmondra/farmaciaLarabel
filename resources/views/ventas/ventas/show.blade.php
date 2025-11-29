@extends('adminlte::page')

@section('title', 'Comprobante')

@section('content_header')
<div class="no-print">
    <h1>Venta Registrada</h1>
</div>
@stop

@section('content')
<div class="container-fluid">

    {{-- BOTONES DE ACCIÓN (Solo visibles en pantalla) --}}
    <div class="row mb-3 no-print">
        <div class="col-12">
            <button onclick="window.print()" class="btn btn-primary btn-lg shadow-sm">
                <i class="fas fa-print"></i> Imprimir Ticket
            </button>
            <a href="{{ route('ventas.create') }}" class="btn btn-success btn-lg shadow-sm ml-2">
                <i class="fas fa-cash-register"></i> Nueva Venta
            </a>
            <a href="{{ route('ventas.index') }}" class="btn btn-secondary btn-lg shadow-sm ml-2">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    {{-- =========================================================== --}}
    {{-- DISEÑO DEL TICKET (Centrado en pantalla, único al imprimir) --}}
    {{-- =========================================================== --}}
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="ticket" id="ticket-content">

                {{-- CABECERA --}}
                <div class="text-center mb-2">
                    <h4 class="font-weight-bold mb-0">{{ $venta->sucursal->nombre ?? 'FARMACIA' }}</h4>
                    <small>{{ $venta->sucursal->direccion ?? 'Dirección Principal' }}</small><br>
                    <small>RUC: 20123456789 | Telf: {{ $venta->sucursal->telefono ?? '---' }}</small>
                </div>

                <div class="text-center mb-3 border-bottom border-dark pb-2">
                    <span class="d-block font-weight-bold">{{ $venta->tipo_comprobante }} DE VENTA ELECTRÓNICA</span>
                    <span class="d-block">{{ $venta->serie }}-{{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}</span>
                    <span class="d-block text-muted" style="font-size: 0.8rem;">{{ $venta->fecha_emision->format('d/m/Y h:i A') }}</span>
                </div>

                {{-- DATOS CLIENTE --}}
                <div class="mb-3" style="font-size: 0.85rem;">
                    <div><strong>Cliente:</strong> {{ $venta->cliente->nombre_completo ?? 'PÚBLICO GENERAL' }}</div>
                    <div><strong>Doc:</strong> {{ $venta->cliente->numero_documento ?? '00000000' }}</div>
                    <div><strong>Cajero:</strong> {{ $venta->usuario->name }}</div>
                </div>

                {{-- TABLA PRODUCTOS --}}
                <table class="table table-sm table-borderless mb-2" style="font-size: 0.85rem;">
                    <thead class="border-bottom border-dark">
                        <tr>
                            <th class="pl-0">Cant.</th>
                            <th>Descripción</th>
                            <th class="text-right pr-0">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($venta->detalles as $item)
                        <tr>
                            <td class="pl-0 font-weight-bold" valign="top">{{ $item->cantidad }}</td>
                            <td valign="top">
                                {{ $item->medicamento->nombre }}
                                {{-- Si quieres mostrar presentación: <br><small>{{ $item->medicamento->presentacion }}</small> --}}
                            </td>
                            <td class="text-right pr-0" valign="top">{{ number_format($item->subtotal_neto, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- TOTALES --}}
                <div class="border-top border-dark pt-2 mb-3">
                    <div class="d-flex justify-content-between">
                        <span>OP. GRAVADA:</span>
                        <span>S/ {{ number_format($venta->total_neto / 1.18, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>I.G.V. (18%):</span>
                        <span>S/ {{ number_format($venta->total_neto - ($venta->total_neto / 1.18), 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between font-weight-bold mt-1" style="font-size: 1.1rem;">
                        <span>TOTAL A PAGAR:</span>
                        <span>S/ {{ number_format($venta->total_neto, 2) }}</span>
                    </div>
                    <div class="text-center mt-2" style="font-size: 0.8rem;">
                        {{-- Comentamos la conversión a letras por ahora --}}
                        <span>Total Pagado: S/ {{ number_format($venta->total_neto, 2) }}</span>
                    </div>
                </div>

                {{-- PIE DE PAGINA --}}
                <div class="text-center mt-4 mb-4">
                    <p class="mb-1">¡Gracias por su preferencia!</p>
                    <small class="text-muted">Conserve este ticket para cualquier reclamo.</small>
                    <br>
                    {{-- Generador de código de barras simple con CSS o fuente --}}
                    <div class="mt-2 pt-2 border-top border-dotted">
                        <small>Representación impresa del comprobante electrónico</small>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* ESTILOS EN PANTALLA (Simulamos el papel) */
    .ticket {
        background: white;
        padding: 15px;
        border: 1px solid #ddd;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        font-family: 'Courier New', Courier, monospace;
        /* Fuente tipo ticket */
    }

    /* ESTILOS DE IMPRESIÓN (Lo que sale en la ticketera) */
    @media print {

        /* Ocultar todo lo que no sea el ticket */
        body * {
            visibility: hidden;
        }

        .no-print {
            display: none !important;
        }

        /* Mostrar solo el ticket */
        #ticket-content,
        #ticket-content * {
            visibility: visible;
        }

        /* Posicionar el ticket al inicio de la hoja */
        #ticket-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 80mm;
            /* ANCHO TÍPICO DE TICKETERA (58mm u 80mm) */
            padding: 0;
            margin: 0;
            border: none;
            box-shadow: none;
            font-size: 12px;
            /* Letra un poco más pequeña para que entre todo */
        }

        /* Ajustes de página */
        @page {
            size: auto;
            /* auto es el valor inicial */
            margin: 0mm;
            /* Sin márgenes para aprovechar el papel */
        }
    }
</style>
@stop