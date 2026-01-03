<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>A4 {{ $venta->serie }}-{{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}</title>
    <style>
        /* CONFIGURACIÓN DE PÁGINA */
        @page {
            size: A4;
            margin: 1cm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
            /* IMPORTANTE: Dejamos espacio abajo para el footer fijo */
            margin-bottom: 5.5cm;
        }

        /* CABECERA */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .col-logo {
            width: 25%;
            vertical-align: top;
        }

        .col-empresa {
            width: 45%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }

        .col-ruc {
            width: 30%;
            vertical-align: top;
        }

        /* CUADRO RUC */
        .ruc-box {
            border: 2px solid #000;
            border-radius: 8px;
            text-align: center;
            padding: 10px 5px;
            background: #fff;
        }

        .ruc-label {
            font-size: 14px;
            font-weight: bold;
        }

        .doc-type-box {
            background: #000;
            color: #fff;
            padding: 5px;
            margin: 6px 0;
            font-weight: bold;
            display: block;
        }

        /* CLIENTE */
        .client-box {
            border: 1px solid #000;
            border-radius: 5px;
            padding: 8px;
            margin-bottom: 10px;
            width: 100%;
        }

        .label {
            font-weight: bold;
            margin-right: 5px;
        }

        /* TABLA ITEMS */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .items-table th {
            border: 1px solid #000;
            background: #eee;
            padding: 6px;
            font-size: 11px;
            text-align: center;
        }

        .items-table td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 11px;
            vertical-align: middle;
        }

        /* UTILIDADES */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .font-bold {
            font-weight: bold;
        }

        /* === FOOTER FIJO AL FINAL === */
        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 5cm;
            /* Altura reservada para el pie */
            background-color: #fff;
            padding-top: 10px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Totales */
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .totals-table td {
            padding: 3px 0;
        }

        .total-final {
            border-top: 2px solid #000;
            font-size: 15px;
            font-weight: bold;
            padding-top: 5px;
            margin-top: 5px;
        }

        /* Marca de Agua */
        .anulado {
            position: fixed;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(200, 0, 0, 0.2);
            border: 10px solid rgba(200, 0, 0, 0.2);
            padding: 20px;
            border-radius: 20px;
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>

<body>

    @if($venta->estado == 'ANULADO')
    <div class="anulado">ANULADO</div>
    @endif

    <table class="header-table">
        <tr>
            <td class="col-logo">
                @if(isset($logoBase64) && !empty($logoBase64))
                <img src="{{ $logoBase64 }}" style="max-width: 100%; max-height: 80px;">
                @endif
            </td>
            <td class="col-empresa">
                <div style="font-size: 16px; font-weight: bold;">{{ $venta->sucursal->nombre }}</div>
                <div style="font-size: 11px; margin-top: 5px;">
                    {{ $venta->sucursal->direccion }}<br>
                    @if($venta->sucursal->telefono) Tel: {{ $venta->sucursal->telefono }} @endif
                    @if($venta->sucursal->email) - {{ $venta->sucursal->email }} @endif
                </div>
            </td>
            <td class="col-ruc">
                <div class="ruc-box">
                    <div class="ruc-label">R.U.C. {{ $config->empresa_ruc ?? $venta->sucursal->ruc }}</div>
                    <div class="doc-type-box">{{ $venta->tipo_comprobante }} ELECTRÓNICA</div>
                    <div class="ruc-label">{{ $venta->serie }} - {{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="client-box">
        <tr>
            <td width="60%">
                <span class="label">Cliente:</span> {{ $venta->cliente->nombre_completo }}<br>
                <span class="label">{{ $venta->cliente->tipo_documento }}:</span> {{ $venta->cliente->documento }}<br>
                <span class="label">Dirección:</span> {{ Str::limit($venta->cliente->direccion, 80, '...') }}
            </td>
            <td width="40%" style="vertical-align: top;">
                <span class="label">Fecha Emisión:</span> {{ $venta->fecha_emision->format('d/m/Y H:i:s') }}<br>
                <span class="label">Moneda:</span> SOLES<br>
                <span class="label">Forma de Pago:</span> {{ $venta->medio_pago }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="8%">CANT</th>
                <th width="10%">UND</th>
                <th class="text-left">DESCRIPCIÓN</th>
                <th width="12%">P. UNIT</th>
                <th width="12%">IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $det)
            <tr>
                <td class="text-center">{{ $det->cantidad }}</td>
                <td class="text-center">NIU</td>
                <td>{{ $det->medicamento->nombre }}</td>
                <td class="text-right">{{ number_format($det->precio_unitario, 2) }}</td>
                <td class="text-right font-bold">{{ number_format($det->subtotal_neto, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <footer>
        <table class="footer-table">
            <tr>
                <td width="60%" style="vertical-align: top;">
                    <table width="100%">
                        <tr>
                            <td width="90">
                                <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" width="85" height="85">
                            </td>
                            <td style="vertical-align: top; padding-left: 10px;">
                                <div class="font-bold" style="font-size: 11px; margin-bottom: 5px;">SON: {{ $montoLetras }}</div>
                                <div style="font-size: 10px; color: #555;">
                                    Representación impresa de la {{ $venta->tipo_comprobante }} ELECTRÓNICA.<br>
                                    Autorizado mediante Resolución de Superintendencia N.° 300-2014/SUNAT.<br>
                                    Consulte validez en: <b>mundofarma.online/consultar</b>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>

                <td width="40%" style="vertical-align: top;">
                    <table class="totals-table">
                        @if($venta->op_gravada > 0)
                        <tr>
                            <td class="text-right font-bold">Op. Gravada:</td>
                            <td class="text-right">S/ {{ number_format($venta->op_gravada, 2) }}</td>
                        </tr>
                        @endif

                        @if($venta->op_exonerada > 0)
                        <tr>
                            <td class="text-right font-bold">Op. Exonerada:</td>
                            <td class="text-right">S/ {{ number_format($venta->op_exonerada, 2) }}</td>
                        </tr>
                        @endif

                        <tr>
                            <td class="text-right font-bold">I.G.V. (18%):</td>
                            <td class="text-right">S/ {{ number_format($venta->total_igv, 2) }}</td>
                        </tr>

                        @if($venta->total_descuento > 0)
                        <tr>
                            <td class="text-right font-bold" style="color:red">Descuento:</td>
                            <td class="text-right" style="color:red">- S/ {{ number_format($venta->total_descuento, 2) }}</td>
                        </tr>
                        @endif

                        <tr>
                            <td colspan="2">
                                <div class="total-final" style="display: flex; justify-content: space-between;">
                                    <span>IMPORTE TOTAL:</span>
                                    <span>S/ {{ number_format($venta->total_neto, 2) }}</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </footer>

    @if(request('imprimir') == 'si')
    <script>
        window.addEventListener('load', () => {
            window.print();
        });
    </script>
    @endif
</body>

</html>