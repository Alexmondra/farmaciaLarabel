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
                                onclick='enviarWhatsApp(
                                    @json($telefono),
                                    @json($venta->cliente->nombre_completo),
                                    @json(URL::signedRoute("publico.descargar", ["id" => $venta->id]))
                                    )'>
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
                            <button type="button" class="dropdown-item"
                                onclick='enviarCorreo(@json($venta->id), @json($email))'>
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
    {{-- VISTA TICKET (80mm) - ACTUALIZADO CON DESGLOSE --}}
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
                    <img src="{{ $logoBase64 }}" style="width: 160px;" alt="Logo">
                </div>
                @endif

                <h5 class="mb-1 mt-1">{{ $venta->sucursal->nombre }}</h5>
                <p class="mb-0">{{ $venta->sucursal->direccion }}</p>
                <p class="mb-0 font-weight-bold">RUC: {{ $config->empresa_ruc ?? $venta->sucursal->ruc }}</p>
                <p class="mb-2">Tel: {{ $venta->sucursal->telefono }}</p>
            </div>

            {{-- INFO VENTA --}}
            <div style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 5px 0; margin-bottom: 8px;">
                <div class="d-flex justify-content-between font-weight-bold">
                    <span>{{ $venta->tipo_comprobante }}</span>
                    <span>{{ $venta->serie }}-{{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="mt-1" style="font-size: 14px !important;">
                    <div>Fecha: {{ $venta->fecha_emision->format('d/m/Y H:i') }}</div>
                    <div>Pago: <b>{{ $venta->medio_pago }}</b></div>
                    <div>Cliente: {{ Str::limit($venta->cliente->nombre_completo, 30) }}</div>
                    @if($venta->cliente->documento != '00000000')
                    <div>{{ $venta->cliente->tipo_documento }}: {{ $venta->cliente->documento }}</div>
                    @endif
                </div>
            </div>

            {{-- ITEMS --}}
            <table class="table-items-ticket mb-2">
                <thead>
                    <tr>
                        <th class="text-left" style="width: 15%;">CANT</th>
                        <th class="text-left" style="width: 55%;">DESCRIPCIÓN</th>
                        <th class="text-right" style="width: 30%;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($venta->detalles as $det)
                    <tr>
                        <td class="font-weight-bold">{{ (int)$det->cantidad }}</td>
                        <td class="text-uppercase">{{ $det->medicamento->nombre }}</td>
                        <td class="text-right">{{ number_format($det->subtotal_neto, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- TOTALES DESGLOSADOS --}}
            <div style="border-top: 1px solid #000; padding-top: 8px;">
                <table style="width: 90%; margin: 0 auto; font-size: 15px !important; line-height: 1.4; border-collapse: collapse;">
                    @if($venta->op_gravada > 0)
                    <tr>
                        <td class="text-left" style="width: 60%;">OP. GRAVADA:</td>
                        <td class="text-right font-weight-bold" style="width: 40%;">S/ {{ number_format($venta->op_gravada, 2) }}</td>
                    </tr>
                    @endif

                    @if($venta->op_exonerada > 0)
                    <tr>
                        <td class="text-left">OP. EXONERADA:</td>
                        <td class="text-right font-weight-bold">S/ {{ number_format($venta->op_exonerada, 2) }}</td>
                    </tr>
                    @endif

                    <tr>
                        <td class="text-left">I.G.V. (18%):</td>
                        <td class="text-right font-weight-bold">S/ {{ number_format($venta->total_igv, 2) }}</td>
                    </tr>

                    @if($venta->total_descuento > 0)
                    <tr>
                        <td class="text-left text-danger">DESCUENTO:</td>
                        <td class="text-right font-weight-bold text-danger">- S/ {{ number_format($venta->total_descuento, 2) }}</td>
                    </tr>
                    @endif

                    <tr class="total-row-big" style="border-top: 2px solid #000;">
                        <td class="pt-2" style="font-size: 24px !important;">TOTAL:</td>
                        <td class="text-right pt-2" style="font-size: 24px !important;">S/ {{ number_format($venta->total_neto, 2) }}</td>
                    </tr>
                </table>

                <p class="text-center mt-2 mb-3 small text-uppercase" style="line-height: 1.2;">SON: {{ $montoLetras }}</p>

                {{-- QR Y PIE --}}
                <div class="text-center mt-2">
                    <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" style="width: 110px; height: 110px;">
                    <p class="mt-2 mb-0 font-weight-bold text-uppercase" style="font-size: 13px; white-space: pre-line;">
                        {{ $config->mensaje_ticket ?? 'GRACIAS POR SU PREFERENCIA' }}
                    </p>
                    <p class="mb-0 mt-1" style="font-size: 11px !important; line-height: 1.3;">
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
                        <th width="15%" class="text-right pr-4">P.UNIT</th>
                        <th width="15%" class="text-right pr-2">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($venta->detalles as $det)
                    <tr>
                        <td class="text-center">{{ $det->cantidad }}</td>
                        <td class="text-center">NIU</td>
                        <td class="pl-3">
                            <span class="font-weight-bold text-uppercase">
                                {{ optional($det->medicamento)->nombre ?? 'PRODUCTO NO DISPONIBLE' }}
                            </span>
                            @if(optional($det->medicamento)->presentacion)
                            <br><small class="text-muted">{{ optional($det->medicamento)->presentacion }}</small>
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
    /* ==========================================================
       1) ADAPTACIÓN INTERFAZ (DARK/LIGHT MODE)
    ========================================================== */
    .card-glass {
        position: relative;
        /* Z-index alto para que el dropdown no se corte */
        z-index: 1020 !important;
        /* Usamos variables de AdminLTE para el fondo */
        background-color: var(--lte-card-bg, #fff) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
    }

    /* Solución definitiva para el menú Enviar que se corta */
    .dropdown-menu {
        z-index: 2000 !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2) !important;
    }

    .view-container {
        position: relative;
        z-index: 1;
        /* Menor que la barra de acciones */
    }

    /* Estilos para el modo oscuro en la vista previa */
    .dark-mode .ticket-box,
    .dark-mode .a4-box {
        color: #000 !important;
        /* El comprobante siempre se lee negro en pantalla */
        background-color: #fff !important;
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

    /* ==========================================================
       2) TICKET (80mm) - DISEÑO
    ========================================================== */
    .ticket-box {
        width: 80mm;
        background: #fff;
        padding: 10px 2px;
        font-family: 'Arial Narrow', Arial, sans-serif;
        position: relative;
        overflow: hidden;
        box-sizing: border-box;
    }

    .table-items-ticket {
        width: 100%;
        border-collapse: collapse;
    }

    .table-items-ticket td {
        font-size: 15px !important;
        padding: 4px 0;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .total-row-big td {
        font-size: 24px !important;
        font-weight: 900 !important;
        padding-top: 10px;
    }

    #wrapper-ticket .watermark-ticket {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 60px;
        color: rgba(255, 0, 0, 0.12) !important;
        border: 6px solid rgba(255, 0, 0, 0.12);
        padding: 10px;
        z-index: 100;
        font-weight: bold;
        white-space: nowrap;
        pointer-events: none;
    }

    /* ==========================================================
       3) A4 - DISEÑO (RECUPERACIÓN DE BORDES)
    ========================================================== */
    .a4-box {
        width: 210mm;
        height: 297mm;
        padding: 15mm;
        background: #fff;
        position: relative;
        box-sizing: border-box;
    }

    .ruc-box {
        border: 2px solid #000 !important;
        border-radius: 8px;
        text-align: center;
        padding: 10px;
    }

    .doc-type-box {
        background: #000 !important;
        color: #fff !important;
        padding: 5px;
        margin: 6px 0;
        font-weight: bold;
    }

    .table-items {
        width: 100%;
        border-collapse: collapse !important;
    }

    .table-items thead th {
        background: #eee !important;
        border: 1px solid #000 !important;
        padding: 8px;
        font-size: 12px;
        text-align: center;
    }

    .table-items tbody td {
        border: 1px solid #ddd !important;
        padding: 8px;
        font-size: 12px;
    }

    .footer-print-area {
        position: absolute;
        bottom: 15mm;
        left: 15mm;
        right: 15mm;
    }

    #wrapper-a4 .watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        font-size: 120px;
        color: rgba(220, 53, 69, 0.1);
        font-weight: bold;
    }

    /* ==========================================================
       4) IMPRESIÓN (BLINDADA CONTRA MODO OSCURO)
    ========================================================== */
    @page {
        margin: 0;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        /* Aseguramos que el papel siempre sea blanco incluso en modo oscuro */
        html,
        body,
        .ticket-box,
        .a4-box {
            background-color: #fff !important;
            color: #000 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body * {
            visibility: hidden !important;
        }

        /* Visibilidad de los comprobantes */
        #wrapper-ticket:not(.d-none),
        #wrapper-ticket:not(.d-none) *,
        #wrapper-a4:not(.d-none),
        #wrapper-a4:not(.d-none) * {
            visibility: visible !important;
        }

        #wrapper-a4:not(.d-none) * {
            border-color: #000 !important;
        }

        #wrapper-ticket:not(.d-none),
        #wrapper-a4:not(.d-none) {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
        }

        #wrapper-ticket.d-flex,
        #wrapper-a4.d-flex {
            display: block !important;
        }

        #wrapper-ticket:not(.d-none) .ticket-box {
            width: 76mm !important;
            margin: 0 auto !important;
        }

        #wrapper-a4:not(.d-none) .a4-box {
            width: 210mm !important;
            height: 297mm !important;
            margin: 0 !important;
        }

        table,
        tr,
        td,
        th {
            page-break-inside: avoid !important;
        }
    }
</style>
@stop