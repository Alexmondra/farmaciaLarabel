@extends('adminlte::page')

@section('title', 'Usuarios')

@section('content_header')
<h1>Usuarios</h1>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if ($errors->any())
<div class="alert alert-danger">
  <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="card">
  <div class="card-body">
    <form method="GET" class="form-inline mb-3">
      <input type="text" id="searchInput" class="form-control mr-2" placeholder="Buscar nombre - email - sucursal">
      @can('usuarios.crear')
      <a href="{{ route('seguridad.usuarios.create') }}" class="btn btn-primary ml-auto">
        <i class="fas fa-user-plus"></i> Nuevo usuario
      </a>
      @endcan

    </form>

    <div class="table-responsive">
      <table class="table table-sm table-striped">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Sucursal</th>
            <th>Roles</th>
            <th style="width:140px;">Acciones</th>
          </tr>
        </thead>
        <tbody id="userTableBody">
          @forelse($users as $u)
          <tr>
            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td>
              @forelse($u->sucursales as $s)
              <span class="badge badge-info">{{ $s->nombre }}</span>
              @empty
              <span class="text-muted">'—'</span>
              @endforelse

            </td>

            <td>
              @forelse($u->roles as $r)
              <span class="badge badge-info">{{ $r->name }}</span>
              @empty
              <span class="text-muted">Sin rol</span>
              @endforelse
            </td>
            <td>
              @can('usuarios.editar')
              <a href="{{ route('seguridad.usuarios.edit', $u) }}" class="btn btn-xs btn-warning">
                <i class="fas fa-edit"></i>
              </a>
              @endcan
              @can('usuarios.borrar')
              <form action="{{ route('seguridad.usuarios.destroy', $u) }}"
                method="POST" class="d-inline"
                onsubmit="return confirm('¿Eliminar usuario {{ $u->name }}?');">
                @csrf @method('DELETE')
                <button class="btn btn-xs btn-danger">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
              @endcan
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center text-muted">Sin resultados</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $users->links() }}
  </div>
</div>
@stop

@section('js')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('userTableBody');
    const rows = tableBody.getElementsByTagName('tr');
    const noResultsRow = `<tr><td colspan="5" class="text-center text-muted">No se encontraron resultados para la búsqueda.</td></tr>`;

    searchInput.addEventListener('keyup', function(event) {
      const searchTerm = event.target.value.toLowerCase();
      let found = false;

      for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const name = row.cells[0].textContent.toLowerCase();
        const email = row.cells[1].textContent.toLowerCase();
        const sucursal = row.cells[2].textContent.toLowerCase();

        if (name.includes(searchTerm) || email.includes(searchTerm) || sucursal.includes(searchTerm)) {
          row.style.display = '';
          found = true;
        } else {
          row.style.display = 'none';
        }
      }
    });
  });
</script>
@stop