@extends('adminlte::page')

@section('title','Sucursales')

@section('content_header')
<h1>Listado de Sucursales</h1>
@stop

@section('content')
<div class="card">
  <div class="card-body">

    <div class="d-flex justify-content-between mb-3">

      <div class="input-group" style="width: 250px;">
        <div class="input-group-prepend">
          <span class="input-group-text"><i class="fas fa-search"></i></span>
        </div>
        <input type="text" id="liveSearchInput" class="form-control" placeholder="Buscar en tiempo real...">
      </div>

      @can('sucursales.crear')
      <button type="button" class="btn btn-primary" onclick="abrirModalCrear()">
        <i class="fas fa-plus"></i> Nueva Sucursal
      </button>
      @endcan
    </div>

    <div class="table-responsive">
      <table class="table table-sm table-striped align-middle">
        <thead>
          <tr>
            <th style="width: 50px;">Img</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Dirección</th>
            <th>Impuesto</th>
            <th>Estado</th>
            <th style="width:100px;">Acciones</th>
          </tr>
        </thead>
        <tbody id="tablaSucursales">
          @forelse($sucursales as $s)
          <tr>
            <td>
              @if($s->imagen_sucursal)
              <img src="{{ asset('storage/'.$s->imagen_sucursal) }}" class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover;">
              @else
              <i class="fas fa-store text-muted fa-lg"></i>
              @endif
            </td>
            <td>{{ $s->codigo }}</td>
            <td>{{ $s->nombre }}</td>
            <td>{{ $s->direccion }}</td>
            <td>{{ $s->impuesto_porcentaje }}%</td>
            <td>
              <span class="badge badge-{{ $s->activo ? 'success' : 'secondary' }}">
                {{ $s->activo ? 'Activa' : 'Inactiva' }}
              </span>
            </td>
            <td>
              @can('sucursales.editar')
              <button class="btn btn-xs btn-warning" onclick="abrirModalEditar({{ $s }})">
                <i class="fas fa-edit"></i>
              </button>
              @endcan

              @can('sucursales.eliminar')
              <form action="{{ route('configuracion.sucursales.destroy',$s) }}" method="POST" class="d-inline"
                onsubmit="return confirm('¿Eliminar {{ $s->nombre }}?');">
                @csrf @method('DELETE')
                <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
              </form>
              @endcan
            </td>
          </tr>
          @empty
          <tr id="noRecordsRow">
            <td colspan="7" class="text-center text-muted">No hay registros</td>
          </tr>
          @endforelse

          <tr id="noResultsFound" style="display: none;">
            <td colspan="7" class="text-center text-muted py-4">
              <i class="fas fa-search mb-2 d-block" style="font-size: 20px;"></i>
              No se encontraron coincidencias.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-2 text-muted text-sm">
      Total: {{ count($sucursales) }} sucursales
    </div>

  </div>
</div>

@include('configuracion.sucursales._modal')

@stop

@section('js')
<script>
  document.addEventListener('DOMContentLoaded', function() {

    // ---------------------------------------------------------
    // 1. REFERENCIAS GLOBALES
    // ---------------------------------------------------------
    const modal = $('#modalSucursal');
    const form = $('#formSucursal');
    const modalTitulo = $('#modalTitulo');
    const methodField = $('#methodField');

    // Referencias a Inputs
    const inputNombre = $('#inputNombre');
    const inputDireccion = $('#inputDireccion');
    const inputTelefono = $('#inputTelefono');
    const inputImpuesto = $('#inputImpuesto');
    const inputCodigo = $('input[name="codigo"]');

    // Referencias Imagen/Estado
    const previewImg = $('#previewImagen');
    const fileInput = $('#customFile');
    const checkActivo = $('#checkActivo');
    const labelActivo = $('#labelActivo');

    // ---------------------------------------------------------
    // 2. BÚSQUEDA EN TIEMPO REAL (Live Search)
    // ---------------------------------------------------------
    $('#liveSearchInput').on('keyup', function() {
      var value = $(this).val().toLowerCase();
      var visibleRows = 0;

      $("#tablaSucursales tr").filter(function() {
        // Ignorar la fila de mensaje "No results"
        if ($(this).attr('id') === 'noResultsFound') return;

        // Buscar texto en la fila
        var match = $(this).text().toLowerCase().indexOf(value) > -1;
        $(this).toggle(match);

        if (match) visibleRows++;
      });

      // Mostrar u ocultar mensaje de vacío
      if (visibleRows === 0) {
        $('#noResultsFound').show();
      } else {
        $('#noResultsFound').hide();
      }
    });

    // ---------------------------------------------------------
    // 3. LOGICA DE IMAGEN
    // ---------------------------------------------------------
    fileInput.change(function(e) {
      if (this.files && this.files[0]) {
        let reader = new FileReader();
        reader.onload = function(e) {
          previewImg.attr('src', e.target.result);
        }
        reader.readAsDataURL(this.files[0]);
      }
    });

    // ---------------------------------------------------------
    // 4. LOGICA DEL SWITCH
    // ---------------------------------------------------------
    checkActivo.change(function() {
      if ($(this).is(':checked')) {
        labelActivo.text('Operativa').removeClass('text-danger').addClass('text-success');
      } else {
        labelActivo.text('Cerrada').removeClass('text-success').addClass('text-danger');
      }
    });

    // ---------------------------------------------------------
    // 5. ABRIR MODAL CREAR
    // ---------------------------------------------------------
    window.abrirModalCrear = function() {
      form[0].reset();
      form.attr('action', "{{ route('configuracion.sucursales.store') }}");
      methodField.html('');

      modalTitulo.html('<i class="fas fa-plus-circle mr-2"></i> Registrar Nueva Sucursal');
      previewImg.attr('src', 'https://ui-avatars.com/api/?name=Nueva&background=cccccc&color=fff&size=128');
      checkActivo.prop('checked', true).trigger('change');

      modal.modal('show');
    }

    // ---------------------------------------------------------
    // 6. ABRIR MODAL EDITAR
    // ---------------------------------------------------------
    window.abrirModalEditar = function(data) {
      form[0].reset();

      let url = "{{ route('configuracion.sucursales.update', ':id') }}";
      url = url.replace(':id', data.id);

      form.attr('action', url);
      methodField.html('<input type="hidden" name="_method" value="PUT">');

      modalTitulo.html('<i class="fas fa-edit mr-2"></i> Editar: ' + data.nombre);

      // Llenar datos
      inputCodigo.val(data.codigo);
      inputNombre.val(data.nombre);
      inputDireccion.val(data.direccion);
      inputTelefono.val(data.telefono);
      inputImpuesto.val(data.impuesto_porcentaje);
      checkActivo.prop('checked', data.activo == 1).trigger('change');

      // Imagen
      if (data.imagen_sucursal) {
        previewImg.attr('src', "/storage/" + data.imagen_sucursal);
      } else {
        let nombreClean = encodeURIComponent(data.nombre);
        previewImg.attr('src', 'https://ui-avatars.com/api/?name=' + nombreClean + '&background=20c997&color=fff&size=128');
      }

      modal.modal('show');
    }

  });
</script>
@stop