@extends('adminlte::page')

@section('title', 'Gestión de Usuarios')

@section('content_header')
<h1><i class="fas fa-users text-dark mr-2"></i> Colaboradores</h1>
@stop

@section('content')

<div class="card card-outline card-primary">
  <div class="card-body">

    {{-- CABECERA RESPONSIVE --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
      <div class="input-group mb-2 mb-md-0 w-100" style="max-width: 300px;">
        <div class="input-group-prepend">
          <span class="input-group-text bg-white" id="search-addon"><i class="fas fa-search text-muted"></i></span>
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
      {{-- AÑADIMOS UNA CLASE IDENTIFICADORA PARA EL CSS RESPONSIVE --}}
      <table class="table table-hover table-striped align-middle responsive-table" id="userTable">
        <thead class="bg-light">
          <tr>
            <th style="width: 60px" class="text-center"></th>
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
            <td class="text-center align-middle" data-label="Foto">
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

            <td class="align-middle" data-label="Empleado">
              <span class="font-weight-bold d-block text-dark">{{ $u->name }}</span>
              <small class="text-muted"><i class="fas fa-envelope mr-1"></i> {{ $u->email }}</small>
            </td>

            <td class="align-middle" data-label="Sucursales">
              @forelse($u->sucursales as $s)
              <span class="badge badge-info font-weight-normal mb-1">{{ $s->nombre }}</span>
              @empty
              <span class="text-muted small font-italic">Sin asignación</span>
              @endforelse
            </td>

            <td class="align-middle" data-label="Rol">
              @forelse($u->roles as $r)
              <span class="badge badge-dark">{{ $r->name }}</span>
              @empty
              <span class="text-muted small">N/A</span>
              @endforelse
            </td>

            <td class="text-center align-middle" data-label="Estado">
              @if($u->activo)
              <span class="badge badge-success px-2 py-1">ACTIVO</span>
              @else
              <span class="badge badge-danger px-2 py-1">BLOQUEADO</span>
              @endif
            </td>

            <td class="align-middle" data-label="Acciones">
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
          {{-- ... filas vacías ... --}}
          @endforelse
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

@section('css')
<style>
  /* === RESPONSIVIDAD: TABLA TRANSFORMADA A TARJETA EN MÓVIL === */
  /* Breakpoint de 768px para activar la transformación (típico móvil/tablet pequeño) */
  @media screen and (max-width: 768px) {
    .responsive-table thead {
      /* Oculta la cabecera tradicional de la tabla */
      display: none;
    }

    .responsive-table tr {
      /* Cada fila se convierte en un bloque con margen */
      display: block;
      margin-bottom: 0.8rem;
      border: 1px solid #dee2e6;
      border-radius: 0.25rem;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      position: relative;
    }

    .responsive-table tbody tr:last-child {
      margin-bottom: 0;
    }

    .responsive-table td {
      /* Cada celda se convierte en un bloque de lista */
      display: block;
      text-align: right !important;
      padding: 0.5rem 1rem;
      border: none;
      /* Elimina los bordes internos de celda */
      word-break: break-word;
      /* Evita desbordamiento de texto */
    }

    /* Pseudo-elemento para mostrar la etiqueta de la columna */
    .responsive-table td::before {
      content: attr(data-label);
      float: left;
      font-weight: bold;
      text-transform: uppercase;
      font-size: 0.75rem;
      color: #6c757d;
      /* Texto gris de Bootstrap */
      padding-right: 10px;
    }

    /* Alineación especial para la primera columna (Foto/Datos de Empleado) */
    .responsive-table tr td:first-child {
      display: flex;
      /* Para alinear la foto a la izquierda y el label */
      align-items: center;
      justify-content: space-between;
      padding-bottom: 0;
    }

    /* Ajustes específicos para la celda de la foto/datos */
    .responsive-table tr td:nth-child(2) {
      text-align: left !important;
      /* Ocultamos el label 'Empleado' ya que el contenido es evidente */
      padding-top: 0;
    }

    .responsive-table tr td:nth-child(2)::before {
      display: none;
    }

    /* Alineación para la celda de Acciones */
    .responsive-table tr td:last-child {
      text-align: left !important;
      border-top: 1px solid #dee2e6;
      /* Separador */
    }
  }


  /* === MODO OSCURO PARA INDEX === */
  @media (prefers-color-scheme: dark) {
    .card-outline.card-primary {
      background-color: #343a40 !important;
      border-top-color: #007bff !important;
    }

    .card-body,
    .card-header,
    h1 {
      color: #d1d9e0 !important;
    }

    /* Buscador */
    .input-group-text {
      background-color: #3e444a !important;
      color: #d1d9e0 !important;
      border-color: #495057 !important;
    }

    #liveSearchInput {
      background-color: #2b3035;
      color: #d1d9e0;
      border-color: #495057;
    }

    #liveSearchInput::placeholder {
      color: #9da5af;
    }

    /* Tabla en modo oscuro (transformada a tarjeta) */
    .responsive-table tr {
      background-color: #3e444a;
      /* Fondo de la "tarjeta" */
      border-color: #495057;
    }

    .responsive-table td::before {
      color: #a0aec0;
      /* Color de la etiqueta */
    }

    .responsive-table tr td:last-child {
      border-top-color: #495057;
      /* Separador */
    }

    /* Tabla normal (escritorio) */
    .table {
      color: #e9ecef;
    }

    .table thead th {
      color: #fff;
      background-color: #495057 !important;
      border-color: #5d6874;
    }

    .table-striped tbody tr:nth-of-type(odd) {
      background-color: rgba(255, 255, 255, 0.05);
    }

    .table-hover tbody tr:hover {
      background-color: rgba(255, 255, 255, 0.1);
    }

    .text-dark {
      color: #d1d9e0 !important;
    }
  }
</style>
@stop