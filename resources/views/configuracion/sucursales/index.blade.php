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
            <th>Cód. SUNAT</th>
            <th>Nombre</th>
            <th>Distrito / Ubigeo</th>
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
            <td class="font-weight-bold">{{ $s->codigo }}</td>
            <td>
              {{ $s->nombre }} <br>
              <small class="text-muted">{{ $s->direccion }}</small>
            </td>
            <td>
              {{ $s->distrito }} <br>
              <small class="text-muted">{{ $s->ubigeo }}</small>
            </td>
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
    // 1. REFERENCIAS (Usamos selectores por 'name' para mayor seguridad)
    // ---------------------------------------------------------
    const modal = $('#modalSucursal');
    const form = $('#formSucursal');
    const modalTitulo = $('#modalTitulo');
    const methodField = $('#methodField');

    // Referencias a inputs de imagen y estado
    const previewImg = $('#previewImagen');
    const checkActivo = $('#checkActivo');
    const labelActivo = $('#labelActivo');

    // VARIABLES DE SUGERENCIA (Vienen del controlador)
    const sugerencias = {
      boleta: @json($sugerenciaBoleta ?? 'B001'),
      factura: @json($sugerenciaFactura ?? 'F001'),
      ticket: @json($sugerenciaTicket ?? 'TK01'),
      nc_factura: @json($sugerenciaNCFactura ?? 'FC01'),
      nc_boleta: @json($sugerenciaNCBoleta ?? 'BC01'),
      guia: @json($sugerenciaGuia ?? 'T001'),
      codigo: @json($sugerenciaCodigo ?? '0001')
    };

    // ---------------------------------------------------------
    // 2. FUNCIÓN PARA ABRIR MODAL "CREAR"
    // ---------------------------------------------------------
    window.abrirModalCrear = function() {
      form[0].reset(); // Limpia el formulario
      form.attr('action', "{{ route('configuracion.sucursales.store') }}");
      methodField.html(''); // Quita el método PUT (será POST por defecto)

      modalTitulo.html('<i class="fas fa-plus-circle mr-2"></i> Registrar Nueva Sucursal');
      previewImg.attr('src', 'https://ui-avatars.com/api/?name=Nueva&background=cccccc&color=fff&size=128');

      // Estado por defecto: Activo
      checkActivo.prop('checked', true).trigger('change');

      // --- LLENAR CON SUGERENCIAS ---
      $('input[name="codigo"]').val(sugerencias.codigo);

      $('input[name="serie_boleta"]').val(sugerencias.boleta);
      $('input[name="serie_factura"]').val(sugerencias.factura);
      $('input[name="serie_ticket"]').val(sugerencias.ticket);

      // Aquí llenamos las nuevas para que no queden vacías al crear
      $('input[name="serie_nc_factura"]').val(sugerencias.nc_factura);
      $('input[name="serie_nc_boleta"]').val(sugerencias.nc_boleta);
      $('input[name="serie_guia"]').val(sugerencias.guia);

      modal.modal('show');
    }

    // ---------------------------------------------------------
    // 3. FUNCIÓN PARA ABRIR MODAL "EDITAR" (Aquí estaba el fallo)
    // ---------------------------------------------------------
    window.abrirModalEditar = function(data) {
      form[0].reset(); // Limpiamos primero

      let url = "{{ route('configuracion.sucursales.update', ':id') }}";
      url = url.replace(':id', data.id);

      form.attr('action', url);
      methodField.html('<input type="hidden" name="_method" value="PUT">'); // Importante para Update

      modalTitulo.html('<i class="fas fa-edit mr-2"></i> Editar: ' + data.nombre);

      // --- LLENADO DE DATOS (Usando name para asegurar que los encuentra) ---
      $('input[name="codigo"]').val(data.codigo);
      $('input[name="nombre"]').val(data.nombre);

      // Ubicación
      $('input[name="ubigeo"]').val(data.ubigeo);
      $('input[name="departamento"]').val(data.departamento);
      $('input[name="provincia"]').val(data.provincia);
      $('input[name="distrito"]').val(data.distrito);
      $('input[name="direccion"]').val(data.direccion);

      // Contacto & Fiscal
      $('input[name="email"]').val(data.email);
      $('input[name="telefono"]').val(data.telefono);
      $('input[name="impuesto_porcentaje"]').val(data.impuesto_porcentaje);

      // --- SERIES (Aquí corregimos para que cargue lo que tiene la BD) ---
      $('input[name="serie_boleta"]').val(data.serie_boleta);
      $('input[name="serie_factura"]').val(data.serie_factura);
      $('input[name="serie_ticket"]').val(data.serie_ticket);

      // ¡ESTO ES LO QUE FALTABA!
      // Si la BD tiene null, ponle vacío o una sugerencia, pero intenta cargar 'data'
      $('input[name="serie_nc_factura"]').val(data.serie_nc_factura);
      $('input[name="serie_nc_boleta"]').val(data.serie_nc_boleta);
      $('input[name="serie_guia"]').val(data.serie_guia);

      // Imagen y Estado
      checkActivo.prop('checked', data.activo == 1).trigger('change');

      if (data.imagen_sucursal) {
        previewImg.attr('src', "/storage/" + data.imagen_sucursal);
      } else {
        // Generar avatar con iniciales si no hay foto
        let nombreClean = data.nombre.replace(/[^a-zA-Z ]/g, "").substring(0, 2);
        previewImg.attr('src', 'https://ui-avatars.com/api/?name=' + nombreClean + '&background=20c997&color=fff&size=128');
      }

      modal.modal('show');
    }

    // ---------------------------------------------------------
    // 4. LOGICA EXTRA (Imagen, Switch, Buscador)
    // ---------------------------------------------------------
    $('#customFile').change(function(e) {
      if (this.files && this.files[0]) {
        let reader = new FileReader();
        reader.onload = function(e) {
          previewImg.attr('src', e.target.result);
        }
        reader.readAsDataURL(this.files[0]);
      }
    });

    checkActivo.change(function() {
      if ($(this).is(':checked')) {
        labelActivo.text('Operativa').removeClass('text-danger').addClass('text-success');
      } else {
        labelActivo.text('Cerrada').removeClass('text-success').addClass('text-danger');
      }
    });

    $('#liveSearchInput').on('keyup', function() {
      var value = $(this).val().toLowerCase();
      var visibleRows = 0;
      $("#tablaSucursales tr").filter(function() {
        if ($(this).attr('id') === 'noResultsFound') return;
        var match = $(this).text().toLowerCase().indexOf(value) > -1;
        $(this).toggle(match);
        if (match) visibleRows++;
      });
      $('#noResultsFound').toggle(visibleRows === 0);
    });

  });
</script>
@stop