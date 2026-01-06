@extends('adminlte::page')

@section('title', $medicamento->nombre)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="text-dark font-weight-bold">Ficha de Medicamento</h1>
    <a href="{{ route('inventario.medicamentos.index') }}" class="btn btn-outline-secondary shadow-sm">
        <i class="fas fa-arrow-left mr-2"></i> Volver al listado
    </a>
</div>
@endsection

@section('content')

<div class="row">
    {{-- COLUMNA IZQUIERDA: FOTO Y DATOS MAESTROS --}}
    <div class="col-md-4">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-body box-profile">
                <div class="text-center mb-4">
                    @if($medicamento->imagen_url)
                    <img class="img-fluid rounded shadow-sm" style="max-height: 200px; object-fit: contain;"
                        src="{{ $medicamento->imagen_url }}" alt="{{ $medicamento->nombre }}">
                    @else
                    <div class="bg-light d-flex align-items-center justify-content-center rounded mx-auto" style="height: 180px; width: 100%; max-width: 200px;">
                        <i class="fas fa-pills fa-4x text-muted"></i>
                    </div>
                    @endif
                </div>

                <h3 class="profile-username text-center font-weight-bold">{{ $medicamento->nombre }}</h3>
                <p class="text-muted text-center">{{ $medicamento->laboratorio ?? 'Laboratorio no definido' }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Código Interno</b> <a class="float-right text-dark">{{ $medicamento->codigo }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Código de Barra</b> <a class="float-right text-dark">{{ $medicamento->codigo_barra ?? '—' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Categoría</b>
                        <a class="float-right">
                            <span class="badge badge-info">{{ $medicamento->categoria->nombre ?? 'General' }}</span>
                        </a>
                    </li>
                    <li class="list-group-item">
                        <b>Presentación</b> <a class="float-right text-dark">{{ $medicamento->presentacion ?? '—' }}</a>
                    </li>
                    <li class="list-group-item d-none d-sm-block"> {{-- Ocultar en móvil --}}
                        <b>Concentración</b> <a class="float-right text-dark">{{ $medicamento->concentracion ?? '—' }}</a>
                    </li>
                    <li class="list-group-item d-none d-sm-block"> {{-- Ocultar en móvil --}}
                        <b>Unidades x Envase</b> <a class="float-right text-dark">{{ $medicamento->unidades_por_envase }}</a>
                    </li>
                </ul>

                @if($medicamento->descripcion)
                <div class="mt-4 border-top pt-3">
                    <strong><i class="fas fa-book mr-1"></i> Descripción</strong>
                    <p class="text-muted small mt-2">
                        {{ $medicamento->descripcion }}
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- COLUMNA DERECHA: STOCK Y PRECIOS POR SUCURSAL --}}
    <div class="col-md-8">
        @forelse($sucursalesDetalle as $item)
        @php
        /** @var \App\Models\Sucursal $sucursal */
        $sucursal = $item['sucursal'];

        // --- LÓGICA DE FILTRADO PARA LA VISTA ---
        // Solo mostramos lotes con stock > 0 Y que no estén vencidos (o sin fecha)
        $lotesActivos = $item['lotes']->filter(function($lote) {
        $vence = $lote->fecha_vencimiento ? \Carbon\Carbon::parse($lote->fecha_vencimiento) : null;
        // Es vigente si no tiene fecha O la fecha es futura (incluyendo hoy)
        $esVigente = $vence ? $vence->endOfDay()->isFuture() : true;
        return $lote->stock_actual > 0 && $esVigente;
        })
        // Ordenamos: Los que vencen antes, van primero (FIFO/FEFO)
        ->sortBy(function($lote) {
        return $lote->fecha_vencimiento ?? '9999-12-31';
        });

        // Detectar si hay ofertas en los lotes activos para colorear la tarjeta
        $tieneOferta = $lotesActivos->where('precio_oferta', '>', 0)->count() > 0;
        @endphp

        <div class="card shadow-sm border-0 mb-4 {{ $tieneOferta ? 'card-outline card-success' : '' }}">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="font-weight-bold mb-0 text-primary">
                            <i class="fas fa-store-alt mr-2"></i> {{ $sucursal->nombre }}
                        </h5>
                        <small class="text-muted">{{ $sucursal->direccion ?? 'Sin dirección registrada' }}</small>
                    </div>
                    <div class="text-right">
                        <h4 class="mb-0 font-weight-bold text-success">
                            S/ {{ number_format($item['precio'] ?? 0, 2) }}
                        </h4>
                        <small class="text-muted">Precio Base</small>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">

                @if($lotesActivos->isEmpty())
                {{-- ESTADO VACÍO (Sin lotes activos) --}}
                <div class="text-center p-4">
                    <i class="fas fa-box-open text-muted fa-3x mb-3 opacity-50"></i>
                    <p class="text-muted mb-3">No hay lotes activos disponibles para venta.</p>

                    {{-- Botón para ver historial --}}
                    @can('lotes.ver')
                    <a href="{{ route('inventario.medicamento_sucursal.historial', [
                                        'medicamento' => $medicamento->id, 
                                        'sucursal' => $sucursal->id
                                ]) }}"
                        class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-history mr-1"></i> Ver lotes agotados o vencidos
                    </a>
                    @endcan
                </div>
                @else
                {{-- TABLA DE LOTES ACTIVOS --}}
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th>Lote</th>
                                <th class="d-none d-sm-table-cell">Vencimiento</th>
                                <th>Stock</th>
                                <th class="d-none d-md-table-cell">Ubicación</th>
                                <th>Precios Oferta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lotesActivos as $lote)
                            @php
                            $vence = $lote->fecha_vencimiento ? \Carbon\Carbon::parse($lote->fecha_vencimiento) : null;
                            // Alerta amarilla si vence en menos de 3 meses
                            $porVencer = $vence && $vence->diffInMonths(now()) < 3;
                                @endphp
                                <tr>
                                <td class="align-middle font-weight-bold">{{ $lote->codigo_lote }}</td>

                                <td class="align-middle d-none d-sm-table-cell" id="vencimiento-cell-{{ $lote->id }}">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            @if($vence)
                                            <span class="badge {{ $porVencer ? 'badge-warning' : 'badge-light border' }}">
                                                {{ $vence->format('d/m/Y') }}
                                            </span>
                                            @if($porVencer)
                                            <br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Próximo</small>
                                            @endif
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </div>

                                        <a href="javascript:void(0)"
                                            class="ml-2 text-secondary js-edit-vencimiento"
                                            title="Editar vencimiento"
                                            data-id="{{ $lote->id }}"
                                            data-current="{{ $lote->fecha_vencimiento ? \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('Y-m-d') : '' }}"
                                            data-url="{{ route('inventario.lotes.update_vencimiento', $lote->id) }}">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                    </div>
                                </td>

                                <td class="align-middle">
                                    <span class="badge badge-pill badge-primary px-3" style="font-size: 0.9rem;">
                                        {{ $lote->stock_actual }}
                                    </span>
                                </td>

                                <td class="align-middle d-none d-md-table-cell" id="ubicacion-cell-{{ $lote->id }}">
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted small">{{ $lote->ubicacion ?? '—' }}</span>
                                        <a href="javascript:void(0)"
                                            class="ml-2 text-secondary js-edit-ubicacion"
                                            title="Editar ubicación"
                                            data-id="{{ $lote->id }}"
                                            data-current="{{ $lote->ubicacion ?? '' }}"
                                            data-url="{{ route('inventario.lotes.update_ubicacion', $lote->id) }}">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                    </div>
                                </td>

                                <td class="align-middle">
                                    @if($lote->precio_oferta > 0)
                                    <div class="d-flex flex-column">
                                        <small class="text-muted" style="text-decoration: line-through;">
                                            S/ {{ number_format($lote->precio_compra ?? $item['precio'], 2) }}
                                        </small>
                                        <span class="text-danger font-weight-bold animate__animated animate__pulse infinite">
                                            <i class="fas fa-tag"></i> S/ {{ number_format($lote->precio_oferta, 2) }}
                                        </span>
                                    </div>
                                    @else
                                    {{-- Espacio vacío o precio normal si se desea mostrar algo --}}
                                    @endif
                                </td>
                                </tr>
                                @endforeach
                        </tbody>
                    </table>

                    {{-- Footer pequeño de la tabla para ir al historial --}}
                    @can('lotes.ver')
                    <div class="bg-light p-2 text-center border-top">
                        <a href="{{ route('inventario.medicamento_sucursal.historial', [
                                        'medicamento' => $medicamento->id, 
                                        'sucursal' => $sucursal->id
                                ]) }}"
                            class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-history mr-1"></i> Ver lotes agotados o vencidos
                        </a>
                    </div>
                    @endcan
                </div>
                @endif
            </div>
            @can('medicamentos.eliminar')
            <div class="card-footer bg-light d-flex justify-content-end p-2">
                {{-- Formulario para desvincular (Eliminar de sucursal) --}}
                <form method="POST"
                    action="{{ route('inventario.medicamento_sucursal.destroy', ['medicamento' => $medicamento->id, 'sucursal' => $sucursal->id]) }}"
                    onsubmit="return confirm('¿Seguro que deseas retirar este producto de la sucursal {{ $sucursal->nombre }}? Esto ocultará el stock restante.');">
                    @csrf @method('DELETE')
                    <button class="btn btn-xs btn-outline-danger border-0">
                        <i class="fas fa-unlink mr-1"></i> Desvincular de sucursal
                    </button>
                </form>
            </div>
            @endcan
        </div>
        @empty
        <div class="alert alert-info shadow-sm">
            <h5><i class="icon fas fa-info-circle"></i> Sin Asignación</h5>
            Este medicamento no está asociado a ninguna de las sucursales que tienes permiso para ver.
            <br>
            <small>Para venderlo, primero debes asociarlo a una sucursal y cargar stock.</small>
        </div>
        @endforelse
    </div>
</div>

{{-- ✅ Modales para editar lote --}}
<div class="modal fade" id="modalEditarVencimiento" tabindex="-1" role="dialog" aria-labelledby="modalEditarVencimientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 520px;">
        <div class="modal-content" style="border-radius: 14px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="modalEditarVencimientoLabel" style="font-weight: 700;">Editar vencimiento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body pt-2">
                <input type="hidden" id="vencimiento_url" value="">
                <div class="form-group mb-0">
                    <label class="small text-muted">Fecha de vencimiento</label>
                    <input type="date" class="form-control" id="fecha_vencimiento_input">
                    <small class="text-muted d-block mt-2">
                        Deja vacío para guardar sin fecha.
                    </small>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarVencimiento">
                    <i class="fas fa-save mr-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarUbicacion" tabindex="-1" role="dialog" aria-labelledby="modalEditarUbicacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 520px;">
        <div class="modal-content" style="border-radius: 14px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="modalEditarUbicacionLabel" style="font-weight: 700;">Editar ubicación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body pt-2">
                <input type="hidden" id="ubicacion_url" value="">
                <div class="form-group mb-0">
                    <label class="small text-muted">Ubicación</label>
                    <input type="text" class="form-control" id="ubicacion_input" maxlength="50" placeholder="Ej: Estante A - Nivel 2">
                    <small class="text-muted d-block mt-2">
                        Máx. 50 caracteres. Deja vacío para borrar ubicación.
                    </small>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarUbicacion">
                    <i class="fas fa-save mr-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        const CSRF = '{{ csrf_token() }}';

        function pad2(n) {
            return (n < 10 ? '0' : '') + n;
        }

        function formatDMY(ymd) {
            if (!ymd) return '';
            const parts = ymd.split('-');
            if (parts.length !== 3) return ymd;
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }

        function porVencer(ymd) {
            if (!ymd) return false;
            const today = new Date();
            const d = new Date(ymd + 'T00:00:00');
            const diffMs = d.getTime() - today.getTime();
            const diffDays = diffMs / (1000 * 60 * 60 * 24);
            return diffDays >= 0 && diffDays < 90; // ~3 meses
        }

        // ====== Abrir modal vencimiento
        $(document).on('click', '.js-edit-vencimiento', function() {
            const url = $(this).data('url');
            const current = $(this).data('current') || '';
            $('#vencimiento_url').val(url);
            $('#fecha_vencimiento_input').val(current);
            $('#modalEditarVencimiento').modal({
                backdrop: true,
                keyboard: true,
                show: true
            });
        });

        // ====== Guardar vencimiento (AJAX)
        $('#btnGuardarVencimiento').on('click', function() {
            const url = $('#vencimiento_url').val();
            const fecha = ($('#fecha_vencimiento_input').val() || '').trim();
            const idMatch = (url || '').match(/\/lotes\/(\d+)\//);
            const loteId = idMatch ? idMatch[1] : null;

            $(this).prop('disabled', true);

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: CSRF,
                    _method: 'PUT',
                    fecha_vencimiento: fecha
                }
            }).done(function() {
                if (loteId) {
                    const cell = $('#vencimiento-cell-' + loteId);
                    const editBtn = cell.find('a.js-edit-vencimiento');

                    // Actualiza dataset
                    editBtn.attr('data-current', fecha);
                    editBtn.data('current', fecha);

                    // Re-render (sin recargar)
                    let html = '';
                    if (fecha) {
                        const warn = porVencer(fecha);
                        const badgeClass = warn ? 'badge-warning' : 'badge-light border';
                        html += `<span class="badge ${badgeClass}">${formatDMY(fecha)}</span>`;
                        if (warn) {
                            html += `<br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Próximo</small>`;
                        }
                    } else {
                        html += `<span class="text-muted">—</span>`;
                    }

                    // Reemplaza solo la parte izquierda (sin tocar el lápiz)
                    cell.find('div > div').html(html);
                }

                $('#modalEditarVencimiento').modal('hide');
            }).fail(function(xhr) {
                alert('No se pudo actualizar el vencimiento. Revisa la consola/log.');
                console.error(xhr.responseText || xhr);
            }).always(function() {
                $('#btnGuardarVencimiento').prop('disabled', false);
            });
        });

        // ====== Abrir modal ubicación
        $(document).on('click', '.js-edit-ubicacion', function() {
            const url = $(this).data('url');
            const current = $(this).data('current') || '';
            $('#ubicacion_url').val(url);
            $('#ubicacion_input').val(current);
            $('#modalEditarUbicacion').modal({
                backdrop: true,
                keyboard: true,
                show: true
            });
        });

        // ====== Guardar ubicación (AJAX)
        $('#btnGuardarUbicacion').on('click', function() {
            const url = $('#ubicacion_url').val();
            const ubic = ($('#ubicacion_input').val() || '').trim();
            const idMatch = (url || '').match(/\/lotes\/(\d+)\//);
            const loteId = idMatch ? idMatch[1] : null;

            $(this).prop('disabled', true);

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: CSRF,
                    _method: 'PUT',
                    ubicacion: ubic
                }
            }).done(function() {
                if (loteId) {
                    const cell = $('#ubicacion-cell-' + loteId);
                    const editBtn = cell.find('a.js-edit-ubicacion');

                    editBtn.attr('data-current', ubic);
                    editBtn.data('current', ubic);

                    const text = ubic ? ubic : '—';
                    cell.find('span').first().text(text);
                }

                $('#modalEditarUbicacion').modal('hide');
            }).fail(function(xhr) {
                alert('No se pudo actualizar la ubicación. Revisa la consola/log.');
                console.error(xhr.responseText || xhr);
            }).always(function() {
                $('#btnGuardarUbicacion').prop('disabled', false);
            });
        });

    });
</script>
@endsection

@section('css')
@include('inventario.medicamentos.css')

<style>
    /* Estilos específicos de show.blade.php */
    .opacity-50 {
        opacity: 0.5;
    }
</style>
@endsection