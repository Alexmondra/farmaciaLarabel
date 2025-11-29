@extends('adminlte::page')

@section('title', 'Nuevo Usuario')

@section('content_header')
<h1><i class="fas fa-user-plus mr-2 text-primary"></i> Registrar Nuevo Colaborador</h1>
@stop

@section('content')

<form method="POST" action="{{ route('seguridad.usuarios.store') }}" enctype="multipart/form-data">
  @csrf

  <div class="row">
    <!-- COLUMNA IZQUIERDA: DATOS -->
    <div class="col-md-8">
      <div class="card card-primary card-outline">
        <div class="card-header">
          <h3 class="card-title">Información Personal</h3>
        </div>
        <div class="card-body">

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Nombre Completo *</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-user"></i></span>
                </div>
                <input type="text" name="name" class="form-control" required value="{{ old('name') }}" placeholder="Ej: Juan Pérez">
              </div>
            </div>
            <div class="form-group col-md-6">
              <label>Email Corporativo *</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
                <input type="email" name="email" class="form-control" required value="{{ old('email') }}" placeholder="juan@farmacia.com">
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-4">
              <label>DNI / Documento</label>
              <input type="text" name="documento" class="form-control" value="{{ old('documento') }}">
            </div>
            <div class="form-group col-md-4">
              <label>Teléfono</label>
              <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}">
            </div>
            <div class="form-group col-md-4">
              <label>Dirección</label>
              <input type="text" name="direccion" class="form-control" value="{{ old('direccion') }}">
            </div>
          </div>

          <hr>

          <!-- PASSWORD OBLIGATORIO AL CREAR -->
          <div class="alert alert-light border">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label class="text-primary font-weight-bold">Contraseña Inicial *</label>
                <input type="password" name="password" class="form-control" required placeholder="Mínimo 8 caracteres">
              </div>
              <div class="form-group col-md-6">
                <label class="text-primary font-weight-bold">Confirmar Contraseña *</label>
                <input type="password" name="password_confirmation" class="form-control" required>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- PERMISOS -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Accesos y Permisos</h3>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 border-right">
              <label>Sucursales Asignadas</label>
              <div class="p-2" style="max-height: 150px; overflow-y: auto;">
                @foreach($sucursales as $s)
                <div class="custom-control custom-checkbox mb-1">
                  <input type="checkbox" class="custom-control-input" id="suc_{{$s->id}}" name="sucursales[]" value="{{$s->id}}">
                  <label class="custom-control-label font-weight-normal" for="suc_{{$s->id}}">{{$s->nombre}}</label>
                </div>
                @endforeach
              </div>
            </div>
            <div class="col-md-6 pl-4">
              <label>Rol de Sistema</label>
              <div class="p-2">
                @foreach($roles as $r)
                <div class="custom-control custom-checkbox mb-1">
                  <input type="checkbox" class="custom-control-input" id="rol_{{$r->id}}" name="roles[]" value="{{$r->id}}">
                  <label class="custom-control-label font-weight-normal" for="rol_{{$r->id}}">{{$r->name}}</label>
                </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- COLUMNA DERECHA: FOTO -->
    <div class="col-md-4">
      <div class="card card-outline card-secondary">
        <div class="card-body text-center">
          <label class="d-block mb-3">Foto de Perfil</label>

          <div class="position-relative d-inline-block">
            <img id="preview" src="https://ui-avatars.com/api/?name=Nuevo+User&size=150&background=f4f6f9&color=6c757d"
              class="img-circle elevation-2"
              style="width: 150px; height: 150px; object-fit: cover;">
          </div>

          <div class="mt-4 text-left">
            <div class="custom-file">
              <input type="file" class="custom-file-input" name="imagen_perfil" id="inputFile" accept="image/*">
              <label class="custom-file-label" data-browse="Buscar">Subir foto...</label>
            </div>
            <small class="text-muted mt-2 d-block">Formatos: JPG, PNG. Max 2MB.</small>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-success btn-lg btn-block shadow-sm">
        <i class="fas fa-save mr-2"></i> Guardar Usuario
      </button>
      <a href="{{ route('seguridad.usuarios.index') }}" class="btn btn-default btn-block">
        Cancelar
      </a>
    </div>
  </div>
</form>
@stop

@section('js')
<script>
  // Preview de imagen
  document.getElementById('inputFile').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
        document.getElementById('preview').src = e.target.result;
      }
      reader.readAsDataURL(this.files[0]);

      var fileName = this.files[0].name;
      $(this).next('.custom-file-label').html(fileName);
    }
  });
</script>
@stop