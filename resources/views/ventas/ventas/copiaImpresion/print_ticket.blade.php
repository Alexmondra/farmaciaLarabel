<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Ticket {{ $venta->serie }}-{{ $venta->numero }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial Narrow', sans-serif;
            font-size: 12px;
            /* Letra un poco más compacta */
            color: #000;
            background: #fff;
            width: 100%;
        }

        .ticket-wrapper {
            width: 100%;
            padding: 0 5mm 5mm 5mm;
            /* Márgenes mínimos */
        }

        /* Utilidades de Texto */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-red {
            color: red !important;
        }

        /* Para el descuento */

        /* Líneas */
        .border-dashed {
            border-top: 1px dashed #000;
            margin: 4px 0;
        }

        .border-solid {
            border-top: 1px solid #000;
            margin: 4px 0;
        }

        /* Tablas */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            padding: 2px 0;
            vertical-align: top;
        }

        /* Logo */
        img {
            max-width: 75%;
            height: auto;
            filter: grayscale(100%) contrast(150%);
            margin-bottom: 2px;
        }

        /* Marca de Agua */
        .watermark {
            position: fixed;
            top: 30%;
            left: 10%;
            transform: rotate(-45deg);
            font-size: 40px;
            border: 3px dashed #000;
            opacity: 0.3;
            padding: 10px;
        }
    </style>
</head>

<body>
    <div class="ticket-wrapper">
        @if($venta->estado == 'ANULADO')
        <div class="watermark">ANULADO</div>
        @endif

        <div class="text-center">
            @if(isset($logoBase64) && !empty($logoBase64))
            <img src="{{ $logoBase64 }}" alt="Logo">
            @endif
            <div style="font-size: 14px; font-weight: bold; line-height: 1;">{{ $venta->sucursal->nombre }}</div>
            <div style="font-size: 10px; margin-top: 2px;">{{ $venta->sucursal->direccion }}</div>
            <div class="font-bold" style="margin-top: 2px;">RUC: {{ $config->empresa_ruc ?? $venta->sucursal->ruc }}</div>
        </div>

        <div class="border-dashed"></div>

        <div style="font-size: 11px;">
            <div style="display:flex; justify-content:space-between;">
                <b>{{ $venta->tipo_comprobante }}:</b>
                <span>{{ $venta->serie }}-{{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div><b>Fecha:</b> {{ $venta->fecha_emision->format('d/m/Y H:i') }}</div>
            <div><b>Cliente:</b> {{ Str::limit($venta->cliente->nombre_completo, 25) }}</div>
            @if($venta->cliente->documento != '00000000')
            <div><b>{{ $venta->cliente->tipo_documento }}:</b> {{ $venta->cliente->documento }}</div>
            @endif
        </div>

        <div class="border-dashed"></div>

        <table style="font-size: 11px;">
            <thead>
                <tr>
                    <th class="text-left" style="width: 10%;">Cant.</th>
                    <th class="text-left">DESCRIPCIÓN</th>
                    <th class="text-right">TOT.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->detalles as $det)
                <tr>
                    <td class="font-bold">{{ (int)$det->cantidad }}</td>
                    <td>{{ $det->medicamento->nombre }}</td>
                    <td class="text-right">{{ number_format($det->subtotal_neto, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="border-dashed"></div>

        <table style="font-size: 11px; margin-top: 2px;">
            {{-- 1. Op. Gravada --}}
            @if($venta->op_gravada > 0)
            <tr>
                <td class="text-right">Op. Gravada:</td>
                <td class="text-right" style="width: 35%;">S/ {{ number_format($venta->op_gravada, 2) }}</td>
            </tr>
            @endif

            {{-- 2. Op. Exonerada --}}
            @if($venta->op_exonerada > 0)
            <tr>
                <td class="text-right">Op. Exonerada:</td>
                <td class="text-right">S/ {{ number_format($venta->op_exonerada, 2) }}</td>
            </tr>
            @endif

            {{-- 3. Op. Inafecta --}}
            @if($venta->op_inafecta > 0)
            <tr>
                <td class="text-right">Op. Inafecta:</td>
                <td class="text-right">S/ {{ number_format($venta->op_inafecta, 2) }}</td>
            </tr>
            @endif

            {{-- 4. IGV --}}
            <tr>
                <td class="text-right">I.G.V. (18%):</td>
                <td class="text-right">S/ {{ number_format($venta->total_igv, 2) }}</td>
            </tr>

            {{-- 5. DESCUENTO (ROJO) --}}
            @if($venta->total_descuento > 0)
            <tr>
                <td class="text-right font-bold text-red">DESCUENTO:</td>
                <td class="text-right font-bold text-red">- S/ {{ number_format($venta->total_descuento, 2) }}</td>
            </tr>
            @endif

            {{-- 6. TOTAL FINAL (LINEA NEGRA ARRIBA) --}}
            <tr>
                <td colspan="2" style="padding: 0;">
                    <div style="border-top: 1px solid #000; margin-top: 3px;"></div>
                </td>
            </tr>
            <tr style="font-size: 15px;">
                <td class="font-bold" style="padding-top: 2px;">TOTAL:</td>
                <td class="text-right font-bold" style="padding-top: 2px;">S/ {{ number_format($venta->total_neto, 2) }}</td>
            </tr>
        </table>

        <div class="text-center" style="margin-top: 5px; font-size: 10px; text-transform: uppercase;">
            SON: {{ $montoLetras }}
        </div>

        <div class="text-center" style="margin-top: 8px;">
            <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" style="width: 90px; height: 90px;">
            <div style="font-weight: bold; margin-top: 4px; font-size: 10px;">GRACIAS POR SU PREFERENCIA</div>
            <div style="font-size: 9px;">Consulta en: mundofarma.online</div>
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