@extends('adminlte::page')

@section('titulo', 'Ventas del Día')

@section('content')

<style>
    /* Estilos Futuristas Base */
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --card-bg: #ffffff;
        --text-main: #333;
        --text-muted: #888;
        --border-color: #eef2f7;
        --input-bg: #f8f9fa;
        --shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.1);
    }

    body.dark-mode {
        --card-bg: #1f1f2e;
        --text-main: #e1e1e6;
        --text-muted: #a0a0b0;
        --border-color: #3f3f4e;
        --input-bg: #2a2a3c;
        --shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.5);
    }

    .modern-card {
        background: var(--card-bg);
        border-radius: 15px;
        box-shadow: var(--shadow);
        margin-bottom: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    /* KPIs (Tarjetas de Colores) */
    .kpi-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }

    .kpi-mini-card {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 15px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow);
        flex: 1;
        min-width: 140px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .border-l-success {
        border-left: 4px solid #28a745;
    }

    .border-l-yape {
        border-left: 4px solid #742284;
    }

    .border-l-plin {
        border-left: 4px solid #00d2ff;
    }

    .border-l-card {
        border-left: 4px solid #17a2b8;
    }

    .kpi-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: var(--text-muted);
        font-weight: 700;
        margin-bottom: 4px;
    }

    .kpi-value {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--text-main);
    }

    .kpi-icon {
        position: absolute;
        right: 10px;
        bottom: 5px;
        font-size: 2rem;
        opacity: 0.1;
    }

    /* Inputs y Header */
    .gradient-header {
        background: var(--primary-gradient);
        padding: 15px;
    }

    .input-futuristic {
        background: var(--input-bg);
        border: 1px solid var(--border-color);
        color: var(--text-main);
        border-radius: 8px;
        height: 40px;
        width: 100%;
        padding-left: 10px;
        font-weight: 500;
    }

    .input-futuristic:focus {
        border-color: #764ba2;
        outline: none;
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1 class="m-0 font-weight-bold" style="color: var(--text-main)">Ventas del Día</h1>
            <button onclick="cargarTabla()" class="btn btn-sm btn-outline-primary rounded-pill">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="kpi-row">
            <div class="kpi-mini-card" style="background: linear-gradient(145deg, #2c3e50, #1a252f); flex: 1.5; min-width: 200px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-label text-white-50">Cierre Estimado</div>
                        <div class="kpi-value text-white" style="font-size: 1.5rem;" id="kpiTotal">S/ {{ $kpiData['total'] }}</div>
                        <small class="text-white-50"><span id="kpiCount">{{ $kpiData['count'] }}</span> tickets hoy</small>
                    </div>
                    <i class="fas fa-cash-register text-white" style="font-size: 2.5rem; opacity: 0.2;"></i>
                </div>
            </div>

            <div class="kpi-mini-card border-l-success">
                <div class="kpi-label">Efectivo (Caja)</div>
                <div class="kpi-value text-success" id="kpiEfectivo">S/ {{ $kpiData['efectivo'] }}</div>
                <i class="fas fa-money-bill-wave kpi-icon text-success"></i>
            </div>

            <div class="kpi-mini-card border-l-yape">
                <div class="kpi-label" style="color: #742284;">Yape</div>
                <div class="kpi-value" style="color: #742284;" id="kpiYape">S/ {{ $kpiData['yape'] }}</div>
                <i class="fas fa-mobile-alt kpi-icon" style="color: #742284;"></i>
            </div>

            <div class="kpi-mini-card border-l-plin">
                <div class="kpi-label" style="color: #009ebf;">Plin</div>
                <div class="kpi-value" style="color: #009ebf;" id="kpiPlin">S/ {{ $kpiData['plin'] }}</div>
                <i class="fas fa-qrcode kpi-icon" style="color: #009ebf;"></i>
            </div>

            <div class="kpi-mini-card border-l-card">
                <div class="kpi-label text-info">Tarjeta</div>
                <div class="kpi-value text-info" id="kpiTarjeta">S/ {{ $kpiData['tarjeta'] }}</div>
                <i class="far fa-credit-card kpi-icon text-info"></i>
            </div>
        </div>

        <div class="modern-card">

            <div class="gradient-header">
                <div class="row">
                    <div class="col-12 col-md-3 mb-2 mb-md-0">
                        <label class="text-white small mb-0">Fecha</label>
                        <input type="date" id="filtroFecha" class="input-futuristic"
                            value="{{ $fecha }}" onchange="cargarTabla()">
                    </div>

                    <div class="col-12 col-md-4 mb-2 mb-md-0">
                        <label class="text-white small mb-0">Farmacia</label>
                        @if(count($sucursalesDisponibles) > 1 || auth()->user()->hasRole('Administrador'))
                        <select id="filtroSucursal" class="input-futuristic" onchange="cargarTabla()">
                            <option value="">Global (Todas)</option>
                            @foreach($sucursalesDisponibles as $suc)
                            <option value="{{ $suc->id }}" {{ request('sucursal_id') == $suc->id ? 'selected' : '' }}>
                                {{ $suc->nombre }}
                            </option>
                            @endforeach
                        </select>
                        @else
                        <input type="text" class="input-futuristic" value="{{ auth()->user()->sucursales->first()->nombre ?? 'Mi Sucursal' }}" disabled>
                        @endif
                    </div>

                    <div class="col-12 col-md-5">
                        <label class="text-white small mb-0">Búsqueda Rápida</label>
                        <input type="text" id="filtroSearch" class="input-futuristic"
                            placeholder="Cliente, Producto, Serie..." onkeyup="delayBusqueda()">
                    </div>
                </div>
            </div>

            <div id="loadingOverlay" style="display:none; position:absolute; width:100%; height:100%; background:rgba(255,255,255,0.8); z-index:10; justify-content:center; align-items:center;">
                <div class="spinner-border text-primary"></div>
            </div>

            <div id="tablaContainer">
                @include('reportes.ventas._tabla')
            </div>
        </div>

    </div>
</section>
@endsection

@section('js')
<script>
    const URL_REPORTE = "{{ route('reportes.ventas-dia') }}";

    function cargarTabla(page = 1) {
        document.getElementById('loadingOverlay').style.display = 'flex';

        // Obtenemos los valores de los inputs
        let fecha = document.getElementById('filtroFecha').value;
        let sucursalElement = document.getElementById('filtroSucursal');
        let sucursal = sucursalElement ? sucursalElement.value : '';
        let search = document.getElementById('filtroSearch').value;

        // Construimos la URL
        let url = `${URL_REPORTE}?page=${page}&fecha=${fecha}&sucursal_id=${sucursal}&search=${search}`;

        fetch(url, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(response => response.json())
            .then(data => {
                // 1. Actualizar Tabla (Reutilizada)
                document.getElementById('tablaContainer').innerHTML = data.table_html;

                // 2. Actualizar KPIs (Tarjetas de colores)
                let kpi = data.kpi;
                document.getElementById('kpiTotal').innerText = 'S/ ' + kpi.total;
                document.getElementById('kpiCount').innerText = kpi.count;
                document.getElementById('kpiEfectivo').innerText = 'S/ ' + kpi.efectivo;
                document.getElementById('kpiYape').innerText = 'S/ ' + kpi.yape;
                document.getElementById('kpiPlin').innerText = 'S/ ' + kpi.plin;
                document.getElementById('kpiTarjeta').innerText = 'S/ ' + kpi.tarjeta;

                document.getElementById('loadingOverlay').style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('loadingOverlay').style.display = 'none';
            });
    }

    let timeout = null;

    function delayBusqueda() {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            cargarTabla(1);
        }, 500);
    }

    // Paginación AJAX
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            let href = e.target.closest('a').getAttribute('href');
            let page = new URLSearchParams(href.split('?')[1]).get('page');
            cargarTabla(page);
        }
    });
</script>
@endsection