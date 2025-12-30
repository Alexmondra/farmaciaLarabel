@extends('adminlte::page')

@section('title', 'Nuevo Usuario')

@section('content_header')
<h1><i class="fas fa-user-plus mr-2 text-primary"></i> Registrar Nuevo Colaborador</h1>
@stop

@section('content')

{{-- Agregamos ID al formulario para controlarlo con JS --}}
<form id="formCrearUsuario" method="POST" action="{{ route('seguridad.usuarios.store') }}" enctype="multipart/form-data" novalidate>
  @csrf

  <div class="row">
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
                {{-- Validación básica: required --}}
                <input type="text" name="name" class="form-control" required value="{{ old('name') }}" placeholder="Ej: Juan Pérez">
                <div class="invalid-feedback">El nombre es obligatorio.</div>
              </div>
            </div>
            <div class="form-group col-md-6">
              <label>Email Corporativo *</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
                {{-- Validación: type="email" y required --}}
                <input type="email" name="email" class="form-control" required value="{{ old('email') }}" placeholder="juan@farmacia.com">
                <div class="invalid-feedback">Ingresa un correo válido.</div>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-4">
              <label>DNI / Documento *</label>
              {{-- Validación: Solo números (vía JS) y max 8 --}}
              <input type="text"
                name="documento"
                id="inputDNI"
                class="form-control"
                value="{{ old('documento') }}"
                maxlength="8"
                minlength="8"
                placeholder="8 dígitos"
                required>
              <div class="invalid-feedback">El DNI debe tener exactamente 8 números.</div>
            </div>
            <div class="form-group col-md-4">
              <label>Teléfono</label>
              {{-- Validación: Solo números (vía JS) --}}
              <input type="text"
                name="telefono"
                id="inputTelefono"
                class="form-control"
                value="{{ old('telefono') }}"
                maxlength="9"
                placeholder="Solo números">
            </div>
            <div class="form-group col-md-4">
              <label>Dirección</label>
              {{-- Sin required, puede ir vacío --}}
              <input type="text" name="direccion" class="form-control" value="{{ old('direccion') }}" placeholder="Opcional">
            </div>
          </div>

          <hr>

          <div class="alert alert-light border">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label class="text-primary font-weight-bold">Contraseña Inicial *</label>
                {{-- Validación: minlength="8" --}}
                <input type="password"
                  name="password"
                  id="password"
                  class="form-control"
                  required
                  minlength="8"
                  placeholder="Mínimo 8 caracteres">
                <div class="invalid-feedback">La contraseña es obligatoria (mín. 8 caracteres).</div>
              </div>
              <div class="form-group col-md-6">
                <label class="text-primary font-weight-bold">Confirmar Contraseña *</label>
                <input type="password"
                  name="password_confirmation"
                  id="password_confirmation"
                  class="form-control"
                  required
                  placeholder="Repite la contraseña">
                <div id="msgPassword" class="invalid-feedback">Las contraseñas no coinciden.</div>
              </div>
            </div>
          </div>

        </div>
      </div>

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

      <button type="submit" id="btnGuardarUsuario" class="btn btn-success btn-lg btn-block shadow-sm">
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
  // 1. Preview de imagen
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

  // 2. VALIDACIONES EN TIEMPO REAL Y AL ENVIAR
  $(document).ready(function() {

    // A. Solo permitir números en DNI y Teléfono
    $('#inputDNI, #inputTelefono').on('input', function() {
      // Reemplaza cualquier cosa que NO sea número del 0 al 9 con vacío
      this.value = this.value.replace(/[^0-9]/g, '');
    });

    // B. Controlar el envío del formulario
    $('#btnGuardarUsuario').click(function(e) {
      let form = $('#formCrearUsuario')[0];
      let isValid = true;

      // Limpiar errores previos
      $('.is-invalid').removeClass('is-invalid');

      // Validar DNI (Exactamente 8 dígitos)
      let dni = $('#inputDNI').val();
      if (dni.length !== 8) {
        $('#inputDNI').addClass('is-invalid');
        isValid = false;
      }

      // Validar Contraseñas Iguales
      let pass1 = $('#password').val();
      let pass2 = $('#password_confirmation').val();

      if (pass1 !== pass2) {
        $('#password_confirmation').addClass('is-invalid');
        $('#msgPassword').text('Las contraseñas no coinciden.');
        isValid = false;
      }

      // Validar Campos HTML5 (Email, Required, etc)
      if (form.checkValidity() === false) {
        e.preventDefault();
        e.stopPropagation();
        form.classList.add('was-validated'); // Esto muestra los errores nativos de Bootstrap
        isValid = false;
      }

      if (!isValid) {
        e.preventDefault(); // Detiene el envío si hay errores custom
        Swal.fire({
          icon: 'error',
          title: 'Datos incompletos',
          text: 'Por favor, revisa los campos en rojo (DNI de 8 dígitos, correos válidos, contraseñas iguales).'
        });
      }
    });

  });
</script>
@stop