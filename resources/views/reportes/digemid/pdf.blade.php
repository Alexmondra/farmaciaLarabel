<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Reporte DIGEMID</title>

    <style>
        @page {
            margin: 18px 18px 26px 18px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111;
        }

        h2 {
            margin: 0 0 8px 0;
        }

        .meta {
            font-size: 9px;
            margin-bottom: 10px;
            color: #444;
        }

        .warn {
            border: 1px solid #f59e0b;
            background: #fffbeb;
            color: #92400e;
            padding: 8px 10px;
            border-radius: 6px;
            margin: 0 0 10px 0;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 5px;
            vertical-align: top;
            word-wrap: break-word;
            white-space: normal;
        }

        th {
            background: #f2f2f2;
            text-transform: uppercase;
            font-size: 9px;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 10px;
            display: inline-block;
        }

        .ok {
            background: #d1fae5;
        }

        .bad {
            background: #fee2e2;
        }

        /* ✅ clave: repetir encabezado en cada hoja */
        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        /* evita cortes feos (si se puede) */
        tr {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
    <h2>Monitor DIGEMID</h2>

    <div class="meta">
        Generado: {{ now()->format('Y-m-d H:i:s') }}
        &nbsp;|&nbsp;
        Total filas: {{ $resultados->count() }}
    </div>

    {{-- ✅ Advertencia si hay muchas filas (pero igual exporta normal) --}}
    @php
    $limitePdf = $limitePdf ?? 500;
    $totalFilas = $resultados->count();
    @endphp

    @if($totalFilas > $limitePdf)
    <div class="warn">
        <b>Advertencia:</b> Este PDF contiene <b>{{ $totalFilas }}</b> filas.
        Para reportes grandes se recomienda exportar en <b>Excel</b> (más rápido y liviano).
        Igual se generó el PDF normalmente.
    </div>
    @endif

    <table>
        <thead>
            <tr>
                @foreach($colsSeleccionadas as $key)
                <th>{{ $columnasDisponibles[$key] ?? $key }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($resultados as $row)
            <tr>
                @foreach($colsSeleccionadas as $key)
                <td>
                    @switch($key)
                    @case('cod_establecimiento')
                    {{ $row->sucursal_cod_digemid ?? 'S/N' }}
                    @break

                    @case('codigo_digemid')
                    {{ $row->medicamento->codigo_digemid ?? '--' }}
                    @break

                    @case('precio_venta')
                    S/ {{ number_format($row->precio_venta ?? 0, 2) }}
                    @break

                    @case('stock_computado')
                    <span class="badge {{ ($row->stock_computado ?? 0) > 0 ? 'ok' : 'bad' }}">
                        {{ $row->stock_computado ?? 0 }}
                    </span>
                    @break

                    @case('estado')
                    {{ $row->activo ? 'Activo' : 'Inactivo' }}
                    @break

                    @default
                    {{ $row->medicamento->$key ?? $row->$key ?? '--' }}
                    @endswitch
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ✅ Paginación real: se posiciona según ancho/alto del PDF (siempre visible) --}}
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";

            // medidas reales del papel
            $w = $pdf->get_width();
            $h = $pdf->get_height();

            // cerca a la esquina inferior derecha (ajusta si quieres)
            $x = $w - 110;
            $y = $h - 18;

            // fuente segura
            $font = null;
            if (isset($fontMetrics)) {
                $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            }

            $pdf->page_text($x, $y, $text, $font, 8, array(0,0,0));
        }
    </script>

</body>

</html>