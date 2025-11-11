@extends('adminlte::page')

@section('title', 'Registrar medicamento')

@section('content_header')
<h1>Registrar nuevo medicamento</h1>
@stop

@section('content')
<form method="POST"
    action="{{ route('inventario.medicamentos.store') }}"
    enctype="multipart/form-data"
    id="form-create"
    data-lookup-url="{{ route('inventario.medicamentos.lookup') }}">
    @csrf

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <input type="hidden" name="medicamento_existente_id" id="medicamento_existente_id" value="{{ old('medicamento_existente_id') }}">

    <div class="row g-3">
        {{-- ===================== IZQUIERDA: MEDICAMENTO ===================== --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><strong>Datos del medicamento</strong></div>
                <div class="card-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Código de barras</label>
                        <input type="text" name="codigo_barra" id="codigo_barra" class="form-control"
                            value="{{ old('codigo_barra') }}" placeholder="Escanear o escribir." autocomplete="off">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Código interno <span class="text-danger">*</span></label>
                        <input type="text" name="codigo" id="codigo" class="form-control"
                            data-required-if-new="1"
                            value="{{ old('codigo') }}" placeholder="Código interno" autocomplete="off">
                    </div>

                    <div class="col-md-4 position-relative">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre" class="form-control"
                            data-required-if-new="1"
                            value="{{ old('nombre') }}" placeholder="Ej. Paracetamol 500mg" autocomplete="off">
                        <div id="suggestions" class="list-group position-absolute w-100 shadow"
                            style="z-index:1050; display:none; max-height:240px; overflow:auto;"></div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Categoría</label>
                        <select name="categoria_id" id="categoria_id" class="form-select">
                            <option value="">Seleccione...</option>
                            @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Laboratorio</label>
                        <input type="text" name="laboratorio" id="laboratorio" class="form-control"
                            value="{{ old('laboratorio') }}" placeholder="Nombre del laboratorio">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Presentación</label>
                        <input type="text" name="presentacion" id="presentacion" class="form-control"
                            value="{{ old('presentacion') }}" placeholder="Ej. Caja x10, Frasco 100ml">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Forma farmacéutica</label>
                        <input type="text" name="forma_farmaceutica" id="forma_farmaceutica" class="form-control"
                            value="{{ old('forma_farmaceutica') }}" placeholder="Ej. Tableta, Jarabe">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Concentración</label>
                        <input type="text" name="concentracion" id="concentracion" class="form-control"
                            value="{{ old('concentracion') }}" placeholder="Ej. 500 mg">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Registro sanitario</label>
                        <input type="text" name="registro_sanitario" id="registro_sanitario" class="form-control"
                            value="{{ old('registro_sanitario') }}" placeholder="Código DIGEMID / INVIMA">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" id="descripcion" class="form-control" rows="2"
                            placeholder="Descripción breve del medicamento">{{ old('descripcion') }}</textarea>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Foto del medicamento</label>
                        <div id="dropzone" class="border rounded d-flex align-items-center justify-content-center p-3 text-center"
                            style="height:220px; cursor:pointer;">
                            <div class="w-100">
                                <img id="preview" src="" alt="" style="max-width:100%; max-height:160px; display:none;">
                                <div id="placeholder" class="text-muted">Arrastra una imagen aquí o haz clic para seleccionar</div>
                            </div>
                        </div>
                        <input type="file" name="foto" id="foto" accept="image/*" class="d-none">
                        <small class="text-muted">Formatos: JPG/PNG. Máx ~2MB.</small>
                    </div>
                </div>
            </div>

            {{-- ===== FICHA de solo lectura cuando elijas un medicamento existente ===== --}}
            <div id="ficha-medicamento" class="card d-none mt-2">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <strong>Medicamento seleccionado</strong>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-editar-med">
                        Editar campos
                    </button>
                </div>
                <div class="card-body row g-3">
                    <div class="col-md-6"><b>Código:</b> <span data-medview="codigo">—</span></div>
                    <div class="col-md-6"><b>Código de barras:</b> <span data-medview="codigo_barra">—</span></div>
                    <div class="col-md-6"><b>Nombre:</b> <span data-medview="nombre">—</span></div>
                    <div class="col-md-6"><b>Categoría:</b> <span data-medview="categoria">—</span></div>
                    <div class="col-md-6"><b>Laboratorio:</b> <span data-medview="laboratorio">—</span></div>
                    <div class="col-md-6"><b>Presentación:</b> <span data-medview="presentacion">—</span></div>
                    <div class="col-md-6"><b>Forma farmacéutica:</b> <span data-medview="forma_farmaceutica">—</span></div>
                    <div class="col-md-6"><b>Concentración:</b> <span data-medview="concentracion">—</span></div>
                    <div class="col-md-6"><b>Registro sanitario:</b> <span data-medview="registro_sanitario">—</span></div>
                    <div class="col-md-12"><b>Descripción:</b> <span data-medview="descripcion">—</span></div>
                    <div class="col-12">
                        <img id="ficha-imagen" src="" alt="" style="max-width:180px; display:none;">
                    </div>
                </div>
            </div>
        </div>

        {{-- ===================== DERECHA: SUCURSAL + MOVIMIENTO INICIAL ===================== --}}
        <div class="col-lg-5">
            {{-- Sucursal (arriba) --}}
            <div class="card mb-3">
                <div class="card-header"><strong>Asignación a sucursal</strong></div>
                <div class="card-body row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Sucursal</label>
                        <select name="sucursal_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            @foreach($sucursales as $s)
                            <option value="{{ $s->id }}" {{ old('sucursal_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Precio venta (V)</label>
                        <input type="number" step="0.01" name="precio_v" class="form-control"
                            value="{{ old('precio_v') }}" placeholder="0.00" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Precio compra (C)</label>
                        <input type="number" step="0.01" name="precio_c" class="form-control"
                            value="{{ old('precio_c') }}" placeholder="0.00" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Stock inicial</label>
                        <input type="number" name="stock" class="form-control"
                            value="{{ old('stock', 0) }}" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Stock mínimo</label>
                        <input type="number" name="stock_minimo" class="form-control"
                            value="{{ old('stock_minimo', 0) }}" min="0">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Ubicación (góndola/almacén)</label>
                        <input type="text" name="ubicacion" class="form-control"
                            value="{{ old('ubicacion') }}" placeholder="Ej. Pasillo B - Estante 2">
                    </div>
                </div>
            </div>

            {{-- Lote inicial (opcional) --}}
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <strong>Lote inicial</strong>
                    <span class="ms-2 small text-muted">(opcional, recomendado)</span>
                </div>
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Código de lote</label>
                        <input type="text" name="lote_codigo" class="form-control"
                            value="{{ old('lote_codigo') }}" placeholder="Ej. L-2025-01">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cantidad (de este lote)</label>
                        <input type="number" name="lote_cantidad" class="form-control"
                            value="{{ old('lote_cantidad') }}" min="0" placeholder="0">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Fecha de vencimiento</label>
                        <input type="date" name="lote_vencimiento" class="form-control"
                            value="{{ old('lote_vencimiento') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Acciones --}}
    <div class="col-12 d-flex justify-content-end mt-3">
        <a href="{{ route('inventario.medicamentos.index') }}" class="btn btn-outline-secondary me-2">Cancelar</a>
        <button class="btn btn-success"><i class="fas fa-save"></i> Registrar medicamento</button>
    </div>
</form>
@stop

@section('js')
{{-- Carga diferida para no bloquear el render --}}
<script src="{{ asset('js/scriptCreate.js') }}" defer></script>
@endsection