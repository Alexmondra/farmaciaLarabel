@extends('adminlte::page')

@section('title', 'Proveedores')

@section('content_header')
<h1 class="mb-0"><i class="fas fa-truck-loading mr-2"></i>Proveedores</h1>
@stop

@section('content')

{{-- ========== INICIO: TOAST (Mensaje de éxito/error) ========== --}}
@if (session('success') || session('error'))
<div class="toast-center">
    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3500">
        <div class="toast-header @if(session('success')) bg-success text-white @elseif(session('error')) bg-danger text-white @endif">
            <strong class="mr-auto">
                @if(session('success')) <i class="fas fa-check-circle mr-1"></i> Éxito
                @elseif(session('error')) <i class="fas fa-times-circle mr-1"></i> Error
                @endif
            </strong>
            <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body">
            {{ session('success') ?? session('error') }}
        </div>
    </div>
</div>
@endif
{{-- ========== FIN: TOAST ========== --}}


<div class="card shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex align-items-center flex-wrap w-100">

            {{-- Buscador (adaptado a tu controller) --}}
            <form method="GET" action="{{ route('proveedores.index') }}" class="input-group" style="max-width: 420px;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                </div>
                <input
                    type="text"
                    name="buscar" {{-- <- Coincide con tu controller --}}
                    class="form-control"
                    placeholder="Buscar por Razón Social o RUC..."
                    value="{{ $busqueda }}" {{-- <- Coincide con tu controller --}}
                    autocomplete="off">
                <div class="input-group-append">
                    <a href="{{ route('proveedores.index') }}" class="btn btn-outline-secondary {{ $busqueda ? '' : 'd-none' }}" title="Limpiar">
                        <i class="fas fa-times-circle"></i>
                    </a>
                    <button class="btn btn-primary" type="submit">
                        Buscar
                    </button>
                </div>
            </form>

            <div class="ml-auto mt-2 mt-md-0">
                @can('proveedores.crear')
                <a href="{{ route('proveedores.create') }}" class="btn btn-primary">
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
                            <a href="{{ route('proveedores.edit', $prov) }}"
                                class="btn btn-outline-primary btn-sm"
                                data-toggle="tooltip"
                                title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>
                            @endcan

                            @can('proveedores.borrar')
                            <button
                                type="button"
                                class="btn btn-outline-danger btn-sm"
                                data-toggle="modal"
                                data-target="#confirmDeleteModal"
                                data-action="{{ route('proveedores.destroy', $prov) }}"
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

{{-- ========== INICIO: MODAL DE BORRADO ========== --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="deleteForm" method="POST" class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteLabel">
                    <i class="fas fa-exclamation-triangle text-danger mr-2"></i> Confirmar eliminación
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Seguro que deseas eliminar el proveedor <strong id="deleteName">—</strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash mr-1"></i> Eliminar
                </button>
            </div>
        </form>
    </div>
</div>
{{-- ========== FIN: MODAL DE BORRADO ========== --}}

@stop

{{-- CSS para el Toast --}}
@section('css')
<style>
    .toast-center {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1080;
    }
</style>
@stop

{{-- JS para el Modal y el Toast --}}
@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Tooltips (AdminLTE/Bootstrap)
        $('[data-toggle="tooltip"]').tooltip();

        // 2. Modal de borrado: set action + nombre
        $('#confirmDeleteModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const action = button.data('action');
            const name = button.data('name');
            $('#deleteForm').attr('action', action);
            $('#deleteName').text(name);
        });

        // 3. Inicializa el toast de Bootstrap (si existe)
        $('.toast').toast('show');
    });
</script>
@stop