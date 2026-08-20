@extends('tienda.layout')
@php $hideGlobalErrors = true; @endphp

@section('title', 'Acceso a la Tienda')

@push('styles')
<style>
    /* Escenario de Perspectiva 3D */
    .auth-stage {
        perspective: 1200px;
        position: relative;
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
        transition: min-height 0.6s cubic-bezier(0.4, 0, 0.2, 1), height 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Tarjetas físicas individuales */
    .auth-card {
        position: absolute;
        top: 0;
        left: 50%;
        width: 92%;
        max-width: 440px;
        transition: all 0.7s cubic-bezier(0.4, 0, 0.2, 1);
        transform-style: preserve-3d;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.08);
    }

    .card-clientes {
        transition: all 0.7s cubic-bezier(0.4, 0, 0.2, 1), min-height 0.6s cubic-bezier(0.4, 0, 0.2, 1), height 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Fondos sólidos opacos por defecto para cada tarjeta */
    .card-personal {
        background-color: #0b2535 !important; /* Azul clínico medianoche sólido */
        border: 1.5px solid #144460 !important; /* Borde azul clínico */
        color: #ffffff;
        min-height: 520px;
        height: 520px;
        border-radius: 1.5rem;
    }

    /* --- ALINEACIONES 3D EN ESCRITORIO --- */

    /* Tarjeta activa (al centro y al frente) */
    .card-active {
        transform: translate3d(-50%, 0, 0) scale(1) rotate(0deg) !important;
        z-index: 30 !important;
        opacity: 1 !important;
        pointer-events: auto !important;
    }

    /* Clientes Inactiva (Personal Activo) */
    .state-personal .card-clientes.card-inactive {
        transform: translate3d(calc(-50% - 90px), 20px, -150px) scale(0.9) rotate(-4deg);
        z-index: 10;
        opacity: 1;
        cursor: pointer;
    }

    /* Personal Inactiva (Clientes Activo) */
    .state-clientes .card-personal.card-inactive {
        transform: translate3d(calc(-50% + 90px), 20px, -150px) scale(0.9) rotate(4deg);
        z-index: 10;
        opacity: 1;
        cursor: pointer;
    }

    /* Hover interactivo para asomarse más al puntero */
    .card-inactive:hover {
        opacity: 1 !important;
    }
    .state-personal .card-clientes.card-inactive:hover {
        transform: translate3d(calc(-50% - 100px), 15px, -120px) scale(0.92) rotate(-2deg);
    }
    .state-clientes .card-personal.card-inactive:hover {
        transform: translate3d(calc(-50% + 100px), 15px, -120px) scale(0.92) rotate(2deg);
    }

    /* Ocultar contenido interno de cualquier tarjeta inactiva */
    .card-inactive .avatar-wrapper,
    .card-inactive form,
    .card-inactive h1,
    .card-inactive p,
    .card-inactive .text-center {
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    
    /* Mostrar suavemente al activarse */
    .card-active .avatar-wrapper,
    .card-active form,
    .card-active h1,
    .card-active p,
    .card-active .text-center {
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto !important;
        transition: opacity 0.5s ease 0.2s, visibility 0.5s ease 0.2s;
    }

    /* Estructura Interna del Card Flip de Clientes */
    .flip-card-inner {
        position: relative;
        width: 100%;
        height: 100%;
        transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        transform-style: preserve-3d;
    }
    .flip-card-inner.flipped {
        transform: rotateY(180deg);
    }
    .flip-card-front, .flip-card-back {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
        border-radius: 1.5rem;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.08);
    }
    .flip-card-front {
        transform: rotateY(0deg);
        z-index: 2;
        background-color: #f0fdf4 !important; /* Verde menta médico claro sólido */
        border: 1.5px solid #a7f3d0 !important; /* Borde verde menta suave */
        color: #064e3b;
    }
    .flip-card-back {
        transform: rotateY(180deg);
        z-index: 1;
        background-color: #e6fcf5 !important; /* Verde esmeralda suave clínico sólido */
        border: 1.5px solid #96f2d7 !important; /* Borde esmeralda suave */
        color: #093325;
    }

    /* Ocultar la cara opuesta que queda de espaldas durante el reposo para evitar bugs de Webkit */
    .flip-card-inner.flipped .flip-card-front {
        visibility: hidden !important;
        pointer-events: none !important;
    }
    .flip-card-inner:not(.flipped) .flip-card-back {
        visibility: hidden !important;
        pointer-events: none !important;
    }

    /* Animaciones de los avatares (monitos interactivos) */
    .avatar-wrapper {
        position: relative;
        width: 120px;
        height: 60px;
        margin: 0 auto -10px auto;
        z-index: 10;
    }
    .avatar-svg {
        width: 120px;
        height: 120px;
        position: absolute;
        top: -60px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
    }
    .arm {
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        transform-origin: bottom center;
    }
    .arm-left {
        transform: translateY(110px) rotate(-10deg);
    }
    .arm-right {
        transform: translateY(110px) rotate(10deg);
    }
    .cover-eyes .arm-left {
        transform: translateY(15px) translateX(5px) rotate(10deg);
    }
    .cover-eyes .arm-right {
        transform: translateY(15px) translateX(-5px) rotate(-10deg);
    }
    .pupil {
        transition: transform 0.2s;
    }
    .looking .pupil {
        transform: translateY(2px);
    }

    /* --- ALINEACIONES 3D EN DISPOSITIVOS MÓVILES (Desfase vertical) --- */
    @media (max-width: 767.98px) {
        .auth-stage {
            max-width: 440px;
        }

        /* Clientes Inactiva en móvil (Personal Activo) */
        .state-personal .card-clientes.card-inactive {
            transform: translate3d(-50%, 25px, -50px) scale(0.96) rotate(0deg);
            z-index: 10;
        }

        /* Personal Inactiva en móvil (Clientes Activo) */
        .state-clientes .card-personal.card-inactive {
            transform: translate3d(-50%, -25px, -50px) scale(0.96) rotate(0deg);
            z-index: 10;
        }

        /* Hover interactivo móvil simplificado */
        .card-inactive:hover {
            transform: translate3d(-50%, -5px, 0) scale(1) rotate(0deg) !important;
            z-index: 40 !important;
        }
    }
</style>
@endpush

@section('content')
@php
    // Determinamos la pestaña activa de clientes inicial
    $clientesTabInicial = 'login';
    $cardInicial = 'clientes';

    // Determinar si viene de query param (?tab=register o ?tab=login_personal)
    $tabParam = request('tab', 'login');
    if ($tabParam === 'login_personal') {
        $cardInicial = 'personal';
    } elseif ($tabParam === 'register') {
        $clientesTabInicial = 'register';
    }

    // Forzar tab si hay errores específicos de registro en la sesión
    if ($errors->hasAny(['documento', 'nombre', 'password_confirmation', 'tipo_documento'])) {
        $cardInicial = 'clientes';
        $clientesTabInicial = 'register';
    }
    // Forzar tab si hay errores del login de personal (correo/contraseña)
    elseif ($errors->has('email') && !$errors->hasAny(['documento', 'nombre'])) {
        $cardInicial = 'personal';
    }
@endphp

<div class="row justify-content-center my-4 md:my-5">
    <div class="col-12 d-flex justify-content-center">
        
        <!-- Escenario de acordeón 3D de 2 tarjetas -->
        <div x-data="{ 
                activeCard: '{{ $cardInicial }}',
                clientesTab: '{{ $clientesTabInicial }}',
                loginFocus: false,
                passFocus: false,
                personalFocus: false,
                personalPassFocus: false,
                registerFocus: false,
                registerPassFocus: false
             }" 
             class="auth-stage"
             :class="'state-' + activeCard"
             :style="activeCard === 'clientes' && clientesTab === 'register' ? 'min-height: 920px; height: 920px;' : 'min-height: 570px; height: 570px;'">
            
            <!-- TARJETA 1: CLIENTES (Lado Front: Login / Lado Back: Registro) -->
            <div class="auth-card card-clientes"
                 :class="activeCard === 'clientes' ? 'card-active' : 'card-inactive'"
                 :style="clientesTab === 'register' ? 'min-height: 870px; height: 870px;' : 'min-height: 520px; height: 520px;'"
                 @click="if (activeCard !== 'clientes') { activeCard = 'clientes'; }">
                
                <div class="flip-card-inner h-100" :class="{'flipped': clientesTab === 'register'}">
                    
                    <!-- FRENTE: LOGIN CLIENTE -->
                    <div class="flip-card-front shadow-xl p-4 p-md-5">
                        
                        <!-- Avatar Cliente -->
                        <div class="avatar-wrapper" :class="{'looking': loginFocus, 'cover-eyes': passFocus}">
                            <svg class="avatar-svg" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="60" cy="60" r="50" fill="#e0f2fe" stroke="#334155" stroke-width="3" />
                                <path d="M20 45 Q 60 15 100 45 Q 60 30 20 45 Z" fill="#475569" stroke="#334155" stroke-width="3" />
                                <g id="client-eyes">
                                    <circle cx="45" cy="70" r="5" fill="#1e293b" />
                                    <circle cx="75" cy="70" r="5" fill="#1e293b" />
                                    <circle class="pupil" cx="47" cy="68" r="1.5" fill="white" />
                                    <circle class="pupil" cx="77" cy="68" r="1.5" fill="white" />
                                </g>
                                <path id="client-mouth" d="M45 85 Q 60 98 75 85" fill="none" stroke="#be123c" stroke-width="3" stroke-linecap="round" />
                                <g class="arm arm-left">
                                    <path d="M10 80 Q 20 50 45 60 Q 30 90 20 100 Z" fill="#f1f5f9" stroke="#334155" stroke-width="3" />
                                </g>
                                <g class="arm arm-right">
                                    <path d="M110 80 Q 100 50 75 60 Q 90 90 100 100 Z" fill="#f1f5f9" stroke="#334155" stroke-width="3" />
                                </g>
                            </svg>
                        </div>

                        <div class="text-center mb-4">
                            <h1 class="h4 font-extrabold text-emerald-950 tracking-tight mb-1" style="font-weight: 800;">Acceso Clientes</h1>
                            <p class="text-xs text-emerald-800/80 font-medium px-2 leading-relaxed">Ingresa a tu cuenta de farmacia virtual.</p>
                        </div>

                        <!-- Formulario de Acceso Cliente -->
                        <form method="POST" action="{{ route('tienda.login') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="text-xs font-semibold text-emerald-900/80 mb-1.5 d-block">Documento, correo o teléfono</label>
                                <div class="position-relative">
                                    <span class="position-absolute top-50 translate-middle-y text-emerald-600/60" style="left: 1rem; z-index: 10;">
                                        <i class="fas fa-user-circle"></i>
                                    </span>
                                    <input type="text" name="login" value="{{ old('login') }}" 
                                           @focus="loginFocus = true" @blur="loginFocus = false"
                                           class="w-100 py-2.5 rounded-2xl border border-emerald-200/80 text-emerald-950 bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all" 
                                           style="padding-left: 2.5rem; padding-right: 1rem; font-size: 0.92rem; border-radius: 1rem;" 
                                           placeholder="Ej. 72345678" required autofocus>
                                </div>
                                @error('login')
                                    <div class="invalid-feedback d-block text-xs font-semibold mt-1 pl-2 text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="text-xs font-semibold text-emerald-900/80 mb-1.5 d-block">Contraseña</label>
                                <div class="position-relative">
                                    <span class="position-absolute top-50 translate-middle-y text-emerald-600/60" style="left: 1rem; z-index: 10;">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" name="password" 
                                           @focus="passFocus = true" @blur="passFocus = false"
                                           class="w-100 py-2.5 rounded-2xl border border-emerald-200/80 text-emerald-950 bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all" 
                                           style="padding-left: 2.5rem; padding-right: 1rem; font-size: 0.92rem; border-radius: 1rem;" 
                                           placeholder="••••••••" required>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block text-xs font-semibold mt-1 pl-2 text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <input type="checkbox" name="remember" class="rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500" id="remember" style="width: 16px; height: 16px; cursor: pointer;">
                                    <label class="text-xs font-medium text-emerald-800 ml-2" for="remember" style="cursor: pointer; margin-left: 0.5rem;">Recordarme</label>
                                </div>
                                <!-- Botón para ir a Personal en el mismo escenario (desplaza las tarjetas) -->
                                <button type="button" @click.stop="activeCard = 'personal'" class="text-xs font-bold text-decoration-none text-emerald-600 hover:text-emerald-700 border-0 bg-transparent p-0">Acceso Personal &rarr;</button>
                            </div>

                            <button class="w-100 py-2.5 text-white font-bold rounded-2xl border-0 transition-all shadow-md hover:shadow-lg d-flex justify-content-center align-items-center gap-2 active:scale-95"
                                    style="background: linear-gradient(135deg, var(--store-green) 0%, var(--store-green-dark) 100%); font-size: 0.95rem; border-radius: 1rem; cursor: pointer;">
                                <span>Ingresar como Cliente</span>
                                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.1rem; height: 1.1rem;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </button>
                        </form>

                        <div class="text-center mt-4 pt-2 border-t border-emerald-100/60">
                            <span class="text-xs text-emerald-800/60 font-medium">¿Nuevo en nuestra tienda?</span>
                            <!-- Botón para girar al Registro (Dorso de la misma tarjeta) -->
                            <button type="button" @click.stop="clientesTab = 'register'" class="text-xs font-bold text-decoration-none transition-all ml-1 border-0 bg-transparent p-0 text-emerald-600 hover:text-emerald-700" style="font-weight: bold; margin-left: 0.25rem;">Regístrate gratis</button>
                        </div>
                    </div>

                    <!-- DORSO: REGISTRO CLIENTE -->
                    <div class="flip-card-back shadow-xl p-4 p-md-5">
                        
                        <!-- Avatar Registro -->
                        <div class="avatar-wrapper" :class="{'looking': registerFocus, 'cover-eyes': registerPassFocus}">
                            <svg class="avatar-svg" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="60" cy="60" r="50" fill="#e0f2fe" stroke="#334155" stroke-width="3" />
                                <path d="M20 45 Q 60 15 100 45 Q 60 30 20 45 Z" fill="#475569" stroke="#334155" stroke-width="3" />
                                <g id="reg-eyes">
                                    <circle cx="45" cy="70" r="5" fill="#1e293b" />
                                    <circle cx="75" cy="70" r="5" fill="#1e293b" />
                                    <circle class="pupil" cx="47" cy="68" r="1.5" fill="white" />
                                    <circle class="pupil" cx="77" cy="68" r="1.5" fill="white" />
                                </g>
                                <path id="reg-mouth" d="M45 85 Q 60 98 75 85" fill="none" stroke="#be123c" stroke-width="3" stroke-linecap="round" />
                                <g class="arm arm-left">
                                    <path d="M10 80 Q 20 50 45 60 Q 30 90 20 100 Z" fill="#f1f5f9" stroke="#334155" stroke-width="3" />
                                </g>
                                <g class="arm arm-right">
                                    <path d="M110 80 Q 100 50 75 60 Q 90 90 100 100 Z" fill="#f1f5f9" stroke="#334155" stroke-width="3" />
                                </g>
                            </svg>
                        </div>

                        <div class="text-center mb-3">
                            <h1 class="h4 font-extrabold text-emerald-950 tracking-tight mb-1" style="font-weight: 800;">Crear Cuenta</h1>
                            <p class="text-xs text-emerald-800/80 font-medium px-2">Crea tu cuenta virtual gratis en segundos.</p>
                        </div>

                        <form method="POST" action="{{ route('tienda.register') }}" id="form-register">
                            @csrf
                            <input type="hidden" name="tipo_documento" id="tipo_documento_hidden" value="{{ old('tipo_documento', 'DNI') }}">

                            <!-- Tipo y Documento -->
                            <div class="row g-2 mb-2" style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                                <div style="flex: 0 0 30%;">
                                    <label class="text-[10px] font-semibold text-emerald-900/80 mb-1 d-block">Tipo Doc.</label>
                                    <select id="tipo_documento" class="w-100 py-2 rounded-2xl border border-emerald-200 text-emerald-950 bg-white focus:outline-none focus:border-emerald-500" style="width: 100%; border-radius: 0.85rem; padding-top: 0.5rem; padding-bottom: 0.5rem; font-size: 0.88rem; outline: none; border: 1px solid #a7f3d0; padding-left: 0.25rem;">
                                        <option value="DNI" @selected(old('tipo_documento') === 'DNI')>DNI</option>
                                        <option value="RUC" @selected(old('tipo_documento') === 'RUC')>RUC</option>
                                        <option value="CE" @selected(old('tipo_documento') === 'CE')>CE</option>
                                    </select>
                                </div>
                                <div style="flex: 1;">
                                    <label class="text-[10px] font-semibold text-emerald-900/80 mb-1 d-block">Número Documento</label>
                                    <div class="position-relative" style="position: relative;">
                                        <span class="position-absolute top-50 translate-middle-y text-emerald-600/60" style="left: 0.75rem; z-index: 10;">
                                            <i class="fas fa-id-card"></i>
                                        </span>
                                        <input type="text" name="documento" id="documento" value="{{ old('documento') }}" 
                                               @focus="registerFocus = true" @blur="registerFocus = false"
                                               class="w-100 py-2 rounded-2xl border border-emerald-200 text-emerald-950 bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all" 
                                               style="padding-left: 2.25rem; padding-right: 2.5rem; font-size: 0.88rem; border-radius: 0.85rem;" 
                                               placeholder="Ej. 72345678" required maxlength="20">
                                        <button type="button" id="btn-buscar-doc" class="position-absolute top-50 translate-middle-y border-0 bg-transparent text-emerald-600 hover-text-primary" 
                                                style="position: absolute; top: 50%; transform: translateY(-50%); right: 0.75rem; z-index: 10; cursor: pointer; color: var(--store-green);">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div id="doc-feedback" class="form-text text-[10px] pl-2 mb-2 text-emerald-700/60" style="margin-top: -0.25rem;"></div>

                            <!-- Nombre Completo -->
                            <div class="mb-2">
                                <label class="text-[10px] font-semibold text-emerald-900/80 mb-1 d-block">Nombre completo o Razón social</label>
                                <div class="position-relative">
                                    <span class="position-absolute top-50 translate-middle-y text-emerald-600/60" style="left: 0.75rem; z-index: 10;">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" 
                                           class="w-100 py-2 rounded-2xl border border-emerald-200 text-emerald-950 bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all" 
                                           style="padding-left: 2.25rem; padding-right: 1rem; font-size: 0.88rem; border-radius: 0.85rem;" required>
                                </div>
                                @error('nombre')
                                    <div class="invalid-feedback d-block text-[10px] font-semibold mt-0.5 pl-2 text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Correo -->
                            <div class="mb-2">
                                <label class="text-[10px] font-semibold text-emerald-900/80 mb-1 d-block">Correo <span class="text-emerald-700/50 font-normal">(Opcional)</span></label>
                                <div class="position-relative">
                                    <span class="position-absolute top-50 translate-middle-y text-emerald-600/60" style="left: 0.75rem; z-index: 10;">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" 
                                           class="w-100 py-2 rounded-2xl border border-emerald-200 text-emerald-950 bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all" 
                                           style="padding-left: 2.25rem; padding-right: 1rem; font-size: 0.88rem; border-radius: 0.85rem;" 
                                           placeholder="ejemplo@correo.com">
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block text-[10px] font-semibold mt-0.5 pl-2 text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Telefono -->
                            <div class="mb-2">
                                <label class="text-[10px] font-semibold text-emerald-900/80 mb-1 d-block">Teléfono <span class="text-emerald-700/50 font-normal">(Opcional)</span></label>
                                <div class="position-relative">
                                    <span class="position-absolute top-50 translate-middle-y text-emerald-600/60" style="left: 0.75rem; z-index: 10;">
                                        <i class="fas fa-phone-alt"></i>
                                    </span>
                                    <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" 
                                           class="w-100 py-2 rounded-2xl border border-emerald-200 text-emerald-950 bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all" 
                                           style="padding-left: 2.25rem; padding-right: 1rem; font-size: 0.88rem; border-radius: 0.85rem;" 
                                           placeholder="Ej. 987654321">
                                </div>
                                @error('telefono')
                                    <div class="invalid-feedback d-block text-[10px] font-semibold mt-0.5 pl-2 text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Contraseña -->
                            <div class="mb-3">
                                <label class="text-[10px] font-semibold text-emerald-900/80 mb-1 d-block">Contraseña</label>
                                <div class="position-relative">
                                    <span class="position-absolute top-50 translate-middle-y text-emerald-600/60" style="left: 0.75rem; z-index: 10;">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" name="password" 
                                           @focus="registerPassFocus = true" @blur="registerPassFocus = false"
                                           class="w-100 py-2 rounded-2xl border border-emerald-200 text-emerald-950 bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all" 
                                           style="padding-left: 2.25rem; padding-right: 1rem; font-size: 0.88rem; border-radius: 0.85rem;" 
                                           placeholder="Mín. 8 caracteres" required>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block text-[10px] font-semibold mt-0.5 pl-2 text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <button id="btn-submit" class="w-100 py-2 text-white font-bold rounded-2xl border-0 transition-all shadow-md hover:shadow-lg d-flex justify-content-center align-items-center gap-2 active:scale-95"
                                    style="background: linear-gradient(135deg, var(--store-green) 0%, var(--store-green-dark) 100%); font-size: 0.9rem; border-radius: 0.85rem; border: none; cursor: pointer;">
                                <span>Crear cuenta</span>
                            </button>
                        </form>

                        <div class="text-center mt-3 pt-2 border-t border-emerald-100/60">
                            <span class="text-xs text-emerald-800/60 font-medium">¿Ya tienes cuenta?</span>
                            <!-- Botón para girar al Login de Clientes (Frente de la misma tarjeta) -->
                            <button type="button" @click.stop="clientesTab = 'login'" class="text-xs font-bold text-decoration-none transition-all ml-1 border-0 bg-transparent p-0 text-emerald-600 hover:text-emerald-700" style="font-weight: bold; margin-left: 0.25rem;">Inicia sesión (Volver)</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TARJETA 2: ACCESO PERSONAL -->
            <div class="auth-card card-personal p-4 p-md-5"
                 :class="activeCard === 'personal' ? 'card-active' : 'card-inactive'"
                 @click="if (activeCard !== 'personal') { activeCard = 'personal'; }">
                
                <!-- Avatar Personal -->
                <div class="avatar-wrapper" :class="{'looking': personalFocus, 'cover-eyes': personalPassFocus}">
                    <svg class="avatar-svg" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="60" cy="60" r="50" fill="#1e293b" stroke="#475569" stroke-width="3" />
                        <path d="M10 60 Q 60 20 110 60 L 110 50 Q 60 10 10 50 Z" fill="#334155" stroke="#475569" stroke-width="3" />
                        <path d="M56 35 h8 v8 h-8 z M60 31 v16" stroke="#2dd4bf" stroke-width="3" stroke-linecap="round" />
                        <g id="eyes">
                            <circle cx="45" cy="75" r="5" fill="#f8fafc" />
                            <circle cx="75" cy="75" r="5" fill="#f8fafc" />
                            <circle class="pupil" cx="47" cy="73" r="1.5" fill="#0f172a" />
                            <circle class="pupil" cx="77" cy="73" r="1.5" fill="#0f172a" />
                        </g>
                        <path id="mouth" d="M45 90 Q 60 100 75 90" fill="none" stroke="#2dd4bf" stroke-width="3" stroke-linecap="round" />
                        <g class="arm arm-left">
                            <path d="M10 80 Q 20 50 45 60 Q 30 90 20 100 Z" fill="#334155" stroke="#475569" stroke-width="3" />
                        </g>
                        <g class="arm arm-right">
                            <path d="M110 80 Q 100 50 75 60 Q 90 90 100 100 Z" fill="#334155" stroke="#475569" stroke-width="3" />
                        </g>
                    </svg>
                </div>

                <div class="text-center mb-4">
                    <h1 class="h4 font-extrabold text-white tracking-tight mb-1" style="font-weight: 800;">Acceso Personal</h1>
                    <p class="text-xs text-slate-400 font-medium px-2 leading-relaxed">Área de administración y farmacéuticos.</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="text-xs font-semibold text-slate-400 mb-1.5 d-block">Correo Electrónico</label>
                        <div class="position-relative">
                            <span class="position-absolute top-50 translate-middle-y text-slate-500" style="left: 1rem; z-index: 10;">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" name="email" value="{{ old('email') }}" 
                                   @focus="personalFocus = true" @blur="personalFocus = false"
                                   class="w-100 py-2.5 rounded-2xl border border-slate-700 text-white bg-slate-800/80 focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-400/20 transition-all" 
                                   style="padding-left: 2.5rem; padding-right: 1rem; font-size: 0.92rem; border-radius: 1rem;" 
                                   placeholder="ejemplo@farmacia.com" required>
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block text-xs font-semibold mt-1 pl-2 text-rose-400">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="text-xs font-semibold text-slate-400 mb-1.5 d-block">Contraseña</label>
                        <div class="position-relative">
                            <span class="position-absolute top-50 translate-middle-y text-slate-500" style="left: 1rem; z-index: 10;">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="password" 
                                   @focus="personalPassFocus = true" @blur="personalPassFocus = false"
                                   class="w-100 py-2.5 rounded-2xl border border-slate-700 text-white bg-slate-800/80 focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-400/20 transition-all" 
                                   style="padding-left: 2.5rem; padding-right: 1rem; font-size: 0.92rem; border-radius: 1rem;" 
                                   placeholder="••••••••" required>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block text-xs font-semibold mt-1 pl-2 text-rose-400">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <input type="checkbox" name="remember" class="rounded border-slate-700 bg-slate-800 text-teal-500 focus:ring-teal-500" id="remember_personal" style="width: 16px; height: 16px; cursor: pointer;">
                            <label class="text-xs font-medium text-slate-400 ml-2" for="remember_personal" style="cursor: pointer; margin-left: 0.5rem;">Recordarme</label>
                        </div>
                        <button type="button" @click.stop="activeCard = 'clientes'" class="text-xs font-bold text-decoration-none text-teal-400 hover:text-teal-300 border-0 bg-transparent p-0">&larr; Acceso Clientes</button>
                    </div>

                    <button class="w-100 py-2.5 text-white font-bold rounded-2xl border-0 transition-all shadow-md hover:shadow-lg d-flex justify-content-center align-items-center gap-2 active:scale-95"
                            style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); font-size: 0.95rem; border-radius: 1rem; cursor: pointer;">
                        <span>Ingresar al Sistema</span>
                        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.1rem; height: 1.1rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </form>

                <div class="text-center mt-4 pt-2 border-t border-slate-800">
                    <span class="text-xs text-slate-500 font-medium">¿Eres cliente?</span>
                    <button type="button" @click.stop="activeCard = 'clientes'" class="text-xs font-bold text-decoration-none transition-all ml-1 border-0 bg-transparent p-0 text-teal-400 hover:text-teal-300" style="font-weight: bold; margin-left: 0.25rem;">Soy Cliente (Volver)</button>
                </div>
            </div>

        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    // Variables para el Formulario de Registro
    const tipoDoc = document.getElementById('tipo_documento');
    const tipoDocHidden = document.getElementById('tipo_documento_hidden');
    const docInput = document.getElementById('documento');
    const btnBuscar = document.getElementById('btn-buscar-doc');
    const feedback = document.getElementById('doc-feedback');
    const nombreInput = document.getElementById('nombre');
    const emailInput = document.getElementById('email');
    const telefonoInput = document.getElementById('telefono');
    const btnSubmit = document.getElementById('btn-submit');
    let buscando = false;
    let tieneCuenta = false;
    let esClienteExistente = false;

    if (tipoDoc) {
        tipoDoc.addEventListener('change', () => {
            tipoDocHidden.value = tipoDoc.value;
            validarLongitud();
            docInput.value = '';
            docInput.classList.remove('is-invalid');
            feedback.textContent = '';
            feedback.style.color = '#64748b';
            esClienteExistente = false;
            tieneCuenta = false;
            nombreInput.readOnly = false;
            nombreInput.classList.remove('bg-light');
            btnSubmit.textContent = 'Crear cuenta';
        });
    }

    function validarLongitud() {
        if (!docInput) return;
        const tipo = tipoDoc.value;
        const valor = docInput.value.replace(/\D/g, '');
        if (tipo === 'DNI' && valor.length > 8) docInput.value = valor.substring(0, 8);
        if (tipo === 'RUC' && valor.length > 11) docInput.value = valor.substring(0, 11);
    }

    function getDocDigits() {
        return docInput ? docInput.value.replace(/\D/g, '') : '';
    }

    function longitudCorrecta() {
        if (!tipoDoc) return false;
        const tipo = tipoDoc.value;
        const digits = getDocDigits();
        return (tipo === 'DNI' && digits.length === 8) || (tipo === 'RUC' && digits.length === 11);
    }

    async function buscarDocumento() {
        const digits = getDocDigits();
        if (!longitudCorrecta()) return;
        if (buscando) return;
        buscando = true;
        btnBuscar.disabled = true;
        btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        feedback.textContent = 'Buscando datos del cliente...';
        feedback.style.color = '#64748b';

        try {
            const resp = await fetch(`{{ route('tienda.check_documento') }}?doc=${encodeURIComponent(digits)}`);
            const data = await resp.json();

            if (data.found && data.data) {
                const d = data.data;
                nombreInput.value = d.nombre_completo || '';
                emailInput.value = d.email || '';
                telefonoInput.value = d.telefono || '';
                esClienteExistente = true;

                if (d.tiene_cuenta) {
                    tieneCuenta = true;
                    docInput.classList.add('is-invalid');
                    feedback.textContent = 'Este documento ya tiene una cuenta activa en la tienda virtual.';
                    feedback.style.color = '#dc3545';
                    nombreInput.readOnly = true;
                    nombreInput.classList.add('bg-light');
                    btnSubmit.disabled = true;
                } else {
                    tieneCuenta = false;
                    docInput.classList.remove('is-invalid');
                    feedback.textContent = 'Cliente ubicado en la farmacia física. Crea una contraseña para tu cuenta online.';
                    feedback.style.color = '#0d9488';
                    nombreInput.readOnly = true;
                    nombreInput.classList.add('bg-light');
                    btnSubmit.textContent = 'Activar cuenta';
                    btnSubmit.disabled = false;
                }
            } else {
                esClienteExistente = false;
                tieneCuenta = false;
                docInput.classList.remove('is-invalid');
                feedback.textContent = data.message || 'Documento no registrado. Ingresa tus datos manualmente.';
                feedback.style.color = '#64748b';
                nombreInput.readOnly = false;
                nombreInput.classList.remove('bg-light');
                btnSubmit.textContent = 'Crear cuenta';
                btnSubmit.disabled = false;
            }
        } catch (err) {
            feedback.textContent = 'No se pudo validar en la central. Ingresa tus datos manualmente.';
            feedback.style.color = '#dc3545';
        } finally {
            buscando = false;
            btnBuscar.disabled = false;
            btnBuscar.innerHTML = '<i class="fas fa-search"></i>';
        }
    }

    if (docInput) {
        docInput.addEventListener('input', () => {
            validarLongitud();
            if (longitudCorrecta()) {
                buscarDocumento();
            } else {
                docInput.classList.remove('is-invalid');
                tieneCuenta = false;
                esClienteExistente = false;
                feedback.textContent = tipoDoc.value === 'DNI' ? 'Ingresa los 8 digitos del DNI' : 'Ingresa los 11 digitos del RUC';
                feedback.style.color = '#64748b';
                nombreInput.readOnly = false;
                nombreInput.classList.remove('bg-light');
                btnSubmit.textContent = 'Crear cuenta';
                btnSubmit.disabled = false;
            }
        });

        docInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (longitudCorrecta()) {
                    buscarDocumento();
                }
            }
        });
    }

    if (btnBuscar) btnBuscar.addEventListener('click', buscarDocumento);

    const formRegister = document.getElementById('form-register');
    if (formRegister) {
        formRegister.addEventListener('submit', (e) => {
            if (buscando) {
                e.preventDefault();
                alert('Por favor espera a que termine la búsqueda del documento.');
                return;
            }

            const tipo = tipoDoc.value;
            const digits = getDocDigits();
            if ((tipo === 'DNI' && digits.length !== 8) || (tipo === 'RUC' && digits.length !== 11)) {
                e.preventDefault();
                docInput.classList.add('is-invalid');
                feedback.textContent = tipo === 'DNI' ? 'El DNI debe tener 8 digitos.' : 'El RUC debe tener 11 digitos.';
                feedback.style.color = '#dc3545';
                return;
            }

            if (tieneCuenta) {
                e.preventDefault();
                feedback.textContent = 'Este documento ya tiene una cuenta creada. Por favor, inicia sesión.';
                feedback.style.color = '#dc3545';
            }
        });
    }
</script>
@endpush
