@csrf
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Código</label>
        <input type="text" name="codigo" class="form-control" value="{{ old('codigo', $m->codigo ?? '') }}" required maxlength="50">
    </div>
    <div class="col-md-3">
        <label class="form-label">Código de barras</label>
        <input type="text" name="codigo_barras" class="form-control" value="{{ old('codigo_barras', $m->codigo_barras ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $m->nombre ?? '') }}" required maxlength="150">
    </div>

    <div class="col-md-6">
        <label class="form-label">Laboratorio</label>
        <input type="text" name="laboratorio" class="form-control" value="{{ old('laboratorio', $m->laboratorio ?? '') }}" maxlength="150">
    </div>
    <div class="col-md-6">
        <label class="form-label">Categoría</label>
        <select name="categoria_id" class="form-control">
            <option value="">-- Selecciona --</option>
            @foreach(($categorias ?? []) as $c)
            <option value="{{ $c->id }}" {{ (string)old('categoria_id', $m->categoria_id ?? '')===(string)$c->id ? 'selected':'' }}>
                {{ $c->nombre }}
            </option>
            @endforeach
        </select>
    </div>
</div>

<hr class="my-3">

<h5 class="mb-2">Vinculación inicial a sucursal</h5>
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Sucursal</label>
        <select name="sucursal_id" class="form-control" required>
            @foreach(($sucursalesDisponibles ?? []) as $s)
            <option value="{{ $s->id }}" {{ (string)old('sucursal_id')===(string)$s->id ? 'selected':'' }}>{{ $s->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Precio</label>
        <input type="number" step="0.01" name="precio" class="form-control" value="{{ old('precio') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Stock mínimo</label>
        <input type="number" name="stock_minimo" class="form-control" value="{{ old('stock_minimo') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Ubicación</label>
        <input type="text" name="ubicacion" class="form-control" value="{{ old('ubicacion') }}" maxlength="120">
    </div>
</div>

<hr class="my-3">

<h5 class="mb-2">Lote inicial (opcional)</h5>
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Código de lote</label>
        <input type="text" name="lote_codigo" class="form-control" value="{{ old('lote_codigo') }}" maxlength="80">
    </div>
    <div class="col-md-4">
        <label class="form-label">Cantidad inicial</label>
        <input type="number" name="cantidad_inicial" class="form-control" value="{{ old('cantidad_inicial') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Fecha de vencimiento</label>
        <input type="date" name="fecha_vencimiento" class="form-control" value="{{ old('fecha_vencimiento') }}">
    </div>
</div>