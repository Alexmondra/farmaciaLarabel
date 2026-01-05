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

        html,
        body {
            width: 100%;
            background: #fff;
            height: auto !important;
            overflow: visible !important;
        }

        @page {
            margin: 0;
            size: auto;
        }

        body {
            font-family: 'Arial Narrow', Arial, sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.2;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .ticket-wrapper {
            width: 100%;
            padding: 2mm 3mm 8mm 3mm;
            position: relative;
            display: block;
        }

        @media print {
            .ticket-wrapper {
                page-break-inside: avoid !important;
            }

            table,
            tr,
            img,
            .points-box {
                page-break-inside: avoid !important;
            }
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .text-red {
            color: red !important;
        }

        .border-dashed {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        /* 4. TABLAS OPTIMIZADAS (No se desbordan) */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        td,
        th {
            padding: 2px 0;
            vertical-align: top;
            word-wrap: break-word;
        }

        img.logo {
            max-width: 65%;
            height: auto;
            filter: grayscale(100%) contrast(150%);
            margin-bottom: 4px;
        }

        /* 5. MARCA DE AGUA ANULADO (Claramente visible en B/N) */
        .watermark {
            position: absolute;
            top: 25%;
            left: 5%;
            transform: rotate(-45deg);
            font-size: 50px;
            font-weight: bold;
            border: 5px solid #000;
            color: #000;
            padding: 10px;
            opacity: 0.25;
            z-index: 100;
            pointer-events: none;
            text-align: center;
            width: 90%;
        }

        /* 6. CAJAS ESPECIALES */
        .total-box {
            background: #000 !important;
            color: #fff !important;
            padding: 5px 8px;
            display: flex;
            justify-content: space-between;
            font-size: 15px;
            margin-top: 5px;
            border-radius: 2px;
        }

        .points-box {
            border: 1px solid #000;
            border-radius: 4px;
            padding: 5px;
            margin: 8px 0;
        }
    </style>
</head>

<body>
    <div class="ticket-wrapper">
        {{-- MARCA DE AGUA SI ESTÁ ANULADO --}}
        @if($venta->estado == 'ANULADO')
        <div class="watermark">ANULADO</div>
        @endif

        <div class="text-center">
            @if(isset($logoBase64) && !empty($logoBase64))
            <img class="logo" src="{{ $logoBase64 }}" alt="Logo">
            @endif

            <div style="font-size: 14px; font-weight: bold; line-height: 1.1;">
                {{ $config->empresa_razon_social ?? $config->empresa_nombre ?? 'FARMACIA MUNDO FARMA S.A.C.' }}
            </div>
            <div style="font-size: 10px;">{{ $venta->sucursal->direccion }}</div>
            <div class="font-bold">RUC: {{ $config->empresa_ruc ?? $venta->sucursal->ruc }}</div>
        </div>

        <div class="border-dashed"></div>

        <div style="font-size: 11px;">
            <div style="display: flex; justify-content: space-between;">
                <b>{{ $venta->tipo_comprobante }}:</b>
                <span>{{ $venta->serie }}-{{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div><b>Fecha:</b> {{ $venta->fecha_emision->format('d/m/Y H:i') }}</div>
            <div><b>Cliente:</b> {{ Str::limit($venta->cliente->nombre_completo, 32) }}</div>
            @if($venta->cliente->documento != '00000000')
            <div><b>{{ $venta->cliente->tipo_documento }}:</b> {{ $venta->cliente->documento }}</div>
            @endif
        </div>

        <div class="border-dashed"></div>

        <table>
            <thead>
                <tr>
                    <th class="text-left" style="width: 15%;">CANT</th>
                    <th class="text-left">DESCRIPCIÓN</th>
                    <th class="text-right" style="width: 25%;">TOTAL</th>
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

        <table>
            @if($venta->op_gravada > 0)
            <tr>
                <td class="text-right">Op. Gravada:</td>
                <td class="text-right" style="width: 40%;">S/ {{ number_format($venta->op_gravada, 2) }}</td>
            </tr>
            @endif
            @if($venta->total_descuento > 0)
            <tr class="text-red">
                <td class="text-right font-bold">DESCUENTO:</td>
                <td class="text-right font-bold">- S/ {{ number_format($venta->total_descuento, 2) }}</td>
            </tr>
            @endif
        </table>

        <div class="total-box">
            <span class="font-bold">TOTAL</span>
            <span class="font-bold">S/ {{ number_format($venta->total_neto, 2) }}</span>
        </div>

        <div class="text-center uppercase" style="margin-top: 6px; font-size: 9px;">
            SON: {{ $montoLetras }}
        </div>

        @if($venta->total_descuento > 0 || $venta->cliente->documento != '00000000')
        <div class="points-box">
            <div style="font-weight: bold; font-size: 10px; background: #eee; text-align: center;">MONEDERO PUNTOS</div>
            <div style="display: flex; justify-content: space-between; padding: 2px 5px; font-size: 10px;">
                <span>Ganados hoy:</span>
                <b>+{{ $puntosGanados ?? 0 }}</b>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 0 5px; font-size: 10px;">
                <span>Saldo Total:</span>
                <b>{{ $venta->cliente->puntos ?? 0 }} pts</b>
            </div>
        </div>
        @endif

        <div class="text-center" style="margin-top: 10px;">
            <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" style="width: 95px;">
            <div style="font-weight: bold; margin-top: 5px; font-size: 10px;">{{ $mensajePie }}</div>
            <div style="font-size: 9px; margin-top: 5px; color: #444;">
                Representación impresa de la <b>{{ $venta->tipo_comprobante }} ELECTRÓNICA</b><br>
                Consulte en: <b>mundofarma.online/consultar</b>
            </div>
        </div>
    </div>

    @if(request('imprimir') == 'si')
    <script>
        window.addEventListener('load', () => {
            window.print();
            setTimeout(() => {
                window.close();
            }, 1000);
        });
    </script>
    @endif
</body>

</html>