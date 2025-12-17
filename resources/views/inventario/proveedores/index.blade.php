@extends('adminlte::page')

@section('title', 'Proveedores')

@section('content_header')
<h1 class="mb-0"><i class="fas fa-truck-loading mr-2"></i>Proveedores</h1>
@stop

@section('content')

<div class="card shadow-sm">
    <div class="card-header bg-white">
        {{-- CAMBIO: Usar d-flex, flex-column, y flex-md-row para responsividad --}}
        <div class="d-flex align-items-center flex-wrap w-100">

            {{-- BUSCADOR --}}
            {{-- ELIMINAMOS 'style="max-width: 420px;"' para que ocupe todo el ancho en móvil --}}
            <form method="GET" action="{{ route('inventario.proveedores.index') }}" class="input-group flex-grow-1 mr-md-2" style="max-width: 420px;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                </div>
                <input
                    type="text"
                    name="buscar"
                    class="form-control"
                    placeholder="Buscar por Razón Social o RUC..."
                    value="{{ $busqueda ?? '' }}"
                    autocomplete="off">
                <div class="input-group-append">
                    @if(!empty($busqueda))
                    <a href="{{ route('inventario.proveedores.index') }}" class="btn btn-outline-secondary" title="Limpiar">
                        <i class="fas fa-times-circle"></i>
                    </a>
                    @endif
                    <button class="btn btn-primary" type="submit">
                        Buscar
                    </button>
                </div>
            </form>

            {{-- CAMBIO: Usar ms-auto y flex-shrink-0 para que el botón de 'Nuevo Proveedor' se alinee correctamente y no se encoja --}}
            <div class="ml-auto mt-2 mt-md-0 flex-shrink-0">
                {{-- PERMISO: CREAR --}}
                @can('proveedores.crear')
                <a href="{{ route('inventario.proveedores.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Nuevo Proveedor
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-striped mb-0 align-middle" id="proveedores-table">
            <thead class="thead-light">
                <tr>
                    <th style="width:12%">RUC</th>
                    <th>Razón Social</th>
                    <th style="width:12%">Teléfono</th>
                    <th style="width:20%">Email</th>
                    <th class="text-center" style="width:10%">Estado</th>
                    <th class="text-right" style="width:12%">Acciones</th>
                </tr>
            </thead>
            <tbody id="proveedores-table-body">
                @forelse($proveedores as $prov)
                <tr>
                    <td class="font-weight-semibold">
                        {{ $prov->ruc }}
                    </td>
                    <td class="font-weight-semibold">
                        <i class="fas fa-truck text-muted mr-2"></i>{{ $prov->razon_social }}
                    </td>
                    <td class="text-muted">{{ $prov->telefono ?? 'N/A' }}</td>
                    <td class="text-muted">{{ $prov->email ?? 'N/A' }}</td>
                    <td class="text-center">
                        @if($prov->activo)
                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Activo</span>
                        @else
                        <span class="badge badge-secondary"><i class="fas fa-pause mr-1"></i>Inactivo</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="btn-group">
                            @can('proveedores.editar')
                            <a href="{{ route('inventario.proveedores.edit', $prov) }}"
                                class="btn btn-outline-primary btn-sm"
                                data-toggle="tooltip"
                                title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>
                            @endcan

                            {{-- PERMISO: ELIMINAR --}}
                            @can('proveedores.eliminar')
                            <button
                                type="button"
                                class="btn btn-outline-danger btn-sm"
                                data-toggle="modal"
                                data-target="#confirmDeleteModal"
                                data-action="{{ route('inventario.proveedores.destroy', $prov) }}"
                                data-name="{{ $prov->razon_social }}"
                                title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="far fa-folder-open fa-2x d-block mb-2"></i>
                        No se encontraron proveedores.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($proveedores->hasPages())
    <div class="card-footer bg-white">
        <div id="pagination-links" class="mb-0">
            {{ $proveedores->links() }}
        </div>
    </div>
    @endif
</div>

{{-- MODAL DE BORRADO --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="deleteForm" method="POST" class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Confirmar eliminación
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Seguro que deseas eliminar el proveedor <strong id="deleteName">—</strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </div>
        </form>
    </div>
</div>

@stop

@section('css')
<style>
    /* Estilos existentes */
    .toast-center {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1080;
    }

    /* === RESPONSIVIDAD === */
    /* Asegura que el contenedor de acciones no se desborde y que los botones quepan */
    #proveedores-table .btn-group {
        flex-wrap: nowrap;
    }

    /* Oculta columnas de baja prioridad en móvil (ej. Email y Teléfono) */
    @media (max-width: 767.98px) {

        /* Oculta Teléfono y Email en pantallas pequeñas */
        #proveedores-table thead th:nth-child(3),
        #proveedores-table tbody td:nth-child(3),
        #proveedores-table thead th:nth-child(4),
        #proveedores-table tbody td:nth-child(4) {
            display: none;
        }

        /* Ajusta el ancho de RUC y Estado para dar espacio a Razón Social */
        #proveedores-table thead th:nth-child(1),
        #proveedores-table tbody td:nth-child(1) {
            width: 25% !important;
            /* RUC */
        }

        #proveedores-table thead th:nth-child(5),
        #proveedores-table tbody td:nth-child(5) {
            width: 25% !important;
            /* Estado */
        }

        #proveedores-table thead th:nth-child(6),
        #proveedores-table tbody td:nth-child(6) {
            width: 25% !important;
            /* Acciones */
        }
    }

    /* === MODO OSCURO (Activado por la clase AdminLTE 'dark-mode' en el body) === */
    body.dark-mode .card.shadow-sm {
        background-color: #343a40 !important;
        /* Color de fondo oscuro */
        border-color: #495057 !important;
    }

    body.dark-mode .card-header.bg-white,
    body.dark-mode .card-footer.bg-white {
        background-color: #3e444a !important;
        /* Un tono más claro para cabecera/pie */
        border-bottom-color: #495057 !important;
        border-top-color: #495057 !important;
    }

    /* Texto dentro de la card */
    body.dark-mode .card,
    body.dark-mode .card-body,
    body.dark-mode .card-header,
    body.dark-mode .card-footer {
        color: #d1d9e0 !important;
    }

    /* Tabla oscura */
    body.dark-mode #proveedores-table {
        color: #e9ecef;
    }

    body.dark-mode #proveedores-table .thead-light th {
        color: #fff;
        background-color: #495057;
        border-color: #5d6874;
    }

    body.dark-mode #proveedores-table .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(255, 255, 255, 0.05);
        /* Rayado suave */
    }

    body.dark-mode #proveedores-table .table-hover tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    /* Input Group - Buscador */
    body.dark-mode .input-group-text.bg-light {
        background-color: #495057 !important;
        color: #d1d9e0 !important;
        border-color: #5d6874 !important;
    }

    body.dark-mode .form-control {
        background-color: #2b3035;
        color: #d1d9e0;
        border-color: #5d6874;
    }

    /* Modal de Borrado */
    body.dark-mode .modal-content {
        background-color: #343a40 !important;
        color: #d1d9e0 !important;
    }

    body.dark-mode .modal-header {
        border-bottom-color: #495057 !important;
    }

    body.dark-mode .modal-footer {
        border-top-color: #495057 !important;
    }

    body.dark-mode .btn-outline-secondary {
        color: #adb5bd;
        border-color: #adb5bd;
    }

    body.dark-mode .btn-outline-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
        color: #fff;
    }
</style>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('[data-toggle="tooltip"]').tooltip();
        $('#confirmDeleteModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const action = button.data('action');
            const name = button.data('name');
            $('#deleteForm').attr('action', action);
            $('#deleteName').text(name);
        });
    });
</script>
@stop