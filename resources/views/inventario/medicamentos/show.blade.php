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
                    <li class="list-group-item">
                        <b>Concentración</b> <a class="float-right text-dark">{{ $medicamento->concentracion ?? '—' }}</a>
                    </li>
                    <li class="list-group-item">
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
                    <a href="{{ route('inventario.medicamento_sucursal.historial', [
                'medicamento' => $medicamento->id, 
                'sucursal' => $sucursal->id  // <--- AHORA ES OBLIGATORIO Y ESTÁ DISPONIBLE AQUÍ
          ]) }}"
                        class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-history mr-1"></i> Ver lotes agotados o vencidos
                    </a>
                </div>
                @else
                {{-- TABLA DE LOTES ACTIVOS --}}
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th>Lote</th>
                                <th>Vencimiento</th>
                                <th>Stock</th>
                                <th>Ubicación</th>
                                <th>Precios</th>
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

                                <td class="align-middle">
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
                                </td>

                                <td class="align-middle">
                                    <span class="badge badge-pill badge-primary px-3" style="font-size: 0.9rem;">
                                        {{ $lote->stock_actual }}
                                    </span>
                                </td>

                                <td class="align-middle text-muted small">{{ $lote->ubicacion ?? '—' }}</td>

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
                                    S/ {{ number_format($lote->precio_compra ?? $item['precio'], 2) }}
                                    @endif
                                </td>
                                </tr>
                                @endforeach
                        </tbody>
                    </table>

                    {{-- Footer pequeño de la tabla para ir al historial --}}
                    <div class="bg-light p-2 text-center border-top">
                        <a href="{{ route('inventario.medicamento_sucursal.historial', [
                'medicamento' => $medicamento->id, 
                'sucursal' => $sucursal->id  // <--- AHORA ES OBLIGATORIO Y ESTÁ DISPONIBLE AQUÍ
          ]) }}"
                            class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-history mr-1"></i> Ver lotes agotados o vencidos
                        </a>
                    </div>
                </div>
                @endif
            </div>

            <div class="card-footer bg-light d-flex justify-content-end p-2">
                {{-- Formulario para desvincular (Eliminar de sucursal) --}}
                {{-- Nota: Ajusta la ruta 'inventario.medicamento_sucursal.destroy' si cambiaste el nombre en web.php --}}
                <form method="POST"
                    action="{{ route('inventario.medicamento_sucursal.destroy', ['medicamento' => $medicamento->id, 'sucursal' => $sucursal->id]) }}"
                    onsubmit="return confirm('¿Seguro que deseas retirar este producto de la sucursal {{ $sucursal->nombre }}? Esto ocultará el stock restante.');">
                    @csrf @method('DELETE')
                    <button class="btn btn-xs btn-outline-danger border-0">
                        <i class="fas fa-unlink mr-1"></i> Desvincular de sucursal
                    </button>
                </form>
            </div>
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
@endsection

@section('css')
<style>
    .opacity-50 {
        opacity: 0.5;
    }

    /* Animación simple si no usas Animate.css completo */
    @keyframes pulse-red {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .animate__pulse {
        animation: pulse-red 2s infinite;
    }
</style>
@endsection