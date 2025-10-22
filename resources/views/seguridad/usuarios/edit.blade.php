@extends('adminlte::page')

@section('title', 'Editar Usuario')

@section('content_header')
  <h1>Editar Usuario</h1>
@stop

@section('content')
@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('seguridad.usuarios.update', $usuario) }}">
      @csrf @method('PUT')

      <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="name" class="form-control" required value="{{ old('name', $usuario->name) }}">
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required value="{{ old('email', $usuario->email) }}">
      </div>

      <div class="form-row">
        <div class="form-group col-md-6">
          <label>Nueva contraseña (opcional)</label>
          <input type="password" name="password" class="form-control" placeholder="Déjalo vacío para no cambiar">
        </div>
        <div class="form-group col-md-6">
          <label>Confirmar nueva contraseña</label>
          <input type="password" name="password_confirmation" class="form-control" placeholder="Si cambias la contraseña, confirma aquí">
        </div>
      </div>



      <div class="form-group">
      <label>Sucursales asignadas</label>
      <div class="row">
        @foreach($sucursales as $s)
          <div class="col-md-4">
            <div class="custom-control custom-checkbox">
              <input type="checkbox"
                    class="custom-control-input"
                    id="suc_{{ $s->id }}"
                    name="sucursales[]"
                    value="{{ $s->id }}"
                    {{ !empty($userSucursales) && in_array($s->id, $userSucursales) ? 'checked' : '' }}>
              <label class="custom-control-label" for="suc_{{ $s->id }}">{{ $s->nombre }}</label>
            </div>
          </div>
        @endforeach
      </div>
    </div>



      <div class="form-group">
        <label>Roles</label>
        <div class="row">
          @foreach($roles as $r)
            <div class="col-md-4">
              <div class="custom-control custom-checkbox">
                <input type="checkbox"
                       class="custom-control-input"
                       id="rol_{{ $r->id }}"
                       name="roles[]"
                       value="{{ $r->name }}"
                       {{ in_array($r->name, $userRoles) ? 'checked' : '' }}>
                <label class="custom-control-label" for="rol_{{ $r->id }}">{{ $r->name }}</label>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <button class="btn btn-primary"><i class="fas fa-save"></i> Actualizar</button>
      <a href="{{ route('seguridad.usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>
</div>
@stop
