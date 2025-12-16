<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>GUÍA DE REMISIÓN REMITENTE {{ $guia->serie }}-{{ $guia->numero }}</title>
    <style>
        /* Adaptación de tu estilo de Comprobante para Guía */
        @page {
            margin: 10px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            margin-top: 20px;
            margin-bottom: 20px;
            /* La guía no requiere footer fijo tan grande */
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
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
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .company-info {
            font-size: 9px;
            color: #555;
            line-height: 1.3;
        }

        /* Cuadro RUC adaptado a Guía */
        .ruc-box {
            border: 2px solid #000;
            border-radius: 4px;
            text-align: center;
            padding: 5px 5px;
            background: #fff;
        }

        .ruc-label {
            font-size: 12px;
            font-weight: bold;
        }

        .doc-type-box {
            background: #000;
            color: #fff;
            padding: 3px;
            margin: 4px 0;
            font-weight: bold;
            display: block;
            font-size: 10px;
        }

        /* Cajas de Datos (Traslado, Cliente, Transportista) */
        .data-box {
            border: 1px solid #000;
            border-radius: 4px;
            padding: 6px;
            margin-bottom: 8px;
            width: 100%;
        }

        .data-label {
            font-weight: bold;
            color: #333;
        }

        /* TABLA ITEMS */
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .table-items th {
            background: #eee;
            border: 1px solid #000;
            padding: 5px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }

        .table-items td {
            border: 1px solid #ddd;
            padding: 4px;
            font-size: 9px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Pie de página Legal */
        .footer-legal {
            margin-top: 15px;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
    </style>
</head>

<body>

    {{-- ============================== HEADER ============================== --}}
    <table class="header-table">
        <tr>
            <td class="col-logo">
                @if($logoBase64)
                <img src="{{ $logoBase64 }}" style="max-width: 100px; max-height: 60px;">
                @endif
            </td>

            <td class="col-empresa">
                <div class="company-name">{{ $emisor['razon_social'] }}</div>
                <div style="font-weight: bold; margin-bottom: 2px;">{{ $guia->sucursal->nombre }} (Origen)</div>
                <div class="company-info">
                    {{ $guia->sucursal->direccion }}<br>
                    RUC: {{ $emisor['ruc'] }} | Anexo: {{ $guia->codigo_establecimiento_partida }}
                </div>
            </td>

            <td class="col-ruc">
                <div class="ruc-box">
                    <div class="ruc-label">R.U.C. {{ $emisor['ruc'] }}</div>
                    <div class="doc-type-box">GUÍA DE REMISIÓN - REMITENTE</div>
                    <div class="ruc-label">{{ $guia->serie }} - {{ str_pad($guia->numero, 8, '0', STR_PAD_LEFT) }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ============================== DESTINATARIO Y DATOS GENERALES ============================== --}}
    <div class="data-box">
        <table width="100%">
            <tr>
                <td width="50%" style="border: none;">
                    <div class="data-label">DESTINATARIO:</div>
                    @if ($guia->motivo_traslado === '04')
                    {{ $emisor['razon_social'] }} (TRASLADO INTERNO)
                    @else
                    {{ optional($guia->cliente)->nombre_completo ?? 'N/A' }}
                    @endif
                    <br>
                    <span class="data-label">RUC/DNI:</span> {{ optional($guia->cliente)->documento ?? $emisor['ruc'] }}
                </td>
                <td width="50%" style="border: none;">
                    <span class="data-label">FECHA DE EMISIÓN:</span> {{ $guia->fecha_emision->format('d/m/Y') }}<br>
                    <span class="data-label">FECHA DE INICIO TRASLADO:</span> {{ $guia->fecha_traslado->format('d/m/Y') }}<br>
                    <span class="data-label">ESTADO:</span> <span style="font-weight: bold;">{{ $guia->estado_traslado }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- ============================== TRASLADO Y TRANSPORTE ============================== --}}
    <div class="data-box">
        <table width="100%">
            <tr>
                <td width="33%" style="border: none;">
                    <span class="data-label">MOTIVO:</span> {{ $guia->motivo_traslado }} - {{ $guia->descripcion_motivo }}<br>
                    <span class="data-label">MODALIDAD:</span> {{ $guia->modalidad_traslado === '01' ? 'PÚBLICO' : 'PRIVADO' }}<br>
                    <span class="data-label">PESO TOTAL:</span> {{ number_format($guia->peso_bruto, 3) }} {{ $guia->unidad_medida ?? 'KGM' }}
                </td>

                <td width="33%" style="border: none;">
                    <div class="data-label">PUNTO DE PARTIDA:</div>
                    UBIGEO: {{ $guia->ubigeo_partida }} (Anexo: {{ $guia->codigo_establecimiento_partida }})<br>
                    DIR: {{ $guia->direccion_partida }}
                </td>

                <td width="34%" style="border: none;">
                    <div class="data-label">PUNTO DE LLEGADA:</div>
                    UBIGEO: {{ $guia->ubigeo_llegada }} (Anexo: {{ $guia->codigo_establecimiento_llegada ?? 'N/A' }})<br>
                    DIR: {{ $guia->direccion_llegada }}
                </td>
            </tr>
        </table>
    </div>

    {{-- DATOS DEL TRANSPORTISTA O CONDUCTOR --}}
    <div class="data-box">
        @if ($guia->modalidad_traslado === '01')
        <div class="data-label">TRANSPORTISTA (PÚBLICO):</div>
        Razón Social: {{ $guia->razon_social_transportista }} | RUC: {{ $guia->doc_transportista_numero }}
        @else
        <div class="data-label">UNIDAD Y CONDUCTOR (PRIVADO):</div>
        Placa: {{ $guia->placa_vehiculo ?? 'N/A' }} | DNI Chofer: {{ $guia->doc_chofer_numero ?? 'N/A' }} | Licencia: {{ $guia->licencia_conducir ?? 'N/A' }}
        @endif
    </div>

    {{-- ============================== DETALLE DE PRODUCTOS ============================== --}}
    <table class="table-items">
        <thead>
            <tr>
                <th width="10%">CÓDIGO</th>
                <th width="48%">DESCRIPCIÓN</th>
                <th width="15%">LOTE/VENC.</th>
                <th width="10%">U.M.</th>
                <th width="12%">CANTIDAD</th>
            </tr>
        </thead>
        <tbody>
            @foreach($guia->detalles as $det)
            <tr>
                <td class="text-center">{{ $det->codigo_producto ?? 'GEN' }}</td>
                <td>{{ $det->descripcion }}</td>
                <td>
                    @if($det->lote)
                    L: {{ $det->lote->codigo_lote }}<br>
                    V: {{ optional($det->lote->fecha_vencimiento)->format('d/m/Y') }}
                    @else
                    N/A
                    @endif
                </td>
                <td class="text-center">{{ $det->unidad_medida }}</td>
                <td class="text-right">{{ number_format($det->cantidad, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ============================== PIE DE PÁGINA LEGAL ============================== --}}
    <div class="footer-legal">
        <table width="100%">
            <tr>
                <td width="70%" valign="top" style="border: none; padding-left: 0;">
                    <div style="font-size: 8px; color: #666; line-height: 1.3;">
                        Representación impresa de la Guía de Remisión Remitente Electrónica.<br>
                        **Este documento no es válido como comprobante de pago.**
                    </div>
                </td>
                <td width="30%" valign="top" style="border: none; text-align: right;">
                    @if($qrBase64)
                    {{-- Placeholder para el QR que SUNAT requiere (Contiene HASH) --}}
                    <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" width="60" height="60" style="margin-right: 10px;">
                    @endif
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; text-align: center; font-size: 9px; padding-top: 5px;">
                    HASH: **{{ $guia->hash ?? 'SIN HASH SUNAT' }}** | Impreso: {{ $fecha_impresion }}
                </td>
            </tr>
        </table>
    </div>

</body>

</html>