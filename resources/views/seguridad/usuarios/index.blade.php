@extends('adminlte::page')

@section('title', 'Gestión de Usuarios')

@section('content_header')
<h1><i class="fas fa-users text-dark mr-2"></i> Colaboradores</h1>
@stop

@section('content')

<div class="card card-outline card-primary">
  <div class="card-body">

    <div class="d-flex justify-content-between mb-3">
      <!-- BUSCADOR TIEMPO REAL -->
      <div class="input-group" style="width: 300px;">
        <div class="input-group-prepend">
          <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
        </div>
        <input type="text" id="liveSearchInput" class="form-control border-left-0" placeholder="Buscar empleado...">
      </div>

      @can('usuarios.crear')
      <a href="{{ route('seguridad.usuarios.create') }}" class="btn btn-primary shadow-sm">
        <i class="fas fa-user-plus mr-1"></i> Nuevo Colaborador
      </a>
      @endcan
    </div>

    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle">
        <thead class="bg-light">
          <tr>
            <th style="width: 60px" class="text-center"><i class="fas fa-image text-muted"></i></th>
            <th>Empleado</th>
            <th>Sucursales</th>
            <th>Rol</th>
            <th class="text-center">Estado</th>
            <th style="width:100px;">Acciones</th>
          </tr>
        </thead>
        <tbody id="userTableBody">
          @forelse($users as $u)
          <tr>
            <!-- FOTO -->
            <td class="text-center align-middle">
              @if($u->imagen_perfil)
              <img src="{{ route('seguridad.usuarios.imagen', $u->id) }}"
                alt="{{ $u->name }}"
                class="rounded-circle"
                width="50"
                height="50"
                style="object-fit: cover;">
              @else
              <img src="{{ asset('img/default-avatar.png') }}"
                alt="Sin foto"
                class="rounded-circle"
                width="50"
                height="50">
              @endif
            </td>

            <!-- DATOS -->
            <td class="align-middle">
              <span class="font-weight-bold d-block text-dark">{{ $u->name }}</span>
              <small class="text-muted"><i class="fas fa-envelope mr-1"></i> {{ $u->email }}</small>
            </td>

            <!-- SUCURSALES -->
            <td class="align-middle">
              @forelse($u->sucursales as $s)
              <span class="badge badge-info font-weight-normal mb-1">{{ $s->nombre }}</span>
              @empty
              <span class="text-muted small font-italic">Sin asignación</span>
              @endforelse
            </td>

            <!-- ROLES -->
            <td class="align-middle">
              @forelse($u->roles as $r)
              <span class="badge badge-dark">{{ $r->name }}</span>
              @empty
              <span class="text-muted small">N/A</span>
              @endforelse
            </td>

            <!-- ESTADO -->
            <td class="text-center align-middle">
              @if($u->activo)
              <span class="badge badge-success px-2 py-1">ACTIVO</span>
              @else
              <span class="badge badge-danger px-2 py-1">BLOQUEADO</span>
              @endif
            </td>

            <!-- ACCIONES -->
            <td class="align-middle">
              @can('usuarios.editar')
              <a href="{{ route('seguridad.usuarios.edit', $u) }}" class="btn btn-sm btn-outline-warning" title="Editar / Reset Clave">
                <i class="fas fa-edit"></i>
              </a>
              @endcan

              @can('usuarios.eliminar')
              <form action="{{ route('seguridad.usuarios.destroy', $u) }}" method="POST" class="d-inline"
                onsubmit="return confirm('¿Eliminar permanentemente a {{ $u->name }}?');">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
              @endcan
            </td>
          </tr>
          @empty
          <tr id="noRecordsRow">
            <td colspan="6" class="text-center text-muted py-4">No hay usuarios registrados</td>
          </tr>
          @endforelse

          <!-- FILA OCULTA BÚSQUEDA -->
          <tr id="noResultsFound" style="display: none;">
            <td colspan="6" class="text-center text-muted py-4">
              <i class="fas fa-search mb-2 d-block" style="font-size: 24px; opacity: 0.5;"></i>
              No se encontraron coincidencias.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</div>
@stop

@section('js')
<script>
  // Script de Búsqueda en Tiempo Real
  document.getElementById('liveSearchInput').addEventListener('keyup', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#userTableBody tr:not(#noResultsFound):not(#noRecordsRow)');
    let hasResults = false;

    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      if (text.includes(searchTerm)) {
        row.style.display = '';
        hasResults = true;
      } else {
        row.style.display = 'none';
      }
    });

    const noResultsRow = document.getElementById('noResultsFound');
    if (noResultsRow) {
      noResultsRow.style.display = hasResults ? 'none' : 'table-row';
    }
  });
</script>
@stop