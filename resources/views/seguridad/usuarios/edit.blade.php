@extends('adminlte::page')

@section('title', 'Editar Accesos')

@section('content_header')
<h1><i class="fas fa-user-shield mr-2 text-primary"></i> Gestión de Accesos</h1>
@stop

@section('content')

<div class="row">

  <!-- COLUMNA 1: INFO Y RESET PASSWORD -->
  <div class="col-md-4">
    <div class="card card-primary card-outline">
      <div class="card-body box-profile">
        <div class="text-center">
          @if($usuario->imagen_perfil)
          <img src="{{ route('seguridad.usuarios.imagen', $usuario->id) }}"
            alt="{{ $usuario->name }}"
            class="rounded-circle"
            width="50"
            height="50"
            style="object-fit: cover;">
          @else
          <img src="{{ 'https://robohash.org/' . $usuario->id }}" alt="avatar"
            alt="Sin foto"
            class="rounded-circle"
            width="50"
            height="50">
          @endif
        </div>

        <h3 class="profile-username text-center">{{ $usuario->name }}</h3>
        <p class="text-muted text-center">{{ $usuario->email }}</p>

        <hr>

        <div class="text-center">
          <h6 class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle"></i> Zona de Seguridad</h6>
          <p class="text-muted small">Si el usuario olvidó su clave, restáurala aquí.</p>

          <!-- BOTÓN RESETEAR -->
          <form action="{{ route('seguridad.usuarios.reset_password', $usuario) }}" method="POST"
            onsubmit="return confirm('¿Estás seguro? La contraseña será: 12345678');">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-danger btn-block font-weight-bold shadow-sm">
              <i class="fas fa-unlock-alt mr-2"></i> Resetear a "12345678"
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- COLUMNA 2: SOLO SUCURSALES Y ROLES -->
  <div class="col-md-8">
    <div class="card">
      <div class="card-header bg-white border-bottom-0">
        <h3 class="card-title text-dark">Asignación de Permisos</h3>
      </div>

      <form method="POST" action="{{ route('seguridad.usuarios.update', $usuario) }}">
        @csrf @method('PUT')

        <!-- 
                   Enviamos los datos personales como ocultos (hidden) 
                   para que la validación del controlador no falle, 
                   pero visualmente NO se pueden editar aquí. 
                -->
        <input type="hidden" name="name" value="{{ $usuario->name }}">
        <input type="hidden" name="email" value="{{ $usuario->email }}">
        <input type="hidden" name="documento" value="{{ $usuario->documento }}">

        <!-- Mantenemos el estado activo actual oculto, o puedes poner el switch si quieres -->
        @if($usuario->activo) <input type="hidden" name="activo" value="1"> @endif

        <div class="card-body pt-0">

          <div class="form-group bg-light p-3 rounded">
            <label class="mb-2 text-primary">
              <i class="fas fa-store mr-1"></i> Sucursales Permitidas
            </label>
            {{-- CAMBIO: Usar col-12 en móvil, col-md-6 en escritorio --}}
            <div class="row" style="max-height: 200px; overflow-y: auto;">
              @foreach($sucursales as $s)
              <div class="col-12 col-md-6">
                <div class="custom-control custom-checkbox mb-2">
                  <input type="checkbox" class="custom-control-input" id="suc_{{$s->id}}" name="sucursales[]" value="{{$s->id}}"
                    {{ in_array($s->id, $userSucursales) ? 'checked' : '' }}>
                  <label class="custom-control-label font-weight-normal cursor-pointer" for="suc_{{$s->id}}">
                    {{$s->nombre}}
                  </label>
                </div>
              </div>
              @endforeach
            </div>
          </div>

          <hr>

          <!-- SECCIÓN ROLES -->
          <div class="form-group bg-light p-3 rounded">
            <label class="mb-2 text-primary">
              <i class="fas fa-user-tag mr-1"></i> Rol en el Sistema
            </label>
            <div class="row">
              @foreach($roles as $r)
              <div class="col-md-6">
                <div class="custom-control custom-checkbox mb-2">
                  <input type="checkbox" class="custom-control-input" id="rol_{{$r->id}}" name="roles[]" value="{{$r->id}}"
                    {{ in_array($r->name, $userRoles) ? 'checked' : '' }}>
                  <label class="custom-control-label font-weight-normal cursor-pointer" for="rol_{{$r->id}}">
                    {{$r->name}}
                  </label>
                </div>
              </div>
              @endforeach
            </div>
          </div>

        </div>

        <div class="card-footer bg-white text-right">
          <a href="{{ route('seguridad.usuarios.index') }}" class="btn btn-default mr-2">Cancelar</a>
          <button type="submit" class="btn btn-primary px-4">
            <i class="fas fa-save mr-2"></i> Guardar Permisos
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop

@section('css')
<style>
  /* === MODO OSCURO PARA EDIT === */
  @media (prefers-color-scheme: dark) {

    /* Tarjetas */
    .card,
    .card-primary.card-outline {
      background-color: #343a40 !important;
      color: #d1d9e0 !important;
      border-color: #495057 !important;
    }

    /* Headers */
    .card-header.bg-white {
      background-color: #3e444a !important;
      border-bottom-color: #495057 !important;
    }

    /* Footer */
    .card-footer.bg-white {
      background-color: #3e444a !important;
      border-top-color: #495057 !important;
    }

    /* Sección de Permisos */
    .form-group.bg-light.p-3 {
      background-color: #3e444a !important;
    }

    .text-muted {
      color: #9da5af !important;
    }

    .text-dark {
      color: #d1d9e0 !important;
    }
  }
</style>
@stop