@extends('adminlte::page')

@section('title', 'Nuevo Usuario')

@section('content_header')
  <h1>Nuevo Usuario</h1>
@stop

@section('content')
@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif



<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('seguridad.usuarios.store') }}">
      @csrf
      <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
      </div>
      <div class="form-row">
        <div class="form-group col-md-6">
          <label>Contraseña</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group col-md-6">
          <label>Confirmar contraseña</label>
          <input type="password" name="password_confirmation" class="form-control" required>
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
                <input type="checkbox" class="custom-control-input" id="rol_{{ $r->id }}" name="roles[]" value="{{ $r->name }}">
                <label class="custom-control-label" for="rol_{{ $r->id }}">{{ $r->name }}</label>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <button class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
      <a href="{{ route('seguridad.usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>
</div>
@stop
