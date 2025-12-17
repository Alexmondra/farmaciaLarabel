@extends('adminlte::page')

@section('title','Sucursales')

@section('content_header')
<h1>Listado de Sucursales</h1>
@stop

@section('content')
<div class="card">
  <div class="card-body">

    {{-- CABECERA RESPONSIVE --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
      <div class="input-group mb-2 mb-md-0" style="max-width: 250px;">
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
      <table class="table table-sm table-striped align-middle responsive-table">
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
            <td data-label="Imagen">
              @if($s->imagen_sucursal)
              <img src="{{ asset('storage/'.$s->imagen_sucursal) }}" class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover;">
              @else
              <i class="fas fa-store text-muted fa-lg"></i>
              @endif
            </td>
            <td class="font-weight-bold" data-label="Cód. SUNAT">{{ $s->codigo }}</td>
            <td data-label="Nombre">
              {{ $s->nombre }} <br>
              <small class="text-muted">{{ $s->direccion }}</small>
            </td>
            <td data-label="Ubicación">
              {{ $s->distrito }} <br>
              <small class="text-muted">{{ $s->ubigeo }}</small>
            </td>
            <td data-label="Impuesto">{{ $s->impuesto_porcentaje }}%</td>
            <td data-label="Estado">
              <span class="badge badge-{{ $s->activo ? 'success' : 'secondary' }}">
                {{ $s->activo ? 'Activa' : 'Inactiva' }}
              </span>
            </td>
            <td data-label="Acciones">
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

    // VARIABLE PARA CONSTRUIR LA RUTA DE STORAGE
    const storagePath = "{{ asset('storage') }}";

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
      // Usar la imagen de avatar o una por defecto al crear
      previewImg.attr('src', 'https://ui-avatars.com/api/?name=Nueva&background=cccccc&color=fff&size=128');

      // Estado por defecto: Activo
      checkActivo.prop('checked', true).trigger('change');

      // --- LLENAR CON SUGERENCIAS ---
      $('input[name="codigo"]').val(sugerencias.codigo);

      $('input[name="serie_boleta"]').val(sugerencias.boleta);
      $('input[name="serie_factura"]').val(sugerencias.factura);
      $('input[name="serie_ticket"]').val(sugerencias.ticket);

      $('input[name="serie_nc_factura"]').val(sugerencias.nc_factura);
      $('input[name="serie_nc_boleta"]').val(sugerencias.nc_boleta);
      $('input[name="serie_guia"]').val(sugerencias.guia);

      // Resetear campo file por si acaso
      $('#customFile').val('');

      modal.modal('show');
    }

    // ---------------------------------------------------------
    // 3. FUNCIÓN PARA ABRIR MODAL "EDITAR"
    // ---------------------------------------------------------
    window.abrirModalEditar = function(data) {
      form[0].reset(); // Limpiamos primero

      let url = "{{ route('configuracion.sucursales.update', ':id') }}";
      url = url.replace(':id', data.id);

      form.attr('action', url);
      methodField.html('<input type="hidden" name="_method" value="PUT">'); // Importante para Update

      modalTitulo.html('<i class="fas fa-edit mr-2"></i> Editar: ' + data.nombre);

      // --- LLENADO DE DATOS ---
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

      // --- SERIES ---
      // Aseguramos que se carguen de la BD, o se queden vacías si son null (el form no permite vacíos ya que tienen required)
      $('input[name="serie_boleta"]').val(data.serie_boleta || sugerencias.boleta);
      $('input[name="serie_factura"]').val(data.serie_factura || sugerencias.factura);
      $('input[name="serie_ticket"]').val(data.serie_ticket || sugerencias.ticket);

      $('input[name="serie_nc_factura"]').val(data.serie_nc_factura || sugerencias.nc_factura);
      $('input[name="serie_nc_boleta"]').val(data.serie_nc_boleta || sugerencias.nc_boleta);
      $('input[name="serie_guia"]').val(data.serie_guia || sugerencias.guia);

      // Imagen y Estado
      checkActivo.prop('checked', data.activo == 1).trigger('change');
      $('#customFile').val(''); // Resetear campo file

      if (data.imagen_sucursal) {
        // Usar la variable JS del asset para construir la ruta, más seguro que concatenar la ruta PHP en JS
        previewImg.attr('src', storagePath + "/" + data.imagen_sucursal);
      } else {
        // Generar avatar con iniciales si no hay foto
        let nombreClean = data.nombre ? data.nombre.replace(/[^a-zA-Z ]/g, "").substring(0, 2) : 'S';
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


@section('css')
<style>
  /* === RESPONSIVIDAD: TABLA TRANSFORMADA A TARJETA EN MÓVIL === */
  @media screen and (max-width: 768px) {
    .responsive-table thead {
      display: none;
    }

    .responsive-table tr {
      display: block;
      margin-bottom: 1rem;
      border: 1px solid #dee2e6;
      border-radius: 0.25rem;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      position: relative;
    }

    .responsive-table td {
      display: block;
      text-align: right !important;
      padding: 0.5rem 1rem;
      border: none;
    }

    .responsive-table td::before {
      content: attr(data-label);
      float: left;
      font-weight: bold;
      text-transform: uppercase;
      font-size: 0.75rem;
      color: #6c757d;
      padding-right: 10px;
    }

    .responsive-table tr td:first-child {
      border-bottom: 1px solid #dee2e6;
    }

    .responsive-table tr td:last-child {
      text-align: left !important;
      border-top: 1px solid #dee2e6;
    }
  }

  /* === MODO OSCURO (Solo se activa si el sistema lo prefiere) === */
  body.dark-mode {
    /* no pongas nada aquí */
  }

  /* y antepone body.dark-mode a cada regla */
  body.dark-mode .card {
    background-color: #343a40 !important;
    color: #d1d9e0 !important;
  }

  body.dark-mode .table {
    color: #e9ecef;
  }

  body.dark-mode .table thead th {
    background-color: #495057 !important;
    color: #fff;
  }
</style>
@stop