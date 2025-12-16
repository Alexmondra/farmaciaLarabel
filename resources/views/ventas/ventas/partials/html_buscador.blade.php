<div class="card card-success card-outline h-100">
    <div class="card-header py-2">
        <h3 class="card-title text-dark-mode-light" style="font-size: 1.1rem;">
            <i class="fas fa-search"></i> Buscar Medicamentos
        </h3>
    </div>
    <div class="card-body pt-2 pb-2" style="min-height: 150px;">
        <div class="row">
            {{-- COLUMNA 1: BUSCADOR DE CATEGORÍAS --}}
            <div class="col-4 pr-1">
                <div class="form-group mb-0 search-container-cat" style="position: relative;">
                    <small class="text-muted-mode">Categoría</small>
                    <input type="hidden" id="filtro_categoria_id">
                    <div class="input-group input-group-sm">
                        <input type="text" id="busqueda_categoria" class="form-control" placeholder="Todas" autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btn-limpiar-cat" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                            <button class="btn btn-outline-secondary text-dark-mode-light" type="button" style="pointer-events: none;">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>
                    <div id="resultados-categorias" class="list-group shadow-sm" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 1060; max-height: 200px; overflow-y: auto; display: none; font-size: 0.9rem;"></div>
                </div>
            </div>

            {{-- COLUMNA 2: BUSCADOR DE MEDICAMENTOS --}}
            <div class="col-8 pl-1">
                <div class="form-group mb-0 search-container">
                    <small class="text-muted-mode">Nombre / Código</small>
                    <div class="input-group input-group-sm">
                        <input type="text" id="busqueda_medicamento" class="form-control" autocomplete="off" placeholder="Escriba para buscar...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-success">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div id="resultados-medicamentos" class="list-group shadow-sm" style="font-size: 0.9rem;"></div>
                </div>
            </div>
        </div>
    </div>
</div>