@extends('tienda.layout')

@section('title', 'Carrito de Compras')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    /* Estilos futuristas y transiciones SPA */
    .step-container {
        transition: opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1), transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        opacity: 1;
        transform: translateY(0);
    }
    .step-container.fade-out {
        opacity: 0;
        transform: translateY(-12px);
    }
    .step-container.d-none {
        display: none !important;
    }
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15) !important;
    }
    .cart-item-card {
        border: 1px solid rgba(241, 245, 249, 0.9);
        box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.02);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .cart-item-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.06);
        border-color: rgba(16, 185, 129, 0.15);
    }
    .step-icon {
        cursor: pointer;
    }
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
</style>
@endpush

@section('content')
@if ($items->isEmpty())
    <!-- Vista Carrito Vacío -->
    <div class="store-card bg-white p-5 text-center rounded-2xl border border-slate-100 shadow-sm max-w-2xl mx-auto my-5">
        <div class="mb-4 text-slate-300">
            <svg class="mx-auto" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="width: 4.5rem; height: 4.5rem;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"></path>
            </svg>
        </div>
        <h3 class="h4 fw-extrabold text-slate-800">Tu carrito está vacío</h3>
        <p class="muted-copy mb-4 text-slate-500">Parece que aún no has agregado medicamentos ni productos de cuidado personal a tu compra.</p>
        <a href="{{ route('tienda.index') }}" class="btn btn-store py-2.5 px-5 rounded-xl font-bold" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: 0; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">Ir al catálogo disponible</a>
    </div>
@else
    <!-- Stepper de Compra (Unificado) -->
    <div class="checkout-stepper mb-5">
        <div class="d-flex justify-content-between align-items-center position-relative mx-auto" style="max-width: 500px;">
            <!-- Line background -->
            <div class="position-absolute start-0 end-0 top-50 translate-middle-y bg-slate-200" style="height: 2px; z-index: 1; margin-top: -10px;"></div>
            <div id="stepper-progress-line" class="position-absolute start-0 top-50 translate-middle-y bg-emerald-500 transition-all duration-500" style="height: 2px; width: 0%; z-index: 1; margin-top: -10px;"></div>
            
            <!-- Step 1: Carrito -->
            <button type="button" onclick="goToStep(1)" class="btn p-0 border-0 d-flex flex-column align-items-center text-decoration-none position-relative" style="z-index: 2; width: 120px;" id="stepper-step-1">
                <div class="step-icon bg-emerald-500 text-white rounded-full d-flex align-items-center justify-content-center border-4 border-white shadow-md transition-all duration-300" style="width: 42px; height: 42px; margin-top: -2px;">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.2rem; height: 1.2rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1,0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0,1-1.12-1.243l1.264-12A1.125 1.125 0 0,1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1,1-.75 0 .375.375 0 0,1 .75 0Zm7.5 0a.375.375 0 1,1-.75 0 .375.375 0 0,1 .75 0Z"></path>
                    </svg>
                </div>
                <span class="step-text text-xs font-bold text-emerald-600 mt-2 transition-all">Carrito</span>
            </button>

            <!-- Step 2: Datos y Entrega -->
            <button type="button" onclick="handleStep2Click()" class="btn p-0 border-0 d-flex flex-column align-items-center text-decoration-none position-relative" style="z-index: 2; width: 120px;" id="stepper-step-2">
                <div class="step-icon bg-white text-slate-400 border border-slate-200 rounded-full d-flex align-items-center justify-content-center border-4 border-white shadow-sm transition-all duration-300" style="width: 38px; height: 38px;">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.1rem; height: 1.1rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"></path>
                    </svg>
                </div>
                <span class="step-text text-xs font-bold text-slate-400 mt-2 transition-all">Datos y Entrega</span>
            </button>

            <!-- Step 3: Confirmación -->
            <div class="d-flex flex-column align-items-center position-relative" style="z-index: 2; width: 120px;" id="stepper-step-3">
                <div class="step-icon bg-white text-slate-400 border border-slate-200 rounded-full d-flex align-items-center justify-content-center border-4 border-white shadow-sm transition-all duration-300" style="width: 38px; height: 38px;">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.1rem; height: 1.1rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"></path>
                    </svg>
                </div>
                <span class="step-text text-xs font-bold text-slate-400 mt-2 transition-all">Confirmación</span>
            </div>
        </div>
    </div>

    <!-- ================= PASO 1: CARRITO ================= -->
    <div id="step-cart" class="step-container">
        @if($esMultiSucursal)
            <div class="alert bg-amber-50 border border-amber-100 text-amber-900 rounded-2xl p-4 d-flex align-items-start gap-3 mb-4 shadow-sm">
                <div class="p-2 bg-amber-100/80 rounded-xl text-amber-700">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.35rem; height: 1.35rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div>
                    <strong class="font-bold text-amber-800">Productos de diferentes sucursales detectados</strong>
                    <div class="small mt-1 text-amber-700/90 leading-relaxed">
                        Te recomendamos elegir productos de una sola sucursal para poder recoger tu pedido de inmediato. Si continúas así, el tiempo de entrega será de **una semana** para centralizar todos los productos en un único punto de recojo.
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-4">
            <!-- Listado de Productos -->
            <div class="col-lg-8">
                <div class="d-flex flex-column gap-3">
                    @foreach ($items as $item)
                        @php
                            $producto = $item['producto'];
                            $categoriaNombre = $producto->medicamento->categoria->nombre ?? 'Medicamento';
                        @endphp
                        <div class="cart-item-card bg-white p-4 rounded-2xl border">
                            <div class="row align-items-center g-3">
                                <!-- Miniatura Imagen -->
                                <div class="col-3 col-sm-2 col-md-1.5 text-center">
                                    <div class="aspect-square bg-slate-50 rounded-xl flex items-center justify-center p-2 border border-slate-100" style="width: 60px; height: 60px; margin: 0 auto;">
                                        @if($producto->imagen_url)
                                            <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                        @else
                                            <span class="text-emerald-600 font-bold" style="font-size: 1.25rem;">+</span>
                                        @endif
                                    </div>
                                </div>
                                <!-- Datos del Producto -->
                                <div class="col-9 col-sm-5 col-md-5.5">
                                    <span class="text-xs text-emerald-600 font-bold uppercase tracking-wider">{{ $categoriaNombre }}</span>
                                    <h3 class="h6 font-extrabold text-slate-800 mb-1 mt-0.5">{{ $producto->nombre }}</h3>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <span class="badge bg-slate-50 text-slate-600 border border-slate-100 rounded-lg px-2 py-1" style="font-size: 0.72rem; font-weight: 500;">
                                            📍 {{ $producto->sucursal->nombre ?? 'Sucursal' }}
                                        </span>
                                    </div>
                                </div>
                                <!-- Precio Unitario -->
                                <div class="col-4 col-sm-2 col-md-1.5 text-sm text-slate-500 text-sm-center">
                                    <div class="d-sm-none text-xs text-slate-400">Unitario</div>
                                    <span class="font-semibold text-slate-700">S/ {{ number_format($item['precio'], 2) }}</span>
                                </div>
                                <!-- Control de Cantidad -->
                                <div class="col-5 col-sm-2 col-md-2">
                                    <div class="d-sm-none text-xs text-slate-400 mb-1">Cantidad</div>
                                    <form method="POST" action="{{ route('tienda.carrito.update', $producto) }}" class="form-actualizar-cantidad">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="cantidad" value="{{ $item['cantidad'] }}" 
                                               onchange="this.form.submit()"
                                               min="1" max="{{ $item['stock_disponible'] === null ? 99 : min(99, $item['stock_disponible']) }}" 
                                               class="form-control form-control-sm border-slate-200 rounded-xl text-center font-bold text-sm bg-slate-50/50 py-1.5 focus:bg-white" style="max-width: 80px; margin: 0 auto;">
                                    </form>
                                    <div class="text-slate-400 text-center mt-1" style="font-size: 0.7rem;">
                                        {{ $item['stock_disponible'] === null ? 'Sin límite' : 'Stock: ' . $item['stock_disponible'] }}
                                    </div>
                                </div>
                                <!-- Subtotal e Icono Eliminar -->
                                <div class="col-3 col-sm-1 col-md-1.5 text-end d-flex align-items-center justify-content-end gap-3">
                                    <div class="text-end">
                                        <div class="d-sm-none text-xs text-slate-400">Subtotal</div>
                                        <span class="font-extrabold text-emerald-600 text-base">S/ {{ number_format($item['subtotal'], 2) }}</span>
                                    </div>
                                    <form method="POST" action="{{ route('tienda.carrito.destroy', $producto) }}" class="mb-0">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-link text-slate-400 hover:text-rose-600 p-2 rounded-xl hover:bg-rose-50 transition-all active:scale-95" aria-label="Quitar {{ $producto->nombre }}">
                                            <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.15rem; height: 1.15rem;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-4">
                    <a href="{{ route('tienda.index') }}" class="btn btn-outline-secondary py-2 px-4 rounded-xl d-inline-flex align-items-center gap-1.5 font-bold transition-all active:scale-95 border-slate-200 text-slate-600 hover:bg-slate-50">
                        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 0.95rem; height: 0.95rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span>Seguir comprando</span>
                    </a>
                </div>
            </div>

            <!-- Resumen Lateral -->
            <div class="col-lg-4">
                <div class="store-card bg-white p-4 rounded-2xl border border-slate-100 position-sticky shadow-sm" style="top: 100px;">
                    <h2 class="h5 font-extrabold text-slate-800 pb-3 border-b border-slate-100 mb-3" style="font-size: 1.05rem;">Resumen del Pedido</h2>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2.5 text-xs text-slate-500 font-medium">
                        <span>Subtotal de productos</span>
                        <span class="font-bold text-slate-700">S/ {{ number_format($total, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 text-xs text-slate-500 font-medium">
                        <span>Costo de recojo</span>
                        <span class="badge bg-emerald-50 text-emerald-600 rounded-lg py-1 px-2 text-xs font-bold">Gratuito</span>
                    </div>
                    
                    <hr class="border-slate-100 my-3">
                    
                    <div class="d-flex justify-content-between align-items-baseline mb-4">
                        <span class="text-sm font-bold text-slate-800">Total estimado</span>
                        <div class="text-end">
                            <div class="h3 font-extrabold text-emerald-600 mb-0">S/ {{ number_format($total, 2) }}</div>
                            <span class="text-slate-400" style="font-size: 0.72rem;">IGV incluido</span>
                        </div>
                    </div>

                    <button type="button" onclick="goToStep(2)" class="btn btn-store w-100 py-3 rounded-xl font-bold tracking-wide shadow-md transition-all active:scale-95 d-inline-flex justify-content-center align-items-center gap-2" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: 0; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
                        <span>Finalizar Pedido</span>
                        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.15rem; height: 1.15rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                    
                    <div class="text-center mt-3">
                        <span class="text-slate-400 text-xs d-inline-flex align-items-center gap-1">
                            🔒 Compra segura con cifrado SSL
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= PASO 2: DATOS Y ENTREGA (CHECKOUT) ================= -->
    <div id="step-checkout" class="step-container d-none">
        @if(auth('tienda')->check() && $cliente)
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
                                    @if($montoInsuficienteOnline)
                                        <option value="PAGO_ONLINE" disabled>Pago online (Monto mínimo S/ 15.00)</option>
                                    @elseif($limiteOnlineAlcanzado)
                                        <option value="PAGO_ONLINE" disabled>Pago online (Límite: 3 pendientes alcanzado)</option>
                                    @else
                                        <option value="PAGO_ONLINE" @selected(old('metodo_pago') === 'PAGO_ONLINE')>Pago online (Con tarjeta/Yape)</option>
                                    @endif
                                </select>
                                @error('metodo_pago')
                                    <div class="invalid-feedback text-xs mt-1 d-block">{{ $message }}</div>
                                @enderror
                                @if($montoInsuficienteOnline)
                                    <div class="alert bg-rose-50/60 border border-rose-100 text-rose-800 rounded-xl p-2.5 mt-2.5 d-flex align-items-center gap-2 small mb-0">
                                        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1rem; height: 1rem; flex-shrink: 0;" class="text-rose-600">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        <span>El pago online requiere compras de S/ 15.00 o más.</span>
                                    </div>
                                @elseif($limiteOnlineAlcanzado)
                                    <div class="alert bg-rose-50/60 border border-rose-100 text-rose-800 rounded-xl p-2.5 mt-2.5 d-flex align-items-center gap-2 small mb-0">
                                        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1rem; height: 1rem; flex-shrink: 0;" class="text-rose-600">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        <span>Límite alcanzado: Tienes 3 pedidos con pago online pendientes de procesar.</span>
                                    </div>
                                @endif
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
                <div class="col-lg-5">
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

                        <div class="text-center mt-3">
                            <button type="button" onclick="goToStep(1)" class="btn btn-link text-xs text-slate-400 hover:text-emerald-600 font-semibold text-decoration-none d-inline-flex align-items-center gap-1 transition-all">
                                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 0.85rem; height: 0.85rem;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"></path>
                                </svg>
                                Volver al carrito
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @else
            <!-- Placeholder para no autenticados en el paso de checkout (aunque el click los redirigirá) -->
            <div class="text-center p-5">
                <div class="spinner-border text-emerald-500" role="status"></div>
                <p class="text-slate-500 mt-2">Redireccionando al inicio de sesión...</p>
            </div>
        @endif
    </div>
@endif

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

@push('scripts')
<script>
    // Variables globales inyectadas de Laravel
    window.isLoggedIn = @json(auth('tienda')->check());
</script>
<script>
    // Navegación Dinámica SPA y efectos
    function goToStep(step) {
        if (step === 2 && !window.isLoggedIn) {
            // Redirigir a login preservando la ruta y el paso de destino
            var path = window.location.pathname;
            window.location.href = '/tienda/login?redirect=' + encodeURIComponent(path + '?step=checkout');
            return;
        }

        var stepCart = document.getElementById('step-cart');
        var stepCheckout = document.getElementById('step-checkout');

        if (!stepCart || !stepCheckout) return;

        if (step === 1) {
            updateStepper(1);
            fadeTransition(stepCheckout, stepCart);
            // Actualizar URL sin recargar
            window.history.pushState({}, '', window.location.pathname);
        } else if (step === 2) {
            updateStepper(2);
            fadeTransition(stepCart, stepCheckout);
            window.history.pushState({}, '', window.location.pathname + '?step=checkout');
            
            // Inicializar/Rediseñar el mapa una vez visible
            setTimeout(function() {
                if (typeof updateCheckoutMap === 'function') {
                    var esMulti = @json($esMultiSucursal);
                    if (esMulti) {
                        var select = document.querySelector('select[name="sucursal_recojo_id"]');
                        if (select && select.value) {
                            updateCheckoutMap(select.value);
                        }
                    } else {
                        var sucursalesJson = @json($sucursalesJson);
                        if (sucursalesJson && sucursalesJson.length > 0) {
                            updateCheckoutMap(sucursalesJson[0].id);
                        }
                    }
                }
            }, 360);
        }
    }

    function handleStep2Click() {
        goToStep(2);
    }

    function updateStepper(step) {
        var progressLine = document.getElementById('stepper-progress-line');
        var step1 = document.getElementById('stepper-step-1');
        var step2 = document.getElementById('stepper-step-2');

        if (!progressLine || !step1 || !step2) return;

        if (step === 1) {
            progressLine.style.width = '0%';
            
            step1.querySelector('.step-icon').className = 'step-icon bg-emerald-500 text-white rounded-full d-flex align-items-center justify-content-center border-4 border-white shadow-md transition-all duration-300';
            step1.querySelector('.step-text').className = 'step-text text-xs font-bold text-emerald-600 mt-2 transition-all';
            
            step2.querySelector('.step-icon').className = 'step-icon bg-white text-slate-400 border border-slate-200 rounded-full d-flex align-items-center justify-content-center border-4 border-white shadow-sm transition-all duration-300';
            step2.querySelector('.step-text').className = 'step-text text-xs font-bold text-slate-400 mt-2 transition-all';
        } else if (step === 2) {
            progressLine.style.width = '50%';

            step1.querySelector('.step-icon').className = 'step-icon bg-emerald-100 text-emerald-600 rounded-full d-flex align-items-center justify-content-center border-4 border-white shadow-sm transition-all duration-300';
            step1.querySelector('.step-text').className = 'step-text text-xs font-bold text-slate-500 mt-2 transition-all';

            step2.querySelector('.step-icon').className = 'step-icon bg-emerald-500 text-white rounded-full d-flex align-items-center justify-content-center border-4 border-white shadow-md transition-all duration-300';
            step2.querySelector('.step-text').className = 'step-text text-xs font-bold text-emerald-600 mt-2 transition-all';
        }
    }

    function fadeTransition(fromEl, toEl) {
        fromEl.classList.add('fade-out');
        setTimeout(function() {
            fromEl.classList.add('d-none');
            fromEl.classList.remove('fade-out');
            
            toEl.classList.remove('d-none');
            toEl.classList.add('fade-out');
            toEl.offsetHeight; // Reflow
            toEl.classList.remove('fade-out');
        }, 300);
    }

    // Escuchar parámetros de carga inicial
    window.addEventListener('DOMContentLoaded', function() {
        var params = new URLSearchParams(window.location.search);
        if (params.get('step') === 'checkout') {
            goToStep(2);
        }
    });
</script>
@endpush

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
    var checkoutMap = null;
    var checkoutMarker = null;

    function updateCheckoutMap(sucursalId) {
        var sucursalesJson = @json($sucursalesJson);
        var s = sucursalesJson.find(function(x) { return x.id == sucursalId; });
        if (!s || !s.lat || !s.lng) {
            var container = document.getElementById('checkout_map_container');
            if (container) container.style.display = 'none';
            return;
        }

        var container = document.getElementById('checkout_map_container');
        if (container) container.style.display = 'block';

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

    (function () {
        var esMulti = @json($esMultiSucursal);
        if (esMulti) {
            var select = document.querySelector('select[name="sucursal_recojo_id"]');
            if (select) {
                select.addEventListener('change', function () {
                    updateCheckoutMap(this.value);
                });
            }
        }
    })();
</script>
@endpush
