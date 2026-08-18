@extends('adminlte::page')

@section('title', 'Publicar producto')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1 class="text-slate-800 font-weight-bold m-0"><i class="fas fa-rocket mr-2 text-emerald-500"></i>Publicar producto en tienda</h1>
    <a href="{{ route('tienda.admin.productos.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
    </a>
</div>
@endsection

@section('content')
@include('tienda.partials.alerts')
<form method="POST" action="{{ route('tienda.admin.productos.store') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="medicamento_sucursal" id="medicamento_sucursal" value="{{ old('medicamento_sucursal') }}">
    <input type="hidden" id="medicamento_id_seleccionado" value="">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Busqueda de Medicamento -->
                    <div class="form-group mb-4" id="grupo_busqueda">
                        <label class="form-label-premium"><i class="fas fa-search mr-1 text-emerald-500"></i> Buscar Medicamento en Inventario</label>
                        <div class="search-wrapper">
                            <i class="fas fa-search text-muted"></i>
                            <input type="search" id="buscar_medicamento" class="form-control premium-input" placeholder="Nombre, código interno o código de barras..." autocomplete="off">
                        </div>
                        <small class="form-text text-muted mt-2">
                            <i class="fas fa-info-circle mr-1 text-info"></i> Escribe al menos 2 caracteres. Solo aparecerán hasta 5 medicamentos con sucursales activas no publicadas.
                        </small>
                    </div>

                    <div id="resultados_medicamentos" class="mb-3"></div>

                    <!-- Contenedor Seleccionado Premium -->
                    <div id="contenedor_seleccionado" class="selected-product-container d-none mb-4">
                        <div class="card border-emerald bg-emerald-light shadow-xs mb-0">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="selected-icon-box">
                                            <i class="fas fa-prescription-bottle-alt text-emerald-600 fa-lg"></i>
                                        </div>
                                        <div>
                                            <span class="badge badge-emerald-solid mb-1"><i class="fas fa-check mr-1"></i> Medicamento Vinculado</span>
                                            <h5 class="mb-1 font-weight-bold text-slate-800" id="seleccion_nombre"></h5>
                                            <div class="d-flex flex-wrap gap-3 text-muted text-sm mt-2">
                                                <span class="mr-3"><i class="fas fa-store mr-1"></i> Sucursal: <strong class="text-slate-800" id="seleccion_sucursal"></strong></span>
                                                <span class="mr-3"><i class="fas fa-tag mr-1"></i> Precio base: <strong class="text-emerald-600" id="seleccion_precio"></strong></span>
                                                <span><i class="fas fa-cubes mr-1"></i> Stock real: <strong class="text-slate-800" id="seleccion_stock"></strong></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" id="btn_editar_med" class="btn btn-warning btn-sm" title="Editar ficha médica">
                                            <i class="fas fa-edit mr-1"></i> Ficha Médica
                                        </button>
                                        <button type="button" id="btn_quitar_seleccion" class="btn btn-outline-danger btn-sm" title="Desvincular medicamento">
                                            <i class="fas fa-times mr-1"></i> Desvincular
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datos Web del Producto -->
                    <div class="form-group mb-4">
                        <label class="form-label-premium"><i class="fas fa-pen mr-1 text-emerald-500"></i> Nombre Comercial en Tienda</label>
                        <input name="nombre_web" value="{{ old('nombre_web') }}" class="form-control premium-input" placeholder="Opcional. Si se deja vacío se usará el nombre oficial del medicamento.">
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label-premium"><i class="fas fa-align-left mr-1 text-emerald-500"></i> Descripción para Clientes</label>
                        <textarea name="descripcion_web" class="form-control premium-input" rows="4" placeholder="Escribe aquí detalles relevantes para el comprador (ej. uso sugerido, contraindicaciones, etc.)...">{{ old('descripcion_web') }}</textarea>
                    </div>

                    <!-- Caja de Subida Premium -->
                    <div class="form-group mb-0">
                        <label class="form-label-premium"><i class="fas fa-images mr-1 text-emerald-500"></i> Galería de Imágenes</label>
                        <label class="upload-box d-flex flex-column align-items-center justify-content-center text-center p-4 border rounded" for="imagenes">
                            <div class="upload-icon-circle mb-2">
                                <i class="fas fa-cloud-upload-alt fa-2x text-emerald-500"></i>
                            </div>
                            <strong class="text-slate-800 d-block mb-1">Arrastra o selecciona imágenes del producto</strong>
                            <span class="text-sm text-slate-500 max-w-md d-block">
                                La primera imagen subida se considerará principal (si la ficha técnica original no tiene una). Las adicionales se mostrarán en la galería del producto. Se convertirán a formato WebP.
                            </span>
                        </label>
                        <input type="file" id="imagenes" name="imagenes[]" class="d-none" accept="image/*" multiple>
                        <div id="preview_imagenes" class="row mt-3"></div>
                    </div>
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
                            <input type="number" step="0.01" min="0" name="precio_web" value="{{ old('precio_web') }}" class="form-control premium-input" placeholder="0.00" style="border-radius: 0 10px 10px 0 !important;">
                        </div>
                        <small id="precio_sucursal" class="form-text text-muted mt-2">
                            <i class="fas fa-info-circle mr-1 text-info"></i> Si se deja vacío, se utilizará el precio normal de sucursal.
                        </small>
                    </div>

                    <!-- Modo de Stock -->
                    <div class="form-group mb-4">
                        <label class="form-label-premium"><i class="fas fa-cubes mr-1 text-emerald-500"></i> Control de Stock</label>
                        <select name="stock_modo" id="stock_modo" class="form-control premium-input select-premium" required>
                            <option value="stock_sucursal" @selected(old('stock_modo', 'stock_sucursal') === 'stock_sucursal')>Usar stock real de sucursal</option>
                            <option value="stock_manual" @selected(old('stock_modo') === 'stock_manual')>Stock web manual</option>
                            <option value="sin_control" @selected(old('stock_modo') === 'sin_control')>Sin control de stock</option>
                        </select>
                    </div>

                    <!-- Stock Web -->
                    <div class="form-group mb-4" id="stock_web_group">
                        <label class="form-label-premium"><i class="fas fa-pencil-alt mr-1 text-emerald-500"></i> Stock Web Manual</label>
                        <input type="number" min="0" name="stock_web" value="{{ old('stock_web') }}" class="form-control premium-input">
                    </div>

                    <!-- Visibilidad y Destacado -->
                    <div class="border-top pt-3 mb-4">
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" name="visible" value="1" class="custom-control-input" id="visible" @checked(old('visible', true))>
                            <label class="custom-control-label" for="visible">Visible en Tienda Virtual</label>
                        </div>
                        <div class="custom-control custom-switch mb-2">
                            <input type="checkbox" name="destacado" value="1" class="custom-control-input" id="destacado" @checked(old('destacado'))>
                            <label class="custom-control-label" for="destacado">Destacar en Portada</label>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <button class="btn btn-primary btn-block mb-2 py-2.5">
                        <i class="fas fa-cloud-upload-alt mr-1"></i> Publicar Producto
                    </button>
                    <a href="{{ route('tienda.admin.productos.index') }}" class="btn btn-secondary btn-block py-2.5">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
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
        
        --sucursal-badge-hover-bg: #ecfdf5;
        --sucursal-badge-hover-text: #065f46;
        --price-badge-bg: #d1fae5;

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
        
        --sucursal-badge-hover-bg: rgba(16, 185, 129, 0.2);
        --sucursal-badge-hover-text: #34d399;
        --price-badge-bg: rgba(16, 185, 129, 0.25);

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

    /* Buscador con Icono */
    .search-wrapper {
        position: relative;
    }

    .search-wrapper i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--slate-500);
        font-size: 1rem;
    }

    .search-wrapper .form-control {
        padding-left: 2.75rem !important;
    }

    /* Tarjetas de Medicamentos Encontrados */
    .med-card {
        background: var(--card-bg);
        border: 1px solid var(--slate-200);
        border-radius: 12px;
        padding: 1.25rem;
        transition: all 0.2s ease-in-out;
        margin-bottom: 0.75rem;
    }

    .med-card:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08);
        background-color: var(--primary-light);
    }

    .med-title {
        font-size: 1.05rem;
        font-weight: 700;
    }

    .med-meta {
        font-size: 0.85rem;
        color: var(--slate-500);
    }

    .badge-lab {
        background-color: var(--slate-100);
        color: var(--slate-600);
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        border: 1px solid var(--slate-200);
    }

    .sucursales-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--slate-600);
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    /* Botón/Píldora de Sucursal */
    .sucursal-badge-btn {
        background-color: var(--card-bg);
        border: 1px solid var(--slate-300);
        color: var(--slate-700);
        padding: 0.45rem 0.8rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.2s ease;
        cursor: pointer;
        margin-right: 0.35rem;
        margin-bottom: 0.35rem;
        display: inline-flex;
        align-items: center;
        outline: none;
    }

    .sucursal-badge-btn:hover {
        background-color: var(--sucursal-badge-hover-bg);
        border-color: var(--primary-color);
        color: var(--sucursal-badge-hover-text);
        transform: translateY(-1px);
    }

    .sucursal-badge-btn .badge-price {
        font-weight: 700;
        color: var(--primary-text);
        background-color: var(--price-badge-bg);
        padding: 0.1rem 0.4rem;
        border-radius: 4px;
        margin-left: 0.5rem;
        font-size: 0.8rem;
    }

    .sucursal-badge-btn:hover .badge-price {
        background-color: var(--primary-color);
        color: #ffffff;
    }

    /* Contenedor de Selección Activa */
    .selected-product-container {
        margin-bottom: 1.5rem;
    }

    .selected-product-container .card {
        border: 1px solid var(--selected-border) !important;
        background: linear-gradient(135deg, var(--selected-bg-start) 0%, var(--selected-bg-end) 100%) !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.05) !important;
    }

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
        min-height: 150px;
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
    const input = document.getElementById('buscar_medicamento');
    const resultados = document.getElementById('resultados_medicamentos');
    const hidden = document.getElementById('medicamento_sucursal');
    const medIdHidden = document.getElementById('medicamento_id_seleccionado');
    const btnEditarMed = document.getElementById('btn_editar_med');
    const precioSucursal = document.getElementById('precio_sucursal');
    const stockModo = document.getElementById('stock_modo');
    const stockWebGroup = document.getElementById('stock_web_group');
    const grupoBusqueda = document.getElementById('grupo_busqueda');
    const contenedorSeleccionado = document.getElementById('contenedor_seleccionado');
    const seleccionNombre = document.getElementById('seleccion_nombre');
    const seleccionSucursal = document.getElementById('seleccion_sucursal');
    const seleccionPrecio = document.getElementById('seleccion_precio');
    const seleccionStock = document.getElementById('seleccion_stock');
    const btnQuitarSeleccion = document.getElementById('btn_quitar_seleccion');
    let timer;

    function toggleStockWeb() {
        stockWebGroup.style.display = stockModo.value === 'stock_manual' ? 'block' : 'none';
    }

    function resetSelection() {
        hidden.value = '';
        medIdHidden.value = '';
        contenedorSeleccionado.classList.add('d-none');
        grupoBusqueda.classList.remove('d-none');
        btnEditarMed.classList.add('d-none');
        precioSucursal.innerHTML = '<i class="fas fa-info-circle mr-1 text-info"></i> Si se deja vacío, se utilizará el precio normal de sucursal.';
        input.value = '';
        resultados.innerHTML = '';
        input.focus();
    }

    stockModo.addEventListener('change', toggleStockWeb);
    toggleStockWeb();

    btnQuitarSeleccion.addEventListener('click', resetSelection);

    input.addEventListener('input', () => {
        clearTimeout(timer);
        const q = input.value.trim();
        
        if (q.length < 2) {
            resultados.innerHTML = '';
            return;
        }

        timer = setTimeout(async () => {
            try {
                const response = await fetch(`{{ route('tienda.admin.productos.buscar_medicamentos') }}?q=${encodeURIComponent(q)}`);
                const data = await response.json();
                resultados.innerHTML = data.length ? data.map(renderMedicamento).join('') : '<div class="alert alert-light border text-center p-3 text-muted"><i class="fas fa-info-circle mr-1"></i> Sin resultados que coincidan.</div>';
            } catch (err) {
                console.error("Error fetching medications:", err);
                resultados.innerHTML = '<div class="alert alert-danger border text-center p-3"><i class="fas fa-exclamation-triangle mr-1"></i> Error al buscar medicamentos.</div>';
            }
        }, 250);
    });

    resultados.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-med-sucursal]');
        if (!btn) return;

        hidden.value = btn.dataset.medSucursal;
        medIdHidden.value = btn.dataset.medId;

        // Populate seleccion displays
        seleccionNombre.textContent = btn.dataset.nombre;
        seleccionSucursal.textContent = btn.dataset.sucursal;
        seleccionPrecio.textContent = `S/ ${Number(btn.dataset.precio).toFixed(2)}`;
        seleccionStock.textContent = btn.dataset.stock;

        contenedorSeleccionado.classList.remove('d-none');
        grupoBusqueda.classList.add('d-none');
        btnEditarMed.classList.remove('d-none');

        precioSucursal.innerHTML = `<i class="fas fa-info-circle mr-1 text-info"></i> Si se deja vacío, se utilizará el precio actual de sucursal: <strong>S/ ${Number(btn.dataset.precio).toFixed(2)}</strong>`;

        input.value = '';
        resultados.innerHTML = '';
    });

    // Restore old selection on validation fail
    const oldMedSucursal = hidden.value;
    if (oldMedSucursal) {
        const parts = oldMedSucursal.split(':');
        if (parts.length === 2) {
            const medId = parts[0];
            const sucursalId = parts[1];
            medIdHidden.value = medId;

            fetch(`/inventario/medicamentos/${medId}/detalle-json`)
                .then(r => r.json())
                .then(info => {
                    seleccionNombre.textContent = info.nombre;
                    const sucursal = info.sucursales ? info.sucursales.find(s => s.id == sucursalId) : null;
                    if (sucursal) {
                        seleccionSucursal.textContent = sucursal.nombre;
                        seleccionPrecio.textContent = `S/ ${Number(sucursal.precio).toFixed(2)}`;
                        seleccionStock.textContent = sucursal.stock;
                        precioSucursal.innerHTML = `<i class="fas fa-info-circle mr-1 text-info"></i> Si se deja vacío, se utilizará el precio actual de sucursal: <strong>S/ ${Number(sucursal.precio).toFixed(2)}</strong>`;
                    } else {
                        seleccionSucursal.textContent = 'Sucursal no encontrada';
                        seleccionPrecio.textContent = '-';
                        seleccionStock.textContent = '-';
                    }
                    contenedorSeleccionado.classList.remove('d-none');
                    grupoBusqueda.classList.add('d-none');
                    btnEditarMed.classList.remove('d-none');
                })
                .catch(err => console.error("Error restoring old selection:", err));
        }
    }

    function renderMedicamento(med) {
        const sucursales = med.sucursales.length
            ? med.sucursales.map(sucursal => `
                <button type="button" class="sucursal-badge-btn" 
                    data-med-sucursal="${med.id}:${sucursal.id}" 
                    data-med-id="${med.id}" 
                    data-nombre="${escapeHtml(med.nombre)}" 
                    data-sucursal="${escapeHtml(sucursal.nombre)}" 
                    data-precio="${sucursal.precio}" 
                    data-stock="${sucursal.stock}">
                    <i class="fas fa-store-alt mr-1"></i> ${escapeHtml(sucursal.nombre)} 
                    <span class="badge-price">S/ ${Number(sucursal.precio).toFixed(2)}</span>
                </button>
            `).join('')
            : '<span class="badge badge-secondary p-2"><i class="fas fa-exclamation-triangle mr-1"></i> Sin sucursales activas</span>';

        const labBadge = med.laboratorio ? `<span class="badge-lab"><i class="fas fa-flask mr-1"></i> ${escapeHtml(med.laboratorio)}</span>` : '';

        return `
            <div class="med-card">
                <div class="d-flex flex-column gap-1">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                        <strong class="med-title text-slate-800">${escapeHtml(med.nombre)}</strong>
                        ${labBadge}
                    </div>
                    <div class="med-meta mb-2">
                        <span><strong>Cód:</strong> ${escapeHtml(med.codigo || '-')}</span>
                        <span class="mx-2">|</span>
                        <span><strong>Reg. Sanitario:</strong> ${escapeHtml(med.registro_sanitario || '-')}</span>
                        <span class="mx-2">|</span>
                        <span><strong>Cód. Barra:</strong> ${escapeHtml(med.codigo_barra || '-')}</span>
                    </div>
                    <div class="mt-2">
                        <div class="sucursales-title mb-2"><i class="fas fa-map-marker-alt mr-1"></i> Selecciona la sucursal para publicar:</div>
                        <div class="d-flex flex-wrap gap-1">${sucursales}</div>
                    </div>
                </div>
            </div>
        `;
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>'"]/g, char => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'}[char]));
    }

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

    document.getElementById('imagenes').addEventListener('change', (event) => {
        const preview = document.getElementById('preview_imagenes');
        preview.innerHTML = '';
        Array.from(event.target.files).forEach((file, index) => {
            const url = URL.createObjectURL(file);
            const badgeType = index === 0 ? 'badge-primary-soft' : 'badge-slate-soft';
            const badgeText = index === 0 ? 'Principal (si falta)' : 'Galería';
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
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
