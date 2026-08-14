@extends('tienda.layout')

@section('title', 'Iniciar sesión')

@section('content')
<div class="row justify-content-center my-4 md:my-5">
    <div class="col-md-5 d-flex justify-content-center">
        <div class="bg-white shadow-xl shadow-slate-100/80 border border-slate-100/50 rounded-3xl p-4 p-md-5 w-100" style="max-width: 440px; border-radius: 1.5rem;">
            
            <!-- Cabecera de Identidad Visual -->
            <div class="text-center mb-4">
                <div class="rounded-2xl d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 52px; height: 52px; background: linear-gradient(135deg, rgba(13, 148, 136, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%); color: var(--store-green); border-radius: 1rem;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.6rem; height: 1.6rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h1 class="h4 font-extrabold text-slate-800 tracking-tight mb-1" style="font-weight: 800;">¡Bienvenido de nuevo!</h1>
                <p class="text-xs text-slate-400 font-medium px-2 leading-relaxed" style="font-size: 0.8rem; color: #64748b;">Accede a tus medicamentos y haz seguimiento de tus pedidos online.</p>
            </div>

            <!-- Formulario de Acceso -->
            <form method="POST" action="{{ route('tienda.login') }}">
                @csrf
                
                <!-- Input: Login (DNI, Email, Teléfono) -->
                <div class="mb-3">
                    <label class="text-xs font-semibold text-slate-500 mb-1.5 d-block" style="font-size: 0.75rem; font-weight: 600; color: #64748b;">Documento, correo o teléfono</label>
                    <div class="position-relative" style="position: relative;">
                        <span class="position-absolute top-50 translate-middle-y text-slate-400" style="position: absolute; top: 50%; transform: translateY(-50%); left: 1rem; z-index: 10; color: #94a3b8;">
                            <i class="fas fa-user-circle"></i>
                        </span>
                        <input type="text" name="login" value="{{ old('login') }}" 
                               class="w-100 py-2.5 rounded-2xl border border-slate-200 text-slate-700 bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all duration-300 @error('login') is-invalid @enderror" 
                               style="padding-left: 2.5rem; padding-right: 1rem; font-size: 0.92rem; width: 100%; border-radius: 1rem; border: 1px solid #e2e8f0; outline: none; padding-top: 0.65rem; padding-bottom: 0.65rem;" 
                               placeholder="Ej. 72345678" required autofocus>
                    </div>
                    @error('login')
                        <div class="invalid-feedback d-block text-xs font-semibold mt-1 pl-2" style="font-size: 0.75rem; color: #dc3545; display: block; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Input: Contraseña -->
                <div class="mb-3">
                    <label class="text-xs font-semibold text-slate-500 mb-1.5 d-block" style="font-size: 0.75rem; font-weight: 600; color: #64748b;">Contraseña</label>
                    <div class="position-relative" style="position: relative;">
                        <span class="position-absolute top-50 translate-middle-y text-slate-400" style="position: absolute; top: 50%; transform: translateY(-50%); left: 1rem; z-index: 10; color: #94a3b8;">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="password" 
                               class="w-100 py-2.5 rounded-2xl border border-slate-200 text-slate-700 bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all duration-300 @error('password') is-invalid @enderror" 
                               style="padding-left: 2.5rem; padding-right: 1rem; font-size: 0.92rem; width: 100%; border-radius: 1rem; border: 1px solid #e2e8f0; outline: none; padding-top: 0.65rem; padding-bottom: 0.65rem;" 
                               placeholder="••••••••" required>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block text-xs font-semibold mt-1 pl-2" style="font-size: 0.75rem; color: #dc3545; display: block; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Recordarme -->
                <div class="mb-4 d-flex align-items-center" style="display: flex; align-items: center;">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" id="remember" style="width: 16px; height: 16px; cursor: pointer; border-radius: 0.25rem; border: 1px solid #cbd5e1;">
                    <label class="text-xs font-medium text-slate-500 ml-2" for="remember" style="cursor: pointer; user-select: none; font-size: 0.8rem; margin-left: 0.5rem; color: #64748b;">Recordarme en este dispositivo</label>
                </div>

                <!-- Botón Submit -->
                <button class="w-100 py-2.5 text-white font-bold rounded-2xl border-0 transition-all duration-300 active:scale-98 shadow-md hover:shadow-lg d-flex justify-content-center align-items-center gap-2"
                        style="background: linear-gradient(135deg, var(--store-green) 0%, var(--store-green-dark) 100%); font-size: 0.95rem; cursor: pointer; width: 100%; border: none; border-radius: 1rem; padding-top: 0.75rem; padding-bottom: 0.75rem; color: white; font-weight: bold; display: flex; justify-content: center; align-items: center; gap: 0.5rem; transition: all 0.2s ease-in-out;">
                    <span>Ingresar</span>
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 1.1rem; height: 1.1rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>

            <!-- Registro Enlace -->
            <div class="text-center mt-4 pt-2 border-t border-slate-100" style="text-align: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                <span class="text-xs text-slate-400 font-medium" style="font-size: 0.8rem; color: #94a3b8;">¿Nuevo en nuestra tienda?</span>
                <a href="{{ route('tienda.register') }}" class="text-xs font-bold text-decoration-none hover-text-primary transition-all ml-1" style="color: var(--store-green); font-size: 0.8rem; font-weight: bold; text-decoration: none; margin-left: 0.25rem;">Regístrate gratis</a>
            </div>
        </div>
    </div>
</div>
@endsection
