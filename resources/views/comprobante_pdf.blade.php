<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $venta->tipo_comprobante }} {{ $venta->serie }}-{{ $venta->numero }}</title>
    <style>
        @page {
            margin: 10px;
        }

        /* Márgenes mínimos de la hoja */

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            margin-top: 20px;
            margin-bottom: 180px;
            /* IMPORTANTE: Espacio para que el footer no tape los items */
        }

        /* === HEADER DE 3 COLUMNAS (Estilo Yape) === */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .col-logo {
            width: 20%;
            text-align: left;
            vertical-align: middle;
        }

        .col-empresa {
            width: 50%;
            text-align: center;
            vertical-align: middle;
        }

        .col-ruc {
            width: 30%;
            text-align: right;
            vertical-align: top;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .company-info {
            font-size: 10px;
            color: #555;
            line-height: 1.3;
        }

        /* Cuadro RUC */
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

        /* === CLIENTE === */
        .client-box {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 8px;
            margin-bottom: 10px;
            width: 100%;
        }

        .label {
            font-weight: bold;
            color: #333;
        }

        /* === TABLA ITEMS === */
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table-items th {
            background: #eee;
            border-bottom: 2px solid #000;
            padding: 6px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .table-items td {
            border-bottom: 1px solid #ddd;
            padding: 8px;
            font-size: 10px;
            vertical-align: middle;
        }

        /* === FOOTER FIJO AL FINAL (Magia de DomPDF) === */
        footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 160px;
            /* Altura reservada para el footer */
            border-top: 1px solid #444;
            padding-top: 10px;
            background-color: #fff;
            /* Fondo blanco por si pasa algo por debajo */
        }

        /* Utilidades Footer */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .total-row {
            background: #333;
            color: #fff;
            font-size: 12px;
            font-weight: bold;
        }

        .total-cell {
            padding: 8px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Marca de Agua Anulado */
        .anulado {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(255, 0, 0, 0.15);
            border: 5px solid rgba(255, 0, 0, 0.15);
            padding: 20px;
            z-index: -1;
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
                @if($logoBase64)
                <img src="{{ $logoBase64 }}" style="max-width: 120px; max-height: 80px;">
                @else
                {{-- Espacio vacío si no hay logo --}}
                @endif
            </td>

            <td class="col-empresa">
                <div class="company-name">{{ $emisor['razon_social'] }}</div>
                <div style="font-weight: bold; margin-bottom: 4px;">{{ $emisor['nombre'] }}</div> {{-- Nombre Sucursal --}}
                <div class="company-info">
                    {{ $emisor['direccion'] }}<br>
                    @if($emisor['telefono']) Tel: {{ $emisor['telefono'] }} | @endif
                    @if($emisor['email']) {{ $emisor['email'] }} @endif
                </div>
            </td>

            <td class="col-ruc">
                <div class="ruc-box">
                    <div class="ruc-label">R.U.C. {{ $emisor['ruc'] }}</div>
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
                @if(!empty($venta->cliente->direccion) && $venta->cliente->direccion != '-')
                <span class="label">Dirección:</span> {{ Str::limit($venta->cliente->direccion, 60) }}<br>
                @endif
            </td>
            <td width="40%" style="vertical-align: top;">
                <span class="label">Fecha:</span> {{ $venta->fecha_emision->format('d/m/Y H:i:s') }}<br>
                <span class="label">Moneda:</span> SOLES<br>
                <span class="label">Pago:</span> {{ $venta->medio_pago }}
            </td>
        </tr>
    </table>

    <table class="table-items">
        <thead>
            <tr>
                <th width="8%">CANT</th>
                <th width="10%">UND</th>
                <th>DESCRIPCIÓN</th>
                <th width="12%">P.UNIT</th>
                <th width="12%">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $det)
            <tr>
                <td class="text-center">{{ $det->cantidad }}</td>
                <td class="text-center">NIU</td>
                <td>
                    {{ $det->medicamento->nombre }}
                    @if($det->medicamento->presentacion)
                    <br><small style="color:#666; font-size:9px;">{{ $det->medicamento->presentacion }}</small>
                    @endif
                </td>
                <td class="text-right">{{ number_format($det->precio_unitario, 2) }}</td>
                <td class="text-right font-bold">{{ number_format($det->subtotal_neto, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <footer>
        <table class="footer-table">
            <tr>
                <td width="65%" valign="top">
                    <table width="100%">
                        <tr>
                            <td width="90">
                                <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" width="85" height="85">
                            </td>
                            <td valign="middle" style="padding-left: 10px;">
                                {{-- MONTO EN LETRAS (Obligatorio) --}}
                                <div style="font-weight: bold; margin-bottom: 5px; font-size: 10px;">
                                    {{ $montoLetras }}
                                </div>
                                <div style="font-size: 9px; color: #666; line-height: 1.4;">
                                    Representación impresa de la {{ $venta->tipo_comprobante }} ELECTRÓNICA.<br>
                                    Autorizado mediante Resolución de Superintendencia N.° 300-2014/SUNAT.<br>

                                    Consulte la validez de este documento en: <br>
                                    <a href="https://mundofarma.online/consulta-comprobante" target="_blank" style="text-decoration: none; color: #000; font-weight: bold;">
                                        mundofarma.online/consulta-comprobante
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>

                <td width="35%" valign="top">
                    <table width="100%" style="font-size: 10px;">
                        <tr>
                            <td class="text-right label">Op. Gravada:</td>
                            <td class="text-right">S/ {{ number_format($venta->op_gravada, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-right label">I.G.V. (18%):</td>
                            <td class="text-right">S/ {{ number_format($venta->total_igv, 2) }}</td>
                        </tr>
                        @if($venta->total_descuento > 0)
                        <tr>
                            <td class="text-right label" style="color:red">Descuento:</td>
                            <td class="text-right" style="color:red">- S/ {{ number_format($venta->total_descuento, 2) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="2" style="height: 5px;"></td>
                        </tr>
                        <tr class="total-row">
                            <td class="total-cell text-right">TOTAL A PAGAR:</td>
                            <td class="total-cell text-right">S/ {{ number_format($venta->total_neto, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </footer>

</body>

</html>