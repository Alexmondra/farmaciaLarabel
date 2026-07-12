@extends('tienda.layout')

@section('title', 'Mi Perfil')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="store-card bg-white p-4">
            <h1 class="h3 mb-4">Mi Perfil</h1>
            <form method="POST" action="{{ route('tienda.perfil.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label text-muted small">Documento</label>
                    <input type="text" value="{{ $cliente->tipo_documento }} - {{ $cliente->documento }}" class="form-control bg-light" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Nombre</label>
                    <input type="text" value="{{ $cliente->nombre_completo }}" class="form-control bg-light" readonly>
                </div>
                <hr>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $cliente->email) }}" class="form-control @error('email') is-invalid @enderror">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Telefono</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $cliente->telefono) }}" class="form-control @error('telefono') is-invalid @enderror">
                    @error('telefono')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr>
                <h2 class="h6 mb-3">Cambiar contraseña</h2>
                <div class="mb-3">
                    <label class="form-label">Contraseña actual</label>
                    <input type="password" name="password_actual" class="form-control @error('password_actual') is-invalid @enderror">
                    @error('password_actual')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Nueva contraseña</label>
                    <input type="password" name="password_nueva" class="form-control @error('password_nueva') is-invalid @enderror">
                    @error('password_nueva')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmar nueva contraseña</label>
                    <input type="password" name="password_nueva_confirmation" class="form-control">
                </div>

                <button class="btn btn-store btn-lg w-100">Guardar cambios</button>
            </form>
        </div>
    </div>
</div>
@endsection
