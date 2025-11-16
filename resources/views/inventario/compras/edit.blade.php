@extends('adminlte::page')

@section('title', 'Editar compra')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Editar compra</h1>

    <a href="{{ route('compras.show', $compra->id) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver al detalle
    </a>
</div>
@endsection

@section('content')

<form method="POST" action="{{ route('compras.update', $compra->id) }}">
    @csrf
    @method('PUT')

    <div class="card mb-3">
        <div class="card-header">
            Datos de la compra (cabecera)
        </div>
        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-5">
                    <label>Proveedor</label>
                    <select name="proveedor_id" class="form-control">
                        @foreach($proveedores as $prov)
                        <option value="{{ $prov->id }}"
                            {{ old('proveedor_id', $compra->proveedor_id) == $prov->id ? 'selected' : '' }}>
                            {{ $prov->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Fecha</label>
                    <input type="date" name="fecha_compra" class="form-control"
                        value="{{ old('fecha_compra', $compra->fecha_compra?->format('Y-m-d')) }}">
                </div>

                <div class="col-md-2">
                    <label>Tipo doc.</label>
                    <input type="text" name="tipo_documento" class="form-control"
                        value="{{ old('tipo_documento', $compra->tipo_documento) }}">
                </div>

                <div class="col-md-1">
                    <label>Serie</label>
                    <input type="text" name="serie" class="form-control"
                        value="{{ old('serie', $compra->serie) }}">
                </div>
                <div class="col-md-1">
                    <label>Número</label>
                    <input type="text" name="numero" class="form-control"
                        value="{{ old('numero', $compra->numero) }}">
                </div>
            </div>

            <div class="mb-3">
                <label>Observación</label>
                <input type="text" name="observacion" class="form-control"
                    value="{{ old('observacion', $compra->observacion) }}">
            </div>

            <div class="mb-3">
                <label>Estado</label>
                <select name="estado" class="form-control">
                    <option value="registrada" {{ old('estado', $compra->estado) == 'registrada' ? 'selected' : '' }}>
                        Registrada
                    </option>
                    <option value="anulada" {{ old('estado', $compra->estado) == 'anulada' ? 'selected' : '' }}>
                        Anulada
                    </option>
                </select>
            </div>

        </div>

        <div class="card-footer text-end">
            <button class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar cambios
            </button>
        </div>
    </div>

</form>

@endsection