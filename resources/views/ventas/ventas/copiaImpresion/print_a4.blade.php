<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>A4 {{ $venta->serie }}-{{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            /* Espacio abajo para que el texto no tape el footer fijo */
            margin-bottom: 5cm;
        }

        /* Estilos Generales */
        .w-100 {
            width: 100%;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-red {
            color: red !important;
        }

        /* Cabecera */
        .header-table {
            width: 100%;
            margin-bottom: 15px;
        }

        .ruc-box {
            border: 2px solid #000;
            border-radius: 8px;
            text-align: center;
            padding: 10px;
        }

        .doc-type {
            background: #000;
            color: #fff;
            font-weight: bold;
            padding: 4px;
            margin: 5px 0;
        }

        /* Cliente */
        .client-box {
            border: 1px solid #000;
            border-radius: 5px;
            padding: 8px;
            margin-bottom: 10px;
            width: 100%;
        }

        /* Tabla Items */
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
        }

        .items-table td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 11px;
        }

        /* === FOOTER FIJO === */
        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4.5cm;
            /* Altura del footer */
            background: #fff;
            border-top: 1px solid #ccc;
            /* Separador sutil opcional */
            padding-top: 10px;
        }

        /* Tabla de Totales (Estilo Imagen) */
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .totals-table td {
            padding: 3px 0;
        }

        .total-final-row {
            border-top: 2px solid #000;
            /* La línea negra gruesa */
            font-size: 16px;
            font-weight: bold;
        }

        .anulado {
            position: fixed;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(200, 0, 0, 0.2);
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
            <td width="25%">
                @if(isset($logoBase64) && !empty($logoBase64))
                <img src="{{ $logoBase64 }}" style="max-height: 80px;">
                @endif
            </td>
            <td width="45%" class="text-center">
                <div style="font-size: 16px; font-weight: bold;">{{ $venta->sucursal->nombre }}</div>
                <div style="font-size: 11px;">{{ $venta->sucursal->direccion }}</div>
            </td>
            <td width="30%">
                <div class="ruc-box">
                    <div class="font-bold">R.U.C. {{ $config->empresa_ruc ?? $venta->sucursal->ruc }}</div>
                    <div class="doc-type">{{ $venta->tipo_comprobante }} ELECTRÓNICA</div>
                    <div class="font-bold">{{ $venta->serie }} - {{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="client-box">
        <tr>
            <td width="60%">
                <b>Cliente:</b> {{ $venta->cliente->nombre_completo }}<br>
                <b>{{ $venta->cliente->tipo_documento }}:</b> {{ $venta->cliente->documento }}<br>
                <b>Dirección:</b> {{ Str::limit($venta->cliente->direccion, 80) }}
            </td>
            <td width="40%" style="vertical-align: top;">
                <b>Fecha:</b> {{ $venta->fecha_emision->format('d/m/Y H:i:s') }}<br>
                <b>Forma Pago:</b> {{ $venta->medio_pago }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>CANT</th>
                <th class="text-left">DESCRIPCIÓN</th>
                <th>P. UNIT</th>
                <th>IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $det)
            <tr>
                <td class="text-center">{{ $det->cantidad }}</td>
                <td>{{ $det->medicamento->nombre }}</td>
                <td class="text-right">{{ number_format($det->precio_unitario, 2) }}</td>
                <td class="text-right font-bold">{{ number_format($det->subtotal_neto, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <footer>
        <table style="width: 100%;">
            <tr>
                <td width="60%" style="vertical-align: top;">
                    <table width="100%">
                        <tr>
                            <td width="90"><img src="data:image/svg+xml;base64,{{ $qrBase64 }}" width="85" height="85"></td>
                            <td style="padding-left: 10px;">
                                <div class="font-bold small">SON: {{ $montoLetras }}</div>
                                <div style="font-size: 9px; color: #555; margin-top: 5px;">
                                    Representación impresa de la {{ $venta->tipo_comprobante }} ELECTRÓNICA.<br>
                                    Consulte en: <b>mundofarma.online/consultar</b>
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
                            <td class="text-right font-bold text-red">Descuento:</td>
                            <td class="text-right font-bold text-red">- S/ {{ number_format($venta->total_descuento, 2) }}</td>
                        </tr>
                        @endif

                        <tr>
                            <td colspan="2" style="padding-top: 5px;"></td>
                        </tr>

                        <tr class="total-final-row">
                            <td class="text-right" style="padding-top: 5px;">IMPORTE TOTAL:</td>
                            <td class="text-right" style="padding-top: 5px;">S/ {{ number_format($venta->total_neto, 2) }}</td>
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