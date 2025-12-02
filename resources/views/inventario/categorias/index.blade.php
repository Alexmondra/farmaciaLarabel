@extends('adminlte::page')

@section('title', 'Categorías')

@section('content_header')
@stop

@section('content')

{{-- 1. IMPORTAMOS LOS ESTILOS (Responsive & Modernos) --}}
@include('inventario.categorias.partials.styles')

<div class="container-fluid pt-4">
    <div class="card card-modern">

        {{-- CABECERA --}}
        <div class="header-modern">
            <div>
                <h3 class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-tags mr-2" style="color: var(--pharma-primary);"></i> Categorías
                </h3>
            </div>

            <div class="header-actions">
                <div class="search-pill">
                    <i class="fas fa-search text-muted small"></i>
                    <input type="text" id="searchInput" placeholder="Buscar..." autocomplete="off">
                </div>

                <button class="btn btn-add-modern" onclick="openCreateModal()">
                    <i class="fas fa-plus mr-2"></i> Nuevo
                </button>
            </div>
        </div>

        {{-- TABLA --}}
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th style="width:35%">Nombre</th>
                        <th class="col-desc">Descripción</th>
                        <th class="text-center" style="width:15%">Estado</th>
                        <th class="text-right" style="width:15%">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>

        {{-- FOOTER --}}
        <div class="card-footer bg-white border-0 py-3 d-flex flex-column flex-md-row justify-content-between align-items-center">
            <span class="text-muted small font-weight-bold mb-2 mb-md-0" id="infoRegistros">Cargando...</span>
            <ul class="pagination pagination-sm mb-0" id="paginationContainer"></ul>
        </div>
    </div>
</div>

{{-- 2. IMPORTAMOS LOS MODALES --}}
@include('inventario.categorias.partials.form_modal')
@include('inventario.categorias.partials.modal_delete')

@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // DATOS
        const allData = @json($categorias);
        const rutas = {
            store: "{{ route('inventario.categorias.store') }}",
            update: "{{ route('inventario.categorias.update', 'ID_PH') }}",
            destroy: "{{ route('inventario.categorias.destroy', 'ID_PH') }}"
        };

        // ESTADO
        let state = {
            data: [...allData],
            currentPage: 1,
            itemsPerPage: 10
        };

        // DOM
        const els = {
            tbody: document.getElementById('tableBody'),
            search: document.getElementById('searchInput'),
            info: document.getElementById('infoRegistros'),
            pag: document.getElementById('paginationContainer'),
            modal: $('#categoryModal'),
            form: document.getElementById('categoryForm'),
            title: document.getElementById('modalTitle'),
            btnText: document.getElementById('btnText'),
            method: document.getElementById('methodField'),
            inpNombre: document.getElementById('inputNombre'),
            inpDesc: document.getElementById('inputDescripcion'),
            inpActivo: document.getElementById('inputActivo'),
            errorAlert: document.getElementById('errorAlert'),
            errorList: document.getElementById('errorList')
        };

        // RENDER
        const renderTable = () => {
            const total = state.data.length;
            const pages = Math.ceil(total / state.itemsPerPage) || 1;
            if (state.currentPage > pages) state.currentPage = pages;

            const start = (state.currentPage - 1) * state.itemsPerPage;
            const chunk = state.data.slice(start, start + state.itemsPerPage);

            if (chunk.length === 0) {
                els.tbody.innerHTML = `<tr><td colspan="4" class="text-center py-5 text-muted">Sin resultados</td></tr>`;
                els.info.textContent = '0 registros';
            } else {
                els.tbody.innerHTML = chunk.map(cat => {
                    const catStr = JSON.stringify(cat).replace(/"/g, '&quot;');
                    const delUrl = rutas.destroy.replace('ID_PH', cat.id);
                    const badge = cat.activo ?
                        `<span class="badge px-3 py-2 rounded-pill" style="background:#e0f2f1; color:#00695c;">Activo</span>` :
                        `<span class="badge px-3 py-2 rounded-pill bg-light text-muted border">Inactivo</span>`;

                    return `
                        <tr class="item-row">
                            <td class="font-weight-bold text-dark">
                                <div class="d-flex align-items-center">
                                    <div style="width:32px; height:32px; background:#e0f2f1; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-right:10px; color:#00b894; flex-shrink:0;">
                                        <i class="fas fa-tag fa-xs"></i>
                                    </div>
                                    ${cat.nombre}
                                </div>
                            </td>
                            <td class="text-muted small col-desc">${cat.descripcion || ''}</td>
                            <td class="text-center">${badge}</td>
                            <td class="text-right">
                                <button class="btn-icon text-info mr-1" onclick="openEditModal(${catStr})"><i class="fas fa-pen fa-xs"></i></button>
                                <button class="btn-icon text-danger" data-toggle="modal" data-target="#confirmDeleteModal" data-action="${delUrl}" data-name="${cat.nombre}"><i class="fas fa-trash fa-xs"></i></button>
                            </td>
                        </tr>
                    `;
                }).join('');
                els.info.textContent = `${start + 1} - ${Math.min(start + state.itemsPerPage, total)} de ${total}`;
            }
            renderPagination(pages);
        };

        const renderPagination = (pages) => {
            if (pages <= 1) {
                els.pag.innerHTML = '';
                return;
            }
            let html = '';
            for (let i = 1; i <= pages; i++) {
                let style = i === state.currentPage ? 'background:#00b894; color:white; border-color:#00b894;' : 'color:#636e72';
                html += `<li class="page-item mx-1"><a href="#" class="page-link border rounded-circle d-flex align-items-center justify-content-center" style="width:30px; height:30px; font-weight:bold; ${style}" data-page="${i}">${i}</a></li>`;
            }
            els.pag.innerHTML = html;
        };

        // MODAL LOGIC
        window.openCreateModal = () => {
            els.form.reset();
            els.errorAlert.classList.add('d-none');
            els.method.value = 'POST';
            els.form.action = rutas.store;
            els.title.textContent = "Nueva Categoría";
            els.btnText.textContent = "Guardar";
            els.inpActivo.checked = true;
            els.modal.modal('show');
        };

        window.openEditModal = (cat) => {
            els.form.reset();
            els.errorAlert.classList.add('d-none');
            els.method.value = 'PUT';
            els.form.action = rutas.update.replace('ID_PH', cat.id);
            els.title.textContent = "Editar Categoría";
            els.btnText.textContent = "Actualizar";
            els.inpNombre.value = cat.nombre;
            els.inpDesc.value = cat.descripcion || '';
            els.inpActivo.checked = (cat.activo == 1);
            els.modal.modal('show');
        };

        // EVENTOS
        els.search.addEventListener('input', (e) => {
            const t = e.target.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            state.data = allData.filter(i => ((i.nombre || '') + ' ' + (i.descripcion || '')).toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").includes(t));
            state.currentPage = 1;
            renderTable();
        });
        els.pag.addEventListener('click', (e) => {
            e.preventDefault();
            const p = e.target.closest('.page-link');
            if (p) {
                state.currentPage = +p.dataset.page;
                renderTable();
            }
        });
        $('#confirmDeleteModal').on('show.bs.modal', function(e) {
            const b = $(e.relatedTarget);
            $('#deleteForm').attr('action', b.data('action'));
            $('#deleteName').text(b.data('name'));
        });


        renderTable();
    });
</script>


@stop