@extends('adminlte::page')

@section('title','Editar Sucursal')

@section('content_header')
<h1>Editar Sucursal</h1>
@stop

@section('content')
@if($errors->any())
<div class="alert alert-danger">
  <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('configuracion.sucursales.update', $sucursal) }}">
      @csrf @method('PUT')

      <div class="form-row">
        <div class="form-group col-md-3">
          <label>Código *</label>
          <input type="text" name="codigo" class="form-control" required value="{{ old('codigo',$sucursal->codigo) }}">
        </div>
        <div class="form-group col-md-6">
          <label>Nombre *</label>
          <input type="text" name="nombre" class="form-control" required value="{{ old('nombre',$sucursal->nombre) }}">
        </div>
        <div class="form-group col-md-3">
          <label>Teléfono</label>
          <input type="text" name="telefono" class="form-control" value="{{ old('telefono',$sucursal->telefono) }}">
        </div>
      </div>

      <div class="form-group">
        <label>Dirección</label>
        <input type="text" name="direccion" class="form-control" value="{{ old('direccion',$sucursal->direccion) }}">
      </div>

      <div class="form-group">
        <label>Activa</label><br>
        <input type="hidden" name="activo" value="0">
        <input type="checkbox" name="activo" value="1"
          {{ old('activo', $sucursal->activo ?? false) ? 'checked' : '' }}>
        Sí
      </div>

      <button class="btn btn-primary"><i class="fas fa-save"></i> Actualizar</button>
      <a href="{{ route('configuracion.sucursales.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>
</div>
@stop