{{-- 2. TABLA DE RESULTADOS --}}
<div class="card shadow-lg border-0 rounded-lg overflow-hidden glass-panel">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-navy text-white">
                    <tr>
                        <th class="py-3 pl-3">Tipo</th>
                        <th class="py-3">Emisión</th>
                        <th class="py-3">Documento</th>
                        {{-- Ocultar en móvil --}}
                        <th class="py-3 d-none d-lg-table-cell">Ruta de Traslado</th>
                        <th class="py-3 d-none d-md-table-cell">Estado</th>
                        <th class="py-3 pr-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white-mode">
                    @forelse($guias as $guia)

                    @php
                    // Determinamos si es SALIDA (Mía) o ENTRADA (Llegada)
                    $esSalida = $guia->sucursal_id == ($sucursalOrigen->id ?? 0);
                    @endphp

                    <tr class="border-bottom-custom transition-row {{ $esSalida ? '' : 'bg-soft-yellow-mode' }}">

                        {{-- TIPO (Salida/Llegada) --}}
                        <td class="pl-3 align-middle">
                            @if($esSalida)
                            <span class="badge badge-light text-success shadow-sm p-2" title="Salida / Enviado por nosotros">
                                <i class="fas fa-arrow-up"></i> <span class="d-none d-sm-inline ml-1">SALIDA</span>
                            </span>
                            @else
                            <span class="badge badge-warning text-white shadow-sm p-2" title="Entrada / Recibido">
                                <i class="fas fa-arrow-down"></i> <span class="d-none d-sm-inline ml-1">LLEGADA</span>
                            </span>
                            @endif
                        </td>

                        {{-- FECHA --}}
                        <td class="align-middle">
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold text-dark-mode-light">
                                    {{ $guia->fecha_emision->format('d/m') }}
                                </span>
                                <small class="text-muted-mode d-block d-md-none">{{ $guia->fecha_emision->format('H:i') }}</small>
                                <small class="text-muted-mode d-none d-md-block">{{ $guia->fecha_emision->format('H:i') }}</small>
                            </div>
                        </td>

                        {{-- DOCUMENTO --}}
                        <td class="align-middle">
                            <div class="d-flex align-items-center">
                                {{-- Icono solo en Desktop --}}
                                <div class="icon-doc mr-2 d-none d-lg-flex bg-light rounded p-2 {{ $esSalida ? 'text-teal' : 'text-warning' }}">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                                <div>
                                    <span class="badge badge-light border text-sm font-weight-bold shadow-sm d-block">
                                        {{ $guia->serie }}-{{ str_pad($guia->numero, 6, '0', STR_PAD_LEFT) }}
                                    </span>
                                    @if($guia->venta_id)
                                    <small class="text-primary font-weight-bold d-block mt-1">
                                        <i class="fas fa-link mr-1"></i>Venta #{{ $guia->venta_id }}
                                    </small>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- RUTA (Solo Desktop Grande) --}}
                        <td class="d-none d-lg-table-cell align-middle">
                            <div class="route-line position-relative pl-3 border-left {{ $esSalida ? 'border-teal' : 'border-warning' }}">
                                <div class="mb-1 text-truncate" style="max-width: 200px;">
                                    <i class="fas fa-circle text-success text-xs mr-2"></i>
                                    <small class="text-muted-mode">
                                        {{ $esSalida ? 'Nosotros' : $guia->direccion_partida }}
                                    </small>
                                </div>
                                <div class="text-truncate" style="max-width: 200px;">
                                    <i class="fas fa-map-marker-alt text-danger text-xs mr-2"></i>
                                    <span class="text-dark-mode-light font-weight-bold">
                                        {{ $esSalida ? $guia->direccion_llegada : 'Nuestra Sucursal' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- ESTADO (Ocultar Fecha en Móvil) --}}
                        <td class="d-none d-md-table-cell align-middle">
                            {{-- Usamos 'estado_visual' y 'color_estado' que creamos en el modelo --}}
                            <span class="badge badge-{{ $guia->color_estado }} px-3 py-2 rounded-pill">
                                {{ $guia->estado_visual }}
                            </span>
                            <br class="d-none d-lg-block">
                            <small class="text-muted d-none d-lg-block">
                                <i class="fas fa-calendar-alt"></i> {{ $guia->fecha_traslado->format('d/m/Y') }}
                            </small>
                        </td>

                        {{-- ACCIONES --}}
                        <td class="pr-3 text-center align-middle">
                            <div class="d-flex justify-content-center align-items-center">

                                {{-- 1. BOTÓN VER (PDF o Detalle) --}}
                                <a href="{{ route('guias.ver_pdf', $guia->id)}}" class="btn btn-icon-only btn-info text-white mr-2" target="_blank"
                                    title="Ver Guía / Imprimir PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                {{-- 2. BOTÓN RECEPCIÓN (Llegada) --}}
                                @if(!$esSalida && $guia->estado_traslado !== 'ANULADO' && $guia->estado_traslado !== 'ENTREGADO')
                                <button type="button"
                                    class="btn btn-icon-only btn-light text-success mr-2 shadow-sm"
                                    onclick="confirmarRecepcion({{ $guia->id }}, '{{ $guia->serie }}-{{ $guia->numero }}')"
                                    title="Confirmar Llegada de Mercadería">
                                    <i class="fas fa-check-double"></i>
                                </button>
                                @endif

                                {{-- 3. BOTÓN ANULAR --}}
                                @if($esSalida && !in_array($guia->estado_traslado, ['ANULADO', 'RECEPCIONADO', 'ENTREGADO']))
                                <button type="button"
                                    class="btn btn-icon-only btn-light text-danger shadow-sm"
                                    onclick="confirmarAnulacion({{ $guia->id }}, '{{ $guia->serie }}-{{ $guia->numero }}')"
                                    title="Anular Guía">
                                    <i class="fas fa-ban"></i>
                                </button>
                                @endif

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="empty-state">
                                <div class="icon-box bg-light-mode rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="fas fa-shipping-fast fa-2x text-muted-mode"></i>
                                </div>
                                <h6 class="text-muted-mode">No se encontraron guías</h6>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($guias->hasPages())
        <div class="d-flex justify-content-center justify-content-md-end p-3 bg-light-mode border-top">
            {{ $guias->links() }}
        </div>
        @endif
    </div>
</div>