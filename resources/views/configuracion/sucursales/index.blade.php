@extends('adminlte::page')

@section('title','Sucursales')

@section('content_header')
<h1>Listado de Sucursales</h1>
@stop

@section('content')

@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if($errors->any())
<div class="alert alert-danger">
  <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="card">
  <div class="card-body">
    <div class="form-inline mb-3">
      <input type="text" id="searchInput" class="form-control mr-2" placeholder="Buscar por código, nombre, dirección...">
      @can('sucursales.crear')
      <a href="{{ route('configuracion.sucursales.create') }}" class="btn btn-primary ml-auto">
        <i class="fas fa-plus"></i> Nueva Sucursal
      </a>
      @endcan
      </form>

      <div class="table-responsive">
        <table class="table table-sm table-striped">
          <thead>
            <tr>
              <th>Código</th>
              <th>Nombre</th>
              <th>Dirección</th>
              <th>Teléfono</th>
              <th>Estado</th>
              <th style="width:130px;">Acciones</th>
            </tr>
          </thead>
          <tbody id="sucursalesTableBody">
            @forelse($sucursales as $s)
            <tr>
              <td>{{ $s->codigo }}</td>
              <td>{{ $s->nombre }}</td>
              <td>{{ $s->direccion }}</td>
              <td>{{ $s->telefono }}</td>
              <td>
                @if($s->activo)
                <span class="badge badge-success">Activa</span>
                @else
                <span class="badge badge-secondary">Inactiva</span>
                @endif
              </td>
              <td>
                @can('sucursales.editar')
                <a href="{{ route('configuracion.sucursales.edit',$s) }}" class="btn btn-xs btn-warning">
                  <i class="fas fa-edit"></i>
                </a>
                @endcan
                @can('sucursales.borrar')
                <form action="{{ route('configuracion.sucursales.destroy',$s) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('¿Eliminar la sucursal {{ $s->nombre }}?');">
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
              <td colspan="6" class="text-center text-muted">No hay registros</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{ $sucursales->links() }}
    </div>
  </div>
  @stop

  @section('js')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('searchInput');
      const tableBody = document.getElementById('sucursalesTableBody');
      const rows = Array.from(tableBody.getElementsByTagName('tr'));
      const noResultsRow = rows.find(row => row.cells.length === 1 && row.cells[0].colSpan === 6);

      searchInput.addEventListener('keyup', function(event) {
        const searchTerm = event.target.value.toLowerCase();
        let found = false;

        rows.forEach(row => {
          // Ignoramos la fila de "No hay registros" si existe
          if (row === noResultsRow) return;

          const rowText = row.textContent.toLowerCase();

          if (rowText.includes(searchTerm)) {
            row.style.display = '';
            found = true;
          } else {
            row.style.display = 'none';
          }
        });
      });
    });
  </script>
  @stop