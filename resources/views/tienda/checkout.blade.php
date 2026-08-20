@extends('tienda.layout')

@section('title', 'Finalizar pedido')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    /* Estilos personalizados para checkout premium */
    .custom-map-marker {
        transition: transform 0.3s ease;
    }
    .custom-map-marker:hover {
        transform: scale(1.15);
    }
    .leaflet-popup-content-wrapper {
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
        border: 1px solid #f1f5f9;
        padding: 4px;
    }
    .leaflet-popup-tip {
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
    }
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15) !important;
    }
</style>
@endpush

@section('content')
<!-- Cabecera de Página -->
<div class="d-flex flex-column align-items-center text-center mb-5">
    <h1 class="h3 fw-extrabold text-slate-800 mb-2">Finalizar tu Compra</h1>
    <p class="text-slate-500 small max-w-lg mb-0">Completa tus datos de contacto y selecciona tu método de entrega preferido para procesar el pedido.</p>
</div>

<!-- Stepper/Timeline -->
<div class="checkout-stepper mb-5">
    <div class="d-flex justify-content-between align-items-center position-relative mx-auto" style="max-width: 500px;">
        <!-- Line background -->
        <div class="position-absolute start-0 end-0 top-50 translate-middle-y bg-slate-200" style="height: 2px; z-index: 1; margin-top: -10px;"></div>
        <div class="position-absolute start-0 top-50 translate-middle-y bg-emerald-500" style="height: 2px; width: 50%; z-index: 1; margin-top: -10px;"></div>
        
        <!-- Step 1: Carrito -->
        <a href="{{ route('tienda.carrito.index') }}" class="d-flex flex-column align-items-center text-decoration-none position-relative" style="z-index: 2; width: 120px;">
            <div class="bg-emerald-100 text-emerald-600 rounded-full d-flex align-items-center justify-content-center border-4 border-white shadow-sm" style="width: 38px; height: 38px;">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.1rem; height: 1.1rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1,0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0,1-1.12-1.243l1.264-12A1.125 1.125 0 0,1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1,1-.75 0 .375.375 0 0,1 .75 0Zm7.5 0a.375.375 0 1,1-.75 0 .375.375 0 0,1 .75 0Z"></path>
                </svg>
            </div>
            <span class="text-xs font-bold text-slate-500 mt-2">Carrito</span>
        </a>

        <!-- Step 2: Datos y Entrega -->
        <div class="d-flex flex-column align-items-center position-relative" style="z-index: 2; width: 120px;">
            <div class="bg-emerald-500 text-white rounded-full d-flex align-items-center justify-content-center border-4 border-white shadow-md" style="width: 42px; height: 42px; margin-top: -2px;">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.2rem; height: 1.2rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"></path>
                </svg>
            </div>
            <span class="text-xs font-bold text-emerald-600 mt-2">Datos y Entrega</span>
        </div>

        <!-- Step 3: Pago / Confirmación -->
        <div class="d-flex flex-column align-items-center position-relative" style="z-index: 2; width: 120px;">
            <div class="bg-white text-slate-400 border border-slate-200 rounded-full d-flex align-items-center justify-content-center border-4 border-white shadow-sm" style="width: 38px; height: 38px;">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.1rem; height: 1.1rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"></path>
                </svg>
            </div>
            <span class="text-xs font-bold text-slate-400 mt-2">Confirmación</span>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('tienda.checkout.store') }}" class="row g-4" id="formCheckout">
    @csrf
    <div class="col-lg-7">
        <!-- Tarjeta: Datos del Cliente -->
        <div class="store-card bg-white p-4 mb-4 border border-slate-100 rounded-2xl shadow-sm hover:shadow-md transition-all">
            <div class="d-flex align-items-center gap-2.5 mb-3 border-bottom border-slate-100 pb-2.5">
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.25rem; height: 1.25rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"></path>
                    </svg>
                </div>
                <h2 class="h5 fw-extrabold mb-0 text-slate-800" style="font-size: 1.05rem;">Datos del cliente</h2>
            </div>
            
            <div class="alert bg-emerald-50/40 border border-emerald-100 text-emerald-800 rounded-xl p-3 d-flex align-items-center gap-2.5 mb-4 shadow-xs">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.15rem; height: 1.15rem; flex-shrink: 0;" class="text-emerald-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                </svg>
                <span class="small text-slate-700 leading-normal">Comprando como <strong>{{ $cliente->nombre_completo }}</strong> ({{ $cliente->tipo_documento }} {{ $cliente->documento }})</span>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label font-bold text-slate-700 text-xs uppercase tracking-wider mb-1.5">Teléfono de contacto <span id="labelTelefonoReq" class="text-danger">*</span></label>
                    <input name="cliente_telefono" id="clienteTelefono" value="{{ old('cliente_telefono', $cliente->telefono) }}" 
                           class="form-control border-slate-200 bg-slate-50/50 px-3.5 py-2.5 rounded-xl text-sm focus:bg-white focus:border-emerald-500 transition-all @error('cliente_telefono') is-invalid @enderror" 
                           placeholder="987654321">
                    @error('cliente_telefono')
                        <div class="invalid-feedback text-xs mt-1">{{ $message }}</div>
                    @enderror
                    <small id="helpTelefono" class="text-slate-400 text-xs mt-1 block">Requerido para la confirmación y pago online.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label font-bold text-slate-700 text-xs uppercase tracking-wider mb-1.5">Correo Electrónico</label>
                    <input type="email" name="cliente_email" value="{{ old('cliente_email', $cliente->email) }}" 
                           class="form-control border-slate-200 bg-slate-50/50 px-3.5 py-2.5 rounded-xl text-sm focus:bg-white focus:border-emerald-500 transition-all @error('cliente_email') is-invalid @enderror" 
                           placeholder="ejemplo@correo.com">
                    @error('cliente_email')
                        <div class="invalid-feedback text-xs mt-1">{{ $message }}</div>
                    @enderror
                    <small class="text-slate-400 text-xs mt-1 block">Enviaremos el comprobante y resumen aquí.</small>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Entrega y Pago -->
        <div class="store-card bg-white p-4 border border-slate-100 rounded-2xl shadow-sm hover:shadow-md transition-all">
            <div class="d-flex align-items-center gap-2.5 mb-3 border-bottom border-slate-100 pb-2.5">
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.25rem; height: 1.25rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 4.643 4.5h14.714a1.125 1.125 0 0 1 1.12 1.243l-1.264 12A1.125 1.125 0 0 1 18.107 18.75m-18 0v-6h18v6m-9-6v6"></path>
                    </svg>
                </div>
                <h2 class="h5 fw-extrabold mb-0 text-slate-800" style="font-size: 1.05rem;">Entrega y pago</h2>
            </div>

            <div class="row g-3">
                <input type="hidden" name="tipo_entrega" value="RECOJO_SUCURSAL">

                @if($esMultiSucursal)
                    <div class="col-12">
                        <div class="alert bg-amber-50 border border-amber-100 text-amber-900 rounded-xl p-3.5 d-flex gap-2.5 mb-3 shadow-xs">
                            <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.3rem; height: 1.3rem; flex-shrink: 0;" class="text-amber-600 mt-0.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div class="small">
                                <strong class="font-bold text-amber-800">Productos de diferentes sucursales:</strong> 
                                <span class="text-slate-700">El tiempo de espera será de al menos <strong>una semana</strong> mientras trasladamos todos los productos a la sucursal que elijas para el recojo.</span>
                            </div>
                        </div>
                        <label class="form-label font-bold text-slate-700 text-xs uppercase tracking-wider mb-1.5">Sucursal donde recogerás tu pedido</label>
                        <select name="sucursal_recojo_id" class="form-select border-slate-200 bg-slate-50/50 px-3.5 py-2.5 rounded-xl text-sm focus:bg-white focus:border-emerald-500 transition-all @error('sucursal_recojo_id') is-invalid @enderror" required>
                            <option value="">Selecciona una sucursal</option>
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}" @selected(old('sucursal_recojo_id') == $sucursal->id)>{{ $sucursal->nombre }}</option>
                            @endforeach
                        </select>
                        @error('sucursal_recojo_id')
                            <div class="invalid-feedback text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                @else
                    <div class="col-12">
                        <div class="alert bg-emerald-50/40 border border-emerald-100 rounded-xl p-3 d-flex align-items-start gap-2.5 mb-2 shadow-xs">
                            <div class="p-2 bg-emerald-100/80 rounded-xl text-emerald-700 mt-0.5">
                                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.1rem; height: 1.1rem;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h2.25m-2.25 0v-4.5a3.75 3.75 0 0 0-3.75-3.75h-3m-6.75 3.75h1.5m9 0h.008v.008H12v-.008Zm-3 0h.008v.008H9v-.008Zm-3 0h.008v.008H6v-.008Zm-3 0h.008v.008H3v-.008Z"></path>
                                </svg>
                            </div>
                            <div>
                                <span class="text-sm font-bold text-slate-800 d-block mb-0.5">Recogerás tu pedido en:</span>
                                <strong class="text-emerald-700 text-sm font-extrabold">{{ $sucursales->first()->nombre }}</strong>
                                <div class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $sucursales->first()->direccion }}, {{ $sucursales->first()->distrito }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Mapa interactivo -->
                <div class="col-12" id="checkout_map_container" style="display: none;">
                    <label class="form-label text-xs uppercase font-bold tracking-wide text-slate-500 mb-1.5">Ubicación de la sucursal de recojo</label>
                    <div id="checkout_map" style="height: 190px; border-radius: 16px; border: 1px solid #e2e8f0; z-index: 5; box-shadow: inset 0 2px 4px rgba(15,23,42,0.02);"></div>
                </div>

                <!-- Método de Pago -->
                <div class="col-md-6">
                    <label class="form-label font-bold text-slate-700 text-xs uppercase tracking-wider mb-1.5">Método de Pago</label>
                    <select name="metodo_pago" class="form-select border-slate-200 bg-slate-50/50 px-3.5 py-2.5 rounded-xl text-sm focus:bg-white focus:border-emerald-500 transition-all @error('metodo_pago') is-invalid @enderror">
                        <option value="PAGO_AL_RECOGER" @selected(old('metodo_pago') === 'PAGO_AL_RECOGER')>Pagar al recoger (Efectivo/Tarjeta)</option>
                        <option value="PAGO_ONLINE" disabled>Pago online (En mantenimiento)</option>
                    </select>
                    @error('metodo_pago')
                        <div class="invalid-feedback text-xs mt-1 d-block">{{ $message }}</div>
                    @enderror
                    <div class="alert bg-amber-50/60 border border-amber-100 text-amber-800 rounded-xl p-2.5 mt-2.5 d-flex align-items-center gap-2 small mb-0">
                        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1rem; height: 1rem; flex-shrink: 0;" class="text-amber-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <span>Los pagos online actualmente se encuentran en mantenimiento. Por favor, use la opción de "Pagar al recoger".</span>
                    </div>
                </div>

                <!-- Fecha de Recojo -->
                <div class="col-md-6">
                    <label class="form-label font-bold text-slate-700 text-xs uppercase tracking-wider mb-1.5">Fecha y hora de recojo</label>
                    <input type="datetime-local" name="fecha_recojo" value="{{ old('fecha_recojo', $fechaRecojoDefault) }}" 
                           class="form-control border-slate-200 bg-slate-50/50 px-3.5 py-2.5 rounded-xl text-sm focus:bg-white focus:border-emerald-500 transition-all @error('fecha_recojo') is-invalid @enderror" 
                           min="{{ $fechaRecojoMin }}">
                    @error('fecha_recojo')
                        <div class="invalid-feedback text-xs mt-1">{{ $message }}</div>
                    @enderror
                    @if($esMultiSucursal)
                        <small class="text-slate-400 text-xs mt-1 block">El traslado de productos requiere mínimo una semana.</small>
                    @endif
                </div>

                <!-- Observaciones -->
                <div class="col-12">
                    <label class="form-label font-bold text-slate-700 text-xs uppercase tracking-wider mb-1.5">Observaciones / Indicaciones especiales</label>
                    <textarea name="observaciones" 
                              class="form-control border-slate-200 bg-slate-50/50 px-3.5 py-2.5 rounded-xl text-sm focus:bg-white focus:border-emerald-500 transition-all" 
                              rows="3" placeholder="Ej. Indicaciones adicionales sobre quién recogerá el pedido o alguna necesidad especial..."></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Resumen de Pedido -->
    <div class="col-lg-5 mt-4 mt-lg-0">
        <div class="store-card bg-white p-4 border border-slate-100 rounded-2xl shadow-sm hover:shadow-md transition-all sticky-top" style="top: 90px; z-index: 10;">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-slate-100 pb-2.5">
                <h2 class="h5 fw-extrabold mb-0 text-slate-800" style="font-size: 1.05rem;">Resumen de compra</h2>
                <span class="badge bg-slate-100 text-slate-600 rounded-full px-2.5 py-1 text-xs font-extrabold">{{ count($items) }} {{ count($items) === 1 ? 'ítem' : 'ítems' }}</span>
            </div>
            
            <div class="scrollbar-hide overflow-auto mb-3" style="max-height: 240px; scrollbar-width: none;">
                @foreach ($items as $item)
                    @php
                        $producto = $item['producto'];
                        $categoriaNombre = $producto->medicamento->categoria->nombre ?? 'Medicamento';
                    @endphp
                    <div class="d-flex align-items-center justify-content-between py-2.5 @if(!$loop->last) border-bottom border-slate-100/70 @endif gap-2">
                        <div class="d-flex align-items-center gap-2.5">
                            <!-- Imagen miniatura -->
                            <div class="aspect-square bg-slate-50 rounded-xl flex items-center justify-center p-1 border border-slate-100" style="width: 44px; height: 44px; flex-shrink: 0;">
                                @if($producto->imagen_url)
                                    <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                @else
                                    <span class="text-emerald-600 font-bold" style="font-size: 0.85rem;">+</span>
                                @endif
                            </div>
                            <!-- Detalles -->
                            <div>
                                <h4 class="text-xs font-bold text-slate-800 mb-0.5 line-clamp-1" style="max-width: 160px;" title="{{ $producto->nombre }}">{{ $producto->nombre }}</h4>
                                <span class="text-slate-400" style="font-size: 0.72rem;">{{ $item['cantidad'] }} u. &times; S/ {{ number_format($item['precio'], 2) }}</span>
                            </div>
                        </div>
                        <span class="font-extrabold text-slate-700 text-sm">S/ {{ number_format($item['subtotal'], 2) }}</span>
                    </div>
                @endforeach
            </div>

            <div class="bg-slate-50/50 rounded-2xl p-3 border border-slate-100/80 mb-4">
                <div class="d-flex justify-content-between text-xs text-slate-500 mb-2 font-medium">
                    <span>Subtotal</span>
                    <span class="font-bold text-slate-700">S/ {{ number_format($total, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between text-xs text-slate-500 mb-2 font-medium">
                    <span>Envío / Traslado</span>
                    <span class="text-emerald-600 font-extrabold">Gratis</span>
                </div>
                <hr class="my-2.5 border-slate-200/80">
                <div class="d-flex justify-content-between align-items-baseline">
                    <span class="text-sm font-bold text-slate-800">Total a pagar</span>
                    <strong class="text-xl font-extrabold text-emerald-600">S/ {{ number_format($total, 2) }}</strong>
                </div>
            </div>

            <!-- Botón Confirmar Pedido -->
            <button type="submit" class="btn btn-store w-100 py-3 rounded-xl font-bold tracking-wide shadow-md hover:shadow-lg active:scale-95 transition-all d-flex align-items-center justify-content-center gap-2" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: 0; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.15rem; height: 1.15rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"></path>
                </svg>
                <span>Confirmar Pedido</span>
            </button>

            <!-- Trust Badge -->
            <div class="d-flex align-items-center justify-content-center gap-2 mt-4 text-slate-400">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 0.95rem; height: 0.95rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"></path>
                </svg>
                <span class="text-xs font-semibold">Transacción Segura &bull; Farmacia Confiable</span>
            </div>
        </div>
    </div>
</form>

<!-- Modal: Confirmación de Cambio de Datos -->
@if(session('confirmar_cambio_datos'))
<div class="modal fade" id="modalCambioDatos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 1.5rem; overflow: hidden;">
            <div class="modal-header border-bottom border-slate-100 pb-3 pt-4 px-4 bg-slate-50/50">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-1.5 bg-amber-50 text-amber-600 rounded-lg">
                        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.15rem; height: 1.15rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h5 class="modal-title fw-extrabold text-slate-800 mb-0" style="font-size: 1.05rem;">Actualización de contacto</h5>
                </div>
                <button type="button" class="btn-close" id="btnCloseCambioDatos" style="font-size: 0.8rem;"></button>
            </div>
            <div class="modal-body p-4 text-center text-sm-start">
                <p class="text-sm text-slate-700 leading-relaxed">Estás cambiando tus datos de contacto: <strong class="text-slate-800">{{ session('confirmar_cambio_datos') }}</strong>.</p>
                <div class="alert bg-slate-50 border border-slate-200 rounded-xl p-3 small text-slate-500 mb-0 leading-normal">
                    Estos nuevos datos se guardarán automáticamente en tu perfil de usuario para futuras compras. ¿Deseas continuar?
                </div>
            </div>
            <div class="modal-footer border-top border-slate-100 p-3 bg-slate-50/30 d-flex gap-2">
                <button type="button" class="btn btn-light border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-600 active:scale-95 transition-all flex-grow-1" id="btnCancelarCambioDatos">Cancelar</button>
                <button type="button" class="btn btn-store rounded-xl px-4 py-2.5 text-xs font-bold active:scale-95 transition-all flex-grow-1" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: 0; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);" id="btnConfirmarDatos">Actualizar y continuar</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@if(session('confirmar_cambio_datos'))
@push('scripts')
<script>
    (function () {
        var modalEl = document.getElementById('modalCambioDatos');
        if (!modalEl) return;

        function showModal() {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            modalEl.setAttribute('aria-hidden', 'false');
            modalEl.setAttribute('aria-modal', 'true');
            document.body.classList.add('modal-open');
            if (!document.querySelector('.modal-backdrop')) {
                var backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
            }
        }

        function hideModal() {
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            modalEl.setAttribute('aria-hidden', 'true');
            modalEl.removeAttribute('aria-modal');
            document.body.classList.remove('modal-open');
            document.querySelectorAll('.modal-backdrop').forEach(function (el) { el.remove(); });
        }

        showModal();

        document.getElementById('btnConfirmarDatos').addEventListener('click', function () {
            var form = document.getElementById('formCheckout');
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'confirmar_datos';
            input.value = '1';
            form.appendChild(input);
            form.submit();
        });

        document.getElementById('btnCancelarCambioDatos').addEventListener('click', hideModal);
        document.getElementById('btnCloseCambioDatos').addEventListener('click', hideModal);
    })();
</script>
@endpush
@endif

@push('scripts')
<script>
(function () {
    var selectMetodo = document.querySelector('[name="metodo_pago"]');
    var telefonoInput = document.getElementById('clienteTelefono');
    var labelReq = document.getElementById('labelTelefonoReq');
    var helpTelefono = document.getElementById('helpTelefono');

    function actualizarTelefono() {
        var esOnline = selectMetodo && selectMetodo.value === 'PAGO_ONLINE';
        if (telefonoInput) {
            telefonoInput.required = esOnline;
        }
        if (labelReq) {
            labelReq.style.display = esOnline ? 'inline' : 'none';
        }
        if (helpTelefono) {
            helpTelefono.style.display = esOnline ? 'block' : 'none';
        }
    }

    if (selectMetodo) {
        selectMetodo.addEventListener('change', actualizarTelefono);
        actualizarTelefono();
    }
})();
</script>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    (function () {
        var sucursalesJson = @json($sucursalesJson);

        var checkoutMap = null;
        var checkoutMarker = null;
        var esMulti = @json($esMultiSucursal);

        function updateCheckoutMap(sucursalId) {
            var s = sucursalesJson.find(function(x) { return x.id == sucursalId; });
            if (!s || !s.lat || !s.lng) {
                document.getElementById('checkout_map_container').style.display = 'none';
                return;
            }

            document.getElementById('checkout_map_container').style.display = 'block';

            var customIcon = L.divIcon({
                html: `<div style="background-color: #10b981; width: 30px; height: 30px; border-radius: 50%; border: 2.5px solid white; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35); display: flex; align-items: center; justify-content: center; color: white;">
                        <i class="fas fa-clinic-medical" style="font-size: 11px;"></i>
                       </div>`,
                className: 'custom-map-marker',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });

            if (!checkoutMap) {
                checkoutMap = L.map('checkout_map').setView([s.lat, s.lng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(checkoutMap);

                checkoutMarker = L.marker([s.lat, s.lng], { icon: customIcon }).addTo(checkoutMap);
            } else {
                checkoutMap.setView([s.lat, s.lng], 16);
                checkoutMarker.setLatLng([s.lat, s.lng]);
            }

            checkoutMarker.bindPopup(`<strong>${s.nombre}</strong><br><span style="font-size: 0.85em; color: #64748b;">${s.direccion}</span>`).openPopup();
            
            setTimeout(function() {
                if (checkoutMap) checkoutMap.invalidateSize();
            }, 150);
        }

        // Si es multi, escuchar cambios del select
        if (esMulti) {
            var select = document.querySelector('select[name="sucursal_recojo_id"]');
            if (select) {
                select.addEventListener('change', function () {
                    updateCheckoutMap(this.value);
                });
                // Initial check
                if (select.value) {
                    updateCheckoutMap(select.value);
                }
            }
        } else {
            // Si es sucursal única, cargar directamente
            if (sucursalesJson.length > 0) {
                updateCheckoutMap(sucursalesJson[0].id);
            }
        }
    })();
</script>
@endpush
