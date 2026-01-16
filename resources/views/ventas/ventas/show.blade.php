@extends('adminlte::page')

@section('title', 'Comprobante Venta')

@section('content_header')
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

    {{-- === BARRA DE HERRAMIENTAS === --}}
    <div class="card shadow-lg no-print mb-4 border-0 card-glass">
        <div class="card-body p-3">
            <div class="row align-items-center">
                {{-- Selector Visual --}}
                <div class="col-md-4 mb-2 mb-md-0">
                    <div class="btn-group w-100 shadow-sm custom-toggle" role="group">
                        <button type="button" class="btn btn-dark active-view" id="btn-ticket" onclick="cambiarVistaPrevia('ticket')">
                            <i class="fas fa-receipt mr-2"></i> Ver Ticket
                        </button>
                        <button type="button" class="btn btn-outline-dark" id="btn-a4" onclick="cambiarVistaPrevia('a4')">
                            <i class="far fa-file-pdf mr-2"></i> Ver PDF
                        </button>
                    </div>
                </div>

                {{-- Botones de Acción --}}
                <div class="col-md-8 text-md-right text-center">
                    <div class="btn-group mb-2 mb-md-0 mr-md-1">
                        {{-- Botón ENVIAR --}}
                        <button type="button" class="btn btn-info btn-lg dropdown-toggle shadow-sm px-4" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-share-alt mr-1"></i> ENVIAR
                        </button>
                        <div class="dropdown-menu dropdown-menu-right shadow-lg border-0">
                            @php
                            $telefonoRaw = preg_replace('/[^0-9]/', '', $venta->cliente->telefono);
                            $tieneWsp = !empty($telefonoRaw) && strlen($telefonoRaw) >= 9;
                            $email = $venta->cliente->email;
                            $tieneEmail = !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
                            @endphp

                            {{-- Opción WhatsApp --}}
                            @if($tieneWsp)
                            <button type="button" class="dropdown-item py-2"
                                onclick="enviarWhatsApp('{{ $telefonoRaw }}', '{{ addslashes($venta->cliente->nombre_completo) }}', '{{ URL::signedRoute('publico.descargar', ['id' => $venta->id]) }}')">
                                <i class="fab fa-whatsapp text-success mr-2"></i> WhatsApp ({{ $telefonoRaw }})
                            </button>
                            @else
                            <span class="dropdown-item disabled text-muted py-2">
                                <i class="fab fa-whatsapp text-secondary mr-2"></i> WhatsApp <small>(No registrado)</small>
                            </span>
                            @endif

                            <div class="dropdown-divider"></div>

                            {{-- Opción Correo --}}
                            @if($tieneEmail)
                            <button type="button" class="dropdown-item py-2" onclick="enviarCorreo('{{ $venta->id }}', '{{ $email }}')">
                                <i class="fas fa-envelope text-primary mr-2"></i> Correo ({{ $email }})
                            </button>
                            @else
                            <span class="dropdown-item disabled text-muted py-2">
                                <i class="fas fa-envelope text-secondary mr-2"></i> Correo <small>(No registrado)</small>
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Botón IMPRIMIR (CORREGIDO: Ahora usa la función inteligente) --}}
                    <button onclick="imprimirDesdeServidor()" class="btn btn-danger btn-lg font-weight-bold shadow px-4 pulse-btn mb-2 mb-md-0">
                        <i class="fas fa-print mr-2"></i> IMPRIMIR
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================= --}}
    {{-- VISTA PREVIA (IFRAMES QUE MUESTRAN TUS ARCHIVOS) --}}
    {{-- ======================================================= --}}
    <div class="d-flex justify-content-center view-container">

        {{-- Iframe Visual Ticket --}}
        <div id="preview-ticket-container" class="">
            <iframe src="{{ route('ventas.print_ticket', $venta->id) }}"
                style="width: 320px; height: 500px; border: 1px solid #ddd; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"
                title="Vista Ticket"></iframe>
        </div>

        {{-- Iframe Visual A4 --}}
        <div id="preview-a4-container" class="d-none">
            <iframe src="{{ route('ventas.print_a4', $venta->id) }}"
                style="width: 215mm; height: 600px; border: 1px solid #ddd; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"
                title="Vista A4"></iframe>
        </div>

    </div>

</div>
@stop

@section('js')
<script>
    let vistaActual = 'ticket';

    // 1. Cambia qué iframe se ve en la pantalla
    function cambiarVistaPrevia(tipo) {
        vistaActual = tipo;

        if (tipo === 'ticket') {
            $('#preview-ticket-container').removeClass('d-none');
            $('#preview-a4-container').addClass('d-none');

            $('#btn-ticket').addClass('btn-dark').removeClass('btn-outline-dark');
            $('#btn-a4').removeClass('btn-dark').addClass('btn-outline-dark');
        } else {
            $('#preview-ticket-container').addClass('d-none');
            $('#preview-a4-container').removeClass('d-none');

            $('#btn-ticket').removeClass('btn-dark').addClass('btn-outline-dark');
            $('#btn-a4').addClass('btn-dark').removeClass('btn-outline-dark');
        }
    }

    // 2. FUNCIÓN DE IMPRESIÓN "INVISIBLE"
    function imprimirDesdeServidor() {
        let url = "";

        // 1. Decidimos qué ruta usar según lo que estás viendo
        if (vistaActual === 'ticket') {
            url = "{{ route('ventas.print_ticket', $venta->id) }}?imprimir=si";
        } else {
            url = "{{ route('ventas.print_a4', $venta->id) }}?imprimir=si";
        }

        // 2. Creamos un iframe invisible temporal solo para lanzar la impresión
        const iframePrint = document.createElement('iframe');
        iframePrint.style.position = 'absolute';
        iframePrint.style.width = '0px';
        iframePrint.style.height = '0px';
        iframePrint.style.border = 'none';
        iframePrint.src = url;

        document.body.appendChild(iframePrint);

        // 3. Limpieza: Eliminamos el iframe después de un minuto 
        setTimeout(() => {
            document.body.removeChild(iframePrint);
        }, 60000);
    }

    function enviarWhatsApp(numero, nombre, urlPdf) {
        const codigoPais = '51';
        const mensaje = `📄 *MUNDO FARMA - Comprobante Electrónico*\n\nHola *${nombre}*, adjuntamos el enlace para descargar su comprobante oficial en formato PDF:\n\n👉 ${urlPdf}\n\n_Gracias por su preferencia._`;
        const textoEncode = encodeURIComponent(mensaje);
        const url = `https://api.whatsapp.com/send?phone=${codigoPais}${numero}&text=${textoEncode}`;
        window.open(url, '_blank');
    }

    function enviarCorreo(ventaId, emailDestino) {
        Swal.fire({
            title: '¿Enviar comprobante?',
            text: `Se enviará al correo registrado: ${emailDestino}`,
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
                    text: 'Procesando envío de comprobante electrónico',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                $.ajax({
                    // Asegúrate de que esta ruta coincida con tu Route de Laravel
                    url: `/ventas/${ventaId}/enviar-email`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire('¡Enviado!', 'El comprobante ha sido enviado al cliente.', 'success');
                    },
                    error: function(xhr) {
                        let msg = 'Error en el servidor de correo.';
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
    .view-container {
        padding-bottom: 20px;
    }

    /* Ajustes para que el iframe visual se vea bonito */
    iframe {
        border-radius: 8px;
    }
</style>
@stop