@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<h1>Tablero de Control {{ $ctx['es_admin'] ? '(Global)' : '' }}</h1>
@stop

@section('content')
<div class="container-fluid">

    {{-- FILA 1: KPIs (Tarjetas Superiores) --}}
    <div class="row">
        {{-- VENTA HOY --}}
        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>S/ {{ number_format($ventasHoy, 2) }}</h3>
                    <p>Ventas de Hoy</p>
                </div>
                <div class="icon"><i class="fas fa-cash-register"></i></div>
            </div>
        </div>

        {{-- VENTA MES --}}
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>S/ {{ number_format($ventasMes, 2) }}</h3>
                    <p>Acumulado del Mes</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-alt"></i></div>
            </div>
        </div>

        {{-- TICKETS --}}
        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $ticketsHoy }}</h3>
                    <p>Tickets Emitidos Hoy</p>
                </div>
                <div class="icon"><i class="fas fa-receipt"></i></div>
            </div>
        </div>
    </div>

    {{-- FILA 2: RANKING DE SUCURSALES (Solo si hay más de 1 sucursal activa hoy o es admin) --}}
    @if($rankingSucursales->count() > 1 || $ctx['es_admin'])
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-navy">
                <div class="card-header border-0">
                    <h3 class="card-title">🏢 Desempeño por Sucursal (Hoy)</h3>
                    <div class="card-tools">
                        <span class="badge badge-info">{{ $rankingSucursales->count() }} Activas</span>
                    </div>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>Sucursal</th>
                                <th class="text-center">Tickets</th>
                                <th class="text-right">Venta Total</th>
                                <th class="text-right" style="width: 20%">Progreso</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rankingSucursales as $rank)
                            <tr>
                                <td>
                                    <i class="fas fa-store-alt text-muted mr-2"></i>
                                    <span class="font-weight-bold">{{ $rank->sucursal->nombre }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">{{ $rank->transacciones }}</span>
                                </td>
                                <td class="text-right text-success font-weight-bold">
                                    S/ {{ number_format($rank->total_dia, 2) }}
                                </td>
                                <td class="text-right align-middle">
                                    @php
                                    $porcentaje = $ventasHoy > 0 ? ($rank->total_dia / $ventasHoy) * 100 : 0;
                                    @endphp
                                    <div class="progress progress-xs">
                                        <div class="progress-bar bg-success" style="width: {{ $porcentaje }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ round($porcentaje) }}%</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- FILA 3: CONTENIDO PRINCIPAL (Gráfico y Tablas) --}}
    <div class="row">

        {{-- COLUMNA IZQUIERDA (Gráfico y Últimas Ventas) --}}
        <div class="col-md-8">
            {{-- GRÁFICO --}}
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Tendencia de Ventas (7 días)</h3>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>

            {{-- ÚLTIMAS VENTAS --}}
            <div class="card card-outline card-teal">
                <div class="card-header border-0">
                    <h3 class="card-title">Últimas Transacciones</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>Comprobante</th>
                                <th>Sucursal</th>
                                <th>Monto</th>
                                <th>Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ultimasVentas as $venta)
                            <tr>
                                <td>
                                    {{ $venta->tipo_comprobante }}
                                    <small>{{ $venta->serie }}-{{ $venta->numero }}</small>
                                </td>
                                <td>
                                    {{ $venta->sucursal->nombre }}
                                </td>
                                <td class="font-weight-bold">
                                    S/ {{ number_format($venta->total_neto, 2) }}
                                </td>
                                <td>
                                    <small class="text-muted"><i class="fas fa-clock"></i> {{ $venta->created_at->format('H:i') }}</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA (Alertas y Top Productos) --}}
        <div class="col-md-4">

            {{-- WIDGET ALERTAS URGENTES --}}
            @if($alertasVencimiento->isNotEmpty())
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title">⚠️ Atención Requerida</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="products-list product-list-in-card pl-2 pr-2">
                        @foreach($alertasVencimiento as $alerta)
                        <li class="item">
                            <div class="product-info ml-2">
                                <a href="javascript:void(0)" class="product-title text-danger">
                                    {{ $alerta->medicamento->nombre }}
                                    <span class="badge badge-danger float-right">{{ $alerta->fecha_vencimiento->diffInDays() }} días</span>
                                </a>
                                <span class="product-description text-muted">
                                    Lote: {{ $alerta->codigo_lote }} | Stock: {{ $alerta->stock_actual }}
                                </span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    <div class="card-footer text-center">
                        <a href="{{ route('reportes.vencimientos') }}" class="small box-footer">Ver todos los vencimientos <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>
            @endif

            {{-- TOP PRODUCTOS --}}
            <div class="card card-outline card-orange">
                <div class="card-header">
                    <h3 class="card-title">🔥 Más Vendidos (Mes)</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="products-list product-list-in-card pl-2 pr-2">
                        @foreach($topProductos as $prod)
                        <li class="item">
                            <div class="product-img">
                                <img src="{{ $prod->imagen_path ? asset('storage/'.$prod->imagen_path) : 'https://via.placeholder.com/50' }}" alt="Product Image" class="img-size-50">
                            </div>
                            <div class="product-info">
                                <a href="javascript:void(0)" class="product-title">{{ $prod->nombre }}
                                    <span class="badge badge-warning float-right">{{ $prod->total_vendido }} un.</span>
                                </a>
                                <span class="product-description">
                                    Generó: S/ {{ number_format($prod->total_dinero, 2) }}
                                </span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById('salesChart').getContext('2d');

    // Datos inyectados desde el Controlador
    var labels = @json($chartLabels ?? []);
    var dataValues = @json($chartData ?? []);

    var salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ventas (S/)',
                data: dataValues,
                backgroundColor: 'rgba(60, 141, 188, 0.2)',
                borderColor: 'rgba(60, 141, 188, 1)',
                borderWidth: 2,
                pointRadius: 4,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' S/ ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'S/ ' + value;
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>
@stop