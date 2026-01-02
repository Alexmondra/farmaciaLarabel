@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<div class="d-flex justify-content-between align-items-center pb-2">
    <div>
        <h1 class="font-weight-bold text-dark" style="font-size: 1.5rem;">Hola, {{ Auth::user()->name }} 👋</h1>
    </div>
    <div class="text-right">
        <span class="badge badge-light p-2 shadow-sm font-weight-normal text-muted small">
            <i class="far fa-clock mr-1"></i> {{ now()->format('d M Y') }}
        </span>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">

    {{-- ======================================================= --}}
    {{-- 1. TARJETAS KPI (AHORA CON ÚLTIMA BOLETA) --}}
    {{-- ======================================================= --}}
    <div class="row mb-3">
        <div class="col-lg-4 col-md-6 mb-2">
            <a href="{{ url('/reportes/ventas-dia') }}" class="card-kpi kpi-purple text-decoration-none">
                <div class="kpi-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="kpi-icon mr-3"><i class="fas fa-cash-register"></i></div>
                        <p class="kpi-label m-0">Ventas Hoy</p>
                    </div>
                    <h3 class="kpi-value">S/ {{ number_format($ventasHoy, 2) }}</h3>
                </div>
                <div class="kpi-bg-icon"><i class="fas fa-cash-register"></i></div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6 mb-2">
            <a href="{{ url('/reportes/ventas-historial') }}" class="card-kpi kpi-blue text-decoration-none">
                <div class="kpi-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="kpi-icon mr-3"><i class="fas fa-chart-line"></i></div>
                        <p class="kpi-label m-0">Acumulado Mes</p>
                    </div>
                    <h3 class="kpi-value">S/ {{ number_format($ventasMes, 2) }}</h3>
                </div>
                <div class="kpi-bg-icon"><i class="fas fa-chart-line"></i></div>
            </a>
        </div>

        {{-- TARJETA TICKETS (CON LA ÚLTIMA BOLETA) --}}
        <div class="col-lg-4 col-md-6 mb-2">
            <div class="card-kpi kpi-orange">
                <div class="kpi-body">
                    <div class="d-flex justify-content-between">
                        <div class="d-flex align-items-center mb-2">
                            <div class="kpi-icon mr-3"><i class="fas fa-receipt"></i></div>
                            <p class="kpi-label m-0">Tickets</p>
                        </div>
                        {{-- DATO CLAVE: ÚLTIMA BOLETA --}}
                        @if($ultimasVentas->isNotEmpty())
                        <div class="text-right">
                            <span class="badge badge-light text-dark shadow-sm" style="font-size: 0.75rem;">
                                Última: {{ $ultimasVentas->first()->serie }}-{{ $ultimasVentas->first()->numero }}
                            </span>
                        </div>
                        @endif
                    </div>

                    <div class="d-flex align-items-baseline">
                        <h3 class="kpi-value mr-2">{{ $ticketsHoy }}</h3>
                        <small style="font-size: 0.75rem; opacity: 0.8">vs {{ $ticketsAyer }} ayer</small>
                    </div>
                </div>
                <div class="kpi-bg-icon"><i class="fas fa-receipt"></i></div>
            </div>
        </div>
    </div>

    @php
    $esMultiSucursal = count($datasetsPorSucursal) > 1;
    @endphp

    {{-- ======================================================= --}}
    {{-- ESTRUCTURA PRINCIPAL --}}
    {{-- ======================================================= --}}
    <div class="row">

        {{-- COLUMNA IZQUIERDA (Menú + Gráfico + Últimas Ventas) --}}
        <div class="{{ $esMultiSucursal ? 'col-md-9' : 'col-lg-8' }}">

            <div class="row">
                {{-- SI ES MULTISUCURSAL: MENÚ IZQUIERDO PEQUEÑO --}}
                @if($esMultiSucursal)
                <div class="col-md-4 mb-3">
                    <div class="card card-outline card-primary h-100 shadow-sm">
                        <div class="card-header border-0 py-2">
                            <h6 class="card-title font-weight-bold" style="font-size: 1rem;">🏢 Sucursales</h6>
                        </div>
                        <div class="card-body p-0 scrollable-list">
                            <div class="list-group list-group-flush">
                                <button type="button" class="list-group-item list-group-item-action active branch-selector py-2"
                                    onclick="updateMainChart('Total Global', globalData, 'rgba(78, 115, 223, 1)', this)">
                                    <span class="font-weight-bold small">Todas (Global)</span>
                                </button>
                                @foreach($datasetsPorSucursal as $index => $sucursal)
                                <button type="button" class="list-group-item list-group-item-action branch-selector py-2"
                                    onclick="updateMainChart('{{ $sucursal['nombre'] }}', {{ json_encode($sucursal['data']) }}, '{{ $sucursal['color'] }}', this)">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <span class="font-weight-bold small">{{ $sucursal['nombre'] }}</span>
                                    </div>
                                    <div class="mt-1" style="height: 20px; width: 100%">
                                        <canvas id="miniChart-{{ $index }}" height="20"></canvas>
                                    </div>
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- GRÁFICO CENTRAL --}}
                <div class="{{ $esMultiSucursal ? 'col-md-8' : 'col-12' }} mb-3">
                    <div class="card card-outline card-primary h-100 shadow-sm">
                        <div class="card-header border-0 py-2">
                            <h3 class="card-title font-weight-bold" id="main-chart-title" style="font-size: 1.1rem;">
                                Tendencia: {{ !$esMultiSucursal ? ($datasetsPorSucursal[0]['nombre'] ?? 'Global') : 'Global' }}
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div style="height: 250px;">
                                <canvas id="mainChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABLA DE ÚLTIMAS VENTAS (NUEVO BLOQUE) --}}
            <div class="card card-outline card-teal shadow-sm mb-4">
                <div class="card-header border-0 py-2">
                    <h3 class="card-title font-weight-bold" style="font-size: 1rem;">🧾 Últimas Transacciones</h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size: 0.9rem;">
                        <thead class="bg-light">
                            <tr>
                                <th class="pl-3 border-0">Comprobante</th>
                                <th class="border-0">Sucursal</th>
                                <th class="text-right border-0">Total</th>
                                <th class="text-right pr-3 border-0">Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimasVentas as $venta)
                            <tr>
                                <td class="pl-3">
                                    <span class="font-weight-bold text-dark">{{ $venta->tipo_comprobante }}</span>
                                    <small class="text-muted d-block">{{ $venta->serie }}-{{ $venta->numero }}</small>
                                </td>
                                <td class="align-middle">{{ $venta->sucursal->nombre }}</td>
                                <td class="text-right align-middle font-weight-bold text-success">
                                    S/ {{ number_format($venta->total_neto, 2) }}
                                </td>
                                <td class="text-right pr-3 align-middle text-muted">
                                    {{ $venta->created_at->format('H:i') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">No hay ventas hoy.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- COLUMNA DERECHA (TOP PRODUCTOS + ALERTAS) --}}
        <div class="{{ $esMultiSucursal ? 'col-md-3' : 'col-lg-4' }}">

            {{-- Top Productos --}}
            <div class="card card-outline card-primary shadow-sm mb-3">
                <div class="card-header border-0 py-2">
                    <h3 class="card-title font-weight-bold" style="font-size: 1rem;">🔥 Top Ventas</h3>
                </div>
                <div class="card-body p-0">
                    @foreach($topProductos as $prod)
                    <div class="d-flex align-items-center px-3 py-2 border-bottom">
                        <img src="{{ $prod->imagen_path ? asset('storage/'.$prod->imagen_path) : 'https://ui-avatars.com/api/?name='.urlencode($prod->nombre) }}"
                            class="rounded shadow-sm mr-3" width="35" height="35">
                        <div class="flex-grow-1 overflow-hidden">
                            <h6 class="mb-0 font-weight-bold text-truncate small">{{ $prod->nombre }}</h6>
                            <small class="text-muted" style="font-size: 0.75rem;">{{ $prod->total_vendido }} un.</small>
                        </div>
                        <span class="font-weight-bold text-primary ml-2 small">S/ {{ number_format($prod->total_dinero, 0) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Alertas --}}
            <div class="card card-outline card-danger shadow-sm">
                <div class="card-header p-1 border-bottom-0">
                    <ul class="nav nav-pills nav-fill" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold small py-1" data-toggle="pill" href="#venc-content" role="tab">Por Vencer</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold small py-1" data-toggle="pill" href="#stock-content" role="tab">Bajo Stock</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0 tab-content">
                    <div class="tab-pane fade show active" id="venc-content" role="tabpanel">
                        <div class="list-group list-group-flush">
                            @forelse($alertasVencimiento as $alerta)
                            @php $dias = floor(now()->diffInDays($alerta->fecha_vencimiento, false)); @endphp
                            <a href="reportes/vencimientos" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-3 py-2">
                                <div style="max-width: 65%">
                                    <span class="d-block font-weight-bold text-truncate small">{{ $alerta->medicamento->nombre }}</span>
                                    <small class="text-muted" style="font-size: 0.7rem;">Lote: {{ $alerta->codigo_lote }}</small>
                                </div>
                                <span class="badge {{ $dias < 15 ? 'badge-danger' : 'badge-warning' }}" style="font-size: 0.75rem;">
                                    {{ (int)$dias }} días
                                </span>
                            </a>
                            @empty
                            <div class="text-center p-3 text-muted small">Todo en orden.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="tab-pane fade" id="stock-content" role="tabpanel">
                        <div class="list-group list-group-flush">
                            @forelse($alertasStock as $stock)
                            <a href="reportes/stock-bajo" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-3 py-2">
                                <div style="max-width: 70%">
                                    <span class="d-block font-weight-bold text-truncate small">{{ $stock->medicamento->nombre }}</span>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $stock->sucursal->nombre }}</small>
                                </div>
                                <span class="badge badge-danger" style="font-size: 0.75rem;">{{ $stock->stock_actual }} un.</span>
                            </a>
                            @empty
                            <div class="text-center p-3 text-muted small">Inventario saludable.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@stop

@section('css')
<style>
    /* VARIABLES */
    :root {
        --kpi-purple-grad: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --kpi-blue-grad: linear-gradient(135deg, #0ba360 0%, #3cba92 100%);
        --kpi-orange-grad: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --radius-card: 12px;
    }

    /* TARJETAS KPI */
    .card-kpi {
        display: block;
        border-radius: var(--radius-card);
        color: white;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease;
    }

    .card-kpi:hover {
        transform: translateY(-3px);
    }

    .kpi-purple {
        background: var(--kpi-purple-grad);
    }

    .kpi-blue {
        background: var(--kpi-blue-grad);
    }

    .kpi-orange {
        background: var(--kpi-orange-grad);
    }

    .kpi-body {
        padding: 15px 20px;
        position: relative;
        z-index: 2;
    }

    .kpi-icon {
        width: 32px;
        height: 32px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    .kpi-label {
        font-size: 0.85rem;
        font-weight: 500;
        opacity: 0.9;
    }

    .kpi-value {
        font-size: 1.6rem;
        font-weight: 700;
        margin: 0;
    }

    .kpi-bg-icon {
        position: absolute;
        right: -15px;
        bottom: -15px;
        font-size: 5rem;
        opacity: 0.15;
        transform: rotate(-15deg);
        z-index: 1;
        pointer-events: none;
    }

    /* ESTILOS MODO OSCURO */
    .branch-selector {
        border-left: 3px solid transparent;
        transition: all 0.2s;
    }

    .branch-selector:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .dark-mode .branch-selector:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .branch-selector.active {
        background-color: #007bff !important;
        color: white !important;
        border-left: 3px solid #0056b3;
    }

    .branch-selector.active span {
        color: white !important;
    }

    .scrollable-list {
        max-height: 350px;
        overflow-y: auto;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var labelsGlobal = @json($chartLabels);
    var globalData = @json($globalData);
    var sucursalesData = @json($datasetsPorSucursal);

    // 1. Gráfico
    var ctxMain = document.getElementById('mainChart').getContext('2d');
    var initialColor = (sucursalesData.length === 1) ? sucursalesData[0].color : '#4e73df';
    var initialData = (sucursalesData.length === 1) ? sucursalesData[0].data : globalData;

    var mainChart = new Chart(ctxMain, {
        type: 'bar',
        data: {
            labels: labelsGlobal,
            datasets: [{
                label: 'Ventas',
                data: initialData,
                backgroundColor: initialColor,
                borderRadius: 4,
                barPercentage: 0.5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        borderDash: [2],
                        color: 'rgba(0,0,0,0.05)'
                    },
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                }
            }
        }
    });

    // 2. Update
    window.updateMainChart = function(nombre, data, color, element) {
        if (!mainChart) return;
        mainChart.data.datasets[0].data = data;
        mainChart.data.datasets[0].backgroundColor = color;
        mainChart.update();
        var titleElem = document.getElementById('main-chart-title');
        if (titleElem) titleElem.innerText = 'Tendencia: ' + nombre;

        document.querySelectorAll('.branch-selector').forEach(el => el.classList.remove('active'));
        if (element) element.classList.add('active');
    }

    // 3. Mini Gráficos
    @if($esMultiSucursal)
    sucursalesData.forEach((suc, index) => {
        var canvas = document.getElementById('miniChart-' + index);
        if (canvas) {
            var ctxMini = canvas.getContext('2d');
            new Chart(ctxMini, {
                type: 'line',
                data: {
                    labels: labelsGlobal,
                    datasets: [{
                        data: suc.data,
                        borderColor: suc.color,
                        borderWidth: 1.5,
                        pointRadius: 0,
                        fill: false,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: false,
                        tooltip: {
                            enabled: false
                        }
                    },
                    scales: {
                        x: {
                            display: false
                        },
                        y: {
                            display: false
                        }
                    },
                    layout: {
                        padding: 0
                    }
                }
            });
        }
    });
    @endif
</script>
@stop