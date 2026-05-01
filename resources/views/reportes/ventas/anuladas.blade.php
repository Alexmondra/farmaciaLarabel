@extends('adminlte::page')

@section('titulo', 'Ventas Anuladas')

@section('content')

<style>
    /* TEMA ROJO (AUDITORÍA/ALERTA) */
    :root {
        /* Gradiente Rojo Intenso */
        --primary-gradient: linear-gradient(135deg, #cb2d3e 0%, #ef473a 100%);
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

    /* KPI DE ALERTA */
    .kpi-alert-card {
        background: linear-gradient(145deg, #741a23, #4a0d12);
        /* Rojo oscuro elegante */
        border-radius: 12px;
        padding: 20px;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow);
    }

    .kpi-value {
        font-size: 2rem;
        font-weight: 800;
    }

    .kpi-icon-bg {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 5rem;
        opacity: 0.15;
        transform: rotate(-15deg);
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
        border-color: #ef473a;
        outline: none;
        box-shadow: 0 0 0 3px rgba(239, 71, 58, 0.2);
    }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">

<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0 font-weight-bold text-danger">Auditoría de Anulaciones</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-4 col-12 mb-3">
                <div class="kpi-alert-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase font-weight-bold">Monto Anulado</div>
                            <div class="kpi-value" id="kpiTotal">S/ {{ $kpiData['total'] }}</div>
                            <div class="mt-2 text-white-50">
                                <i class="fas fa-file-invoice-dollar mr-1"></i> <span id="kpiCount">{{ $kpiData['count'] }}</span> operaciones canceladas
                            </div>
                        </div>
                    </div>
                    <i class="fas fa-ban kpi-icon-bg text-white"></i>
                </div>
            </div>

            <div class="col-md-8 col-12 mb-3 d-flex align-items-center">
                <div class="callout callout-danger w-100 bg-white shadow-sm border-0" style="border-left: 5px solid #ef473a !important;">
                    <h5 class="text-danger"><i class="fas fa-info-circle"></i> Importante</h5>
                    <p class="text-muted mb-0">Estas ventas <strong>NO</strong> suman al cierre de caja ni afectan el stock actual (el stock ya fue retornado). Revise periódicamente para detectar patrones sospechosos.</p>
                </div>
            </div>
        </div>

        <div class="modern-card">

            <div class="gradient-header">
                <div class="row align-items-end">
                    <div class="col-12 col-sm-6 col-md-3 mb-2 mb-md-0">
                        <label class="text-white small mb-0">Periodo de Auditoría</label>
                        <input type="text" id="rangoPicker" class="input-futuristic" placeholder="Seleccionar..." readonly>
                        <input type="hidden" id="fecha_inicio" value="{{ $fInicioStr }}">
                        <input type="hidden" id="fecha_fin" value="{{ $fFinStr }}">
                    </div>

                    <div class="col-12 col-sm-6 col-md-3 mb-2 mb-md-0">
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

                    <div class="col-12 col-sm-6 col-md-3 mb-2 mb-sm-0">
                        <label class="text-white small mb-0">Buscar Operación</label>
                        <input type="text" id="filtroSearch" class="input-futuristic"
                            placeholder="Cajero, Cliente, Serie..." onkeyup="delayBusqueda()">
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="text-white small mb-0"><i class="fas fa-file-excel"></i> Exportar</label>
                        <div class="d-flex flex-wrap gap-2">
                            <select id="modoExport" class="input-futuristic" style="min-width: 100px; flex:1;">
                                <option value="ambos">Anuladas + Detalles</option>
                                <option value="ventas">Solo Anuladas</option>
                                <option value="detalles">Solo Detalles</option>
                                <option value="resumen">Resumen Simple</option>
                            </select>
                            <button type="button" class="btn btn-light btn-sm" onclick="exportarExcel()" title="Descargar Excel">
                                <i class="fas fa-download"></i>
                            </button>
                            <button type="button" class="btn btn-info btn-sm" onclick="abrirModalCompartirExcel()" title="Compartir Excel">
                                <i class="fas fa-envelope"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="loadingOverlay" style="display:none; position:absolute; width:100%; height:100%; background:rgba(255,255,255,0.8); z-index:10; justify-content:center; align-items:center;">
                <div class="spinner-border text-danger"></div>
            </div>

            <div id="tablaContainer">
                @include('reportes.ventas._tabla')
            </div>

        </div>

    </div>

    <div class="modal fade" id="modalCompartirExcel" tabindex="-1" role="dialog" aria-labelledby="modalCompartirExcelLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-danger text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title font-weight-bold" id="modalCompartirExcelLabel">
                        <i class="fas fa-envelope-open-text mr-2"></i> Compartir Reporte Anuladas
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small">El sistema generará el Excel de anuladas con los filtros actuales y lo enviará al correo que indiques.</p>

                    <div class="form-group">
                        <label for="email_reporte" class="font-weight-bold">Correo del destinatario</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0"><i class="fas fa-at text-danger"></i></span>
                            </div>
                            <input type="email" id="email_reporte" class="form-control border-left-0"
                                placeholder="ejemplo@correo.com" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-radius: 0 0 15px 15px;">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger px-4 font-weight-bold" id="btnEnviarExcel" onclick="enviarExcelCorreo()">
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
    const URL_REPORTE = "{{ route('reportes.ventas-anuladas') }}"; // Asegúrate de crear esta ruta

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
                // 1. Tabla
                document.getElementById('tablaContainer').innerHTML = data.table_html;

                // 2. KPI Simple (Solo mostramos total anulado)
                let kpi = data.kpi;
                document.getElementById('kpiTotal').innerText = 'S/ ' + kpi.total;
                document.getElementById('kpiCount').innerText = kpi.count;

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

    // Paginación
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

        let url = `{{ route('reportes.ventas-anuladas.export-excel') }}?fecha_inicio=${inicio}&fecha_fin=${fin}&sucursal_id=${sucursal}&search=${encodeURIComponent(search)}&modo=${modo}`;

        window.location.href = url;
    }

    function abrirModalCompartirExcel() {
        $('#modalCompartirExcel').modal('show');
    }

    function enviarExcelCorreo() {
        let emailInput = document.getElementById('email_reporte');
        let email = emailInput.value;
        let btn = document.getElementById('btnEnviarExcel');

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

        let datos = {
            email: email,
            fecha_inicio: document.getElementById('fecha_inicio').value,
            fecha_fin: document.getElementById('fecha_fin').value,
            sucursal_id: document.getElementById('filtroSucursal') ? document.getElementById('filtroSucursal').value : '',
            search: document.getElementById('filtroSearch').value,
            modo: document.getElementById('modoExport').value
        };

        fetch("{{ route('reportes.ventas-anuladas.compartir-excel') }}", {
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
                    Swal.fire({
                        icon: 'success',
                        title: '¡Enviado!',
                        text: 'El reporte de anuladas se está procesando y llegará pronto.',
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
