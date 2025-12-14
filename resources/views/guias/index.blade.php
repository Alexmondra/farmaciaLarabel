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

    {{-- BOTÓN INTELIGENTE --}}
    @if($permiteCrear)
    {{-- ENLACE DIRECTO A LA VISTA CREATE --}}
    <a href="{{ route('guias.create') }}" class="btn btn-gradient-teal shadow-lg btn-lg rounded-pill px-4 transition-hover w-100 w-md-auto">
        <i class="fas fa-plus-circle mr-2"></i> Nueva Guía
    </a>
    @else
    {{-- Botón bloqueado si no tiene sucursal (se mantiene igual) --}}
    <button type="button" class="btn btn-secondary shadow-sm btn-lg rounded-pill px-4 w-100 w-md-auto" onclick="alertaSeleccionarSucursal()">
        <i class="fas fa-lock mr-2"></i> Nueva Guía
    </button>
    @endif

</div>
@stop

@section('content')

{{-- 1. BARRA DE BÚSQUEDA Y FILTROS --}}
<div class="card card-outline card-teal shadow-sm border-0 rounded-lg mb-3">
    <div class="card-body py-3 bg-light-mode">
        <form action="{{ route('guias.index') }}" method="GET">
            <div class="row align-items-end">
                {{-- Fechas --}}
                {{-- Fechas --}}
                <div class="col-6 col-md-3 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-muted-mode">Desde</label>
                    {{-- Usamos la variable $fecha_desde que pasamos desde el controlador --}}
                    <input type="date" name="fecha_desde" class="form-control form-control-sm bg-input-mode"
                        value="{{ $fecha_desde ?? now()->startOfMonth()->format('Y-m-d') }}">
                </div>
                <div class="col-6 col-md-3 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-muted-mode">Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control form-control-sm bg-input-mode"
                        value="{{ $fecha_hasta ?? now()->format('Y-m-d') }}">
                </div>

                {{-- Buscador Texto --}}
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-muted-mode">Buscar (Serie, Num, Cliente)</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search_q" class="form-control bg-input-mode"
                            placeholder="Ej: T001-45 o Juan Perez"
                            value="{{ request('search_q') }}">
                        <div class="input-group-append">
                            <button class="btn btn-teal" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Limpiar --}}
                <div class="col-12 col-md-2 text-right">
                    <a href="{{ route('guias.index') }}" class="btn btn-outline-secondary btn-sm btn-block">
                        <i class="fas fa-sync-alt mr-1"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- 2. TABLA DE RESULTADOS --}}
<div class="card shadow-lg border-0 rounded-lg overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-navy text-white">
                    <tr>
                        <th class="py-3 pl-3">Tipo</th>
                        <th class="py-3">Emisión</th>
                        <th class="py-3">Documento</th>
                        {{-- Ocultar en móvil --}}
                        <th class="py-3 d-none d-lg-table-cell">Ruta de Traslado</th>
                        <th class="py-3 d-none d-md-table-cell">Estado</th>
                        <th class="py-3 pr-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white-mode">
                    @forelse($guias as $guia)

                    @php
                    // Determinamos si es SALIDA (Mía) o ENTRADA (Llegada)
                    $esSalida = $guia->sucursal_id == ($sucursalOrigen->id ?? 0);
                    @endphp

                    <tr class="border-bottom-custom transition-row {{ $esSalida ? '' : 'bg-soft-yellow-mode' }}">

                        {{-- TIPO (Salida/Llegada) --}}
                        <td class="pl-3 align-middle">
                            @if($esSalida)
                            <span class="badge badge-light text-success shadow-sm p-2" title="Salida / Enviado por nosotros">
                                <i class="fas fa-arrow-up"></i> <span class="d-none d-sm-inline ml-1">SALIDA</span>
                            </span>
                            @else
                            <span class="badge badge-warning text-white shadow-sm p-2" title="Entrada / Recibido">
                                <i class="fas fa-arrow-down"></i> <span class="d-none d-sm-inline ml-1">LLEGADA</span>
                            </span>
                            @endif
                        </td>

                        {{-- FECHA --}}
                        <td class="align-middle">
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold text-dark-mode-light">
                                    {{ $guia->fecha_emision->format('d/m') }}
                                </span>
                                <small class="text-muted-mode">{{ $guia->fecha_emision->format('H:i') }}</small>
                            </div>
                        </td>

                        {{-- DOCUMENTO --}}
                        <td class="align-middle">
                            <div class="d-flex align-items-center">
                                {{-- Icono solo en Desktop --}}
                                <div class="icon-doc mr-2 d-none d-lg-flex bg-light rounded p-2 {{ $esSalida ? 'text-teal' : 'text-warning' }}">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                                <div>
                                    <span class="badge badge-light border text-md font-weight-bold shadow-sm d-block">
                                        {{ $guia->serie }}-{{ str_pad($guia->numero, 6, '0', STR_PAD_LEFT) }}
                                    </span>
                                    @if($guia->venta_id)
                                    <small class="text-primary font-weight-bold d-block mt-1">
                                        <i class="fas fa-link mr-1"></i>Venta #{{ $guia->venta_id }}
                                    </small>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- RUTA (Solo Desktop Grande) --}}
                        <td class="d-none d-lg-table-cell align-middle">
                            <div class="route-line position-relative pl-3 border-left {{ $esSalida ? 'border-teal' : 'border-warning' }}">
                                <div class="mb-1 text-truncate" style="max-width: 200px;">
                                    <i class="fas fa-circle text-success text-xs mr-2"></i>
                                    <small class="text-muted-mode">
                                        {{ $esSalida ? 'Nosotros' : $guia->direccion_partida }}
                                    </small>
                                </div>
                                <div class="text-truncate" style="max-width: 200px;">
                                    <i class="fas fa-map-marker-alt text-danger text-xs mr-2"></i>
                                    <span class="text-dark-mode-light font-weight-bold">
                                        {{ $esSalida ? $guia->direccion_llegada : 'Nuestra Sucursal' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- ESTADO --}}
                        <td>
                            {{-- Usamos 'estado_visual' y 'color_estado' que creamos en el modelo --}}
                            <span class="badge badge-{{ $guia->color_estado }} px-3 py-2 rounded-pill">
                                {{ $guia->estado_visual }}
                            </span>
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt"></i> {{ $guia->fecha_traslado->format('d/m/Y') }}
                            </small>
                        </td>

                        {{-- ACCIONES --}}
                        <td class="pr-3 text-center align-middle">
                            <div class="d-flex justify-content-center align-items-center">

                                {{-- 1. BOTÓN VER (PDF o Detalle) --}}
                                {{-- Asumiendo ruta 'guias.show' o 'guias.pdf' --}}
                                <a href="{{ route('guias.pdf', $guia->id) }}"
                                    class="btn btn-sm btn-icon-only btn-light text-danger mr-2 shadow-sm"
                                    title="Imprimir Guía A4"
                                    target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                {{-- 2. BOTÓN RECEPCIÓN (Llegada) --}}
                                {{-- Condición: NO soy quien la envió (!esSalida) Y no está anulada Y no está entregada aún --}}
                                @if(!$esSalida && $guia->estado_traslado !== 'ANULADO' && $guia->estado_traslado !== 'ENTREGADO')
                                <button type="button"
                                    class="btn btn-sm btn-icon-only btn-light text-success mr-2 shadow-sm"
                                    onclick="confirmarRecepcion({{ $guia->id }}, '{{ $guia->serie }}-{{ $guia->numero }}')"
                                    title="Confirmar Llegada de Mercadería">
                                    <i class="fas fa-check-double"></i>
                                </button>
                                @endif

                                {{-- 3. BOTÓN ANULAR --}}
                                {{-- Condición: SOY quien la envió (esSalida) Y no está anulada --}}
                                @if($esSalida && $guia->estado_traslado !== 'ANULADO')
                                <button type="button"
                                    class="btn btn-sm btn-icon-only btn-light text-danger shadow-sm"
                                    onclick="confirmarAnulacion({{ $guia->id }}, '{{ $guia->serie }}-{{ $guia->numero }}')"
                                    title="Anular Guía">
                                    <i class="fas fa-ban"></i>
                                </button>
                                @endif

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="empty-state">
                                <div class="icon-box bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="fas fa-shipping-fast fa-2x text-muted"></i>
                                </div>
                                <h6 class="text-muted">No se encontraron guías</h6>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($guias->hasPages())
        <div class="d-flex justify-content-center justify-content-md-end p-3 bg-light-mode border-top">
            {{ $guias->links() }}
        </div>
        @endif
    </div>
</div>

<style>
    /* === ESTILOS BASE Y RESPONSIVE === */
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

    /* Hover y Transiciones */
    .hover-scale:hover {
        transform: scale(1.15);
        transition: transform 0.2s;
    }

    .transition-row {
        transition: background-color 0.2s;
    }

    .transition-row:hover {
        background-color: rgba(32, 201, 151, 0.05);
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
    body.dark-mode .bg-input-mode {
        background-color: #3f474e;
        border: 1px solid #6c757d;
        color: #fff;
    }

    body.dark-mode .bg-input-mode:focus {
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

    body.dark-mode .btn-light {
        background-color: #3f474e;
        color: #fff;
    }

    body.dark-mode .icon-doc {
        background-color: #2c3136 !important;
    }

    /* Diferenciación Filas (Entrada/Salida) */
    body.dark-mode .bg-soft-yellow-mode {
        background-color: rgba(255, 193, 7, 0.05);
    }

    .bg-soft-yellow-mode {
        background-color: #fffdf5;
    }

    /* === RESPONSIVIDAD EXTRA === */
    @media (max-width: 576px) {
        .w-md-auto {
            width: 100% !important;
        }

        h1 {
            font-size: 1.5rem;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.4em 0.6em;
        }
    }

    .btn-icon-only {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        /* Botón redondo */
        transition: all 0.2s ease;
    }

    .btn-icon-only:hover {
        transform: translateY(-2px);
        /* Pequeño salto al pasar mouse */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
    }

    /* Colores específicos al hover */
    .btn-icon-only.text-info:hover {
        background-color: #17a2b8;
        color: white !important;
    }

    .btn-icon-only.text-success:hover {
        background-color: #28a745;
        color: white !important;
    }

    .btn-icon-only.text-danger:hover {
        background-color: #dc3545;
        color: white !important;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });

    function confirmarAnulacion(id, documento) {
        Swal.fire({
            title: '¿Anular Guía ' + documento + '?',
            text: "Esta acción comunicará la baja a SUNAT. ¿Desea continuar?",
            icon: 'warning',
            input: 'text', // Pedimos motivo
            inputPlaceholder: 'Escriba el motivo de anulación...',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, Anular',
            cancelButtonText: 'Cancelar',
            preConfirm: (motivo) => {
                if (!motivo) {
                    Swal.showValidationMessage('El motivo es obligatorio');
                }
                return motivo;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Creamos un formulario dinámico para enviar la anulación
                let form = document.createElement('form');
                form.action = `/guias/${id}/anular`; // Asegúrate que esta ruta exista en web.php
                form.method = 'POST';

                // Token CSRF
                let csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';

                // Method DELETE o PATCH según uses en Laravel
                let method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'PATCH';

                // Motivo
                let inputMotivo = document.createElement('input');
                inputMotivo.type = 'hidden';
                inputMotivo.name = 'motivo_anulacion';
                inputMotivo.value = result.value;

                form.appendChild(csrf);
                form.appendChild(method);
                form.appendChild(inputMotivo);
                document.body.appendChild(form);
                form.submit();
            }
        })
    }

    // B. LÓGICA PARA RECEPCIONAR (LLEGADA)
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

                // Formulario dinámico para enviar
                let form = document.createElement('form');
                form.action = `/guias/${id}/recibir`; // Ruta en web.php
                form.method = 'POST';

                let csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';

                let method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'PUT';

                // Inputs
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