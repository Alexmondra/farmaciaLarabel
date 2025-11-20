@extends('adminlte::page')

@section('title', 'Detalle de Caja Sesión #'.$cajaSesion->id)

@section('content_header')
<div class="d-flex justify-content-between">
    <div>
        <h1>Detalle de Sesión de Caja #{{ $cajaSesion->id }}</h1>
        <p class="mb-0">
            <strong>Usuario:</strong> {{ $cajaSesion->usuario->name ?? 'N/A' }} |
            <strong>Sucursal:</strong> {{ $cajaSesion->sucursal->nombre ?? 'N/A' }}
        </p>
    </div>
    <a href="{{ route('cajas.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
</div>
@stop

@section('content')

{{-- TARJETA DE RESUMEN DE LA CAJA --}}
<div class="row">
    <div class="col-md-3">
        <x-adminlte-info-box title="Estado" text="{{ $cajaSesion->estado === 'abierta' ? 'ABIERTA' : 'CERRADA' }}"
            icon="fas fa-fw fa-info-circle" theme="{{ $cajaSesion->estado === 'abierta' ? 'success' : 'secondary' }}" />
    </div>
    <div class="col-md-3">
        <x-adminlte-info-box title="Apertura" text="{{ $cajaSesion->fecha_apertura->format('d/m/Y H:i') }}"
            icon="fas fa-fw fa-play" theme="info" />
    </div>
    <div class="col-md-3">
        <x-adminlte-info-box title="Cierre" text="{{ $cajaSesion->fecha_cierre ? $cajaSesion->fecha_cierre->format('d/m/Y H:i') : '---' }}"
            icon="fas fa-fw fa-stop" theme="info" />
    </div>
    <div class="col-md-3">
        <x-adminlte-info-box title="Saldo Inicial" text="S/ {{ number_format($cajaSesion->saldo_inicial, 2) }}"
            icon="fas fa-fw fa-dollar-sign" theme="warning" />
    </div>
</div>

{{-- TARJETA CON LA TABLA DE VENTAS --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Ventas Registradas en esta Sesión</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Comprobante</th>
                    <th>Número</th>
                    <th>Fecha Emisión</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Medio Pago</th>
                    <th style="text-align: right;">Total Neto</th>
                    <th style="width: 100px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cajaSesion->ventas as $venta)
                <tr>
                    <td>{{ $venta->tipo_comprobante }}</td>
                    <td>{{ $venta->serie }}-{{ $venta->numero }}</td>
                    <td>{{ $venta->fecha_emision->format('d/m/Y H:i') }}</td>
                    <td>{{ $venta->cliente->nombre ?? 'Varios' }} {{ $venta->cliente->apellidos ?? '' }}</td>
                    <td>{{ $venta->usuario->name ?? 'N/A' }}</td>
                    <td>{{ $venta->medio_pago }}</td>
                    <td style="text-align: right;">S/ {{ number_format($venta->total_neto, 2) }}</td>
                    <td>
                        {{-- Este es el botón que abre el modal --}}
                        <button class="btn btn-sm btn-info btn-ver-detalle"
                            data-toggle="modal"
                            data-target="#modalDetalleVenta"
                            data-venta-id="{{ $venta->id }}"
                            {{-- Pasamos los detalles como un JSON --}}
                            data-detalles="{{ $venta->detalles->toJson() }}"
                            data-venta-info="Venta {{ $venta->serie }}-{{ $venta->numero }}">
                            <i class="fas fa-eye"></i> Detalle
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">No hay ventas registradas en esta sesión de caja.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


{{--
      EL MODAL DE DETALLE DE VENTA 
    --}}
<div class="modal fade" id="modalDetalleVenta" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Detalle de Venta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-center">Cargando detalles...</p>
                <table class="table table-bordered d-none" id="tablaDetalles">
                    <thead>
                        <tr>
                            <th>Medicamento</th>
                            <th>Cantidad</th>
                            <th>Precio Unit.</th>
                            <th>Descuento Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="modalDetalleBody">
                        {{-- El contenido se generará aquí --}}
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@stop

{{-- ================================================================= --}}
{{-- SECCIÓN DE JAVASCRIPT CORREGIDA --}}
{{-- ================================================================= --}}
@push('js')
<script>
    // Usamos jQuery, que ya viene con AdminLTE
    $(document).ready(function() {

        // Evento que se dispara CADA VEZ que se intenta mostrar el modal
        $('#modalDetalleVenta').on('show.bs.modal', function(event) {

            // 1. Obtener el botón que disparó el modal
            var button = $(event.relatedTarget);

            // 2. Extraer la información de los atributos 'data-*'
            var ventaInfo = button.data('venta-info');

            // --- INICIO DE LA CORRECCIÓN ---
            // jQuery's .data() ya parsea el JSON por nosotros.
            // No necesitamos JSON.parse(), que era lo que causaba el error.
            var detalles = button.data('detalles');
            // --- FIN DE LA CORRECCIÓN ---

            // 3. Obtener referencias a elementos del modal
            var modal = $(this);
            var modalTitle = modal.find('.modal-title');
            var modalBodyTable = modal.find('#tablaDetalles');
            var modalBodyTbody = modal.find('#modalDetalleBody');

            // 4. Actualizar el título del modal
            modalTitle.text('Detalle de ' + ventaInfo);

            // 5. Limpiar la tabla y mostrar "cargando"
            modalBodyTbody.empty();
            modalBodyTable.addClass('d-none'); // Ocultar tabla
            modal.find('.modal-body p').text('Procesando...').show(); // Mostrar 'Procesando'

            // 6. Validar y construir la tabla
            if (detalles && Array.isArray(detalles) && detalles.length > 0) {

                detalles.forEach(function(item) {

                    // Verificamos si 'medicamento' no es nulo
                    var nombreMedicamento = 'Medicamento no encontrado';
                    if (item.medicamento) {
                        nombreMedicamento = item.medicamento.nombre;
                    }

                    var fila = '<tr>' +
                        '<td>' + nombreMedicamento + '</td>' +
                        '<td>' + item.cantidad + '</td>' +
                        '<td>S/ ' + parseFloat(item.precio_unitario).toFixed(2) + '</td>' +
                        '<td>S/ ' + parseFloat(item.descuento_unitario).toFixed(2) + '</td>' +
                        '<td>S/ ' + parseFloat(item.subtotal_neto).toFixed(2) + '</td>' +
                        '</tr>';
                    modalBodyTbody.append(fila);
                });

                modal.find('.modal-body p').hide(); // Ocultar 'Procesando'
                modalBodyTable.removeClass('d-none'); // Mostrar tabla

            } else {
                // Si no hay detalles
                modal.find('.modal-body p').text('Esta venta no tiene detalles registrados.');
            }
        });
    });
</script>
@endpush