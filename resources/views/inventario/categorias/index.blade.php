@extends('adminlte::page')

@section('title', 'Categorías')

@section('content_header')
<h1 class="mb-0"><i class="fas fa-tags mr-2"></i>Categorías</h1>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex align-items-center flex-wrap w-100">
            {{-- Buscador --}}
            <div class="input-group" style="max-width: 420px;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                </div>
                <input
                    type="text"
                    id="searchInput"
                    name="q"
                    class="form-control"
                    placeholder="Buscar por nombre o descripción..."
                    value="{{ $q }}"
                    autocomplete="off">
                <div class="input-group-append">
                    <button id="clearSearch" class="btn btn-outline-secondary {{ $q ? '' : 'd-none' }}" type="button" title="Limpiar">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
            </div>

            <div class="ml-auto mt-2 mt-md-0">
                <a href="{{ route('inventario.categorias.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Nueva categoría
                </a>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-striped mb-0 align-middle" id="categorias-table">
            <thead class="thead-light">
                <tr>
                    <th style="width:24%">Nombre</th>
                    <th>Descripción</th>
                    <th class="text-center" style="width:10%">Estado</th>
                    <th class="text-right" style="width:12%">Acciones</th>
                </tr>
            </thead>
            <tbody id="categorias-table-body">
                @forelse($categorias as $cat)
                <tr>
                    <td class="font-weight-semibold">
                        <i class="fas fa-tag text-muted mr-2"></i>{{ $cat->nombre }}
                    </td>
                    <td class="text-muted">
                        {{ \Illuminate\Support\Str::limit(trim($cat->descripcion ?? ''), 160) }}
                    </td>
                    <td class="text-center">
                        @if($cat->activo)
                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Activo</span>
                        @else
                        <span class="badge badge-secondary"><i class="fas fa-pause mr-1"></i>Inactivo</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="btn-group">
                            <a href="{{ route('inventario.categorias.edit', $cat) }}" class="btn btn-outline-primary btn-sm" data-toggle="tooltip" title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>
                            <button
                                type="button"
                                class="btn btn-outline-danger btn-sm"
                                data-toggle="modal"
                                data-target="#confirmDeleteModal"
                                data-action="{{ route('inventario.categorias.destroy', $cat) }}"
                                data-name="{{ $cat->nombre }}"
                                title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-5">
                        <i class="far fa-folder-open fa-2x d-block mb-2"></i>
                        No se encontraron categorías.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white">
        <div id="pagination-links" class="mb-0">
            {{ $categorias->links() }}
        </div>
    </div>
</div>

{{-- Modal de confirmación de borrado --}}
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
                ¿Seguro que deseas eliminar la categoría <strong id="deleteName">—</strong>?
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
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tooltips (AdminLTE/Bootstrap)
        $('[data-toggle="tooltip"]').tooltip();

        // Búsqueda AJAX con debounce
        const searchInput = document.getElementById('searchInput');
        const clearSearch = document.getElementById('clearSearch');
        const tbody = document.getElementById('categorias-table-body');
        const pagination = document.getElementById('pagination-links');
        const baseUrl = `{{ route('inventario.categorias.index') }}`;
        let debounce;

        function fetchCategorias(url) {
            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const newTbody = doc.getElementById('categorias-table-body');
                    const newPagination = doc.getElementById('pagination-links');
                    if (newTbody) tbody.innerHTML = newTbody.innerHTML;
                    if (newPagination) pagination.innerHTML = newPagination.innerHTML;
                })
                .catch(console.error);
        }

        searchInput.addEventListener('input', () => {
            clearTimeout(debounce);
            clearSearch.classList.toggle('d-none', !searchInput.value);
            debounce = setTimeout(() => {
                const q = encodeURIComponent(searchInput.value || '');
                fetchCategorias(`${baseUrl}?q=${q}`);
            }, 300);
        });

        clearSearch.addEventListener('click', () => {
            searchInput.value = '';
            clearSearch.classList.add('d-none');
            fetchCategorias(baseUrl);
        });

        document.addEventListener('click', e => {
            const a = e.target.closest('#pagination-links a');
            if (!a) return;
            e.preventDefault();
            fetchCategorias(a.href);
        });

        // Modal de borrado: set action + nombre
        $('#confirmDeleteModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const action = button.data('action');
            const name = button.data('name');
            $('#deleteForm').attr('action', action);
            $('#deleteName').text(name);
        });
    });
</script>


{{-- ========== TOAST CENTRADO (Bootstrap 4) ========== --}}
<style>
    .toast-center {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1080;
        /* por encima del header de AdminLTE */
        pointer-events: none;
        /* no bloquea clics del fondo */
    }

    .toast-center .toast {
        pointer-events: auto;
        /* sí permite cerrar el toast */
        min-width: 320px;
    }
</style>

@if (session('success') || session('error') || session('warning') || session('info'))
<div class="toast-center">
    <div
        class="toast"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
        data-delay="3200">
        <div class="toast-header
        @if(session('success')) bg-success text-white
        @elseif(session('error')) bg-danger text-white
        @elseif(session('warning')) bg-warning
        @elseif(session('info')) bg-info text-white
        @endif">
            <strong class="mr-auto">
                @if(session('success')) <i class="fas fa-check-circle mr-1"></i> Éxito
                @elseif(session('error')) <i class="fas fa-times-circle mr-1"></i> Error
                @elseif(session('warning')) <i class="fas fa-exclamation-triangle mr-1"></i> Advertencia
                @elseif(session('info')) <i class="fas fa-info-circle mr-1"></i> Información
                @endif
            </strong>
            <small class="ml-2">Ahora</small>
            <button type="button" class="ml-2 mb-1 close @if(session('warning')) text-dark @else text-white @endif" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body">
            {{ session('success') ?? session('error') ?? session('warning') ?? session('info') }}
        </div>
    </div>
</div>
@endif

@push('js')
<script>
    $(function() {
        // Inicializa el toast de Bootstrap (no el helper de AdminLTE)
        $('.toast').toast('show');
    });
</script>
@endpush

@stop