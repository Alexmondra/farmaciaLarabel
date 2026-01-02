@extends('adminlte::page')

@section('title', 'Comprobante Venta')

@section('content_header')
{{-- Cabecera oculta al imprimir --}}
<div class="no-print animate__animated animate__fadeInDown">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-bold">
            <i class="fas fa-receipt text-teal mr-2"></i>
            <span class="d-none d-sm-inline">Comprobante:</span>
            {{ $venta->tipo_comprobante }}
            <span class="text-muted" style="font-size: 0.8em;">{{ $venta->serie }}-{{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}</span>
        </h1>
        <div>
            <a href="{{ route('ventas.create') }}" class="btn btn-primary btn-lg shadow-sm">
                <i class="fas fa-cash-register mr-1"></i> <span class="d-none d-md-inline">Nueva Venta</span>
            </a>
        </div>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid pb-5 animate__animated animate__fadeIn">

    {{-- === BARRA DE ACCIONES === --}}
    <div class="card shadow-lg no-print mb-4 border-0 card-glass">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-4 mb-2 mb-md-0">
                    <div class="btn-group w-100 shadow-sm custom-toggle" role="group">
                        <button type="button" class="btn btn-outline-dark active-view" id="btn-ticket" onclick="cambiarVista('ticket')">
                            <i class="fas fa-receipt mr-2"></i> Ticket
                        </button>
                        <button type="button" class="btn btn-outline-dark" id="btn-a4" onclick="cambiarVista('a4')">
                            <i class="far fa-file-pdf mr-2"></i> Hoja A4
                        </button>
                    </div>
                </div>

                <div class="col-md-8 text-right">
                    {{-- Botón Enviar Inteligente --}}
                    <div class="btn-group mr-2">
                        <button type="button" class="btn btn-info shadow-sm dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-share-alt mr-1"></i> Enviar
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            @php
                            $telefono = preg_replace('/[^0-9]/', '', $venta->cliente->telefono);
                            $tieneWsp = !empty($telefono) && strlen($telefono) >= 9;
                            @endphp

                            @if($tieneWsp)
                            <button type="button" class="dropdown-item"
                                onclick="enviarWhatsApp(
                                        '{{ $telefono }}', 
                                        '{{ $venta->cliente->nombre_completo }}', 
                                        '{{ URL::signedRoute('publico.descargar', ['id' => $venta->id]) }}'
                                    )">
                                <i class="fab fa-whatsapp text-success mr-2"></i> Enviar a WhatsApp
                            </button>
                            @else
                            <a href="#" class="dropdown-item disabled text-muted" title="El cliente no tiene teléfono registrado">
                                <i class="fab fa-whatsapp text-secondary mr-2"></i> WhatsApp (Sin Número)
                            </a>
                            @endif

                            <div class="dropdown-divider"></div>
                            @php
                            $email = $venta->cliente->email;
                            $tieneEmail = !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
                            @endphp

                            @if($tieneEmail)
                            <button type="button" class="dropdown-item" onclick="enviarCorreo('{{ $venta->id }}', '{{ $email }}')">
                                <i class="fas fa-envelope text-primary mr-2"></i> Enviar a Correo
                            </button>
                            @else
                            <a href="#" class="dropdown-item disabled text-muted" title="El cliente no tiene email registrado">
                                <i class="fas fa-envelope text-secondary mr-2"></i> Correo (Sin Email)
                            </a>
                            @endif
                        </div>
                    </div>

                    {{-- Botón Imprimir --}}
                    <button onclick="window.print()" class="btn btn-danger btn-lg font-weight-bold shadow px-4 pulse-btn">
                        <i class="fas fa-print mr-2"></i> IMPRIMIR
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================= --}}
    {{-- VISTA TICKET (80mm) --}}
    {{-- ======================================================= --}}
    <div id="wrapper-ticket" class="d-flex justify-content-center view-container">
        <div class="ticket-box elevation-3">
            @if($venta->estado == 'ANULADO')
            <div class="watermark-ticket">ANULADO</div>
            @endif

            {{-- CABECERA --}}
            <div class="text-center mb-2 mt-1">
                @if(isset($logoBase64) && !empty($logoBase64))
                <div style="width: 100%; text-align: center; margin-bottom: 5px;">
                    <img src="{{ $logoBase64 }}" class="ticket-logo" alt="Logo">
                </div>
                @endif

                <h5 class="mb-1 mt-1">{{ $venta->sucursal->nombre }}</h5>
                <p class="mb-0 small">{{ $venta->sucursal->direccion }}</p>
                <p class="mb-0 small font-weight-bold">RUC: {{ $config->empresa_ruc ?? $venta->sucursal->ruc }}</p>
                <p class="mb-0 small">Tel: {{ $venta->sucursal->telefono }}</p>
            </div>

            {{-- INFO VENTA --}}
            <div class="border-top border-bottom border-dark py-2 mb-2">
                <div class="d-flex justify-content-between font-weight-bold small">
                    <span>{{ $venta->tipo_comprobante }}</span>
                    <span>{{ $venta->serie }}-{{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="mt-1 small" style="line-height: 1.3;">
                    <div>Fecha: {{ $venta->fecha_emision->format('d/m/Y H:i') }}</div>
                    <div>Pago: <b>{{ $venta->medio_pago }}</b></div>
                    <div>Cliente: {{ Str::limit($venta->cliente->nombre_completo, 22) }}</div>
                    @if($venta->cliente->documento != '00000000')
                    <div>{{ $venta->cliente->tipo_documento }}: {{ $venta->cliente->documento }}</div>
                    @endif
                </div>
            </div>

            {{-- ITEMS --}}
            <table class="table table-sm table-borderless mb-2 w-100">
                <thead class="border-bottom border-dark">
                    <tr class="small text-uppercase">
                        <th class="pl-0 text-left" style="width: 15%; padding-bottom: 5px;">Cant</th>
                        <th class="text-left" style="width: 55%; padding-bottom: 5px;">Descrip.</th>
                        <th class="text-right pr-0" style="width: 30%; padding-bottom: 5px;">Total</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @foreach($venta->detalles as $det)
                    <tr>
                        <td class="pl-0 text-left align-top font-weight-bold pt-1">{{ $det->cantidad }}</td>
                        <td class="text-left align-top pt-1">{{ Str::limit($det->medicamento->nombre, 18) }}</td>
                        <td class="text-right pr-0 align-top pt-1">{{ number_format($det->subtotal_neto, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- TOTALES --}}
            <div class="border-top border-dark pt-2">
                <table class="w-100 mb-2 small" style="line-height: 1.2;">
                    @if($venta->op_gravada > 0)
                    <tr>
                        <td class="text-right pr-2">OP. GRAVADA:</td>
                        <td class="text-right font-weight-bold">{{ number_format($venta->op_gravada, 2) }}</td>
                    </tr>
                    @endif

                    @if($venta->op_exonerada > 0)
                    <tr>
                        <td class="text-right pr-2">OP. EXONERADA:</td>
                        <td class="text-right font-weight-bold">{{ number_format($venta->op_exonerada, 2) }}</td>
                    </tr>
                    @endif

                    <tr>
                        <td class="text-right pr-2">IGV (18%):</td>
                        <td class="text-right font-weight-bold">{{ number_format($venta->total_igv, 2) }}</td>
                    </tr>

                    @if($venta->total_descuento > 0)
                    <tr>
                        <td class="text-right pr-2">DESCUENTO:</td>
                        <td class="text-right font-weight-bold">-{{ number_format($venta->total_descuento, 2) }}</td>
                    </tr>
                    @endif

                    <tr class="total-row">
                        <td class="text-right pr-2 pt-2" style="font-size: 1.1em;"><b>TOTAL:</b></td>
                        <td class="text-right pt-2 text-nowrap" style="font-size: 1.1em;"><b>S/ {{ number_format($venta->total_neto, 2) }}</b></td>
                    </tr>
                </table>

                <p class="text-center mt-2 mb-3 small text-uppercase" style="font-size: 12px !important; line-height: 1.2;">SON: {{ $montoLetras }}</p>

                <div class="text-center mt-2">
                    <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" style="width: 95px; height: 95px;">
                    <p class="mt-2 mb-0 font-weight-bold text-uppercase" style="font-size: 12px; white-space: pre-line;">
                        {{ $config->mensaje_ticket ?? 'GRACIAS POR SU PREFERENCIA' }}
                    </p>
                    <p class="mb-0 mt-1 legal-text" style="line-height: 1.3;">
                        Representación impresa de la<br>
                        <strong class="text-uppercase">{{ $venta->tipo_comprobante }} ELECTRÓNICA</strong><br>
                        Revisar en: <b>mundofarma.online/consultar</b>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================= --}}
    {{-- VISTA A4 (Sin cambios solicitados, se mantiene igual) --}}
    {{-- ======================================================= --}}
    <div id="wrapper-a4" class="d-none justify-content-center view-container">
        <div class="a4-box bg-white elevation-3 position-relative">
            @if($venta->estado == 'ANULADO')
            <div class="watermark">ANULADO</div>
            @endif

            <table class="w-100 mb-4">
                <tr>
                    <td width="20%" class="align-middle">
                        @if(isset($logoBase64))
                        <img src="{{ $logoBase64 }}" style="max-width: 120px; max-height: 80px;">
                        @endif
                    </td>
                    <td width="50%" class="text-center align-middle">
                        <div class="h5 font-weight-bold text-uppercase mb-1">{{ $config->empresa_razon_social ?? $venta->sucursal->nombre }}</div>
                        <div class="font-weight-bold text-secondary mb-1">{{ $venta->sucursal->nombre }}</div>
                        <div class="small text-muted" style="line-height: 1.3;">
                            {{ $venta->sucursal->direccion }}<br>
                            Tel: {{ $venta->sucursal->telefono }} - {{ $venta->sucursal->email }}
                        </div>
                    </td>
                    <td width="30%" class="align-top text-right">
                        <div class="ruc-box">
                            <div class="h5 font-weight-bold mb-1">R.U.C. {{ $config->empresa_ruc ?? $venta->sucursal->ruc }}</div>
                            <div class="doc-type-box">{{ $venta->tipo_comprobante }} ELECTRÓNICA</div>
                            <div class="h5 font-weight-bold mb-0">{{ $venta->serie }} - {{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}</div>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="client-box mb-4">
                <div class="row m-0">
                    <div class="col-8 pl-0">
                        <span class="font-weight-bold">Cliente:</span> {{ $venta->cliente->nombre_completo }}<br>
                        <span class="font-weight-bold">{{ $venta->cliente->tipo_documento }}:</span> {{ $venta->cliente->documento }}<br>
                        @if(!empty($venta->cliente->direccion) && $venta->cliente->direccion != '-')
                        <span class="font-weight-bold">Dirección:</span> {{ Str::limit($venta->cliente->direccion, 80) }}
                        @endif
                    </div>
                    <div class="col-4 pr-0 border-left">
                        <span class="font-weight-bold">Fecha:</span> {{ $venta->fecha_emision->format('d/m/Y H:i:s') }}<br>
                        <span class="font-weight-bold">Pago:</span> {{ $venta->medio_pago }}<br>
                        <span class="font-weight-bold">Moneda:</span> SOLES
                    </div>
                </div>
            </div>

            <table class="table-items w-100 mb-4">
                <thead>
                    <tr>
                        <th width="8%">CANT</th>
                        <th width="10%">UND</th>
                        <th class="text-left pl-3">DESCRIPCIÓN</th>
                        <th width="15%" class="text-right pr-4">P.UNIT</th> {{-- Alineado derecha con padding --}}
                        <th width="15%" class="text-right pr-2">TOTAL</th> {{-- Alineado derecha con padding --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach($venta->detalles as $det)
                    <tr>
                        <td class="text-center">{{ $det->cantidad }}</td>
                        <td class="text-center">NIU</td>
                        <td class="pl-3">
                            <span class="font-weight-bold text-uppercase">{{ $det->medicamento->nombre }}</span>
                            @if($det->medicamento->presentacion)
                            <br><small class="text-muted">{{ $det->medicamento->presentacion }}</small>
                            @endif
                        </td>
                        <td class="text-right pr-4">{{ number_format($det->precio_unitario, 2) }}</td>
                        <td class="text-right pr-2 font-weight-bold">{{ number_format($det->subtotal_neto, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="footer-print-area">
                <div class="row m-0">
                    <div class="col-8 pl-0">
                        <div class="d-flex">
                            <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" style="width: 85px; height: 85px;">
                            <div class="ml-3 mt-1">
                                <div class="font-weight-bold small mb-1">{{ $montoLetras }}</div>
                                <div style="font-size: 10px; color: #666; line-height: 1.4;">
                                    Representación impresa de la {{ $venta->tipo_comprobante }} ELECTRÓNICA.<br>
                                    Autorizado mediante Resolución N.° 300-2014/SUNAT.<br>
                                    Consulte validez en: <a href="https://mundofarma.online/consultar" class="text-dark font-weight-bold text-decoration-none">mundofarma.online/consultar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4 pr-0">
                        <table class="w-100 small mb-2">
                            @if($venta->op_gravada > 0)
                            <tr>
                                <td class="text-right font-weight-bold">OP. GRAVADA:</td>
                                <td class="text-right">S/ {{ number_format($venta->op_gravada, 2) }}</td>
                            </tr>
                            @endif
                            @if($venta->op_exonerada > 0)
                            <tr>
                                <td class="text-right font-weight-bold">OP. EXONERADA:</td>
                                <td class="text-right">S/ {{ number_format($venta->op_exonerada, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-right font-weight-bold">I.G.V. (18%):</td>
                                <td class="text-right">S/ {{ number_format($venta->total_igv, 2) }}</td>
                            </tr>
                            @if($venta->total_descuento > 0)
                            <tr>
                                <td class="text-right font-weight-bold text-danger">DESCUENTO:</td>
                                <td class="text-right text-danger">- S/ {{ number_format($venta->total_descuento, 2) }}</td>
                            </tr>
                            @endif
                        </table>
                        <div class="bg-dark text-white p-2 d-flex justify-content-between font-weight-bold rounded">
                            <span>TOTAL:</span>
                            <span>S/ {{ number_format($venta->total_neto, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            window.print();
        }
    });

    function cambiarVista(tipo) {
        if (tipo === 'ticket') {
            $('#wrapper-ticket').removeClass('d-none').addClass('d-flex');
            $('#wrapper-a4').addClass('d-none').removeClass('d-flex');
            $('#btn-ticket').addClass('active-view').removeClass('btn-outline-dark').addClass('btn-dark');
            $('#btn-a4').removeClass('active-view').addClass('btn-outline-dark').removeClass('btn-dark');
        } else {
            $('#wrapper-ticket').addClass('d-none').removeClass('d-flex');
            $('#wrapper-a4').removeClass('d-none').addClass('d-flex');
            $('#btn-ticket').removeClass('active-view').addClass('btn-outline-dark').removeClass('btn-dark');
            $('#btn-a4').addClass('active-view').removeClass('btn-outline-dark').addClass('btn-dark');
        }
    }

    function enviarWhatsApp(numero, nombre, urlPdf) {
        let codigoPais = '51';
        let mensaje = `Hola *${nombre}*, gracias por tu compra.\n\nPuedes descargar tu comprobante electrónico aquí:\n${urlPdf}`;
        let textoEncode = encodeURIComponent(mensaje);
        let url = `https://wa.me/${codigoPais}${numero}?text=${textoEncode}`;
        window.open(url, '_blank');
    }

    function enviarCorreo(ventaId, emailDestino) {
        Swal.fire({
            title: '¿Enviar comprobante?',
            text: `Se enviará al correo: ${emailDestino}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, enviar ahora',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Enviando...',
                    text: 'Conectando con el servidor de correo',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });
                $.ajax({
                    url: `/ventas/${ventaId}/enviar-email`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire('¡Enviado!', 'El correo ha sido enviado correctamente.', 'success');
                    },
                    error: function(xhr) {
                        let msg = 'Ocurrió un error al enviar.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    }
</script>
@stop

@section('css')
<style>
    .ticket-box {
        width: 80mm;
        min-height: 200px;
        background: #fff;
        padding: 22px 15px;
        border-radius: 8px;
        color: #000;
        position: relative;
        /* VITAL para que la marca de agua se centre aquí */
        overflow: hidden;
        /* Evita que la palabra 'ANULADO' se salga del papel */
    }

    /* MARCA DE AGUA TICKET CENTRADA */
    .watermark-ticket {
        position: absolute;
        top: 50%;
        /* Centrado vertical */
        left: 50%;
        /* Centrado horizontal */
        transform: translate(-50%, -50%) rotate(-45deg);
        /* Centrado perfecto y rotación */
        font-size: 55px;
        /* Tamaño ajustado para no desbordar */
        color: rgba(0, 0, 0, 0.1) !important;
        z-index: 1000;
        pointer-events: none;
        font-weight: bold;
        text-transform: uppercase;
        border: 5px solid rgba(0, 0, 0, 0.1);
        padding: 5px 15px;
        white-space: nowrap;
    }

    @media print {

        .watermark,
        .watermark-ticket {
            display: block !important;
            color: #d0d0d0 !important;
            /* Gris claro que la térmica reconoce */
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .table-items th.text-right,
        .table-items td.text-right {
            text-align: right !important;
        }
    }

    :root {
        --paper-bg: #fff;
        --paper-text: #000;
    }

    .dark-mode .card-glass {
        background: rgba(52, 58, 64, 0.9);
        backdrop-filter: blur(10px);
    }

    .card-glass {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        transition: all 0.3s;
    }

    .pulse-btn {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
        }
    }

    /* ESTILOS COMUNES */
    .ruc-box {
        border: 2px solid #000;
        border-radius: 8px;
        text-align: center;
        padding: 10px;
        background: #fff;
        width: 100%;
        color: #000;
    }

    .doc-type-box {
        background: #000;
        color: #fff;
        padding: 5px;
        margin: 6px 0;
        font-weight: bold;
        display: block;
    }

    .client-box {
        border: 1px solid #ccc;
        border-radius: 5px;
        padding: 10px;
        font-size: 11px;
        color: #333;
    }

    .table-items thead th {
        background: #eee;
        border-bottom: 2px solid #000;
        padding: 8px;
        font-size: 10px;
        font-weight: bold;
        text-align: center;
        color: #000;
    }

    .table-items td {
        border-bottom: 1px solid #eee;
        padding: 8px;
        font-size: 11px;
        vertical-align: middle;
        color: #000;
    }

    .footer-print-area {
        position: absolute;
        bottom: 15mm;
        left: 15mm;
        right: 15mm;
    }

    /* ESTILOS TICKET (80mm) */
    .ticket-box {
        width: 80mm;
        min-height: 200px;
        background: #fff;
        /* AJUSTE: Aumentado el padding superior e inferior para dar "aire" */
        padding: 22px 15px;
        border-radius: 8px;
        color: #000;
        position: relative;
    }

    .ticket-logo {
        max-width: 50%;
        height: auto;
        display: block;
        margin: 0 auto;
    }

    .legal-text {
        font-size: 9px;
        color: #555;
    }

    /* ESTILOS A4 */
    .a4-box {
        width: 210mm;
        height: 297mm;
        padding: 15mm;
        background: #fff;
        color: #000;
        overflow: hidden;
    }

    .watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        font-size: 10rem;
        color: rgba(220, 53, 69, 0.15);
        z-index: 0;
        pointer-events: none;
        font-weight: bold;
    }

    /* ESTILOS DE IMPRESIÓN */
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: #fff !important;
            color: #000 !important;
        }

        .view-container {
            display: flex !important;
            justify-content: center !important;
            align-items: flex-start !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Si es TICKET */
        #wrapper-ticket.d-flex .ticket-box {
            box-shadow: none !important;
            border-radius: 0 !important;
            width: 80mm !important;
            /* Mantenemos el padding extra en la impresión */
            padding: 20px 5px !important;
            margin: 0 auto !important;
            page-break-after: always;
        }

        /* Si es A4 */
        #wrapper-a4.d-flex .a4-box {
            box-shadow: none !important;
            width: 210mm !important;
            height: 297mm !important;
            padding: 15mm !important;
            margin: 0 auto !important;
            page-break-after: always;
        }
    }

    /* Modo oscuro ajuste */
    .dark-mode .ticket-box,
    .dark-mode .a4-box {
        background: #fff !important;
        color: #000 !important;
    }

    .dark-mode .ticket-logo {
        filter: none !important;
    }
</style>
@stop