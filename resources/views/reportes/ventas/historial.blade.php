@extends('adminlte::page')

@section('titulo', 'Historial Ventas')

@section('content')

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --card-bg: #ffffff;
        --text-main: #333;
        --text-muted: #888;
        --border-color: #eef2f7;
        --input-bg: #f8f9fa;
        --shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.1);
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
        border: none;
        border-radius: 15px;
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    /* ESTILOS DE LAS NUEVAS TARJETAS DE PAGO */
    .kpi-mini-card {
        background: var(--card-bg);
        border-radius: 15px;
        padding: 15px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    /* Barritas de color decorativas a la izquierda */
    .border-left-success {
        border-left: 4px solid #28a745;
    }

    .border-left-yape {
        border-left: 4px solid #742284;
    }

    /* Color Yape */
    .border-left-plin {
        border-left: 4px solid #00d2ff;
    }

    /* Color Plin */
    .border-left-card {
        border-left: 4px solid #4a90e2;
    }

    .kpi-title {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        font-weight: 700;
        margin-bottom: 5px;
    }

    .kpi-number {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--text-main);
    }

    .kpi-icon-bg {
        position: absolute;
        right: 10px;
        bottom: 10px;
        font-size: 2.5rem;
        opacity: 0.05;
        transform: rotate(-15deg);
    }

    /* Estilos del Header y Filtros (Igual que antes) */
    .gradient-header {
        background: var(--primary-gradient);
        padding: 20px;
        border-radius: 15px 15px 0 0;
    }

    .input-futuristic {
        background-color: var(--input-bg);
        border: 1px solid var(--border-color);
        color: var(--text-main);
        border-radius: 10px;
        height: 42px;
        padding-left: 15px;
        font-weight: 500;
        width: 100%;
    }

    .input-futuristic:focus {
        border-color: #764ba2;
        box-shadow: 0 0 0 3px rgba(118, 75, 162, 0.2);
    }

    .input-icon-wrapper {
        position: relative;
    }

    .input-icon-wrapper i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .kpi-mini-card {
            margin-bottom: 10px;
        }

        .gradient-header {
            padding: 15px;
        }
    }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">

<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0 font-weight-bold mb-3" style="color: var(--text-main)">Historial de Ventas</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="row mb-4">

            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-mini-card" style="background: linear-gradient(145deg, #2c3e50, #1a252f); border:none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="kpi-title text-white-50">Ingreso Total</div>
                            <div class="kpi-number text-white" id="kpiTotal">S/ {{ $kpiData['total'] }}</div>
                            <small class="text-white-50"><span id="kpiCount">{{ $kpiData['count'] }}</span> operaciones</small>
                        </div>
                        <i class="fas fa-chart-line text-white" style="font-size: 2rem; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-2 mb-2">
                <div class="kpi-mini-card border-left-success">
                    <div class="kpi-title">Efectivo</div>
                    <div class="kpi-number text-success" id="kpiEfectivo">S/ {{ $kpiData['efectivo'] }}</div>
                    <i class="fas fa-money-bill-wave kpi-icon-bg text-success"></i>
                </div>
            </div>

            <div class="col-6 col-md-2 mb-2">
                <div class="kpi-mini-card border-left-yape">
                    <div class="kpi-title" style="color: #742284;">Yape</div>
                    <div class="kpi-number" style="color: #742284;" id="kpiYape">S/ {{ $kpiData['yape'] }}</div>
                    <i class="fas fa-mobile-alt kpi-icon-bg" style="color: #742284;"></i>
                </div>
            </div>

            <div class="col-6 col-md-2 mb-2">
                <div class="kpi-mini-card border-left-plin">
                    <div class="kpi-title" style="color: #009ebf;">Plin</div>
                    <div class="kpi-number" style="color: #009ebf;" id="kpiPlin">S/ {{ $kpiData['plin'] }}</div>
                    <i class="fas fa-qrcode kpi-icon-bg" style="color: #009ebf;"></i>
                </div>
            </div>

            <div class="col-6 col-md-2 mb-2">
                <div class="kpi-mini-card border-left-card">
                    <div class="kpi-title text-info">Tarjeta</div>
                    <div class="kpi-number text-info" id="kpiTarjeta">S/ {{ $kpiData['tarjeta'] }}</div>
                    <i class="far fa-credit-card kpi-icon-bg text-info"></i>
                </div>
            </div>

        </div>

        <div class="modern-card">

            <div class="gradient-header">
                <div class="row">

                    <div class="col-12 col-md-4 mb-3 mb-md-0">
                        <label class="text-white small mb-1"><i class="far fa-calendar-alt"></i> Periodo</label>
                        <div class="input-icon-wrapper">
                            <input type="text" id="rangoPicker" class="input-futuristic"
                                placeholder="Seleccionar..." readonly="readonly">
                            <i class="far fa-calendar text-dark opacity-50"></i>
                        </div>
                        <input type="hidden" id="fecha_inicio" value="{{ $fInicioStr }}">
                        <input type="hidden" id="fecha_fin" value="{{ $fFinStr }}">
                    </div>

                    <div class="col-12 col-md-3 mb-3 mb-md-0">
                        <label class="text-white small mb-1"><i class="fas fa-store"></i> Farmacia</label>
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
                        <label class="text-white small mb-1">Búsqueda Rápida</label>
                        <div class="input-icon-wrapper">
                            <input type="text" id="filtroSearch" class="input-futuristic"
                                placeholder="Cliente, Serie, DNI..." onkeyup="delayBusqueda()">
                            <i class="fas fa-search text-dark opacity-50"></i>
                        </div>
                    </div>

                </div>
                {{-- debajo del row actual (después de la columna Search) --}}
                <div class="col-12 col-md-3 mt-3 mt-md-0">
                    <label class="text-white small mb-1"><i class="fas fa-file-excel"></i> Exportar</label>

                    <div class="d-flex gap-2">
                        <select id="modoExport" class="input-futuristic" style="min-width: 140px;">
                            <option value="ambos">Ventas + Detalles</option>
                            <option value="ventas">Solo Ventas</option>
                            <option value="detalles">Solo Detalles</option>
                            <option value="resumen">Resumen Simple</option>
                        </select>

                        <button type="button" class="btn btn-light" onclick="exportarExcel()">
                            <i class="fas fa-download"></i>
                        </button>
                        <button type="button" class="btn btn-info ml-1" onclick="abrirModalCompartirExcel()" title="Compartir Excel">
                            <i class="fas fa-envelope"></i>
                        </button>
                    </div>
                </div>


            </div>

            <div id="loadingOverlay" style="display:none; position:absolute; z-index:50; width:100%; height:100%; background:rgba(255,255,255,0.7); align-items:center; justify-content:center; top:0; left:0;">
                <div class="spinner-border text-primary"></div>
            </div>

            <div id="tablaContainer">
                @include('reportes.ventas._tabla')
            </div>

        </div>
    </div>


    <div class="modal fade" id="modalCompartirExcel" tabindex="-1" role="dialog" aria-labelledby="modalCompartirExcelLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-info text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title font-weight-bold" id="modalCompartirExcelLabel">
                        <i class="fas fa-envelope-open-text mr-2"></i> Compartir Reporte Excel
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small">El sistema generará el Excel con los filtros actuales y lo enviará al correo que indiques.</p>

                    <div class="form-group">
                        <label for="email_reporte" class="font-weight-bold">Correo del destinatario</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0"><i class="fas fa-at text-info"></i></span>
                            </div>
                            <input type="email" id="email_reporte" class="form-control border-left-0"
                                placeholder="ejemplo@correo.com" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-radius: 0 0 15px 15px;">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-info px-4 font-weight-bold" id="btnEnviarExcel" onclick="enviarExcelCorreo()">
                        <i class="fas fa-paper-plane mr-1"></i> Enviar Reporte
                    </button>
                </div>
            </div>
        </div>
    </div>

</section>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

<script>
    const URL_REPORTE = "{{ route('reportes.ventas-historial') }}";

    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#rangoPicker", {
            mode: "range",
            dateFormat: "Y-m-d",
            defaultDate: ["{{ $fInicioStr }}", "{{ $fFinStr }}"],
            locale: "es",
            theme: "dark",
            disableMobile: "true",
            onClose: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    let inicio = instance.formatDate(selectedDates[0], "Y-m-d");
                    let fin = selectedDates[1] ? instance.formatDate(selectedDates[1], "Y-m-d") : inicio;
                    document.getElementById('fecha_inicio').value = inicio;
                    document.getElementById('fecha_fin').value = fin;
                    cargarTabla(1);
                }
            }
        });
    });

    // Función AJAX actualizada para llenar los 5 cuadros
    function cargarTabla(page = 1) {
        document.getElementById('loadingOverlay').style.display = 'flex';

        let inicio = document.getElementById('fecha_inicio').value;
        let fin = document.getElementById('fecha_fin').value;
        let sucursalElement = document.getElementById('filtroSucursal');
        let sucursal = sucursalElement ? sucursalElement.value : '';
        let search = document.getElementById('filtroSearch').value;

        let url = `${URL_REPORTE}?page=${page}&fecha_inicio=${inicio}&fecha_fin=${fin}&sucursal_id=${sucursal}&search=${search}`;

        fetch(url, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(response => response.json())
            .then(data => {
                // 1. Actualizar Tabla
                document.getElementById('tablaContainer').innerHTML = data.table_html;

                // 2. Actualizar KPIs (Magia)
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

    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            let href = e.target.closest('a').getAttribute('href');
            let page = new URLSearchParams(href.split('?')[1]).get('page');
            cargarTabla(page);
        }
    });





    function exportarExcel() {
        let inicio = document.getElementById('fecha_inicio').value;
        let fin = document.getElementById('fecha_fin').value;

        let sucursalElement = document.getElementById('filtroSucursal');
        let sucursal = sucursalElement ? sucursalElement.value : '';

        let search = document.getElementById('filtroSearch').value;
        let modo = document.getElementById('modoExport').value;

        let url = `{{ route('reportes.ventas-historial.export-excel') }}?fecha_inicio=${inicio}&fecha_fin=${fin}&sucursal_id=${sucursal}&search=${encodeURIComponent(search)}&modo=${modo}`;

        window.location.href = url;
    }


    function abrirModalCompartirExcel() {
        $('#modalCompartirExcel').modal('show');
    }

    function enviarExcelCorreo() {
        let emailInput = document.getElementById('email_reporte');
        let email = emailInput.value;
        let btn = document.getElementById('btnEnviarExcel');

        // 1. Validación de Correo Electrónico
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            Swal.fire({
                icon: 'warning',
                title: 'Correo Inválido',
                text: 'Por favor, ingresa una dirección de correo electrónica válida.',
                confirmButtonColor: '#17a2b8'
            });
            return;
        }

        // 2. Alerta de "Enviando..." (Sin botón de OK, solo progreso)
        Swal.fire({
            title: 'Enviando reporte...',
            text: 'Estamos procesando el Excel, esto puede tardar unos segundos.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        btn.disabled = true;

        // 3. Captura de datos y envío
        let datos = {
            email: email,
            fecha_inicio: document.getElementById('fecha_inicio').value,
            fecha_fin: document.getElementById('fecha_fin').value,
            sucursal_id: document.getElementById('filtroSucursal') ? document.getElementById('filtroSucursal').value : '',
            search: document.getElementById('filtroSearch').value,
            modo: document.getElementById('modoExport').value
        };

        fetch("{{ route('reportes.ventas-historial.compartir-excel') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(datos)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    $('#modalCompartirExcel').modal('hide');
                    // Alerta de éxito elegante
                    Swal.fire({
                        icon: 'success',
                        title: '¡Enviado!',
                        text: 'El reporte se está procesando en la cola y llegará pronto.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo contactar con el servidor.'
                });
            })
            .finally(() => {
                btn.disabled = false;
            });
    }
</script>
@endsection