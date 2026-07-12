@extends('tienda.layout')

@section('title', 'Iniciar sesion')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="store-card bg-white p-4">
            <h1 class="h3 mb-4">Iniciar sesion</h1>
            <form method="POST" action="{{ route('tienda.login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Documento, email o telefono</label>
                    <input type="text" name="login" value="{{ old('login') }}" class="form-control @error('login') is-invalid @enderror" required autofocus>
                    @error('login')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Recordarme</label>
                </div>
                <button class="btn btn-store btn-lg w-100">Ingresar</button>
            </form>
            <div class="text-center mt-3">
                <span class="text-muted">No tienes cuenta?</span>
                <a href="{{ route('tienda.register') }}" class="fw-bold text-decoration-none">Registrarse</a>
            </div>
        </div>
    </div>
</div>
@endsection
