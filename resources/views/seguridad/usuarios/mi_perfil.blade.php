@extends('adminlte::page')

@section('title', 'Mi Perfil')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="text-dark font-weight-bold" style="letter-spacing: -1px;">
        <i class="fas fa-bolt text-warning mr-2"></i>Centro de Comando Personal
    </h1>
    <ol class="breadcrumb bg-transparent p-0 m-0">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item active">Perfil</li>
    </ol>
</div>
@stop

{{-- ======================================================= --}}
{{-- CONTENIDO PRINCIPAL: FORMULARIO Y ESTRUCTURA HTML --}}
{{-- ======================================================= --}}
@section('content')
<form action="{{ route('perfil.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">

        {{-- COLUMNA IZQUIERDA: TARJETA DE IDENTIDAD --}}
        <div class="col-md-4 mb-4">
            <div class="cyber-card h-100 text-center p-4">

                <h5 class="text-muted mb-4 font-weight-bold" style="letter-spacing: 1px;">IDENTIDAD DIGITAL</h5>

                {{-- ZONA DE FOTO CLICKABLE --}}
                <div class="profile-pic-wrapper mb-3" onclick="document.getElementById('imagen_perfil').click()">

                    {{-- 1. La Imagen (Con el truco del tiempo para evitar caché) --}}
                    @if($user->imagen_perfil)
                    <img id="preview_img"
                        src="{{ route('seguridad.usuarios.imagen', $user->id) . '?t=' . time() }}"
                        alt="Avatar">
                    @else
                    <img id="preview_img"
                        src="{{ asset('img/default-avatar.png') }}"
                        alt="Avatar">
                    @endif

                    {{-- 2. El Overlay (Capa oscura con icono) --}}
                    <div class="profile-overlay">
                        <i class="fas fa-camera camera-icon"></i>
                        <span class="text-white font-weight-bold small position-absolute" style="bottom: 20px">CAMBIAR FOTO</span>
                    </div>
                </div>

                {{-- Input oculto real --}}
                <input type="file" id="imagen_perfil" name="imagen_perfil" accept="image/*" style="display: none;" onchange="previewImage(this)">

                <h3 class="font-weight-bold text-dark mt-3">{{ $user->name }}</h3>
                <p class="text-muted mb-4">{{ $user->email }}</p>

                <div class="text-left mt-4">
                    <label class="small text-uppercase text-muted font-weight-bold ml-1">Roles Asignados</label>
                    <div class="d-flex flex-wrap mb-3">
                        @forelse($user->roles as $rol)
                        <span class="cyber-badge"><i class="fas fa-shield-alt mr-1 text-primary"></i> {{ $rol->name }}</span>
                        @empty
                        <span class="text-muted small ml-1">Sin rol</span>
                        @endforelse
                    </div>

                    <label class="small text-uppercase text-muted font-weight-bold ml-1">Sucursales Activas</label>
                    <div class="d-flex flex-wrap">
                        @forelse($user->sucursales as $sucursal)
                        <span class="cyber-badge"><i class="fas fa-building mr-1 text-success"></i> {{ $sucursal->nombre }}</span>
                        @empty
                        <span class="text-muted small ml-1">N/A</span>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

        {{-- COLUMNA DERECHA: FORMULARIO FUTURISTA --}}
        <div class="col-md-8">
            <div class="cyber-card p-4 p-md-5">

                <div class="d-flex align-items-center mb-4">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; background: var(--primary-gradient) !important;">
                        <i class="fas fa-user-edit text-white"></i>
                    </div>
                    <h4 class="m-0 font-weight-bold text-dark">Editar Información</h4>
                </div>

                {{-- GRUPO 1: DATOS PERSONALES --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="cyber-input-group">
                            <label class="cyber-label">Nombre Completo</label>
                            <input type="text" name="name" class="cyber-input @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}">
                            @error('name') <span class="text-danger small pl-2">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="cyber-input-group">
                            <label class="cyber-label">Correo Electrónico</label>
                            <input type="email" name="email" class="cyber-input @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}">
                            @error('email') <span class="text-danger small pl-2">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="cyber-input-group">
                            <label class="cyber-label">Documento / DNI</label>
                            <input type="text" name="documento" class="cyber-input @error('documento') is-invalid @enderror"
                                value="{{ old('documento', $user->documento) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="cyber-input-group">
                            <label class="cyber-label">Teléfono</label>
                            <input type="text" name="telefono" class="cyber-input"
                                value="{{ old('telefono', $user->telefono) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="cyber-input-group">
                            <label class="cyber-label">Dirección</label>
                            <input type="text" name="direccion" class="cyber-input"
                                value="{{ old('direccion', $user->direccion) }}">
                        </div>
                    </div>
                </div>

                <div class="divider-text">
                    <span><i class="fas fa-lock mr-1"></i> Seguridad y Acceso</span>
                </div>

                {{-- GRUPO 2: CONTRASEÑA --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="cyber-input-group">
                            <label class="cyber-label">Nueva Contraseña</label>
                            <input type="password" name="password" class="cyber-input @error('password') is-invalid @enderror"
                                placeholder="••••••••">
                            <small class="text-muted ml-2">Déjalo vacío si no deseas cambiarla</small>
                            @error('password') <span class="text-danger small d-block pl-2">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="cyber-input-group">
                            <label class="cyber-label">Confirmar Contraseña</label>
                            <input type="password" name="password_confirmation" class="cyber-input"
                                placeholder="••••••••">
                        </div>
                    </div>
                </div>

                {{-- BOTONES DE ACCIÓN --}}
                <div class="d-flex justify-content-end align-items-center mt-4">
                    {{-- Botón CANCELAR --}}
                    <a href="{{ route('dashboard') }}" class="btn-cyber-secondary mr-3 text-decoration-none">
                        <i class="fas fa-home mr-1"></i> Volver al Inicio
                    </a>

                    {{-- Botón GUARDAR --}}
                    <button type="submit" class="btn-cyber-primary">
                        Guardar Cambios <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>

            </div>
        </div>
    </div>
</form>
@stop


{{-- ======================================================= --}}
{{-- ESTILOS CSS --}}
{{-- ======================================================= --}}
@section('css')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-dark-bg: rgba(20, 25, 30, 0.95);
        /* Nuevo color oscuro */
        --neon-shadow: 0 0 15px rgba(118, 75, 162, 0.2);
    }

    /* Estilos base (Modo Claro) */
    .cyber-card {
        background: var(--glass-bg);
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        backdrop-filter: blur(10px);
        transition: transform 0.3s ease;
        overflow: hidden;
    }

    /* Input Flotante Moderno */
    .cyber-input-group {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .cyber-input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e1e5ee;
        border-radius: 12px;
        background: #f8f9fa;
        transition: all 0.3s ease;
        font-weight: 500;
        color: #495057;
    }

    .cyber-input:focus {
        border-color: #764ba2;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(118, 75, 162, 0.1);
        outline: none;
    }

    .cyber-label {
        position: absolute;
        left: 15px;
        top: -10px;
        background: #fff;
        padding: 0 5px;
        font-size: 12px;
        color: #764ba2;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Efecto Foto de Perfil */
    .profile-pic-wrapper {
        position: relative;
        width: 160px;
        height: 160px;
        margin: 0 auto;
        border-radius: 50%;
        cursor: pointer;
        overflow: hidden;
        border: 4px solid #fff;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s;
    }

    .profile-pic-wrapper:hover {
        transform: scale(1.05);
        box-shadow: 0 15px 35px rgba(118, 75, 162, 0.3);
    }

    .profile-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(33, 37, 41, 0.6);
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .profile-pic-wrapper:hover .profile-overlay {
        opacity: 1;
    }

    .camera-icon {
        color: #fff;
        font-size: 2rem;
    }

    /* Badges Modernos */
    .cyber-badge {
        background: #eff2f7;
        color: #555;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-right: 5px;
        border: 1px solid #dce1e9;
    }

    /* Botones */
    .btn-cyber-primary {
        background: var(--primary-gradient);
        border: none;
        color: white;
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(118, 75, 162, 0.3);
        transition: all 0.3s;
    }

    .btn-cyber-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(118, 75, 162, 0.4);
        color: white;
    }

    .btn-cyber-secondary {
        background: #fff;
        border: 2px solid #e1e5ee;
        color: #6c757d;
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-cyber-secondary:hover {
        background: #f8f9fa;
        border-color: #d1d5db;
        color: #495057;
    }

    .divider-text {
        display: flex;
        align-items: center;
        text-align: center;
        color: #a0aec0;
        margin: 2rem 0;
    }

    .divider-text::before,
    .divider-text::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e2e8f0;
    }

    .divider-text span {
        padding: 0 10px;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* === MODO OSCURO (Activado por la clase AdminLTE 'dark-mode' en el body) === */
    body.dark-mode .text-dark {
        color: #d1d9e0 !important;
        /* Asegura que h1 y otros text-dark sean claros */
    }

    body.dark-mode .cyber-card {
        background: var(--glass-dark-bg);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }

    body.dark-mode .profile-pic-wrapper {
        border-color: #3e444a;
        /* Borde oscuro */
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
    }

    body.dark-mode .font-weight-bold {
        color: #d1d9e0 !important;
    }

    body.dark-mode .text-muted,
    body.dark-mode .small.text-muted {
        color: #9da5af !important;
    }

    /* Inputs */
    body.dark-mode .cyber-input {
        background: #2b3035;
        border-color: #495057;
        color: #d1d9e0;
    }

    body.dark-mode .cyber-input:focus {
        background: #343a40;
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
    }

    body.dark-mode .cyber-label {
        background: #343a40;
        color: #667eea;
    }

    /* Badges */
    body.dark-mode .cyber-badge {
        background: #3e444a;
        color: #d1d9e0;
        border: 1px solid #495057;
    }

    /* Divider */
    body.dark-mode .divider-text {
        color: #6c757d;
    }

    body.dark-mode .divider-text::before,
    body.dark-mode .divider-text::after {
        border-bottom: 1px solid #495057;
    }

    /* Botón Secundario (Cancelar) */
    body.dark-mode .btn-cyber-secondary {
        background: #495057;
        border: 2px solid #5d6874;
        color: #d1d9e0;
    }

    body.dark-mode .btn-cyber-secondary:hover {
        background: #5d6874;
        border-color: #6c757d;
        color: #fff;
    }
</style>
@stop

{{-- ======================================================= --}}
{{-- SCRIPTS JS --}}
{{-- ======================================================= --}}
@section('js')
<script>
    // Script para previsualizar la imagen al seleccionarla
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function(e) {
                // Cambiamos el src de la imagen de perfil por la nueva data
                $('#preview_img').attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@stop