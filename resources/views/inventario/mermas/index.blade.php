@extends('adminlte::page')

@section('title', 'Mermas y Dañados')

@section('content_header')
@stop

@section('content')

{{-- ESTILOS PERSONALIZADOS PREMIUM --}}
<style>
    :root {
        --pharma-primary: #ff7675;
        --pharma-primary-hover: #d63031;
        --pharma-text: #2d3436;
        --pharma-bg-card: #ffffff;
        --pharma-bg-subtle: #f1f2f6;
        --pharma-border: #f1f2f6;
        --pharma-hover: #fff5f5;
        --pharma-shadow: rgba(0, 0, 0, 0.08);
    }

    .dark-mode {
        --pharma-text: #ecf0f1;
        --pharma-bg-card: #343a40;
        --pharma-bg-subtle: #3f474e;
        --pharma-border: #4b545c;
        --pharma-hover: #483434;
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
        box-shadow: 0 0 0 3px rgba(255, 118, 117, 0.15);
        color: var(--pharma-text);
    }

    .btn-search-modern {
        border-radius: 12px;
        background-color: var(--pharma-primary);
        color: white;
        font-weight: 600;
        padding: 0.5rem 1.5rem;
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(255, 118, 117, 0.2);
    }

    .btn-search-modern:hover {
        background-color: var(--pharma-primary-hover);
        transform: translateY(-2px);
        color: white;
    }

    .btn-confirm-modern {
        border-radius: 50px;
        background-color: var(--pharma-primary);
        color: white;
        font-weight: 600;
        padding: 0.4rem 1rem;
        border: none;
        transition: all 0.3s ease;
        font-size: 0.85rem;
        box-shadow: 0 4px 10px rgba(255, 118, 117, 0.2);
    }

    .btn-confirm-modern:hover {
        background-color: var(--pharma-primary-hover);
        transform: translateY(-1px);
        color: white;
    }

    .nav-tabs-modern {
        border-bottom: 2px solid var(--pharma-border);
        padding: 0 2rem;
        background: var(--pharma-bg-card);
    }

    .nav-tabs-modern .nav-link {
        border: none;
        color: var(--pharma-text);
        opacity: 0.6;
        font-weight: 700;
        padding: 1rem 1.5rem;
        position: relative;
        transition: all 0.3s ease;
        background: transparent;
    }

    .nav-tabs-modern .nav-link.active {
        color: var(--pharma-primary) !important;
        opacity: 1;
        background: transparent;
    }

    .nav-tabs-modern .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 100%;
        height: 3px;
        background-color: var(--pharma-primary);
        border-radius: 3px 3px 0 0;
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
        {{-- CABECERA E INFORMACIÓN --}}
        <div class="header-modern">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h3 class="font-weight-bold mb-0 text-dark">
                        <i class="fas fa-exclamation-circle mr-2 text-danger"></i> Mermas y Dañados
                    </h3>
                    <p class="text-muted small mb-0">Control de bajas y mermas por vencimiento de productos o daños físicos en el inventario.</p>
                </div>
            </div>

            {{-- FORMULARIO DE FILTROS --}}
            <form action="{{ route('inventario.mermas.index') }}" method="GET" class="mt-4">
                <div class="row g-3">
                    {{-- Buscador por Medicamento --}}
                    <div class="col-md-4">
                        <label class="small font-weight-bold text-muted">Medicamento</label>
                        <input type="text" name="medicamento" class="form-control form-control-modern" placeholder="Buscar medicamento..." value="{{ request('medicamento') }}">
                    </div>

                    {{-- Selector de Sucursal --}}
                    @if(count($sucursalesPermitidas) > 1)
                        <div class="col-md-4">
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

                    {{-- Botón de Filtrar --}}
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-search-modern w-100">
                            <i class="fas fa-filter mr-2"></i> Filtrar Mermas
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- PESTAÑAS (TABS) --}}
        <ul class="nav nav-tabs nav-tabs-modern" id="mermaTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="pendientes-tab" data-toggle="tab" href="#pendientes" role="tab" aria-controls="pendientes" aria-selected="true">
                    ⚠️ Pendientes por Confirmar
                    @if(count($pendientes) > 0)
                        <span class="badge badge-danger ml-1" style="border-radius: 10px; font-size: 0.75rem;">{{ count($pendientes) }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="historial-tab" data-toggle="tab" href="#historial" role="tab" aria-controls="historial" aria-selected="false">
                    📋 Historial de Mermas
                </a>
            </li>
        </ul>

        {{-- CONTENIDO DE LAS PESTAÑAS --}}
        <div class="tab-content" id="mermaTabsContent">
            
            {{-- PESTAÑA: PENDIENTES --}}
            <div class="tab-pane fade show active" id="pendientes" role="tabpanel" aria-labelledby="pendientes-tab">
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th>Código Lote</th>
                                <th>Medicamento / Presentación</th>
                                <th>Sucursal</th>
                                <th class="text-center">Stock Vencido</th>
                                <th>Fecha Vencimiento</th>
                                <th>Ubicación</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendientes as $lote)
                                <tr class="item-row" style="background-color: rgba(220, 53, 69, 0.02);">
                                    <td class="font-weight-bold text-danger">{{ $lote->codigo_lote }}</td>
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
                                        <span class="font-weight-bold text-danger" style="font-size: 1.1rem;">
                                            {{ $lote->stock_actual }}
                                        </span>
                                    </td>
                                    <td class="font-weight-bold text-danger">
                                        {{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : 'N/A' }}
                                        <br>
                                        <small class="text-muted font-weight-normal">
                                            Venció hace {{ abs($lote->fecha_vencimiento->diffInDays(now())) }} días
                                        </small>
                                    </td>
                                    <td>
                                        <span class="text-muted"><i class="fas fa-map-marker-alt text-danger mr-1"></i>{{ $lote->ubicacion ?? 'General' }}</span>
                                    </td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-confirm-modern" onclick="openConfirmarModal({{ json_encode($lote) }})">
                                            <i class="fas fa-check mr-1"></i> Confirmar Merma
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-shield-alt mb-3 d-block text-success" style="font-size: 3rem; opacity: 0.5;"></i>
                                        <strong>¡Excelente!</strong> No hay lotes vencidos pendientes por confirmar merma.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINACIÓN PENDIENTES --}}
                @if($pendientes->hasPages())
                    <div class="card-footer bg-white border-0 py-3 d-flex flex-column flex-md-row justify-content-between align-items-center">
                        <span class="text-muted small font-weight-bold mb-2 mb-md-0">
                            Mostrando del {{ $pendientes->firstItem() }} al {{ $pendientes->lastItem() }} de {{ $pendientes->total() }} lotes
                        </span>
                        <div>
                            {{ $pendientes->links() }}
                        </div>
                    </div>
                @endif
            </div>

            {{-- PESTAÑA: HISTORIAL --}}
            <div class="tab-pane fade" id="historial" role="tabpanel" aria-labelledby="historial-tab">
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th>Fecha Baja</th>
                                <th>Medicamento / Lote</th>
                                <th>Sucursal</th>
                                <th class="text-center">Cant. Reportada</th>
                                <th>Motivo / Referencia</th>
                                <th>Responsable</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historial as $mov)
                                <tr class="item-row">
                                    <td>
                                        <div class="font-weight-bold">{{ $mov->created_at->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $mov->medicamento->nombre ?? 'N/A' }}</div>
                                        <small class="text-muted">Lote: <strong>{{ $mov->lote->codigo_lote ?? 'N/A' }}</strong></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border px-2 py-1" style="border-radius: 8px;">
                                            <i class="fas fa-store text-muted mr-1"></i>{{ $mov->sucursal->nombre ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="font-weight-bold text-danger" style="font-size: 1.05rem;">
                                            -{{ $mov->cantidad }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-muted" style="font-size: 0.85rem;">
                                            {{ $mov->motivo }}
                                        </div>
                                        <small class="text-secondary">{{ $mov->referencia }}</small>
                                    </td>
                                    <td>
                                        <span class="small text-muted"><i class="fas fa-user mr-1"></i>{{ $mov->usuario->name ?? 'N/A' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-history mb-3 d-block" style="font-size: 3rem; opacity: 0.3;"></i>
                                        No se registran mermas procesadas en el historial.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINACIÓN HISTORIAL --}}
                @if($historial->hasPages())
                    <div class="card-footer bg-white border-0 py-3 d-flex flex-column flex-md-row justify-content-between align-items-center">
                        <span class="text-muted small font-weight-bold mb-2 mb-md-0">
                            Mostrando del {{ $historial->firstItem() }} al {{ $historial->lastItem() }} de {{ $historial->total() }} registros
                        </span>
                        <div>
                            {{ $historial->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODAL CONFIRMAR MERMA --}}
<div class="modal fade" id="modalConfirmarMerma" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-modern">
            <div class="modal-header modal-header-modern bg-danger">
                <h5 class="modal-title font-weight-bold text-white"><i class="fas fa-check-circle mr-2"></i> Confirmar Baja de Merma</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formConfirmarMerma" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-danger py-2 px-3 small mb-3">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Al confirmar esta merma, el stock del lote se dará de baja en el inventario.
                    </div>

                    <div class="mb-3">
                        <span class="small font-weight-bold text-muted d-block">Medicamento</span>
                        <strong id="confirm-medicamento-nombre" class="text-dark" style="font-size: 1.1rem;"></strong>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <span class="small font-weight-bold text-muted d-block">Lote a Dar de Baja</span>
                            <span id="confirm-lote-codigo" class="font-weight-bold text-danger"></span>
                        </div>
                        <div class="col-6 mb-3">
                            <span class="small font-weight-bold text-muted d-block">Stock Físico Vencido</span>
                            <span id="confirm-lote-stock" class="font-weight-bold" style="font-size: 1.1rem;"></span>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Cantidad a dar de baja --}}
                    <div class="form-group mb-3">
                        <label for="inputCantidad" class="small font-weight-bold text-muted">Cantidad a Dar de Baja (Unidades)</label>
                        <input type="number" name="cantidad" id="inputCantidad" class="form-control form-control-modern" min="1" required>
                    </div>

                    {{-- Motivo de la Merma --}}
                    <div class="form-group mb-0">
                        <label for="selectMotivo" class="small font-weight-bold text-muted">Motivo de la Baja</label>
                        <select name="motivo" id="selectMotivo" class="form-control form-control-modern" required>
                            <option value="VENCIMIENTO">Vencimiento del Medicamento</option>
                            <option value="DETERIORO">Deterioro / Humedad / Daño Físico</option>
                            <option value="ROTO">Blister / Caja Rotas</option>
                            <option value="ROBO / EXTRAVIO">Extravío o Faltante</option>
                            <option value="OTROS">Otros Motivos</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 12px; font-weight: 600;">Cancelar</button>
                    <button type="submit" class="btn btn-danger px-4" style="border-radius: 12px; font-weight: 600; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);">
                        <i class="fas fa-ban mr-2"></i> Dar de Baja
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

    function openConfirmarModal(lote) {
        currentLoteId = lote.id;
        
        $('#confirm-medicamento-nombre').text(lote.medicamento ? lote.medicamento.nombre : 'N/A');
        $('#confirm-lote-codigo').text(lote.codigo_lote);
        $('#confirm-lote-stock').text(lote.stock_actual + ' unidades');
        
        $('#inputCantidad').val(lote.stock_actual);
        $('#inputCantidad').attr('max', lote.stock_actual);
        $('#selectMotivo').val('VENCIMIENTO');

        $('#modalConfirmarMerma').modal('show');
    }

    $('#formConfirmarMerma').on('submit', function(e) {
        e.preventDefault();
        
        if (!currentLoteId) return;

        let cantidad = $('#inputCantidad').val();
        let motivo = $('#selectMotivo').val();

        $.ajax({
            url: `{{ url('inventario/mermas') }}/${currentLoteId}/confirmar`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                cantidad: cantidad,
                motivo: motivo
            },
            success: function(response) {
                $('#modalConfirmarMerma').modal('hide');
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Merma Procesada',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                let msg = 'Hubo un error al procesar la merma.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: msg
                });
            }
        });
    });

    $(document).ready(function() {
        let urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('page_historial')) {
            $('#historial-tab').tab('show');
        } else if (urlParams.has('page_pendientes')) {
            $('#pendientes-tab').tab('show');
        }
    });
</script>
@stop
