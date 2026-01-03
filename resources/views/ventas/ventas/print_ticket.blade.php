<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Ticket {{ $venta->serie }}-{{ $venta->numero }}</title>
    <style>
        /* RESET TOTAL */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* CONFIGURACIÓN BÁSICA */
        body {
            font-family: 'Arial Narrow', sans-serif;
            /* Fuente estrecha ahorra papel */
            font-size: 13px;
            color: #000;
            background: #fff;
            width: 100%;
        }

        /* CONTENEDOR PRINCIPAL FLUIDO */
        .ticket-wrapper {
            width: 100%;
            /* Arriba: 0 | Der: 2mm | Abajo: 5mm (para corte) | Izq: 2mm */
            padding: 0 2mm 5mm 2mm;
        }

        /* UTILIDADES */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        .font-bold {
            font-weight: bold;
        }

        .mb-1 {
            margin-bottom: 4px;
        }

        .border-dashed {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        /* TABLA ITEMS */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            padding: 3px 0;
            vertical-align: top;
        }

        /* LOGO */
        img {
            max-width: 80%;
            height: auto;
            /* Filtros para asegurar nitidez en impresora térmica */
            filter: grayscale(100%) contrast(150%);
            /* Margen inferior pequeño */
            margin-bottom: 2px;
        }

        /* MARCA DE AGUA ANULADO */
        .watermark {
            position: fixed;
            top: 30%;
            left: 10%;
            transform: rotate(-45deg);
            font-size: 40px;
            border: 3px dashed #000;
            padding: 10px;
            opacity: 0.3;
        }
    </style>
</head>

<body>

    <div class="ticket-wrapper">
        @if($venta->estado == 'ANULADO')
        <div class="watermark">ANULADO</div>
        @endif

        <div class="text-center" style="padding-top: 2px;">
            @if(isset($logoBase64) && !empty($logoBase64))
            <img src="{{ $logoBase64 }}" alt="Logo">
            @endif

            <div style="font-size: 14px; font-weight: bold; line-height: 1.1;">{{ $venta->sucursal->nombre }}</div>
            <div style="font-size: 11px; margin-top: 2px;">{{ $venta->sucursal->direccion }}</div>
            <div class="font-bold" style="margin-top: 2px;">RUC: {{ $config->empresa_ruc ?? $venta->sucursal->ruc }}</div>
        </div>

        <div class="border-dashed"></div>

        <div>
            <div><b>{{ $venta->tipo_comprobante }}:</b> {{ $venta->serie }}-{{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}</div>
            <div><b>Fecha:</b> {{ $venta->fecha_emision->format('d/m/Y H:i') }}</div>
            <div><b>Cliente:</b> {{ Str::limit($venta->cliente->nombre_completo, 25) }}</div>
            @if($venta->cliente->documento != '00000000')
            <div><b>{{ $venta->cliente->tipo_documento }}:</b> {{ $venta->cliente->documento }}</div>
            @endif
        </div>

        <div class="border-dashed"></div>

        <table>
            <thead>
                <tr>
                    <th class="text-left" style="width: 15%;">C.</th>
                    <th class="text-left">DESCRIPCIÓN</th>
                    <th class="text-right">TOT.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->detalles as $det)
                <tr>
                    <td class="font-bold">{{ (int)$det->cantidad }}</td>
                    <td style="font-size: 12px;">{{ $det->medicamento->nombre }}</td>
                    <td class="text-right">{{ number_format($det->subtotal_neto, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="border-dashed"></div>

        <table style="font-size: 12px;">
            @if($venta->op_gravada > 0)
            <tr>
                <td>OP. GRAVADA</td>
                <td class="text-right">S/ {{ number_format($venta->op_gravada, 2) }}</td>
            </tr>
            @endif

            <tr>
                <td>I.G.V.</td>
                <td class="text-right">S/ {{ number_format($venta->total_igv, 2) }}</td>
            </tr>

            @if($venta->total_descuento > 0)
            <tr>
                <td>DESCUENTO</td>
                <td class="text-right">- S/ {{ number_format($venta->total_descuento, 2) }}</td>
            </tr>
            @endif

            <tr style="font-size: 16px;">
                <td class="font-bold" style="padding-top: 5px;">TOTAL</td>
                <td class="text-right font-bold" style="padding-top: 5px;">S/ {{ number_format($venta->total_neto, 2) }}</td>
            </tr>
        </table>

        <div class="text-center" style="margin-top: 5px; font-size: 11px;">
            SON: {{ $montoLetras }}
        </div>

        <div class="text-center" style="margin-top: 10px;">
            <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" style="width: 100px; height: 100px;">
            <div style="font-weight: bold; margin-top: 5px; font-size: 11px;">GRACIAS POR SU COMPRA</div>
            <div style="font-size: 10px;">Consulta: mundofarma.online</div>
        </div>
    </div>

    @if(request('imprimir') == 'si')
    <script>
        window.addEventListener('load', () => {
            window.print();
        });
    </script>
    @endif
</body>

</html>