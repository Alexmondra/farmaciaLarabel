@extends('adminlte::page')

@section('title', 'Editar producto tienda')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1 class="text-slate-800 font-weight-bold m-0"><i class="fas fa-edit mr-2 text-emerald-500"></i>Editar producto tienda</h1>
    <a href="{{ route('tienda.admin.productos.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
    </a>
</div>
@endsection

@section('content')
@include('tienda.partials.alerts')
<form method="POST" action="{{ route('tienda.admin.productos.update', $producto) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <input type="hidden" id="medicamento_id_seleccionado" value="{{ $producto->medicamento_id }}">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Medicamento Vinculado -->
                    <div class="card border-emerald bg-emerald-light shadow-xs mb-4">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="selected-icon-box">
                                        <i class="fas fa-prescription-bottle-alt text-emerald-600 fa-lg"></i>
                                    </div>
                                    <div>
                                        <span class="badge badge-emerald-solid mb-1"><i class="fas fa-link mr-1"></i> Ficha Vinculada</span>
                                        <h5 class="mb-1 font-weight-bold text-slate-800" id="seleccion_nombre">{{ $producto->medicamento->nombre ?? '-' }}</h5>
                                        <div class="d-flex flex-wrap gap-3 text-muted text-sm mt-2">
                                            <span class="mr-3"><i class="fas fa-store mr-1"></i> Sucursal: <strong class="text-slate-800">{{ $producto->sucursal->nombre ?? '-' }}</strong></span>
                                            <span class="mr-3"><i class="fas fa-cubes mr-1"></i> Stock real: <strong class="text-slate-800">{{ $producto->sucursal->pivot->stock ?? $producto->stock_web ?? '-' }}</strong></span>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="btn_editar_med" class="btn btn-warning btn-sm" title="Editar ficha médica">
                                    <i class="fas fa-edit mr-1"></i> Ficha Médica
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Campos del Formulario -->
                    <div class="form-group mb-4">
                        <label class="form-label-premium"><i class="fas fa-pen mr-1 text-emerald-500"></i> Nombre Comercial en Tienda</label>
                        <input name="nombre_web" value="{{ old('nombre_web', $producto->nombre_web) }}" class="form-control premium-input" placeholder="Nombre en la tienda virtual">
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label-premium"><i class="fas fa-align-left mr-1 text-emerald-500"></i> Descripción para Clientes</label>
                        <textarea name="descripcion_web" class="form-control premium-input" rows="4" placeholder="Escribe detalles del producto para la web...">{{ old('descripcion_web', $producto->descripcion_web) }}</textarea>
                    </div>

                    <!-- Banner de información -->
                    <div class="info-banner mb-4">
                        <i class="fas fa-info-circle info-banner-icon"></i>
                        <p class="mb-0">
                            La imagen principal se toma de la ficha médica. Si el medicamento no cuenta con una imagen principal, la primera que subas aquí quedará como la principal. Las adicionales irán a la galería.
                        </p>
                    </div>

                    <!-- Imagen desde ficha médica si existe -->
                    @if($producto->medicamento?->imagen_path)
                        <div class="mb-4">
                            <label class="form-label-premium">Imagen Principal de Ficha Médica</label>
                            <div class="image-preview-card d-inline-block p-2" style="max-width: 180px;">
                                <img src="{{ asset('storage/' . $producto->medicamento->imagen_path) }}" alt="{{ $producto->nombre_web }}" class="img-fluid" style="height: 110px; object-fit: contain;">
                                <span class="preview-badge badge-primary-soft d-block mt-1">Ficha Médica</span>
                            </div>
                        </div>
                    @endif

                    <!-- Caja de subida de imágenes adicionales -->
                    <div class="form-group mb-4">
                        <label class="form-label-premium"><i class="fas fa-images mr-1 text-emerald-500"></i> Agregar Imágenes de Tienda</label>
                        <label class="upload-box d-flex flex-column align-items-center justify-content-center text-center p-4 border rounded" for="imagenes">
                            <div class="upload-icon-circle mb-2">
                                <i class="fas fa-cloud-upload-alt fa-2x text-emerald-500"></i>
                            </div>
                            <strong class="text-slate-800 d-block mb-1">Arrastra o selecciona imágenes del producto</strong>
                            <span class="text-sm text-slate-500 max-w-md d-block">
                                Se convertirán automáticamente a formato WebP para mejorar el rendimiento de carga.
                            </span>
                        </label>
                        <input type="file" id="imagenes" name="imagenes[]" class="d-none" accept="image/*" multiple>
                        <div id="preview_imagenes" class="row mt-3"></div>
                    </div>

                    <!-- Galería de imágenes existentes -->
                    @if($producto->imagenes->isNotEmpty())
                        <hr class="my-4">
                        <h5 class="mb-3 text-slate-800 font-weight-bold"><i class="fas fa-images mr-2 text-emerald-500"></i> Galería de Imágenes Existentes</h5>
                        <div class="row">
                            @foreach($producto->imagenes as $imagen)
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="card h-100 border-slate shadow-sm overflow-hidden mb-0">
                                        <div class="bg-slate-50 d-flex align-items-center justify-content-center p-3" style="height: 140px; border-bottom: 1px solid var(--slate-200);">
                                            <img src="{{ $imagen->url }}" alt="{{ $imagen->alt ?: $producto->nombre_web }}" class="img-fluid" style="max-height: 100%; object-fit: contain;">
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="form-group mb-2">
                                                <label class="small mb-1 text-slate-600 font-weight-semibold">Texto descriptivo (Alt)</label>
                                                <input name="imagen_alt[{{ $imagen->id }}]" value="{{ old('imagen_alt.' . $imagen->id, $imagen->alt) }}" class="form-control form-control-sm premium-input" placeholder="Ej: Vista lateral">
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="small mb-1 text-slate-600 font-weight-semibold">Orden de visualización</label>
                                                <input type="number" min="0" name="imagen_orden[{{ $imagen->id }}]" value="{{ old('imagen_orden.' . $imagen->id, $imagen->orden) }}" class="form-control form-control-sm premium-input">
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between pt-2" style="border-top: 1px solid var(--slate-100);">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" name="imagen_visible[{{ $imagen->id }}]" value="1" class="custom-control-input" id="imagen_visible_{{ $imagen->id }}" @checked(old('imagen_visible.' . $imagen->id, $imagen->visible))>
                                                    <label class="custom-control-label small" for="imagen_visible_{{ $imagen->id }}">Visible</label>
                                                </div>
                                                <button type="submit" form="eliminar-imagen-{{ $imagen->id }}" class="btn btn-xs btn-outline-danger" onclick="return confirm('¿Eliminar esta imagen de la galería?')">
                                                    <i class="fas fa-trash-alt mr-1"></i> Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Columna de Configuración Lateral -->
        <div class="col-md-4">
            <div class="card card-sidebar-premium shadow-sm">
                <div class="card-body">
                    <h5 class="mb-4 text-slate-800 font-weight-bold border-bottom pb-2">
                        <i class="fas fa-sliders-h mr-2 text-emerald-500"></i>Ajustes de Venta
                    </h5>

                    <!-- Precio Web -->
                    <div class="form-group mb-4">
                        <label class="form-label-premium"><i class="fas fa-dollar-sign mr-1 text-emerald-500"></i> Precio Web (S/)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text premium-input-prepend"><i class="fas fa-tags text-muted"></i></span>
                            </div>
                            <input type="number" step="0.01" min="0" name="precio_web" value="{{ old('precio_web', $producto->precio_web) }}" class="form-control premium-input" placeholder="0.00" style="border-radius: 0 10px 10px 0 !important;">
                        </div>
                        <small id="precio_sucursal" class="form-text text-muted mt-2">
                            <i class="fas fa-info-circle mr-1 text-info"></i> Si se deja vacío, se utilizará el precio normal de sucursal.
                        </small>
                    </div>

                    <!-- Modo de Stock -->
                    <div class="form-group mb-4">
                        <label class="form-label-premium"><i class="fas fa-cubes mr-1 text-emerald-500"></i> Control de Stock</label>
                        <select name="stock_modo" id="stock_modo" class="form-control premium-input select-premium" required>
                            <option value="stock_sucursal" @selected(old('stock_modo', $producto->stock_modo) === 'stock_sucursal')>Usar stock real de sucursal</option>
                            <option value="stock_manual" @selected(old('stock_modo', $producto->stock_modo) === 'stock_manual')>Stock web manual</option>
                            <option value="sin_control" @selected(old('stock_modo', $producto->stock_modo) === 'sin_control')>Sin control de stock</option>
                        </select>
                    </div>

                    <!-- Stock Web -->
                    <div class="form-group mb-4" id="stock_web_group">
                        <label class="form-label-premium"><i class="fas fa-pencil-alt mr-1 text-emerald-500"></i> Stock Web Manual</label>
                        <input type="number" min="0" name="stock_web" value="{{ old('stock_web', $producto->stock_web) }}" class="form-control premium-input">
                    </div>

                    <!-- Visibilidad y Destacado -->
                    <div class="border-top pt-3 mb-4">
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" name="visible" value="1" class="custom-control-input" id="visible" @checked(old('visible', $producto->visible))>
                            <label class="custom-control-label" for="visible">Visible en Tienda Virtual</label>
                        </div>
                        <div class="custom-control custom-switch mb-2">
                            <input type="checkbox" name="destacado" value="1" class="custom-control-input" id="destacado" @checked(old('destacado', $producto->destacado))>
                            <label class="custom-control-label" for="destacado">Destacar en Portada</label>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <button class="btn btn-primary btn-block mb-2 py-2.5">
                        <i class="fas fa-save mr-1"></i> Guardar Cambios
                    </button>
                    <a href="{{ route('tienda.admin.productos.index') }}" class="btn btn-secondary btn-block py-2.5">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

@if($producto->imagenes->isNotEmpty())
    <div class="d-none">
        @foreach($producto->imagenes as $imagen)
            <form id="eliminar-imagen-{{ $imagen->id }}" method="POST" action="{{ route('tienda.admin.productos.imagenes.destroy', [$producto, $imagen]) }}">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    </div>
@endif

@include('inventario.medicamentos.general.modals')
@endsection

@push('css')
<style>
    /* Variables y Paleta de Colores Clínicos (Claro por Defecto) */
    :root {
        --primary-color: #10b981;
        --primary-hover: #059669;
        --primary-light: #ecfdf5;
        --primary-glow: rgba(16, 185, 129, 0.15);
        --primary-text: #059669;
        
        --slate-50: #f8fafc;
        --slate-100: #f1f5f9;
        --slate-200: #e2e8f0;
        --slate-300: #cbd5e1;
        --slate-500: #64748b;
        --slate-600: #475569;
        --slate-700: #334155;
        --slate-800: #1e293b;

        --card-bg: #ffffff;
        --input-bg: #ffffff;
        --input-text: #1e293b;
        
        --selected-bg-start: #f0fdf4;
        --selected-bg-end: #ffffff;
        --selected-border: rgba(16, 185, 129, 0.3);
        --selected-icon-bg: #d1fae5;
        --selected-icon-color: #059669;
        
        --upload-circle-bg: #f1f5f9;
        --upload-circle-hover-bg: #d1fae5;
        
        --info-banner-bg: #eff6ff;
        --info-banner-border: #bfdbfe;
        --info-banner-text: #1e3a8a;
        --info-banner-icon: #3b82f6;

        --badge-primary-soft-bg: #e6fffa;
        --badge-primary-soft-text: #047857;
        --badge-primary-soft-border: #a7f3d0;
    }

    /* Adaptación para Modo Oscuro de AdminLTE */
    .dark-mode {
        --primary-color: #10b981;
        --primary-hover: #059669;
        --primary-light: rgba(16, 185, 129, 0.1);
        --primary-glow: rgba(16, 185, 129, 0.3);
        --primary-text: #34d399;
        
        --slate-50: #2b3035;
        --slate-100: #343a40;
        --slate-200: #4b545c;
        --slate-300: #6c757d;
        --slate-500: #a0aec0;
        --slate-600: #cbd5e1;
        --slate-700: #e2e8f0;
        --slate-800: #f8fafc;

        --card-bg: #343a40;
        --input-bg: #3f474e;
        --input-text: #f8fafc;
        
        --selected-bg-start: rgba(16, 185, 129, 0.15);
        --selected-bg-end: rgba(16, 185, 129, 0.03);
        --selected-border: rgba(16, 185, 129, 0.5);
        --selected-icon-bg: rgba(16, 185, 129, 0.25);
        --selected-icon-color: #34d399;
        
        --upload-circle-bg: #4b545c;
        --upload-circle-hover-bg: rgba(16, 185, 129, 0.25);
        
        --info-banner-bg: rgba(59, 130, 246, 0.1);
        --info-banner-border: rgba(59, 130, 246, 0.3);
        --info-banner-text: #93c5fd;
        --info-banner-icon: #60a5fa;

        --badge-primary-soft-bg: rgba(16, 185, 129, 0.15);
        --badge-primary-soft-text: #34d399;
        --badge-primary-soft-border: rgba(16, 185, 129, 0.3);
    }

    /* Rediseño de Textos Globales */
    .text-slate-800 { color: var(--slate-800) !important; }
    .text-slate-700 { color: var(--slate-700) !important; }
    .text-slate-600 { color: var(--slate-600) !important; }
    .text-slate-500 { color: var(--slate-500) !important; }
    .text-emerald-600 { color: var(--primary-text) !important; }
    .text-emerald-800 { color: var(--primary-text) !important; }
    
    .bg-slate-50 {
        background-color: var(--slate-50) !important;
    }

    /* Rediseño de Cards */
    .card {
        border: 1px solid var(--slate-200) !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -1px rgba(0, 0, 0, 0.02) !important;
        background-color: var(--card-bg) !important;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .card-body {
        padding: 1.75rem !important;
    }

    .card-sidebar-premium {
        border-top: 4px solid var(--primary-color) !important;
    }

    /* Info Banner */
    .info-banner {
        display: flex;
        align-items: start;
        background-color: var(--info-banner-bg);
        border: 1px solid var(--info-banner-border);
        color: var(--info-banner-text);
        padding: 1rem;
        border-radius: 12px;
        font-size: 0.9rem;
        line-height: 1.4;
    }

    .info-banner-icon {
        font-size: 1.25rem;
        color: var(--info-banner-icon);
        margin-right: 0.75rem;
        margin-top: 0.1rem;
    }

    /* Etiquetas Premium */
    .form-label-premium {
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        color: var(--slate-700) !important;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.6rem !important;
        display: block;
    }

    /* Inputs Personalizados */
    .premium-input {
        border: 1px solid var(--slate-300) !important;
        border-radius: 10px !important;
        padding: 0.65rem 1rem !important;
        height: auto !important;
        font-size: 0.95rem !important;
        color: var(--input-text) !important;
        background-color: var(--input-bg) !important;
        transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out !important;
    }

    .premium-input:focus {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 3px var(--primary-glow) !important;
        outline: none !important;
    }

    .premium-input-prepend {
        background-color: var(--input-bg) !important;
        border-color: var(--slate-300) !important;
        border-right: none !important;
        border-radius: 10px 0 0 10px !important;
        color: var(--slate-500) !important;
        display: flex;
        align-items: center;
        padding: 0.375rem 0.75rem;
    }

    /* Select con Estilo */
    .select-premium {
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 10px 10px;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        padding-right: 2.5rem !important;
    }

    .dark-mode .select-premium {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23cbd5e1' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    }

    /* Contenedor de Selección Activa */
    .selected-icon-box {
        background-color: var(--selected-icon-bg);
        border-radius: 12px;
        width: 46px;
        height: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 2px 4px rgba(16, 185, 129, 0.1);
    }

    .selected-icon-box i {
        color: var(--selected-icon-color) !important;
    }

    .badge-emerald-solid {
        background-color: var(--primary-color);
        color: #ffffff;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        display: inline-block;
    }

    /* Caja de Carga de Archivos */
    .upload-box {
        background: var(--slate-50) !important;
        border: 2px dashed var(--slate-300) !important;
        border-radius: 12px !important;
        cursor: pointer;
        min-height: 130px;
        transition: all 0.2s ease;
        color: var(--slate-600);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1.75rem !important;
    }

    .upload-box:hover {
        background: var(--primary-light) !important;
        border-color: var(--primary-color) !important;
        color: var(--primary-hover);
    }

    .upload-icon-circle {
        background-color: var(--upload-circle-bg);
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.75rem;
        transition: all 0.2s ease;
    }

    .upload-box:hover .upload-icon-circle {
        background-color: var(--upload-circle-hover-bg);
        transform: scale(1.05);
    }

    /* Vista Previa de Imágenes en Cola */
    .image-preview-card {
        position: relative;
        border: 1px solid var(--slate-200);
        border-radius: 12px;
        padding: 8px;
        background-color: var(--card-bg);
        text-align: center;
        transition: all 0.25s ease;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .image-preview-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.05);
        border-color: var(--slate-300);
    }

    .image-preview-card img {
        height: 100px;
        width: 100%;
        object-fit: contain;
        border-radius: 8px;
        margin-bottom: 8px;
    }

    .preview-badge {
        display: inline-block;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.15rem 0.5rem;
        border-radius: 6px;
    }

    .badge-primary-soft {
        background-color: var(--badge-primary-soft-bg);
        color: var(--badge-primary-soft-text);
        border: 1px solid var(--badge-primary-soft-border);
    }

    .badge-slate-soft {
        background-color: var(--slate-100);
        color: var(--slate-600);
        border: 1px solid var(--slate-200);
    }

    /* Switches Personalizados */
    .custom-switch .custom-control-label::before {
        background-color: var(--slate-200);
        border-color: var(--slate-300);
        height: 1.5rem;
        width: 2.75rem;
        border-radius: 1rem;
        transition: background-color 0.2s ease, border-color 0.2s ease;
    }

    .custom-switch .custom-control-label::after {
        background-color: #ffffff;
        height: calc(1.5rem - 4px);
        width: calc(1.5rem - 4px);
        border-radius: 50%;
        top: calc(0.125rem + 2px);
        left: calc(-2.25rem + 2px);
        transition: transform 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
    }

    .custom-switch .custom-control-input:checked ~ .custom-control-label::before {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
    }

    .custom-switch .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateX(1.25rem);
    }

    .custom-switch .custom-control-label {
        padding-left: 1.25rem;
        padding-top: 2px;
        font-size: 0.95rem;
        font-weight: 500;
        color: var(--slate-700);
        cursor: pointer;
    }

    /* Botones Premium */
    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-hover)) !important;
        border: none !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        padding: 0.75rem 1.5rem !important;
        border-radius: 10px !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 4px 6px rgba(16, 185, 129, 0.15) !important;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--primary-hover), #047857) !important;
        box-shadow: 0 6px 12px rgba(16, 185, 129, 0.25) !important;
        transform: translateY(-1px);
    }

    .btn-primary:active {
        transform: translateY(1px);
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.15) !important;
    }

    .btn-secondary {
        background-color: var(--slate-100) !important;
        border: 1px solid var(--slate-200) !important;
        color: var(--slate-700) !important;
        font-weight: 600 !important;
        padding: 0.75rem 1.5rem !important;
        border-radius: 10px !important;
        transition: all 0.2s ease !important;
    }

    .btn-secondary:hover {
        background-color: var(--slate-200) !important;
        color: var(--slate-800) !important;
    }

    .btn-warning {
        background-color: #fef3c7 !important;
        border: 1px solid #fde68a !important;
        color: #b45309 !important;
        font-weight: 600 !important;
        padding: 0.65rem 1rem !important;
        border-radius: 10px !important;
        transition: all 0.2s ease !important;
    }

    .btn-warning:hover {
        background-color: #fde68a !important;
        color: #92400e !important;
    }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const stockModo = document.getElementById('stock_modo');
    const stockWebGroup = document.getElementById('stock_web_group');

    function toggleStockWeb() {
        stockWebGroup.style.display = stockModo.value === 'stock_manual' ? 'block' : 'none';
    }

    stockModo.addEventListener('change', toggleStockWeb);
    toggleStockWeb();

    document.getElementById('imagenes').addEventListener('change', (event) => {
        const preview = document.getElementById('preview_imagenes');
        preview.innerHTML = '';
        Array.from(event.target.files).forEach((file, index) => {
            const url = URL.createObjectURL(file);
            const badgeType = index === 0 ? 'badge-primary-soft' : 'badge-slate-soft';
            const badgeText = index === 0 ? 'Primera subida' : 'Galería';
            preview.insertAdjacentHTML('beforeend', `
                <div class="col-md-3 col-sm-4 col-6 mb-3">
                    <div class="image-preview-card">
                        <img src="${url}" class="img-fluid">
                        <span class="preview-badge ${badgeType}">${badgeText}</span>
                    </div>
                </div>
            `);
        });
    });

    const btnEditarMed = document.getElementById('btn_editar_med');
    const medIdHidden = document.getElementById('medicamento_id_seleccionado');
    const seleccionNombre = document.getElementById('seleccion_nombre');

    btnEditarMed.addEventListener('click', async () => {
        const medId = medIdHidden.value;
        if (!medId) return;

        btnEditarMed.disabled = true;
        btnEditarMed.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Cargando...';

        try {
            const response = await fetch(`/inventario/medicamentos/${medId}/detalle-json`);
            const info = await response.json();

            $('#edit_med_id').val(info.id);
            $('#edit_med_nombre').val(info.nombre);
            $('#edit_med_codigo').val(info.codigo);
            $('#edit_med_digemid').val(info.codigo_digemid);
            $('#edit_med_lab').val(info.laboratorio);
            $('#edit_med_cat').val(info.categoria_id);
            $('#edit_med_pres').val(info.presentacion);
            $('#edit_med_conc').val(info.concentracion);
            $('#edit_med_forma').val(info.forma_farmaceutica);
            $('#edit_med_desc').val(info.descripcion);
            $('#edit_med_reg').val(info.registro_sanitario);
            $('#edit_med_barra').val(info.codigo_barra);
            $('#edit_med_barra_blister').val(info.codigo_barra_blister);
            $('#edit_med_unidades').val(info.unidades_por_envase);
            $('#edit_med_unidades_blister').val(info.unidades_por_blister);
            $('#edit_med_igv').prop('checked', info.afecto_igv == 1 || info.afecto_igv === true);
            $('#edit_med_receta').prop('checked', info.receta_medica == 1 || info.receta_medica === true);

            if (info.imagen_url) {
                $('#img_med_foto_edit').attr('src', info.imagen_url).show();
                $('#div_med_placeholder_edit').hide();
            } else {
                $('#img_med_foto_edit').hide();
                $('#div_med_placeholder_edit').show();
            }

            $('#modalVerMedicamento').modal('show');
        } catch (err) {
            Swal.fire('Error', 'No se pudo cargar la información del medicamento.', 'error');
        } finally {
            btnEditarMed.disabled = false;
            btnEditarMed.innerHTML = '<i class="fas fa-edit mr-1"></i> Ficha Médica';
        }
    });

    $('#formEditarMedicamento').on('submit', function(e) {
        e.preventDefault();
        const id = $('#edit_med_id').val();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).text('Actualizando...');

        const formData = new FormData(this);
        formData.append('_method', 'PUT');

        $.ajax({
            url: `/inventario/medicamentos/${id}/update-rapido`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function() {
                $('#modalVerMedicamento').modal('hide');
                Swal.fire({ icon: 'success', title: 'Actualizado', timer: 1000, showConfirmButton: false });
                
                const newName = $('#edit_med_nombre').val();
                if (newName) {
                    seleccionNombre.textContent = newName;
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Error.';
                if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                Swal.fire('Error', msg, 'error');
            },
            complete: () => btn.prop('disabled', false).text('Actualizar Cambios')
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
