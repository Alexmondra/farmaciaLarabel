<div class="d-flex justify-content-between align-items-center mb-3">
    <h5><i class="fas fa-boxes"></i> Gestión de Lotes</h5>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarLote">
        <i class="fas fa-plus"></i> Agregar Lote
    </button>
</div>

@if($lotes->count() > 0)
<div class="table-responsive">
    <table class="table table-hover">
        <thead class="thead-light">
            <tr>
                <th>Código Lote</th>
                <th>Sucursal</th>
                <th>Fecha Vencimiento</th>
                <th>Cantidad Inicial</th>
                <th>Cantidad Actual</th>
                <th>Estado</th>
                <th>Días Restantes</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lotes as $lote)
            @php
                $diasRestantes = null;
                $estadoClass = 'success';
                
                if ($lote->fecha_vencimiento) {
                    $diasRestantes = \Carbon\Carbon::parse($lote->fecha_vencimiento)->diffInDays(now(), false);
                    if ($diasRestantes < 0) {
                        $estadoClass = 'danger'; // Vencido
                    } elseif ($diasRestantes <= 30) {
                        $estadoClass = 'warning'; // Por vencer
                    }
                }
                
                $porcentajeUso = $lote->cantidad_inicial > 0 ? 
                    (($lote->cantidad_inicial - $lote->cantidad_actual) / $lote->cantidad_inicial) * 100 : 0;
            @endphp
            <tr class="{{ $lote->estado === 'vencido' ? 'table-danger' : ($estadoClass === 'warning' ? 'table-warning' : '') }}">
                <td>
                    <code class="text-primary">{{ $lote->codigo_lote }}</code>
                </td>
                <td>
                    <strong>{{ $lote->sucursal->nombre }}</strong><br>
                    <small class="text-muted">{{ $lote->sucursal->direccion }}</small>
                </td>
                <td>
                    @if($lote->fecha_vencimiento)
                        {{ \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y') }}
                    @else
                        <span class="text-muted">Sin fecha</span>
                    @endif
                </td>
                <td>
                    <span class="badge badge-info">{{ $lote->cantidad_inicial }}</span>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-{{ $lote->cantidad_actual > 0 ? 'success' : 'secondary' }} mr-2">
                            {{ $lote->cantidad_actual }}
                        </span>
                        @if($lote->cantidad_actual > 0)
                        <div class="progress" style="width: 60px; height: 8px;">
                            <div class="progress-bar bg-{{ $estadoClass }}" 
                                 style="width: {{ 100 - $porcentajeUso }}%"></div>
                        </div>
                        @endif
                    </div>
                </td>
                <td>
                    @switch($lote->estado)
                        @case('vigente')
                            <span class="badge badge-success">Vigente</span>
                            @break
                        @case('vencido')
                            <span class="badge badge-danger">Vencido</span>
                            @break
                        @case('agotado')
                            <span class="badge badge-secondary">Agotado</span>
                            @break
                    @endswitch
                </td>
                <td>
                    @if($lote->fecha_vencimiento && $diasRestantes !== null)
                        @if($diasRestantes < 0)
                            <span class="text-danger">
                                <i class="fas fa-exclamation-triangle"></i> Vencido
                            </span>
                        @elseif($diasRestantes <= 30)
                            <span class="text-warning">
                                <i class="fas fa-clock"></i> {{ $diasRestantes }} días
                            </span>
                        @else
                            <span class="text-success">
                                <i class="fas fa-check"></i> {{ $diasRestantes }} días
                            </span>
                        @endif
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    <div class="btn-group-vertical btn-group-sm" role="group">
                        @if($lote->cantidad_actual > 0)
                        <button type="button" class="btn btn-warning btn-sm" 
                                onclick="ajustarStock({{ $lote->id }}, '{{ $lote->codigo_lote }}', {{ $lote->cantidad_actual }})"
                                title="Ajustar stock">
                            <i class="fas fa-edit"></i>
                        </button>
                        @endif
                        <button type="button" class="btn btn-info btn-sm" 
                                onclick="verMovimientos({{ $lote->id }})"
                                title="Ver movimientos">
                            <i class="fas fa-history"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Resumen de lotes -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4>{{ $lotes->where('estado', 'vigente')->count() }}</h4>
                <small>Lotes Vigentes</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h4>{{ $lotes->where('estado', 'vencido')->count() }}</h4>
                <small>Lotes Vencidos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4>{{ $lotes->sum('cantidad_actual') }}</h4>
                <small>Stock Total</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4>{{ $lotes->count() }}</h4>
                <small>Total Lotes</small>
            </div>
        </div>
    </div>
</div>
@else
<div class="text-center py-5">
    <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
    <h5 class="text-muted">No hay lotes registrados</h5>
    <p class="text-muted">Agrega lotes para gestionar el inventario</p>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarLote">
        <i class="fas fa-plus"></i> Agregar Primer Lote
    </button>
</div>
@endif

