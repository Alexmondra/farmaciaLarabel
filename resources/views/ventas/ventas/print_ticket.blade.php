<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Ticket {{ $venta->serie }}-{{ $venta->numero }}</title>
    <style>
        /* RESET */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial Narrow', sans-serif;
            font-size: 12px;
            color: #000;
            background: #fff;
            width: 100%;
        }

        .ticket-wrapper {
            width: 100%;
            /* Ajusté el padding para que no quede tan pegado ni tan separado */
            padding: 2mm 4mm 5mm 4mm;
        }

        /* Utilidades */
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

        .uppercase {
            text-transform: uppercase;
        }

        /* Separadores */
        .border-dashed {
            border-top: 1px dashed #000;
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

        /* LOGO AJUSTADO: Más pequeño y nítido */
        img.logo {
            max-width: 55%;
            /* Antes era 80%, ahora es más discreto */
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
            padding: 10px;
            opacity: 0.3;
        }

        /* Caja de Puntos */
        .points-box {
            border: 1px solid #000;
            border-radius: 4px;
            padding: 4px;
            margin: 8px 0;
            text-align: center;
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
            <img class="logo" src="{{ $logoBase64 }}" alt="Logo">
            @endif

            {{-- 1. RAZÓN SOCIAL (El nombre legal de la empresa) --}}
            {{-- Si tienes el campo en config úsalo, si no, escribe el nombre fijo aquí --}}
            <div style="font-size: 15px; font-weight: bold; text-transform: uppercase; margin-top: 5px; line-height: 1.1;">
                {{ $config->empresa_razon_social ?? $config->empresa_nombre ?? 'FARMACIA MUNDO FARMA S.A.C.' }}
            </div>

            {{-- 2. SUCURSAL (El nombre del local específico) --}}
            @if($venta->sucursal->nombre)
            <div style="font-size: 12px; font-weight: bold; margin-top: 3px; color: #333;">
                Sucursal: {{ $venta->sucursal->nombre }}
            </div>
            @endif

            {{-- 3. DIRECCIÓN Y RUC --}}
            <div style="font-size: 10px; margin-top: 2px;">{{ $venta->sucursal->direccion }}</div>
            <div class="font-bold" style="font-size: 12px; margin-top: 3px;">RUC: {{ $config->empresa_ruc ?? $venta->sucursal->ruc }}</div>

            {{-- 4. TELÉFONO (Opcional pero recomendado) --}}
            @if(!empty($venta->sucursal->telefono))
            <div style="font-size: 10px;">Telf: {{ $venta->sucursal->telefono }}</div>
            @endif
        </div>

        <div class="border-dashed"></div>

        <div style="font-size: 11px;">
            <div style="display: flex; justify-content: space-between;">
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
                    <th class="text-left" style="width: 10%;">C.</th>
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
            @if($venta->op_gravada > 0)
            <tr>
                <td class="text-right">Op. Gravada:</td>
                <td class="text-right" style="width: 35%;">S/ {{ number_format($venta->op_gravada, 2) }}</td>
            </tr>
            @endif

            @if($venta->op_exonerada > 0)
            <tr>
                <td class="text-right">Op. Exonerada:</td>
                <td class="text-right">S/ {{ number_format($venta->op_exonerada, 2) }}</td>
            </tr>
            @endif

            <tr>
                <td class="text-right">I.G.V. (18%):</td>
                <td class="text-right">S/ {{ number_format($venta->total_igv, 2) }}</td>
            </tr>

            @if($venta->total_descuento > 0)
            <tr>
                <td class="text-right font-bold text-red">DESCUENTO:</td>
                <td class="text-right font-bold text-red">- S/ {{ number_format($venta->total_descuento, 2) }}</td>
            </tr>
            @endif

            {{-- TOTAL EN NEGRO (Estilo Inverso) --}}
            <tr style="font-size: 15px;">
                <td colspan="2" style="padding-top: 4px;">
                    <div style="background: #000; color: #fff; padding: 4px 6px; display: flex; justify-content: space-between; border-radius: 3px;">
                        <span class="font-bold">TOTAL</span>
                        <span class="font-bold">S/ {{ number_format($venta->total_neto, 2) }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <div class="text-center uppercase" style="margin-top: 4px; font-size: 9px;">
            SON: {{ $montoLetras }}
        </div>
        @if($venta->total_descuento > 0 || $venta->cliente->documento != '00000000')
        <div class="points-box">
            @if($venta->total_descuento > 0)
            <div style="border-bottom: 1px dashed #000; padding-bottom: 2px; margin-bottom: 2px;">
                ¡Felicidades! 🎉 Ahorraste: <b class="text-red">S/ {{ number_format($venta->total_descuento, 2) }}</b>
            </div>
            @endif

            {{-- CAJA DE PUNTOS --}}
            <div style="font-weight: bold; font-size: 11px; background: #eee;">MONEDERO / PUNTOS</div>

            <div style="display: flex; justify-content: space-between; padding: 2px 10px; font-size: 10px;">
                <span>Ganados hoy:</span>
                <b>+{{ $puntosGanados ?? 0 }} pts</b>
            </div>

            <div style="display: flex; justify-content: space-between; padding: 0 10px; font-size: 10px;">
                <span>Saldo Total:</span>
                <b>{{ $venta->cliente->puntos ?? 0 }} pts</b>
            </div>

            <div style="font-size: 9px; font-style: italic; margin-top: 2px;">
                ¡Úsalos en tu próxima compra!
            </div>
        </div>
        @endif
        <div class="text-center" style="margin-top: 10px;">
            {{-- 1. CÓDIGO QR --}}
            <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" style="width: 90px; height: 90px;">

            {{-- 2. MENSAJE DE AGRADECIMIENTO (Viene de tu base de datos) --}}
            <div style="font-weight: bold; margin-top: 5px; font-size: 10px; text-transform: uppercase;">
                {{ $mensajePie }}
            </div>

            {{-- 3. FRASE DE CONSULTA "ELEGANTE" --}}
            <div style="margin-top: 8px; font-size: 9px; color: #555; line-height: 1.2;">
                Representación impresa de la<br>
                {{ $venta->tipo_comprobante }} ELECTRÓNICA
            </div>

            <div style="margin-top: 4px; font-size: 9px;">
                Consulte su validez en:<br>
                <span style="font-weight: bold; font-size: 10px; color: #000;">mundofarma.online/consultar</span>
            </div>

            {{-- Detalle final sutil --}}
            <div style="margin-top: 5px; border-top: 1px solid #ddd; width: 50%; margin-left: auto; margin-right: auto;"></div>
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