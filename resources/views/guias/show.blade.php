<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Guía Remisión {{ $guia->serie }}-{{ $guia->numero }}</title>
    <style>
        @page {
            margin: 0cm 0cm;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            /* Márgenes para no chocar con header/footer fijos */
            margin-top: 3.2cm;
            margin-bottom: 3.5cm;
            margin-left: 1cm;
            margin-right: 1cm;
            color: #333;
            background: #fff;
            font-size: 11px;
            /* Letra legible */
        }

        /* ================= HEADER ================= */
        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 3cm;
            padding: 0.5cm 1cm;
            background-color: #fff;
            z-index: 1000;
        }

        /* Logo y Datos Empresa */
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .company-details {
            font-size: 9px;
            color: #555;
            line-height: 1.3;
            margin-top: 4px;
        }

        /* Cuadro RUC */
        .ruc-box {
            border: 2px solid #333;
            /* Borde más grueso */
            border-radius: 8px;
            text-align: center;
            padding: 8px 0;
            background: #fff;
        }

        .ruc-number {
            font-size: 14px;
            font-weight: bold;
        }

        .ruc-title {
            background-color: #eee;
            color: #000;
            font-weight: bold;
            font-size: 13px;
            padding: 5px 0;
            margin: 5px 0;
            display: block;
            width: 100%;
        }

        /* ================= FOOTER ================= */
        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 3cm;
            padding: 0cm 1cm 0.5cm 1cm;
            background-color: #fff;
            border-top: 1px solid #ccc;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .obs-container {
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            padding: 5px;
            border-radius: 4px;
            font-size: 9px;
            height: 60px;
            /* Altura fija para mantener orden */
        }

        .legal-text {
            font-size: 8px;
            color: #777;
            margin-top: 5px;
        }

        /* QR y Hash */
        .qr-section {
            text-align: right;
            vertical-align: top;
        }

        .hash-text {
            font-size: 8px;
            font-family: 'Courier New', Courier, monospace;
            color: #555;
            margin-top: 2px;
            word-break: break-all;
        }

        /* ================= CUERPO ================= */

        /* Títulos de Sección (Barras Grises) */
        .section-header {
            background-color: #eee;
            color: #000;
            font-weight: bold;
            font-size: 10px;
            padding: 4px 6px;
            border: 1px solid #ccc;
            margin-top: 10px;
            text-transform: uppercase;
        }

        /* Tablas de Información */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ccc;
            /* Borde alrededor del bloque */
            border-top: none;
            /* Ya tiene el header arriba */
            font-size: 10px;
        }

        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            color: #444;
        }

        /* Tabla de Items */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10px;
        }

        .items-table th {
            background-color: #333;
            /* Encabezado negro */
            color: #fff;
            padding: 6px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        .items-table td {
            border: 1px solid #ddd;
            padding: 6px;
            vertical-align: middle;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* Utilidades */
        .w-50 {
            width: 50%;
        }

        .w-100 {
            width: 100%;
        }

        .border-right {
            border-right: 1px solid #ccc;
        }
    </style>
</head>

<body>

    {{-- HEADER FIJO --}}
    <header>
        <table class="header-table">
            <tr>
                <td width="25%" valign="top">
                    @if(isset($logoBase64) && $logoBase64)
                    <img src="{{ $logoBase64 }}" style="max-width: 160px; max-height: 80px;">
                    @else
                    {{-- Placeholder si no hay logo --}}
                    <div style="font-weight: bold; font-size: 20px; color: #ccc;">SIN LOGO</div>
                    @endif
                </td>

                <td width="45%" valign="top" align="center">
                    <div class="company-name">{{ $empresa->razon_social ?? 'MI FARMACIA S.A.C.' }}</div>
                    <div class="company-details">
                        {{ $guia->sucursal->direccion }}<br>
                        {{ $guia->sucursal->distrito ?? '' }} - {{ $guia->sucursal->provincia ?? '' }}<br>
                        Tel: {{ $guia->sucursal->telefono ?? '-' }} | Email: {{ $guia->sucursal->email ?? '-' }}
                    </div>
                </td>

                <td width="30%" valign="top">
                    <div class="ruc-box">
                        <div class="ruc-number">R.U.C. {{ $empresa->empresa_ruc ?? '20000000001' }}</div>
                        <div class="ruc-title">GUÍA DE REMISIÓN<br>REMITENTE</div>
                        <div class="ruc-number">{{ $guia->serie }} - {{ str_pad($guia->numero, 8, '0', STR_PAD_LEFT) }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </header>

    {{-- FOOTER FIJO --}}
    <footer>
        <table class="footer-table">
            <tr>
                <td width="70%" valign="top" style="padding-right: 15px;">
                    <div style="font-weight: bold; font-size: 9px; margin-bottom: 2px;">OBSERVACIONES:</div>
                    <div class="obs-container">
                        {{ $guia->observaciones ?: 'Sin observaciones.' }}
                    </div>

                    <div class="legal-text">
                        Representación impresa de la <strong>GUÍA DE REMISIÓN ELECTRÓNICA</strong>.<br>
                        Consulte la validez de este documento en el portal de la SUNAT.<br>
                        Emitido por Software de Farmacia.
                    </div>
                </td>

                <td width="30%" class="qr-section">
                    @if(isset($qrBase64) && $qrBase64)
                    <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" width="90" height="90" style="border: 1px solid #333; padding: 2px;">
                    @else
                    <div style="width: 90px; height: 90px; border: 1px dashed #ccc; display: inline-block;"></div>
                    @endif

                    <div class="hash-text">
                        <strong>HASH:</strong> {{ $guia->hash ?? '---' }}
                    </div>
                </td>
            </tr>
        </table>
    </footer>

    {{-- CONTENIDO PRINCIPAL --}}
    <main>

        <div class="section-header">DATOS DE TRASLADO Y DESTINATARIO</div>
        <table class="info-table">
            <tr>
                <td width="60%" class="border-right">
                    <div class="label">DESTINATARIO:</div>
                    <div>
                        @if ($guia->motivo_traslado === '04')
                        {{ $empresa->razon_social ?? $guia->sucursal->nombre }} (TRASLADO INTERNO)
                        @else
                        {{ optional($guia->cliente)->nombre_completo ?? 'VARIOS' }}
                        @endif
                    </div>
                    <div style="margin-top: 4px;">
                        <span class="label">RUC/DNI:</span>
                        {{ optional($guia->cliente)->documento ?? ($empresa->empresa_ruc ?? '-') }}
                    </div>
                </td>
                <td width="40%">
                    <div><span class="label">Fecha Emisión:</span> {{ $guia->fecha_emision->format('d/m/Y') }}</div>
                    <div><span class="label">Fecha Traslado:</span> {{ $guia->fecha_traslado->format('d/m/Y') }}</div>
                    <div style="margin-top: 4px;"><span class="label">Motivo:</span> {{ $guia->descripcion_motivo }}</div>
                </td>
            </tr>
        </table>

        <div class="section-header">PUNTOS DE PARTIDA Y LLEGADA</div>
        <table class="info-table">
            <tr>
                <td width="50%" class="border-right">
                    <div class="label">PUNTO DE PARTIDA (UBIGEO: {{ $guia->ubigeo_partida }})</div>
                    <div>{{ $guia->direccion_partida }}</div>
                </td>
                <td width="50%">
                    <div class="label">PUNTO DE LLEGADA (UBIGEO: {{ $guia->ubigeo_llegada }})</div>
                    <div>{{ $guia->direccion_llegada }}</div>
                </td>
            </tr>
        </table>

        <div class="section-header">DATOS DEL TRANSPORTE ({{ $guia->modalidad_traslado === '01' ? 'PÚBLICO' : 'PRIVADO' }})</div>
        <table class="info-table">
            @if ($guia->modalidad_traslado === '01')
            <tr>
                <td width="60%"><span class="label">Empresa Transporte:</span> {{ $guia->razon_social_transportista }}</td>
                <td width="40%"><span class="label">RUC:</span> {{ $guia->doc_transportista_numero }}</td>
            </tr>
            @else
            <tr>
                <td><span class="label">Conductor:</span> {{ $guia->nombre_chofer }} <br> <span class="label">Licencia:</span> {{ $guia->licencia_conducir }}</td>
                <td><span class="label">DNI:</span> {{ $guia->doc_chofer_numero }}</td>
                <td><span class="label">Placa Vehículo:</span> {{ $guia->placa_vehiculo }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="3" style="border-top: 1px dotted #ccc;">
                    <span class="label">Peso Bruto Total:</span> {{ number_format($guia->peso_bruto, 3) }} KGM
                    &nbsp;|&nbsp;
                    <span class="label">N° Bultos:</span> {{ $guia->numero_bultos ?? 1 }}
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="10%">CÓDIGO</th>
                    <th width="10%">CANT.</th>
                    <th width="10%">UND</th>
                    <th width="70%" style="text-align: left;">DESCRIPCIÓN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($guia->detalles as $det)
                <tr>
                    <td class="text-center">{{ $det->codigo_producto ?? 'GEN' }}</td>
                    <td class="text-center">{{ number_format($det->cantidad, 2) }}</td>
                    <td class="text-center">{{ $det->unidad_medida }}</td>
                    <td>
                        {{ $det->descripcion }}
                        @if($det->lote)
                        <br>
                        <span style="font-size: 9px; color: #666;">
                            LOTE: {{ $det->lote->codigo_lote }}
                            @if($det->lote->fecha_vencimiento)
                            | VENC: {{ $det->lote->fecha_vencimiento->format('d/m/Y') }}
                            @endif
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 20px; color: #777;">
                        No hay items registrados en esta guía.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </main>

</body>

</html>