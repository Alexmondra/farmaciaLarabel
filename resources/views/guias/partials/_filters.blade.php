{{-- 1. BARRA DE BÚSQUEDA Y FILTROS --}}
<div class="card card-outline card-teal shadow-sm border-0 rounded-lg mb-3 glass-panel">
    <div class="card-body py-3 bg-light-mode">
        <form action="{{ route('guias.index') }}" method="GET">
            <div class="row align-items-end">
                {{-- Fechas Desde --}}
                <div class="col-6 col-md-3 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-muted-mode label-futuristic">Desde</label>
                    <input type="date" name="fecha_desde" class="form-control form-control-sm bg-input-mode form-control-futuristic"
                        value="{{ $fecha_desde ?? now()->startOfMonth()->format('Y-m-d') }}">
                </div>
                {{-- Fechas Hasta --}}
                <div class="col-6 col-md-3 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-muted-mode label-futuristic">Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control form-control-sm bg-input-mode form-control-futuristic"
                        value="{{ $fecha_hasta ?? now()->format('Y-m-d') }}">
                </div>

                {{-- Buscador Texto --}}
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-muted-mode label-futuristic">Buscar (Serie, Num, Cliente)</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search_q" class="form-control bg-input-mode form-control-futuristic"
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