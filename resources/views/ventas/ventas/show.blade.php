@extends('adminlte::page')

@section('title', 'Ver Comprobante')

@section('content_header')
<div class="no-print">
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-file-invoice text-teal mr-2"></i>
            {{ $venta->tipo_comprobante }}: {{ $venta->serie }}-{{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}
        </h1>
        <a href="{{ route('ventas.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-cash-register mr-1"></i> Nueva Venta
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid pb-5">

    {{-- BARRA DE HERRAMIENTAS (NO SE IMPRIME) --}}
    <div class="card shadow-sm no-print mb-4 border-0">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="btn-group w-100 shadow-sm" role="group">
                        <button type="button" class="btn btn-dark" id="btn-ticket" onclick="verTicket()">
                            <i class="fas fa-receipt mr-2"></i> Ticket (80mm)
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btn-a4" onclick="verA4()">
                            <i class="far fa-file-pdf mr-2"></i> Formato A4
                        </button>
                    </div>
                </div>
                <div class="col-md-8 text-right">
                    <button onclick="window.print()" class="btn btn-danger font-weight-bold shadow px-4">
                        <i class="fas fa-print mr-2"></i> IMPRIMIR
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================= --}}
    {{-- VISTA TICKET (80mm)                                     --}}
    {{-- ======================================================= --}}
    <div id="wrapper-ticket" class="d-flex justify-content-center">
        <div class="ticket-box">
            <div class="text-center mb-2">
                <h5 class="font-weight-bold mb-1 mt-2 text-uppercase" style="font-size: 1.1rem;">{{ $venta->sucursal->nombre }}</h5>
                <p class="small mb-0">{{ $venta->sucursal->direccion }}</p>
                <p class="small mb-0 font-weight-bold">RUC: {{ $venta->sucursal->ruc ?? '20000000001' }}</p>
                <p class="small">{{ $venta->sucursal->telefono ?? '' }}</p>
            </div>
            <div class="text-center border-top border-bottom border-dark py-2 mb-2">
                <h6 class="font-weight-bold mb-0">{{ $venta->tipo_comprobante }} ELECTRÓNICA</h6>
                <h6 class="font-weight-bold mb-0">{{ $venta->serie }}-{{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}</h6>
                <small class="d-block mt-1">Fecha: {{ $venta->fecha_emision->format('d/m/Y H:i:s') }}</small>
            </div>
            <div class="mb-3 small">
                <div><strong>CLI:</strong> {{ Str::limit($venta->cliente->nombre_completo, 25) }}</div>
                <div><strong>DOC:</strong> {{ $venta->cliente->documento }}</div>
            </div>
            <table class="table table-sm table-borderless small mb-2" style="font-size: 11px;">
                <thead class="border-bottom border-dark">
                    <tr>
                        <th class="pl-0">Cant.</th>
                        <th>Descripción</th>
                        <th class="text-right pr-0">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($venta->detalles as $det)
                    <tr>
                        <td class="pl-0 font-weight-bold align-top">{{ $det->cantidad }}</td>
                        <td class="align-top">
                            {{ Str::limit($det->medicamento->nombre, 18) }}
                        </td>
                        <td class="text-right pr-0 align-top font-weight-bold">{{ number_format($det->subtotal_neto, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-top border-dark pt-1 mt-1" style="font-size: 11px;">
                <div class="d-flex justify-content-between mt-1 pt-1 border-top border-dark" style="font-size: 14px;">
                    <span class="font-weight-bold">TOTAL</span>
                    <span class="font-weight-bold">S/ {{ number_format($venta->total_neto, 2) }}</span>
                </div>
                <p class="text-center small mt-2 mb-0">SON: {{ $montoLetras }}</p>
            </div>
        </div>
    </div>


    {{-- ======================================================= --}}
    {{-- VISTA A4                                                --}}
    {{-- ======================================================= --}}
    <div id="wrapper-a4" class="d-none justify-content-center">
        <div class="a4-box bg-white">

            {{-- CABECERA A4 --}}
            <div class="row mb-4">
                <div class="col-7">
                    <h3 class="font-weight-bold text-uppercase text-dark">{{ $venta->sucursal->nombre }}</h3>
                    <div class="text-muted small">
                        <p class="mb-0">{{ $venta->sucursal->direccion }}</p>
                        <p class="mb-0">Tel: {{ $venta->sucursal->telefono }}</p>
                    </div>
                </div>
                <div class="col-5">
                    <div class="border border-dark rounded text-center p-3">
                        <h5 class="font-weight-bold">R.U.C. {{ $venta->sucursal->ruc ?? '20000000001' }}</h5>
                        <div class="bg-dark text-white py-1 my-2 font-weight-bold header-box-print">
                            {{ $venta->tipo_comprobante }} ELECTRÓNICA
                        </div>
                        <h4 class="mb-0 font-weight-bold">{{ $venta->serie }} - {{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}</h4>
                    </div>
                </div>
            </div>

            {{-- DATOS CLIENTE --}}
            <div class="card mb-4 border-dark shadow-none">
                <div class="card-body p-3 small">
                    <div class="row">
                        <div class="col-sm-7 border-right">
                            <h6 class="font-weight-bold text-secondary text-uppercase mb-2">Cliente</h6>
                            <p class="mb-1"><strong>Razón Social:</strong> {{ $venta->cliente->nombre_completo }}</p>
                            <p class="mb-1"><strong>Documento:</strong> {{ $venta->cliente->documento }}</p>
                            <p class="mb-0"><strong>Dirección:</strong> {{ $venta->cliente->direccion ?? '-' }}</p>
                        </div>
                        <div class="col-sm-5 pl-4">
                            <h6 class="font-weight-bold text-secondary text-uppercase mb-2">Detalles</h6>
                            <p class="mb-1"><strong>Fecha:</strong> {{ $venta->fecha_emision->format('d/m/Y') }}</p>
                            <p class="mb-1"><strong>Hora:</strong> {{ $venta->fecha_emision->format('H:i:s') }}</p>
                            <p class="mb-0"><strong>Pago:</strong> {{ $venta->medio_pago }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABLA A4 --}}
            <table class="table table-bordered table-sm text-sm mb-4 table-print">
                <thead class="bg-dark text-white">
                    <tr class="text-center text-uppercase">
                        <th style="width: 50px;">Cant.</th>
                        <th style="width: 60px;">Und.</th>
                        <th>Descripción</th>
                        <th style="width: 100px;">P. Unit</th>
                        <th style="width: 100px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($venta->detalles as $det)
                    <tr>
                        <td class="text-center">{{ $det->cantidad }}</td>
                        <td class="text-center">NIU</td>
                        <td>
                            <span class="font-weight-bold">{{ $det->medicamento->nombre }}</span>
                            <br><small class="text-muted">{{ $det->medicamento->presentacion }}</small>
                        </td>
                        <td class="text-right">{{ number_format($det->precio_unitario, 2) }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($det->subtotal_neto, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- TOTALES --}}
            <div class="row" style="page-break-inside: avoid;">
                <div class="col-8">
                    <div class="border p-2">
                        <p class="small mb-1"><strong>SON:</strong> {{ $montoLetras }}</p>
                        <div class="d-flex align-items-center mt-2">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode($qrString) }}" style="width: 80px;">
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <table class="table table-sm table-clear text-right small">
                        @if($venta->op_gravada > 0)
                        <tr>
                            <td><strong>Op. Gravada:</strong></td>
                            <td>{{ number_format($venta->op_gravada, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>I.G.V. (18%):</strong></td>
                            <td>{{ number_format($venta->total_igv, 2) }}</td>
                        </tr>
                        @endif
                        @if($venta->op_exonerada > 0)
                        <tr>
                            <td><strong>Op. Exonerada:</strong></td>
                            <td>{{ number_format($venta->op_exonerada, 2) }}</td>
                        </tr>
                        @endif
                        @if($venta->total_descuento > 0)
                        <tr>
                            <td class="text-danger">Desc. Total:</td>
                            <td class="text-danger">- {{ number_format($venta->total_descuento, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="bg-dark text-white footer-print">
                            <td class="py-2"><strong>TOTAL:</strong></td>
                            <td class="py-2"><strong>S/ {{ number_format($venta->total_neto, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@stop

@section('js')
<script>
    function verTicket() {
        $('#wrapper-ticket').removeClass('d-none').addClass('d-flex');
        $('#wrapper-a4').addClass('d-none').removeClass('d-flex');

        $('#btn-ticket').addClass('btn-dark').removeClass('btn-outline-secondary');
        $('#btn-a4').removeClass('btn-dark').addClass('btn-outline-secondary');
    }

    function verA4() {
        $('#wrapper-ticket').addClass('d-none').removeClass('d-flex');
        $('#wrapper-a4').removeClass('d-none').addClass('d-flex');

        $('#btn-ticket').removeClass('btn-dark').addClass('btn-outline-secondary');
        $('#btn-a4').addClass('btn-dark').removeClass('btn-outline-secondary');
    }
</script>
@stop

@section('css')
<style>
    /* PANTALLA */
    .ticket-box {
        width: 80mm;
        margin: 20px auto;
        padding: 10px;
        background: #fff;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
        font-family: 'Courier New', Courier, monospace;
    }

    .a4-box {
        width: 210mm;
        min-height: 297mm;
        margin: 20px auto;
        padding: 40px;
        background: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        font-family: sans-serif;
    }

    /* IMPRESIÓN */
    @media print {
        @page {
            size: A4 portrait;
            margin: 0;
            /* <--- ESTO ES LO QUE ELIMINA EL ENCABEZADO Y URL AUTOMÁTICO */
        }

        body {
            background: #fff !important;
            margin: 0;
            padding: 0;
        }

        .no-print,
        nav,
        footer,
        aside,
        .content-header {
            display: none !important;
        }

        .content-wrapper,
        .card {
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
        }

        /* TICKET */
        #wrapper-ticket:not(.d-none) {
            display: block !important;
            width: 80mm !important;
            margin: 0 !important;
        }

        #wrapper-ticket:not(.d-none) .ticket-box {
            width: 100% !important;
            padding: 5mm !important;
            box-shadow: none !important;
        }

        /* A4 */
        #wrapper-a4:not(.d-none) {
            display: block !important;
            width: 100% !important;
            position: static !important;
            overflow: visible !important;
        }

        #wrapper-a4:not(.d-none) .a4-box {
            width: 100% !important;
            /* Aquí devolvemos el espacio que quitamos en el @page margin */
            padding: 15mm 15mm !important;
            box-shadow: none !important;
        }

        /* ESTILOS PARA QUE SE VEA BIEN AL IMPRIMIR */
        .table-print thead th {
            color: #000 !important;
            background-color: transparent !important;
            border-bottom: 2px solid #000 !important;
        }

        .header-box-print,
        .footer-print {
            color: #000 !important;
            background-color: transparent !important;
            border: 1px solid #000 !important;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        /* FIX COLUMNAS BOOTSTRAP */
        .col-sm-7 {
            width: 58%;
            float: left;
        }

        .col-sm-5 {
            width: 42%;
            float: left;
        }

        .col-7 {
            width: 58%;
            float: left;
        }

        .col-5 {
            width: 42%;
            float: left;
        }

        .col-8 {
            width: 66%;
            float: left;
        }

        .col-4 {
            width: 33%;
            float: left;
        }
    }
</style>
@stop