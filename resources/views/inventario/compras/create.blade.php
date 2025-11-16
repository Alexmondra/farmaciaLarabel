{{-- resources/views/inventario/compras/create.blade.php --}}

@extends('adminlte::page') {{-- o el layout que uses --}}

@section('title', 'Registrar compra')

@section('content_header')
<h1>Registrar nueva compra</h1>
@stop

@section('content')
{{-- MENSAJES DE ERROR / ÉXITO --}}
@if ($errors->any())
<div class="alert alert-danger">
    <strong>Hay errores en el formulario:</strong>
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Si quieres mostrar sucursal actual --}}
@isset($sucursalSeleccionada)
<div class="alert alert-info">
    <strong>Sucursal:</strong> {{ $sucursalSeleccionada->nombre ?? 'N/D' }}
</div>
@endisset

<form action="{{ route('compras.store') }}" method="POST" enctype="multipart/form-data" id="form-compra">
    @csrf

    {{-- ================= CABECERA DE COMPRA ================= --}}
    <div class="card mb-3">
        <div class="card-header">
            <strong>Datos de la compra</strong>
        </div>
        <div class="card-body">
            <div class="row">

                {{-- PROVEEDOR --}}
                <div class="col-md-6 mb-3">
                    <label for="proveedor_id" class="form-label">Proveedor</label>
                    <select name="proveedor_id" id="proveedor_id" class="form-control">
                        <option value="">-- Seleccione proveedor --</option>
                        @foreach ($proveedores ?? [] as $proveedor)
                        <option value="{{ $proveedor->id }}"
                            {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                            {{ $proveedor->razon_social }} ({{ $proveedor->ruc }})
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- FECHA RECEPCIÓN --}}
                <div class="col-md-3 mb-3">
                    <label for="fecha_recepcion" class="form-label">Fecha de recepción</label>
                    <input type="date" name="fecha_recepcion" id="fecha_recepcion"
                        class="form-control" value="{{ old('fecha_recepcion', date('Y-m-d')) }}">
                </div>

                {{-- TIPO COMPROBANTE --}}
                <div class="col-md-3 mb-3">
                    <label for="tipo_comprobante" class="form-label">Tipo de comprobante</label>
                    <select name="tipo_comprobante" id="tipo_comprobante" class="form-control">
                        <option value="">-- Seleccione --</option>
                        <option value="FACTURA" {{ old('tipo_comprobante') == 'FACTURA' ? 'selected' : '' }}>Factura</option>
                        <option value="BOLETA" {{ old('tipo_comprobante') == 'BOLETA' ? 'selected' : '' }}>Boleta</option>
                        <option value="NOTA CREDITO" {{ old('tipo_comprobante') == 'NOTA CREDITO' ? 'selected' : '' }}>Nota de crédito</option>
                    </select>
                </div>
            </div>

            <div class="row">
                {{-- NÚMERO FACTURA / BOLETA PROVEEDOR --}}
                <div class="col-md-4 mb-3">
                    <label for="numero_factura_proveedor" class="form-label">N° comprobante proveedor</label>
                    <input type="text" name="numero_factura_proveedor" id="numero_factura_proveedor"
                        class="form-control" value="{{ old('numero_factura_proveedor') }}"
                        placeholder="Ej: F001-000123">
                </div>

                {{-- ARCHIVO COMPROBANTE --}}
                <div class="col-md-4 mb-3">
                    <label for="archivo_comprobante" class="form-label">Archivo comprobante (PDF / imagen)</label>
                    <input type="file" name="archivo_comprobante" id="archivo_comprobante"
                        class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                </div>

                {{-- OBSERVACIONES --}}
                <div class="col-md-4 mb-3">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="2" class="form-control"
                        placeholder="Notas adicionales...">{{ old('observaciones') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= DETALLE DE COMPRA / LOTES ================= --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Detalle de la compra (lotes)</strong>
            <button type="button" class="btn btn-sm btn-primary" onclick="agregarFilaItem()">
                + Agregar ítem
            </button>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Medicamento</th>
                            <th>Código lote</th>
                            <th>F. vencimiento</th>
                            <th class="text-end">Cant. recibida</th>
                            <th class="text-end">P. compra unit.</th>
                            <th class="text-end">P. oferta</th>
                            <th>Ubicación</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="items-table-body">
                        {{-- Fila inicial (ítem 0) --}}
                        <tr data-index="0" class="fila-item">
                            <td class="align-middle indice-fila">1</td>

                            {{-- MEDICAMENTO --}}
                            <td>
                                <select name="items[0][medicamento_id]"
                                    class="form-control select-medicamento">
                                    <option value="">-- Medicamento --</option>
                                    @foreach ($medicamentos ?? [] as $med)
                                    <option value="{{ $med->id }}">
                                        {{ $med->nombre }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- CÓDIGO LOTE --}}
                            <td>
                                <input type="text" name="items[0][codigo_lote]"
                                    class="form-control" placeholder="Lote...">
                            </td>

                            {{-- FECHA VENCIMIENTO --}}
                            <td>
                                <input type="date" name="items[0][fecha_vencimiento]"
                                    class="form-control">
                            </td>

                            {{-- CANTIDAD RECIBIDA --}}
                            <td>
                                <input type="number" name="items[0][cantidad_recibida]"
                                    class="form-control text-end input-cantidad"
                                    min="1" value="1"
                                    oninput="recalcularSubtotal(this)">
                            </td>

                            {{-- PRECIO COMPRA UNITARIO --}}
                            <td>
                                <input type="number" step="0.0001"
                                    name="items[0][precio_compra_unitario]"
                                    class="form-control text-end input-precio"
                                    min="0" value="0"
                                    oninput="recalcularSubtotal(this)">
                            </td>

                            {{-- PRECIO OFERTA --}}
                            <td>
                                <input type="number" step="0.01"
                                    name="items[0][precio_oferta]"
                                    class="form-control text-end"
                                    min="0" value="">
                            </td>

                            {{-- UBICACIÓN --}}
                            <td>
                                <input type="text" name="items[0][ubicacion]"
                                    class="form-control" placeholder="Estante / fila">
                            </td>

                            {{-- SUBTOTAL (solo visual) --}}
                            <td class="text-end align-middle subtotal-fila">
                                S/ 0.00
                            </td>

                            {{-- ELIMINAR --}}
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-sm btn-danger"
                                    onclick="eliminarFilaItem(this)">
                                    &times;
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="8" class="text-end">Total</th>
                            <th class="text-end" id="total-general">S/ 0.00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card-footer text-end">
            <button type="submit" class="btn btn-success">
                Guardar compra
            </button>
            <a href="{{ route('compras.index') }}" class="btn btn-secondary">
                Cancelar
            </a>
        </div>
    </div>
</form>
@stop

@section('js')
<script>
    let itemIndex = 0;

    function agregarFilaItem() {
        itemIndex++;

        const tbody = document.getElementById('items-table-body');

        const tr = document.createElement('tr');
        tr.classList.add('fila-item');
        tr.setAttribute('data-index', itemIndex);

        tr.innerHTML = `
            <td class="align-middle indice-fila"></td>

            <td>
                <select name="items[${itemIndex}][medicamento_id]"
                        class="form-control select-medicamento">
                    <option value="">-- Medicamento --</option>
                    @foreach ($medicamentos ?? [] as $med)
                        <option value="{{ $med->id }}">
                            {{ $med->nombre }}
                        </option>
                    @endforeach
                </select>
            </td>

            <td>
                <input type="text" name="items[${itemIndex}][codigo_lote]"
                    class="form-control" placeholder="Lote...">
            </td>

            <td>
                <input type="date" name="items[${itemIndex}][fecha_vencimiento]"
                    class="form-control">
            </td>

            <td>
                <input type="number" name="items[${itemIndex}][cantidad_recibida]"
                    class="form-control text-end input-cantidad"
                    min="1" value="1"
                    oninput="recalcularSubtotal(this)">
            </td>

            <td>
                <input type="number" step="0.0001"
                    name="items[${itemIndex}][precio_compra_unitario]"
                    class="form-control text-end input-precio"
                    min="0" value="0"
                    oninput="recalcularSubtotal(this)">
            </td>

            <td>
                <input type="number" step="0.01"
                    name="items[${itemIndex}][precio_oferta]"
                    class="form-control text-end"
                    min="0" value="">
            </td>

            <td>
                <input type="text" name="items[${itemIndex}][ubicacion]"
                    class="form-control" placeholder="Estante / fila">
            </td>

            <td class="text-end align-middle subtotal-fila">
                S/ 0.00
            </td>

            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-danger"
                    onclick="eliminarFilaItem(this)">
                    &times;
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        reenumerarFilas();
        recalcularTotalGeneral();
    }

    function eliminarFilaItem(button) {
        const tr = button.closest('tr');
        const tbody = document.getElementById('items-table-body');

        if (tbody.querySelectorAll('tr').length === 1) {
            // Si solo queda una fila, en vez de borrarla, la limpiamos
            tr.querySelectorAll('input, select').forEach(el => el.value = '');
            tr.querySelector('.subtotal-fila').textContent = 'S/ 0.00';
        } else {
            tr.remove();
        }

        reenumerarFilas();
        recalcularTotalGeneral();
    }

    function reenumerarFilas() {
        const filas = document.querySelectorAll('#items-table-body .fila-item');
        filas.forEach((fila, index) => {
            const indiceCell = fila.querySelector('.indice-fila');
            if (indiceCell) {
                indiceCell.textContent = index + 1;
            }
        });
    }

    function recalcularSubtotal(input) {
        const fila = input.closest('tr');
        const cantidad = parseFloat(fila.querySelector('.input-cantidad').value) || 0;
        const precio = parseFloat(fila.querySelector('.input-precio').value) || 0;
        const subtotal = cantidad * precio;

        const subtotalCell = fila.querySelector('.subtotal-fila');
        subtotalCell.textContent = 'S/ ' + subtotal.toFixed(2);

        recalcularTotalGeneral();
    }

    function recalcularTotalGeneral() {
        let total = 0;
        const filas = document.querySelectorAll('#items-table-body .fila-item');

        filas.forEach(fila => {
            const subtotalCell = fila.querySelector('.subtotal-fila');
            if (!subtotalCell) return;

            const texto = subtotalCell.textContent.replace('S/', '').trim();
            const valor = parseFloat(texto) || 0;
            total += valor;
        });

        document.getElementById('total-general').textContent = 'S/ ' + total.toFixed(2);
    }
</script>
@stop