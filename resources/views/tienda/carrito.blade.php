@extends('tienda.layout')

@section('title', 'Carrito de Compras')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <h1 class="h3 fw-bold mb-0 text-slate-800">Carrito de Compras</h1>
    @if(!$items->isEmpty())
        <span class="badge bg-slate-100 text-slate-600 rounded-full px-2.5 py-1 text-xs font-semibold">
            {{ count($items) }} {{ count($items) === 1 ? 'producto' : 'productos' }}
        </span>
    @endif
</div>

@if ($items->isEmpty())
    <div class="store-card bg-white p-5 text-center rounded-2xl border border-slate-100">
        <div class="mb-4 text-slate-300">
            <svg class="mx-auto" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="width: 4.5rem; height: 4.5rem;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"></path>
            </svg>
        </div>
        <h3 class="h4 fw-bold text-slate-800">Tu carrito está vacío</h3>
        <p class="muted-copy mb-4">Parece que aún no has agregado medicamentos ni productos de cuidado personal a tu compra.</p>
        <a href="{{ route('tienda.index') }}" class="btn btn-store py-2.5 px-5">Ir al catálogo disponible</a>
    </div>
@else
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
                    <div class="store-card bg-white p-4 rounded-2xl border border-slate-100 hover:shadow-md transition-shadow">
                        <div class="row align-items-center g-3">
                            <!-- Miniatura Imagen -->
                            <div class="col-3 col-sm-2 col-md-1.5 text-center">
                                <div class="aspect-square bg-slate-50 rounded-xl flex items-center justify-center p-2 border border-slate-100" style="width: 60px; height: 60px;">
                                    @if($producto->imagen_url)
                                        <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                    @else
                                        <span class="text-emerald-600 font-bold" style="font-size: 1.25rem;">+</span>
                                    @endif
                                </div>
                            </div>
                            <!-- Datos del Producto -->
                            <div class="col-9 col-sm-6 col-md-5">
                                <span class="text-xs text-emerald-600 font-bold uppercase tracking-wider">{{ $categoriaNombre }}</span>
                                <h3 class="h6 font-bold text-slate-800 mb-1 mt-0.5">{{ $producto->nombre }}</h3>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span class="badge bg-slate-50 text-slate-600 border border-slate-100 rounded-lg px-2 py-1" style="font-size: 0.72rem; font-weight: 500;">
                                        📍 {{ $producto->sucursal->nombre ?? 'Sucursal' }}
                                    </span>
                                </div>
                            </div>
                            <!-- Precio Unitario -->
                            <div class="col-4 col-sm-2 col-md-1.5 text-sm text-slate-500">
                                <div class="d-sm-none text-xs text-slate-400">Unitario</div>
                                <span class="font-semibold text-slate-700">S/ {{ number_format($item['precio'], 2) }}</span>
                            </div>
                            <!-- Control de Cantidad -->
                            <div class="col-5 col-sm-2 col-md-2.5">
                                <div class="d-sm-none text-xs text-slate-400 mb-1">Cantidad</div>
                                <form method="POST" action="{{ route('tienda.carrito.update', $producto) }}" class="form-actualizar-cantidad">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="cantidad" value="{{ $item['cantidad'] }}" 
                                           onchange="this.form.submit()"
                                           min="1" max="{{ $item['stock_disponible'] === null ? 99 : min(99, $item['stock_disponible']) }}" 
                                           class="form-control form-control-sm border-slate-200 rounded-xl text-center font-bold text-sm bg-slate-50/50 py-1.5" style="max-width: 80px;">
                                </form>
                                <div class="text-slate-400 mt-1" style="font-size: 0.7rem;">
                                    {{ $item['stock_disponible'] === null ? 'Sin límite stock' : 'Stock: ' . $item['stock_disponible'] }}
                                </div>
                            </div>
                            <!-- Subtotal e Icono Eliminar -->
                            <div class="col-3 col-sm-2 col-md-1.5 text-end d-flex align-items-center justify-content-end gap-3">
                                <div>
                                    <div class="d-sm-none text-xs text-slate-400">Subtotal</div>
                                    <span class="font-extrabold text-emerald-600 text-base">S/ {{ number_format($item['subtotal'], 2) }}</span>
                                </div>
                                <form method="POST" action="{{ route('tienda.carrito.destroy', $producto) }}" class="mb-0">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-link text-slate-400 hover:text-danger p-2 rounded-xl hover:bg-danger-50 transition-colors" aria-label="Quitar {{ $producto->nombre }}">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 1.15rem; height: 1.15rem;">
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
                <a href="{{ route('tienda.index') }}" class="btn btn-outline-secondary btn-sm py-2 px-4 rounded-xl d-inline-flex align-items-center gap-1.5 font-semibold">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 0.9rem; height: 0.9rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Seguir comprando</span>
                </a>
            </div>
        </div>

        <!-- Resumen de Pedido -->
        <div class="col-lg-4">
            <div class="store-card bg-white p-4 rounded-2xl border border-slate-100 position-sticky" style="top: 100px;">
                <h2 class="h5 font-bold text-slate-800 pb-3 border-b border-slate-100 mb-3">Resumen del Pedido</h2>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-slate-500 text-sm">Subtotal de productos</span>
                    <span class="font-bold text-slate-700">S/ {{ number_format($total, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-slate-500 text-sm">Costo de recojo</span>
                    <span class="badge bg-emerald-50 text-emerald-600 rounded-lg py-1 px-2 text-xs font-bold">Gratuito</span>
                </div>
                
                <hr class="border-slate-100 my-3">
                
                <div class="d-flex justify-content-between align-items-baseline mb-4">
                    <span class="font-bold text-slate-800">Total estimado</span>
                    <div class="text-end">
                        <div class="h3 font-extrabold text-emerald-600 mb-0">S/ {{ number_format($total, 2) }}</div>
                        <span class="text-slate-400" style="font-size: 0.72rem;">IGV incluido</span>
                    </div>
                </div>

                <a href="{{ route('tienda.checkout.create') }}" class="btn btn-store w-100 py-3 rounded-xl font-bold tracking-wide shadow-md transition-all active:scale-98 d-inline-flex justify-content-center align-items-center gap-2">
                    <span>Finalizar Pedido</span>
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.15rem; height: 1.15rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
                
                <div class="text-center mt-3">
                    <span class="text-slate-400 text-xs d-inline-flex align-items-center gap-1">
                        🔒 Compra segura con cifrado SSL
                    </span>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
