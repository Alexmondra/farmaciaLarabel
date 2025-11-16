@extends('adminlte::page')

@section('title', 'Compras')
@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Compra #{{ $compra->id }}</h3>
        <a href="{{ route('compras.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Volver al listado
        </a>
    </div>

    {{-- FILA SUPERIOR: PROVEEDOR / COMPRA --}}
    <div class="row">
        {{-- PROVEEDOR --}}
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Datos del proveedor</strong>
                </div>
                <div class="card-body">
                    <p><strong>Razón social:</strong>
                        {{ $compra->proveedor->razon_social ?? '-' }}
                    </p>
                    <p><strong>RUC:</strong>
                        {{ $compra->proveedor->ruc ?? '-' }}
                    </p>
                    <p><strong>Contacto:</strong>
                        {{ $compra->proveedor->contacto ?? '-' }}
                    </p>
                    <p><strong>Teléfono:</strong>
                        {{ $compra->proveedor->telefono ?? '-' }}
                    </p>
                    <p><strong>Correo:</strong>
                        {{ $compra->proveedor->email ?? '-' }}
                    </p>
                    <p><strong>Dirección:</strong>
                        {{ $compra->proveedor->direccion ?? '-' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- COMPRA --}}
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Datos de la compra</strong>
                </div>
                <div class="card-body">
                    <p><strong>Fecha de recepción:</strong>
                        {{ optional($compra->fecha_recepcion)->format('d/m/Y') }}
                    </p>

                    <p><strong>Sucursal:</strong>
                        {{ $compra->sucursal->nombre ?? '-' }}
                    </p>

                    <p><strong>Documento:</strong>
                        {{ trim(($compra->tipo_comprobante ?? '').' '.($compra->numero_factura_proveedor ?? '')) ?: '-' }}
                    </p>

                    <p><strong>Estado:</strong>
                        @switch($compra->estado)
                        @case('registrada')
                        <span class="badge bg-secondary">Registrada</span>
                        @break
                        @case('recibida')
                        <span class="badge bg-success">Recibida</span>
                        @break
                        @case('pendiente')
                        <span class="badge bg-warning text-dark">Pendiente</span>
                        @break
                        @case('anulada')
                        <span class="badge bg-danger">Anulada</span>
                        @break
                        @default
                        <span class="badge bg-light text-muted">Desconocido</span>
                        @endswitch
                    </p>

                    <p><strong>Total factura:</strong>
                        S/ {{ number_format($compra->costo_total_factura ?? 0, 2) }}
                    </p>

                    <p><strong>Observaciones:</strong>
                        {{ $compra->observaciones ?? '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- DETALLES DE LA COMPRA --}}
    {{-- DETALLES DE LA COMPRA --}}
    <div class="card">
        <div class="card-header">
            <strong>Detalle de la compra</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Medicamento</th>
                            <th class="text-center">Lote</th>
                            <th class="text-center">F. Vencimiento</th>
                            <th class="text-end">Cant. recibida</th>
                            <th class="text-end">Stock actual</th>
                            <th class="text-end">P. compra unit.</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-center">Ubicación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($compra->detalles as $i => $det)
                        @php
                        $lote = $det->lote;
                        $medicamento = optional($lote)->medicamento;
                        @endphp
                        <tr>
                            {{-- Número de ítem --}}
                            <td>{{ $i + 1 }}</td>

                            {{-- Nombre del medicamento --}}
                            <td>{{ $medicamento->nombre ?? '-' }}</td>

                            {{-- Código de lote --}}
                            <td class="text-center">
                                {{ $lote->codigo_lote ?? '-' }}
                            </td>

                            {{-- Fecha de vencimiento del lote --}}
                            <td class="text-center">
                                {{ optional($lote->fecha_vencimiento)->format('d/m/Y') ?? '-' }}
                            </td>

                            {{-- Cantidad recibida (detalle_compra) --}}
                            <td class="text-end">
                                {{ $det->cantidad_recibida }}
                            </td>

                            {{-- Stock actual del lote --}}
                            <td class="text-end">
                                {{ $lote->stock_actual ?? 0 }}
                            </td>

                            {{-- Precio de compra unitario --}}
                            <td class="text-end">
                                S/ {{ number_format($det->precio_compra_unitario, 2) }}
                            </td>

                            {{-- Subtotal = cantidad_recibida * precio_unitario --}}
                            <td class="text-end">
                                S/
                                {{ number_format($det->cantidad_recibida * $det->precio_compra_unitario, 2) }}
                            </td>

                            {{-- Ubicación física del lote --}}
                            <td class="text-center">
                                {{ $lote->ubicacion ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                No hay detalles registrados para esta compra.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>


</div>
@endsection