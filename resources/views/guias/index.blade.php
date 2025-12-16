@extends('adminlte::page')

@section('title', 'Guías de Remisión')

@section('content_header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">
    <h1 class="text-navy font-weight-bold d-flex align-items-center mb-3 mb-md-0">
        <span class="bg-teal rounded-circle p-2 mr-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
            <i class="fas fa-shipping-fast text-white"></i>
        </span>
        <span class="text-dark-mode-light">Guías de Remisión</span>
    </h1>

    {{-- BOTÓN INTELIGENTE (Se mantiene en el principal por ser vital) --}}
    @if($permiteCrear)
    <a href="{{ route('guias.create') }}" class="btn btn-gradient-teal shadow-lg btn-lg rounded-pill px-4 transition-hover w-100 w-md-auto">
        <i class="fas fa-plus-circle mr-2"></i> Nueva Guía
    </a>
    @else
    <button type="button" class="btn btn-secondary shadow-sm btn-lg rounded-pill px-4 w-100 w-md-auto" onclick="alertaSeleccionarSucursal()">
        <i class="fas fa-lock mr-2"></i> Nueva Guía
    </button>
    @endif

</div>
@stop

@section('content')

{{-- 1. BARRA DE BÚSQUEDA Y FILTROS (PARCIAL) --}}
@include('guias.partials._filters', ['fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta])

{{-- 2. TABLA DE RESULTADOS (PARCIAL) --}}
@include('guias.partials._table', ['guias' => $guias, 'sucursalOrigen' => $sucursalOrigen])

{{-- ESTILOS UNIFICADOS Y OPTIMIZADOS --}}
<style>
    /* === VARIABLES FUTURISTAS/DARK MODE === */
    :root {
        --neon-teal: #20c997;
        --dark-bg: #1e272e;
        --panel-bg: #ffffff;
        --border-color: #e9ecef;
    }

    /* Adaptación Dark Mode */
    body.dark-mode {
        --panel-bg: #2c3e50;
        --border-color: #4b6584;
    }

    /* === CLASES DE ADMINLTE (MODIFICADAS/EXTENDIDAS) === */

    /* Panel Principal (Futurista) */
    .glass-panel {
        background: var(--panel-bg);
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.04);
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .glass-panel:hover {
        box-shadow: 0 8px 15px rgba(32, 201, 151, 0.08);
        border-color: var(--neon-teal);
    }

    /* Botones y Colores */
    .btn-gradient-teal {
        background: linear-gradient(135deg, #20c997 0%, #0c8f6b 100%);
        color: white;
        border: none;
    }

    .btn-teal {
        background-color: #20c997;
        color: white;
        border: none;
    }

    .btn-teal:hover {
        background-color: #17a589;
        color: white;
    }

    /* Tablas y Transiciones */
    .transition-row {
        transition: background-color 0.2s;
    }

    .transition-row:hover {
        background-color: rgba(32, 201, 151, 0.05);
    }

    .border-bottom-custom {
        border-bottom: 1px solid #dee2e6;
    }

    /* Input Futurista (Usado en Buscador) */
    .form-control-futuristic {
        background-color: transparent;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 0.9rem;
        color: #495057;
    }

    .label-futuristic {
        text-transform: uppercase;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: #adb5bd;
        margin-bottom: 5px;
        display: block;
    }

    /* Botones de Acción Redondos (icon-only) */
    .btn-icon-only {
        width: 34px;
        height: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .btn-icon-only:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
    }

    .btn-icon-only.btn-info {
        background-color: #17a2b8;
    }

    .btn-icon-only.btn-info:hover {
        background-color: #117a8b;
    }


    /* === DARK MODE ADAPTATIONS === */

    body.dark-mode .bg-white-mode {
        background-color: #343a40 !important;
    }

    body.dark-mode .bg-light-mode {
        background-color: #454d55 !important;
    }

    body.dark-mode .text-dark-mode-light {
        color: #f8f9fa !important;
    }

    body.dark-mode .text-muted-mode {
        color: #ced4da !important;
    }

    /* Inputs en Dark Mode */
    body.dark-mode .form-control-futuristic {
        background-color: #3f474e;
        border: 1px solid #6c757d;
        color: #fff;
    }

    body.dark-mode .form-control-futuristic:focus {
        background-color: #4b545c;
        border-color: #20c997;
        color: #fff;
    }

    /* Tablas y Bordes */
    body.dark-mode .border-bottom-custom {
        border-bottom: 1px solid #4b545c;
    }

    body.dark-mode .badge-light {
        background-color: #3f474e;
        color: #fff;
        border: 1px solid #6c757d !important;
    }

    /* Botones de Acción en Dark Mode */
    body.dark-mode .btn-light {
        background-color: #3f474e;
        color: #fff;
        border: 1px solid #6c757d;
    }

    body.dark-mode .icon-doc {
        background-color: #2c3136 !important;
    }

    /* Diferenciación Filas (Entrada/Salida) */
    .bg-soft-yellow-mode {
        background-color: #fffdf5;
    }

    body.dark-mode .bg-soft-yellow-mode {
        background-color: rgba(255, 193, 7, 0.05);
        /* Tonalidad tenue en Dark Mode */
    }

    /* === RESPONSIVIDAD (Móvil) === */
    @media (max-width: 767.98px) {
        .w-md-auto {
            width: 100% !important;
        }

        /* Ocultar elementos menos críticos en móvil */
        .d-none.d-md-table-cell {
            display: none !important;
        }

        h1 {
            font-size: 1.5rem;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.4em 0.6em;
        }

        /* Ajustar padding y tamaño de botón de acción en móvil */
        .btn-icon-only {
            width: 30px;
            height: 30px;
            font-size: 0.8rem;
        }
    }
</style>
@stop

@section('js')
<script>
    // Tu lógica de SweetAlert (Anular, Recepcionar) se mantiene igual
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });

    function confirmarAnulacion(id, documento) {
        Swal.fire({
            title: 'Anular Guía de Remisión',
            html: `
            <p class="text-muted">Está a punto de anular la Guía <b>${documento}</b>. Esta acción revertirá el stock.</p>
            <div class="text-left">
                <label class="small font-weight-bold">Motivo de Anulación (Mínimo 5 caracteres)</label>
                <textarea id="swal-motivo-anulacion" class="form-control" placeholder="Error en la emisión del documento..."></textarea>
            </div>
        `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sí, Anular',
            preConfirm: () => {
                const motivo = document.getElementById('swal-motivo-anulacion').value;
                if (motivo.length < 5) {
                    Swal.showValidationMessage('El motivo debe tener al menos 5 caracteres.');
                    return false;
                }
                return {
                    motivo: motivo
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let data = result.value;

                let form = document.createElement('form');
                form.action = `/guias/${id}/anular`;
                form.method = 'POST';

                let csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';

                let method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'PATCH';

                let inMotivo = document.createElement('input');
                inMotivo.name = 'motivo_anulacion';
                inMotivo.value = data.motivo;

                form.append(csrf, method, inMotivo);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function confirmarRecepcion(id, documento) {
        Swal.fire({
            title: 'Recepción de Mercadería',
            html: `
                <p class="text-muted">Estás confirmando la llegada de la Guía <b>${documento}</b></p>
                <div class="text-left">
                    <label class="small font-weight-bold">Fecha de Recepción</label>
                    <input type="date" id="swal-fecha" class="form-control mb-2" value="{{ date('Y-m-d') }}">
                    
                    <label class="small font-weight-bold">Observaciones (Opcional)</label>
                    <textarea id="swal-obs" class="form-control" placeholder="Todo llegó conforme..."></textarea>
                    
                    <div class="custom-control custom-checkbox mt-2">
                        <input type="checkbox" class="custom-control-input" id="swal-conformidad" checked>
                        <label class="custom-control-label small" for="swal-conformidad">Doy conformidad de los productos</label>
                    </div>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Confirmar Llegada',
            preConfirm: () => {
                return {
                    fecha: document.getElementById('swal-fecha').value,
                    obs: document.getElementById('swal-obs').value,
                    conformidad: document.getElementById('swal-conformidad').checked ? 1 : 0
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let data = result.value;

                let form = document.createElement('form');
                form.action = `/guias/${id}/recibir`;
                form.method = 'POST';

                let csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';

                let method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'PUT';

                let inFecha = document.createElement('input');
                inFecha.name = 'fecha_recepcion';
                inFecha.value = data.fecha;
                let inObs = document.createElement('input');
                inObs.name = 'observaciones';
                inObs.value = data.obs;
                let inConf = document.createElement('input');
                inConf.name = 'conformidad';
                inConf.value = data.conformidad;

                form.append(csrf, method, inFecha, inObs, inConf);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function alertaSeleccionarSucursal() {
        Swal.fire({
            title: '¡Acción Requerida!',
            text: 'Para emitir una Guía de Remisión, primero debes seleccionar una SUCURSAL DE ORIGEN en la barra superior.',
            icon: 'warning',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#3085d6',
            footer: '<span class="text-muted small">Esto asegura que la serie (Ej: T001) sea la correcta.</span>'
        });
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stop