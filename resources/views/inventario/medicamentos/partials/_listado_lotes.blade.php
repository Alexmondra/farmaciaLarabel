<div class="mb-4">
    <a href="{{ route('inventario.medicamentos.index') }}" class="btn btn-default shadow-sm border">
        <i class="fas fa-arrow-left mr-2 text-primary"></i>
        <span class="font-weight-bold">Regresar al Listado de Medicamentos</span>
    </a>
</div>

@foreach($sucursalesDetalle as $item)
<div class="card card-outline card-info shadow-sm bg-dark mb-4">
    {{-- Ubicación sugerida: En el card-header de cada sucursal dentro de _listado_lotes.blade.php --}}
    <div class="card-header border-0 d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold text-info">
            <i class="fas fa-store mr-2"></i> {{ $item['sucursal']->nombre }}
        </h3>

        <div class="btn-group">
            {{-- BOTÓN DE HISTORIAL (NUEVO) --}}
            <a href="{{ route('inventario.medicamento_sucursal.historial', ['medicamento' => $medicamento->id, 'sucursal' => $item['sucursal']->id]) }}"
                class="btn btn-outline-light btn-sm shadow-sm mr-2">
                <i class="fas fa-history mr-1"></i> Historial
            </a>

            {{-- Botón de Precio Maestro (Existente) --}}
            <button type="button" class="btn btn-info btn-sm shadow-sm">
                <i class="fas fa-tags mr-1"></i> Precio Maestro: S/ {{ number_format($item['precio'] ?? 0, 2) }}
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-secondary small text-uppercase">
                    <tr>
                        <th class="pl-3">LOTE</th>
                        <th>VENCIMIENTO</th>
                        <th>OFERTA (UNID)</th>
                        <th class="text-center">STOCK</th>
                        <th>UBICACIÓN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($item['lotes']->filter(fn($l) => $l->stock_actual > 0) as $lote)
                    <tr>
                        <td class="align-middle pl-3 font-weight-bold">{{ $lote->codigo_lote }}</td>
                        <td class="align-middle">
                            <span class="{{ $lote->fecha_vencimiento <= now()->addMonths(3) ? 'text-danger font-weight-bold' : '' }}">
                                {{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : '—' }}
                            </span>
                            <a href="javascript:void(0)" class="text-primary js-edit-vencimiento ml-1"
                                data-url="{{ route('inventario.lotes.update_vencimiento', $lote->id) }}"
                                data-current="{{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('Y-m-d') : '' }}">
                                <i class="fas fa-calendar-alt fa-xs"></i>
                            </a>
                        </td>
                        <td class="align-middle text-warning">
                            <b>S/ {{ number_format($lote->precio_oferta ?? 0, 2) }}</b>
                            <a href="javascript:void(0)" class="text-warning js-edit-oferta ml-1"
                                data-url="{{ route('inventario.lotes.update_vencimiento', $lote->id) }}"
                                data-oferta="{{ $lote->precio_oferta }}">
                                <i class="fas fa-tag fa-xs"></i>
                            </a>
                        </td>
                        <td class="align-middle text-center">
                            <span class="badge badge-pill badge-primary">{{ $lote->stock_actual }}</span>
                        </td>
                        <td class="align-middle small">
                            {{ $lote->ubicacion ?? '—' }}
                            <a href="javascript:void(0)" class="text-primary js-edit-ubicacion ml-1"
                                data-url="{{ route('inventario.lotes.update_ubicacion', $lote->id) }}"
                                data-current="{{ $lote->ubicacion }}">
                                <i class="fas fa-map-marker-alt fa-xs"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach