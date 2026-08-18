@extends('tienda.layout')

@section('title', 'Pedido ' . $pedido->codigo)

@push('styles')
<style>
    /* Estilos premium para detalle de pedido */
    .order-status-badge {
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        font-size: 0.72rem;
    }
    .custom-table th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        font-weight: 700;
        border-bottom: 2px solid #f1f5f9;
        padding: 12px 16px;
    }
    .custom-table td {
        padding: 16px;
        vertical-align: middle;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
    }
    .qr-download-btn {
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .qr-download-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
    }
    .qr-download-btn:active {
        transform: scale(0.96);
    }

    /* Animaciones del Doctor y la Mano */
    @keyframes doctorFloat {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-8px); }
        100% { transform: translateY(0px); }
    }
    @keyframes handPoint {
        0% { transform: translateX(0px); }
        50% { transform: translateX(6px); }
        100% { transform: translateX(0px); }
    }
    .doctor-anim-container {
        animation: doctorFloat 3.5s ease-in-out infinite;
    }
    .pointing-hand {
        animation: handPoint 1.2s ease-in-out infinite;
        transform-origin: left center;
    }
</style>
@endpush

@section('content')
<!-- Cabecera de Agradecimiento e Inicio -->
<div class="d-flex flex-column align-items-center text-center mb-4">
    <div class="d-inline-flex p-3 bg-emerald-50 text-emerald-600 rounded-full mb-3 shadow-xs">
        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 2rem; height: 2rem;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"></path>
        </svg>
    </div>
    <h1 class="h3 fw-extrabold text-slate-800 mb-2">¡Gracias por tu confianza!</h1>
    <p class="text-slate-500 small max-w-lg mb-0">Tu pedido ha sido registrado con éxito. A continuación encontrarás el resumen y las instrucciones para el recojo.</p>
</div>

<!-- Stepper de Confirmación -->
<div class="checkout-stepper mb-5">
    <div class="d-flex justify-content-between align-items-center position-relative mx-auto" style="max-width: 500px;">
        <!-- Line background -->
        <div class="position-absolute start-0 end-0 top-50 translate-middle-y bg-slate-200" style="height: 2px; z-index: 1; margin-top: -10px;"></div>
        <div class="position-absolute start-0 top-50 translate-middle-y bg-emerald-500" style="height: 2px; width: 100%; z-index: 1; margin-top: -10px;"></div>
        
        <!-- Step 1: Carrito (Completed) -->
        <div class="d-flex flex-column align-items-center text-decoration-none position-relative" style="z-index: 2; width: 120px;">
            <div class="bg-emerald-100 text-emerald-600 rounded-full d-flex align-items-center justify-content-center border-4 border-white shadow-sm" style="width: 38px; height: 38px;">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.1rem; height: 1.1rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1,0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0,1-1.12-1.243l1.264-12A1.125 1.125 0 0,1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1,1-.75 0 .375.375 0 0,1 .75 0Zm7.5 0a.375.375 0 1,1-.75 0 .375.375 0 0,1 .75 0Z"></path>
                </svg>
            </div>
            <span class="text-xs font-bold text-slate-500 mt-2">Carrito</span>
        </div>

        <!-- Step 2: Datos y Entrega (Completed) -->
        <div class="d-flex flex-column align-items-center position-relative" style="z-index: 2; width: 120px;">
            <div class="bg-emerald-100 text-emerald-600 rounded-full d-flex align-items-center justify-content-center border-4 border-white shadow-sm" style="width: 38px; height: 38px;">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.1rem; height: 1.1rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"></path>
                </svg>
            </div>
            <span class="text-xs font-bold text-slate-500 mt-2">Datos y Entrega</span>
        </div>

        <!-- Step 3: Confirmación (Active) -->
        <div class="d-flex flex-column align-items-center position-relative" style="z-index: 2; width: 120px;">
            <div class="bg-emerald-500 text-white rounded-full d-flex align-items-center justify-content-center border-4 border-white shadow-md" style="width: 42px; height: 42px; margin-top: -2px;">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.2rem; height: 1.2rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"></path>
                </svg>
            </div>
            <span class="text-xs font-bold text-emerald-600 mt-2">Confirmación</span>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Columna Izquierda: Información de Pedido -->
    <div class="col-lg-8">
        <div class="store-card bg-white p-4 border border-slate-100 rounded-2xl shadow-sm mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 border-bottom border-slate-100 pb-3 mb-3">
                <div>
                    <span class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">Código del pedido</span>
                    <h2 class="h4 fw-extrabold text-slate-800 mb-0 mt-0.5">{{ $pedido->codigo }}</h2>
                </div>
                <div class="d-flex gap-2">
                    <!-- Badge Estado -->
                    <span class="badge rounded-xl px-2.5 py-1.5 order-status-badge @if($pedido->estado === 'PENDIENTE') bg-amber-50 text-amber-700 border border-amber-100/80 @elseif($pedido->estado === 'CONFIRMADO') bg-emerald-50 text-emerald-700 border border-emerald-100/80 @else bg-slate-50 text-slate-700 border border-slate-100 @endif">
                        Estado: {{ $pedido->estado }}
                    </span>
                    <!-- Badge Pago -->
                    <span class="badge rounded-xl px-2.5 py-1.5 order-status-badge @if($pedido->estado_pago === 'PENDIENTE') bg-rose-50 text-rose-700 border border-rose-100/80 @elseif($pedido->estado_pago === 'PAGADO') bg-emerald-50 text-emerald-700 border border-emerald-100/80 @else bg-slate-50 text-slate-700 border border-slate-100 @endif">
                        Pago: {{ $pedido->estado_pago }}
                    </span>
                </div>
            </div>

            <!-- Datos de Sucursal y Fecha -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="p-3 bg-slate-50/60 rounded-xl border border-slate-100/80 h-100">
                        <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block mb-1">📍 Sucursal de Recojo</span>
                        <strong class="text-slate-800 text-sm font-bold">{{ $pedido->sucursal->nombre ?? '-' }}</strong>
                        <p class="text-xs text-slate-500 mt-1 mb-0">{{ $pedido->sucursal->direccion ?? '' }}, {{ $pedido->sucursal->distrito ?? '' }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-slate-50/60 rounded-xl border border-slate-100/80 h-100">
                        <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block mb-1">🕒 Fecha y Hora Programada</span>
                        <strong class="text-slate-800 text-sm font-bold">{{ \Carbon\Carbon::parse($pedido->fecha_recojo)->format('d/m/Y - h:i A') }}</strong>
                        <p class="text-xs text-slate-500 mt-1 mb-0">Por favor, acércate dentro del horario establecido.</p>
                    </div>
                </div>
            </div>

            <!-- Tabla de Detalle -->
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center" style="width: 100px;">Cantidad</th>
                            <th class="text-end" style="width: 140px;">Precio Unitario</th>
                            <th class="text-end" style="width: 140px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pedido->detalles as $detalle)
                            <tr>
                                <td class="font-semibold text-slate-800">{{ $detalle->descripcion }}</td>
                                <td class="text-center font-bold text-slate-600">{{ $detalle->cantidad }}</td>
                                <td class="text-end text-slate-600">S/ {{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                                <td class="text-end font-bold text-slate-700">S/ {{ number_format((float) $detalle->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end align-items-baseline gap-3 p-3 bg-slate-50/40 rounded-xl border border-slate-100/60 mt-3">
                <span class="text-sm font-bold text-slate-500">Monto total:</span>
                <strong class="text-2xl font-extrabold text-emerald-600">S/ {{ number_format((float) $pedido->total, 2) }}</strong>
            </div>

            @if($pedido->metodo_pago === 'PAGO_ONLINE' && $pedido->estado_pago === 'PENDIENTE')
                <div class="mt-4 p-4 border border-rose-100 bg-rose-50/30 rounded-2xl d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <strong class="text-rose-800 font-bold d-block">Pago Online Pendiente</strong>
                        <span class="text-xs text-rose-700/90 leading-relaxed block mt-0.5">Para confirmar y preparar tu pedido, es necesario completar la transacción a través de Culqi.</span>
                    </div>
                    <a href="{{ route('tienda.pago.show', $pedido->codigo) }}" class="btn btn-store py-2.5 px-4 rounded-xl font-bold transition-all active:scale-95 d-inline-flex align-items-center gap-2" style="background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); border: 0; box-shadow: 0 4px 12px rgba(225, 29, 72, 0.25);">
                        <span>Pagar ahora S/ {{ number_format((float) $pedido->total, 2) }}</span>
                        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.05rem; height: 1.05rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-5.625-10.5h16.5a1.125 1.125 0 0 1 1.125 1.125v11.25a1.125 1.125 0 0 1-1.125 1.125H3.375a1.125 1.125 0 0 1-1.125-1.125V3.375A1.125 1.125 0 0 1 3.375 2.25Z"></path>
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Columna Derecha: QR de Recojo con Doctor Animado -->
    <div class="col-lg-4">
        <div class="store-card bg-white p-4 text-center border border-slate-100 rounded-2xl shadow-sm position-sticky animate-fade-in" style="top: 100px;">
            <h2 class="h5 fw-extrabold text-slate-800 mb-2" style="font-size: 1.05rem;">Código QR de Recojo</h2>
            <p class="text-xs text-slate-400 leading-normal mb-1">Presenta este código en la farmacia para retirar tu pedido.</p>
            
            <div class="d-flex align-items-center justify-content-center gap-2 my-4">
                <!-- Doctor Animado SVG -->
                <div class="doctor-anim-container" id="doctor-svg-source" style="width: 90px; height: 120px; flex-shrink: 0;">
                    <svg viewBox="0 0 120 160" width="90" height="120" class="doctor-character" id="doctor-svg-element">
                        <!-- Cap / Gorro Médico -->
                        <path d="M25,60 C25,25 75,25 75,60 Z" fill="#0d9488" />
                        <rect x="45" y="32" width="10" height="10" rx="2" fill="#ffffff" />
                        <rect x="47" y="30" width="6" height="14" rx="1.5" fill="#ffffff" />
                        <rect x="43" y="34" width="14" height="6" rx="1.5" fill="#ffffff" />
                        <!-- Face -->
                        <circle cx="50" cy="65" r="22" fill="#fbcfe8" />
                        <!-- Eyes -->
                        <circle cx="42" cy="62" r="3" fill="#1e293b" />
                        <circle cx="58" cy="62" r="3" fill="#1e293b" />
                        <!-- Smile -->
                        <path d="M45,73 Q50,77 55,73" stroke="#1e293b" stroke-width="2.5" stroke-linecap="round" fill="none" />
                        <!-- Glasses -->
                        <circle cx="42" cy="62" r="8" fill="none" stroke="#0ea5e9" stroke-width="2" />
                        <circle cx="58" cy="62" r="8" fill="none" stroke="#0ea5e9" stroke-width="2" />
                        <line x1="50" y1="62" x2="50" y2="62" stroke="#0ea5e9" stroke-width="2" />
                        <!-- Body / Bata -->
                        <path d="M15,115 C15,95 30,90 50,90 C70,90 85,95 85,115 L80,150 L20,150 Z" fill="#ffffff" stroke="#cbd5e1" stroke-width="1.5" />
                        <!-- Shirt (Teal) -->
                        <path d="M42,90 L50,105 L58,90 Z" fill="#0d9488" />
                        <!-- Stethoscope -->
                        <path d="M35,90 C35,110 65,110 65,90" fill="none" stroke="#475569" stroke-width="2.5" />
                        <!-- Pointing Hand -->
                        <g class="pointing-hand">
                            <path d="M80,108 C84,108 92,106 95,101 C97,97 92,94 88,97 L105,97 C108,97 108,93 103,93 L88,93 C92,93 92,89 88,89 L75,97 Z" fill="#fbcfe8" />
                        </g>
                    </svg>
                </div>

                <!-- QR Code SVG -->
                <div id="qr-svg-container" class="bg-white p-2 border border-slate-100 rounded-2xl shadow-xs" style="background-color: #ffffff; flex-shrink: 0;">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(140)->generate(route('tienda.pedidos.recojo', $pedido->qr_token)) !!}
                </div>
            </div>

            <!-- Botón Descargar QR Premium -->
            <button type="button" onclick="downloadQRPNG()" class="btn btn-outline-secondary w-100 py-2.5 rounded-xl font-bold text-xs d-flex align-items-center justify-content-center gap-2 border-slate-200 text-slate-700 hover:bg-slate-50 active:scale-95 qr-download-btn transition-all mb-3 shadow-xs">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.05rem; height: 1.05rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"></path>
                </svg>
                <span>Descargar Tarjeta de Recojo (PNG)</span>
            </button>

            <a href="{{ route('tienda.mis-pedidos') }}" class="btn btn-light border-slate-100 w-100 py-2.5 rounded-xl font-bold text-xs text-slate-600 hover:bg-slate-50 transition-all active:scale-95 mb-3">
                Ver todos mis pedidos
            </a>
            
            <div class="text-center mt-3 pt-2 border-top border-slate-100/80">
                <span class="text-slate-400" style="font-size: 0.72rem;">
                    📍 Recojo exclusivo en sucursal elegida.
                </span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Generador de Confeti de Pastillas / Medicamentos
    function triggerPillConfetti() {
        const icons = ['💊', '💊', '🩹', '🧪', '🩹', '💚', '➕', '🩺'];
        const container = document.createElement('div');
        container.style.position = 'fixed';
        container.style.top = '0';
        container.style.left = '0';
        container.style.width = '100vw';
        container.style.height = '100vh';
        container.style.pointerEvents = 'none';
        container.style.zIndex = '99999';
        document.body.appendChild(container);

        const count = 90;
        const centerX = window.innerWidth / 2;
        const centerY = window.innerHeight / 3;

        for (let i = 0; i < count; i++) {
            const el = document.createElement('div');
            el.innerText = icons[Math.floor(Math.random() * icons.length)];
            el.style.position = 'absolute';
            el.style.left = `${centerX}px`;
            el.style.top = `${centerY}px`;
            el.style.fontSize = `${Math.floor(Math.random() * 22) + 16}px`;
            el.style.pointerEvents = 'none';
            el.style.userSelect = 'none';
            container.appendChild(el);

            const angle = Math.random() * Math.PI * 2;
            const velocity = Math.random() * 320 + 180;
            const xOffset = Math.cos(angle) * velocity;
            const yOffset = Math.sin(angle) * velocity;
            const rotation = Math.random() * 720 - 360;
            const duration = Math.random() * 1.6 + 1.4;

            el.animate([
                { transform: 'translate(-50%, -50%) translate(0, 0) scale(0) rotate(0deg)', opacity: 1 },
                { transform: `translate(-50%, -50%) translate(${xOffset * 0.45}px, ${yOffset * 0.45}px) scale(1.35) rotate(${rotation * 0.45}deg)`, opacity: 1, offset: 0.15 },
                { transform: `translate(-50%, -50%) translate(${xOffset}px, ${yOffset + 420}px) scale(0.6) rotate(${rotation}deg)`, opacity: 0 }
            ], {
                duration: duration * 1000,
                easing: 'cubic-bezier(0.12, 0.82, 0.35, 1)',
                fill: 'forwards'
            });
        }

        setTimeout(() => {
            container.remove();
        }, 3500);
    }

    // Descarga de Tarjeta de Recojo PNG
    function downloadQRPNG() {
        const svgContainer = document.getElementById('qr-svg-container');
        const doctorContainer = document.getElementById('doctor-svg-source');
        if (!svgContainer || !doctorContainer) return;
        
        const qrSvg = svgContainer.querySelector('svg');
        const doctorSvg = doctorContainer.querySelector('svg');
        if (!qrSvg || !doctorSvg) return;

        const serializer = new XMLSerializer();
        const qrString = serializer.serializeToString(qrSvg);
        const doctorString = serializer.serializeToString(doctorSvg);

        const qrBlob = new Blob([qrString], { type: 'image/svg+xml;charset=utf-8' });
        const doctorBlob = new Blob([doctorString], { type: 'image/svg+xml;charset=utf-8' });

        const URL = window.URL || window.webkitURL || window;
        const qrURL = URL.createObjectURL(qrBlob);
        const doctorURL = URL.createObjectURL(doctorBlob);

        const qrImage = new Image();
        const doctorImage = new Image();

        let loadedCount = 0;
        function checkLoad() {
            loadedCount++;
            if (loadedCount === 2) {
                drawCanvasCard();
            }
        }

        qrImage.onload = checkLoad;
        doctorImage.onload = checkLoad;

        qrImage.src = qrURL;
        doctorImage.src = doctorURL;

        function drawCanvasCard() {
            const canvas = document.createElement('canvas');
            canvas.width = 460;
            canvas.height = 320;
            const ctx = canvas.getContext('2d');

            // 1. Fondo de la tarjeta con bordes redondeados
            ctx.fillStyle = '#ffffff';
            ctx.strokeStyle = '#e2e8f0';
            ctx.lineWidth = 4;
            
            const radius = 24;
            ctx.beginPath();
            ctx.moveTo(radius, 0);
            ctx.lineTo(canvas.width - radius, 0);
            ctx.quadraticCurveTo(canvas.width, 0, canvas.width, radius);
            ctx.lineTo(canvas.width, canvas.height - radius);
            ctx.quadraticCurveTo(canvas.width, canvas.height, canvas.width - radius, canvas.height);
            ctx.lineTo(radius, canvas.height);
            ctx.quadraticCurveTo(0, canvas.height, 0, canvas.height - radius);
            ctx.lineTo(0, radius);
            ctx.quadraticCurveTo(0, 0, radius, 0);
            ctx.closePath();
            ctx.fill();
            ctx.stroke();

            // 2. Título de Cabecera
            ctx.fillStyle = '#0f172a'; // slate-900
            ctx.font = 'bold 20px "Inter", system-ui, sans-serif';
            ctx.fillText('Código QR de Recojo', 30, 45);

            ctx.fillStyle = '#10b981'; // emerald-500
            ctx.font = 'bold 16px "Inter", sans-serif';
            ctx.fillText('Pedido: {{ $pedido->codigo }}', 30, 75);

            // 3. Dibujar al Doctor (SVG) a la izquierda
            ctx.drawImage(doctorImage, 30, 95, 110, 147);

            // 4. Dibujar el QR (SVG) a la derecha con un marco sutil
            ctx.strokeStyle = '#f1f5f9';
            ctx.lineWidth = 1;
            ctx.strokeRect(225, 95, 180, 180);
            ctx.drawImage(qrImage, 230, 100, 170, 170);

            // 5. Dibujar texto de ayuda
            ctx.fillStyle = '#64748b'; // slate-500
            ctx.font = '11px "Inter", sans-serif';
            ctx.fillText('Presenta este código en la farmacia para retirar tu pedido.', 30, 290);

            // 6. Descargar el archivo
            const pngURL = canvas.toDataURL('image/png');
            const link = document.createElement('a');
            link.href = pngURL;
            link.download = 'Tarjeta_Recojo_{{ $pedido->codigo }}.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Limpieza de URLs de Blobs
            URL.revokeObjectURL(qrURL);
            URL.revokeObjectURL(doctorURL);
        }
    }

    // Activar confeti automáticamente si proviene de una creación de pedido exitosa
    @if(session('success'))
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(triggerPillConfetti, 250);
        });
    @endif
</script>
@endpush
@endsection
