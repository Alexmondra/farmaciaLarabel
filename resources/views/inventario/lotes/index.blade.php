@extends('adminlte::page')

@section('title', 'Lotes de Medicamentos')

@section('content_header')
@stop

@section('content')

{{-- ESTILOS PERSONALIZADOS PREMIUM --}}
<style>
    :root {
        --pharma-primary: #00b894;
        --pharma-primary-hover: #00a383;
        --pharma-text: #2d3436;
        --pharma-bg-card: #ffffff;
        --pharma-bg-subtle: #f1f2f6;
        --pharma-border: #f1f2f6;
        --pharma-hover: #f0fbf7;
        --pharma-shadow: rgba(0, 0, 0, 0.08);
    }

    .dark-mode {
        --pharma-text: #ecf0f1;
        --pharma-bg-card: #343a40;
        --pharma-bg-subtle: #3f474e;
        --pharma-border: #4b545c;
        --pharma-hover: #3f474e;
        --pharma-shadow: rgba(0, 0, 0, 0.3);
    }

    .card-modern {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 40px var(--pharma-shadow);
        background-color: var(--pharma-bg-card);
        color: var(--pharma-text);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .header-modern {
        background: var(--pharma-bg-card);
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--pharma-border);
    }

    .table-modern thead th {
        border: none;
        background: var(--pharma-bg-subtle);
        color: var(--pharma-text);
        opacity: 0.8;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 1rem 1.5rem;
        vertical-align: middle;
    }

    .table-modern tbody td {
        border-bottom: 1px solid var(--pharma-border);
        padding: 1rem 1.5rem;
        vertical-align: middle;
        color: var(--pharma-text);
    }

    .item-row {
        transition: background-color 0.2s ease-in-out;
    }

    .item-row:hover {
        background-color: var(--pharma-hover);
    }

    .badge-modern {
        font-size: 0.75rem;
        padding: 0.35em 0.8em;
        border-radius: 10px;
        font-weight: 700;
    }

    .form-control-modern {
        border-radius: 12px;
        border: 2px solid var(--pharma-border);
        background-color: var(--pharma-bg-subtle);
        color: var(--pharma-text);
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control-modern:focus {
        background-color: var(--pharma-bg-card);
        border-color: var(--pharma-primary);
        box-shadow: 0 0 0 3px rgba(0, 184, 148, 0.15);
        color: var(--pharma-text);
    }

    .btn-icon-modern {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: var(--pharma-bg-subtle);
        color: var(--pharma-text);
        border: none;
        transition: all 0.2s ease;
    }

    .btn-icon-modern:hover {
        transform: scale(1.1);
        background-color: var(--pharma-primary);
        color: white;
    }

    .btn-search-modern {
        border-radius: 12px;
        background-color: var(--pharma-primary);
        color: white;
        font-weight: 600;
        padding: 0.5rem 1.5rem;
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 184, 148, 0.2);
    }

    .btn-search-modern:hover {
        background-color: var(--pharma-primary-hover);
        transform: translateY(-2px);
        color: white;
    }

    .modal-content-modern {
        border-radius: 20px;
        border: none;
        overflow: hidden;
        box-shadow: 0 15px 50px rgba(0,0,0,0.15);
    }

    .modal-header-modern {
        background-color: var(--pharma-primary);
        color: white;
        border-bottom: none;
        padding: 1.2rem 1.5rem;
    }
</style>

<div class="container-fluid pt-4">
    <div class="card card-modern">
        {{-- CABECERA Y BUSCADORES --}}
        <div class="header-modern">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h3 class="font-weight-bold mb-0 text-dark">
                        <i class="fas fa-barcode mr-2 text-success"></i> Lotes de Medicamentos
                    </h3>
                    <p class="text-muted small mb-0">Listado general de lotes de medicamentos vigentes, próximos a vencer o vencidos.</p>
                </div>
            </div>

            {{-- FORMULARIO DE FILTROS --}}
            <form action="{{ route('inventario.lotes.index') }}" method="GET" class="mt-4">
                <div class="row g-3">
                    {{-- Buscador por Código --}}
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-muted">Código de Lote</label>
                        <input type="text" name="codigo" class="form-control form-control-modern" placeholder="Buscar por código..." value="{{ request('codigo') }}">
                    </div>

                    {{-- Buscador por Medicamento --}}
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-muted">Medicamento</label>
                        <input type="text" name="medicamento" class="form-control form-control-modern" placeholder="Buscar medicamento..." value="{{ request('medicamento') }}">
                    </div>

                    {{-- Selector de Sucursal --}}
                    @if(count($sucursalesPermitidas) > 1)
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-muted">Sucursal</label>
                            <select name="sucursal_id" class="form-control form-control-modern">
                                <option value="">Todas las Sucursales</option>
                                @foreach($sucursalesPermitidas as $suc)
                                    <option value="{{ $suc->id }}" {{ request('sucursal_id') == $suc->id ? 'selected' : '' }}>
                                        {{ $suc->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Selector de Estado --}}
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-muted">Estado del Lote</label>
                        <select name="estado" class="form-control form-control-modern">
                            <option value="todos" {{ $estado === 'todos' ? 'selected' : '' }}>Todos los Lotes</option>
                            <option value="vigentes" {{ $estado === 'vigentes' ? 'selected' : '' }}>🟢 Vigentes (Con Stock)</option>
                            <option value="por_vencer" {{ $estado === 'por_vencer' ? 'selected' : '' }}>🟡 Por Vencer (< 30 días)</option>
                            <option value="vencidos" {{ $estado === 'vencidos' ? 'selected' : '' }}>🔴 Vencidos</option>
                            <option value="sin_stock" {{ $estado === 'sin_stock' ? 'selected' : '' }}>⚪ Agotados (Stock 0)</option>
                        </select>
                    </div>

                    {{-- Botón de Filtrar --}}
                    <div class="col-md-12 text-right mt-3">
                        <a href="{{ route('inventario.lotes.index') }}" class="btn btn-light px-4 mr-2" style="border-radius: 12px; font-weight: 600; padding: 0.5rem 1.5rem;">
                            Limpiar Filtros
                        </a>
                        <button type="submit" class="btn btn-search-modern">
                            <i class="fas fa-filter mr-2"></i> Filtrar Lotes
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- TABLA DE LOTES --}}
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th>Código Lote</th>
                        <th>Medicamento / Presentación</th>
                        <th>Sucursal</th>
                        <th class="text-center">Stock Físico</th>
                        <th>Vencimiento</th>
                        <th>Ubicación</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lotes as $lote)
                        <tr class="item-row">
                            <td class="font-weight-bold">{{ $lote->codigo_lote }}</td>
                            <td>
                                <div class="font-weight-bold text-dark">{{ $lote->medicamento->nombre ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $lote->medicamento->presentacion ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="badge badge-light border px-2 py-1" style="border-radius: 8px;">
                                    <i class="fas fa-store text-muted mr-1"></i>{{ $lote->sucursal->nombre ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="font-weight-bold {{ $lote->stock_actual > 0 ? 'text-primary' : 'text-muted' }}" style="font-size: 1.05rem;">
                                    {{ $lote->stock_actual }}
                                </span>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : 'SIN FECHA' }}</div>
                                @if($lote->fecha_vencimiento)
                                    <small class="text-muted">
                                        {{ $lote->estaVencido() ? 'Venció hace ' . abs($lote->fecha_vencimiento->diffInDays(now())) . ' días' : 'Vence en ' . $lote->fecha_vencimiento->diffInDays(now()) . ' días' }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted"><i class="fas fa-map-marker-alt text-danger mr-1"></i>{{ $lote->ubicacion ?? 'General' }}</span>
                            </td>
                            <td class="text-center">
                                @php
                                    $est = $lote->estado_vencimiento;
                                @endphp
                                @if($lote->stock_actual <= 0)
                                    <span class="badge badge-secondary badge-modern">⚪ Agotado</span>
                                @elseif($est === 'Vencido')
                                    <span class="badge badge-danger badge-modern">🔴 Vencido</span>
                                @elseif($est === 'Por vencer')
                                    <span class="badge badge-warning badge-modern text-dark">🟡 Por Vencer</span>
                                @else
                                    <span class="badge badge-success badge-modern">🟢 Vigente</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <button type="button" class="btn-icon-modern" onclick="openEditarModal({{ json_encode($lote) }})" title="Editar Lote">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open mb-3 d-block" style="font-size: 3rem; opacity: 0.3;"></i>
                                No se encontraron lotes que coincidan con los filtros de búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        @if($lotes->hasPages())
            <div class="card-footer bg-white border-0 py-3 d-flex flex-column flex-md-row justify-content-between align-items-center">
                <span class="text-muted small font-weight-bold mb-2 mb-md-0">
                    Mostrando del {{ $lotes->firstItem() }} al {{ $lotes->lastItem() }} de {{ $lotes->total() }} lotes
                </span>
                <div>
                    {{ $lotes->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- MODAL EDITAR LOTE --}}
<div class="modal fade" id="modalEditarLote" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-modern">
            <div class="modal-header modal-header-modern">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i> Editar Lote</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditarLote" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <span class="small font-weight-bold text-muted d-block">Medicamento</span>
                        <strong id="edit-medicamento-nombre" class="text-dark" style="font-size: 1.1rem;"></strong>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <span class="small font-weight-bold text-muted d-block">Código Lote</span>
                            <span id="edit-lote-codigo" class="font-weight-bold"></span>
                        </div>
                        <div class="col-6 mb-3">
                            <span class="small font-weight-bold text-muted d-block">Stock Actual</span>
                            <span id="edit-lote-stock" class="font-weight-bold"></span>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Campo Ubicación --}}
                    <div class="form-group mb-3">
                        <label for="inputUbicacion" class="small font-weight-bold text-muted">Ubicación física en Sucursal</label>
                        <input type="text" name="ubicacion" id="inputUbicacion" class="form-control form-control-modern" placeholder="Ej. Estante A-3, Cajón 2">
                    </div>

                    {{-- Campo Vencimiento --}}
                    <div class="form-group mb-3">
                        <label for="inputVencimiento" class="small font-weight-bold text-muted">Fecha de Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" id="inputVencimiento" class="form-control form-control-modern">
                    </div>

                    {{-- Campo Precio Oferta --}}
                    <div class="form-group mb-0">
                        <label for="inputPrecioOferta" class="small font-weight-bold text-muted">Precio de Oferta Especial (Opcional)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text border-0" style="border-radius: 12px 0 0 12px; background-color: var(--pharma-bg-subtle); font-weight: bold; color: var(--pharma-text);">S/</span>
                            </div>
                            <input type="number" name="precio_oferta" id="inputPrecioOferta" step="0.01" class="form-control form-control-modern" placeholder="0.00" style="border-radius: 0 12px 12px 0;">
                        </div>
                        <small class="text-muted mt-1 d-block">Establece un precio de oferta temporal para este lote en particular.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 12px; font-weight: 600;">Cancelar</button>
                    <button type="submit" class="btn btn-search-modern px-4">
                        <i class="fas fa-save mr-2"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let currentLoteId = null;

    function openEditarModal(lote) {
        currentLoteId = lote.id;
        
        $('#edit-medicamento-nombre').text(lote.medicamento ? lote.medicamento.nombre : 'N/A');
        $('#edit-lote-codigo').text(lote.codigo_lote);
        $('#edit-lote-stock').text(lote.stock_actual);
        
        $('#inputUbicacion').val(lote.ubicacion);
        
        if (lote.fecha_vencimiento) {
            let fechaStr = lote.fecha_vencimiento.substring(0, 10);
            $('#inputVencimiento').val(fechaStr);
        } else {
            $('#inputVencimiento').val('');
        }

        $('#inputPrecioOferta').val(lote.precio_oferta || '');

        $('#modalEditarLote').modal('show');
    }

    $('#formEditarLote').on('submit', function(e) {
        e.preventDefault();
        
        if (!currentLoteId) return;

        let ubicacion = $('#inputUbicacion').val();
        let fecha = $('#inputVencimiento').val();
        let precioOferta = $('#inputPrecioOferta').val();

        // 1) Guardar Ubicación
        let pUbicacion = $.ajax({
            url: `{{ url('inventario/lotes') }}/${currentLoteId}/ubicacion`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'PUT',
                ubicacion: ubicacion
            }
        });

        // 2) Guardar Vencimiento y Oferta
        let pVencimiento = $.ajax({
            url: `{{ url('inventario/lotes') }}/${currentLoteId}/vencimiento`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'PUT',
                fecha_vencimiento: fecha,
                precio_oferta: precioOferta
            }
        });

        // Esperar a que ambas peticiones se completen
        Promise.all([pUbicacion, pVencimiento])
            .then(responses => {
                $('#modalEditarLote').modal('hide');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Lote Actualizado',
                    text: 'Los datos del lote se guardaron correctamente.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            })
            .catch(error => {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Hubo un error al actualizar los datos del lote. Por favor intente de nuevo.'
                });
            });
    });
</script>
@stop
