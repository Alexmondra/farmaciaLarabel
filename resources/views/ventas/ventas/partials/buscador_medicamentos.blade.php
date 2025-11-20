<div class="card card-success card-outline">
    <div class="card-header py-2">
        <h3 class="card-title" style="font-size: 1.1rem;">
            <i class="fas fa-search"></i> Buscar Medicamentos
        </h3>
    </div>
    <div class="card-body pt-2 pb-2" style="min-height: 150px;">

        {{-- VARIABLE PHP A JS: Pasamos las categorías para usarlas en el script --}}
        <script>
            window.listadoCategorias = @json($categorias ?? []);
        </script>

        <div class="row">
            {{-- COLUMNA 1: BUSCADOR DE CATEGORÍAS --}}
            <div class="col-4 pr-1">
                <div class="form-group mb-0 search-container-cat" style="position: relative;">
                    <small class="text-muted">Categoría</small>

                    {{-- Input oculto para el ID real de la categoría --}}
                    <input type="hidden" id="filtro_categoria_id">

                    <div class="input-group input-group-sm">
                        <input type="text"
                            id="busqueda_categoria"
                            class="form-control"
                            placeholder="Todas"
                            autocomplete="off">
                        <div class="input-group-append">
                            {{-- Botón X para limpiar categoría --}}
                            <button class="btn btn-outline-secondary" type="button" id="btn-limpiar-cat" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                            <button class="btn btn-outline-secondary" type="button" style="pointer-events: none;">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>

                    {{-- LISTA FLOTANTE DE CATEGORÍAS --}}
                    <div id="resultados-categorias" class="list-group shadow-sm"
                        style="position: absolute; top: 100%; left: 0; right: 0; z-index: 1060; max-height: 200px; overflow-y: auto; display: none; font-size: 0.9rem;">
                        {{-- Se llena con JS --}}
                    </div>
                </div>
            </div>

            {{-- COLUMNA 2: BUSCADOR DE MEDICAMENTOS (Igual que antes) --}}
            <div class="col-8 pl-1">
                <div class="form-group mb-0 search-container">
                    <small class="text-muted">Nombre / Código</small>
                    <div class="input-group input-group-sm">
                        <input type="text"
                            id="busqueda_medicamento"
                            class="form-control"
                            autocomplete="off"
                            placeholder="Escriba para buscar...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-success">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>

                    {{-- LISTA FLOTANTE MEDICAMENTOS --}}
                    <div id="resultados-medicamentos" class="list-group shadow-sm"
                        style="font-size: 0.9rem;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: LOTES DEL MEDICAMENTO --}}
<div class="modal fade" id="modalLotes" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success py-2"> {{-- Header compacto --}}
                <h5 class="modal-title text-white" style="font-size: 1rem;">Seleccionar Lote</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-2">
                <div class="alert alert-secondary py-1 px-2 mb-2">
                    <strong id="modal-medicamento-nombre" class="text-dark"></strong>
                    <span class="float-right text-muted" style="font-size: 0.85rem">
                        Pres: <span id="modal-medicamento-presentacion"></span>
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0" style="font-size: 0.9rem;">
                        <thead class="thead-light">
                            <tr>
                                <th>Lote</th>
                                <th>Vence</th>
                                <th class="text-center">Stock</th>
                                <th style="width: 100px;">Cant.</th>
                                <th>Precio</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="modal-lotes-tbody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>