<div class="d-flex justify-content-between align-items-center mb-3">
    <h5><i class="fas fa-history"></i> Historial de Movimientos</h5>
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="filtrarMovimientos('todos')">
            Todos
        </button>
        <button type="button" class="btn btn-outline-success btn-sm" onclick="filtrarMovimientos('entrada')">
            Entradas
        </button>
        <button type="button" class="btn btn-outline-warning btn-sm" onclick="filtrarMovimientos('salida')">
            Salidas
        </button>
    </div>
</div>

@php
    $movimientos = $medicamento->movimientos()->with(['sucursal', 'lote', 'usuario'])->orderBy('created_at', 'desc')->get();
@endphp

@if($movimientos->count() > 0)
<div class="table-responsive">
    <table class="table table-hover" id="tablaMovimientos">
        <thead class="thead-light">
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Sucursal</th>
                <th>Lote</th>
                <th>Cantidad</th>
                <th>Stock Final</th>
                <th>Motivo</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimientos as $movimiento)
            <tr data-tipo="{{ $movimiento->tipo }}">
                <td>
                    <small>{{ $movimiento->created_at->format('d/m/Y H:i') }}</small>
                </td>
                <td>
                    @if($movimiento->tipo === 'entrada')
                        <span class="badge badge-success">
                            <i class="fas fa-arrow-up"></i> Entrada
                        </span>
                    @else
                        <span class="badge badge-warning">
                            <i class="fas fa-arrow-down"></i> Salida
                        </span>
                    @endif
                </td>
                <td>
                    <strong>{{ $movimiento->sucursal->nombre }}</strong><br>
                    <small class="text-muted">{{ $movimiento->sucursal->direccion }}</small>
                </td>
                <td>
                    @if($movimiento->lote)
                        <code class="text-primary">{{ $movimiento->lote->codigo_lote }}</code>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    <span class="badge badge-{{ $movimiento->tipo === 'entrada' ? 'success' : 'warning' }} badge-lg">
                        {{ $movimiento->tipo === 'entrada' ? '+' : '-' }}{{ $movimiento->cantidad }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-info">{{ $movimiento->stock_final }}</span>
                </td>
                <td>
                    <strong>{{ $movimiento->motivo }}</strong>
                    @if($movimiento->referencia)
                        <br><small class="text-muted">{{ $movimiento->referencia }}</small>
                    @endif
                </td>
                <td>
                    <small>{{ $movimiento->usuario->name ?? 'Usuario' }}</small>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Resumen de movimientos -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4>{{ $movimientos->where('tipo', 'entrada')->sum('cantidad') }}</h4>
                <small>Total Entradas</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h4>{{ $movimientos->where('tipo', 'salida')->sum('cantidad') }}</h4>
                <small>Total Salidas</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4>{{ $movimientos->count() }}</h4>
                <small>Total Movimientos</small>
            </div>
        </div>
    </div>
</div>
@else
<div class="text-center py-5">
    <i class="fas fa-history fa-3x text-muted mb-3"></i>
    <h5 class="text-muted">No hay movimientos registrados</h5>
    <p class="text-muted">Los movimientos aparecerán cuando agregues lotes o modifiques el stock</p>
</div>
@endif

<script>
function filtrarMovimientos(tipo) {
    const filas = document.querySelectorAll('#tablaMovimientos tbody tr');
    
    filas.forEach(fila => {
        if (tipo === 'todos' || fila.dataset.tipo === tipo) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
    
    // Actualizar botones activos
    document.querySelectorAll('.btn-group button').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}
</script>

