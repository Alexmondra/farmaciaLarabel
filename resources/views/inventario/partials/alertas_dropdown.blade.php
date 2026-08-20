@php
    use App\Models\Inventario\Lote;
    use App\Models\Inventario\MedicamentoSucursal;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Session;

    $sucursalId = Session::get('sucursal_id');
    $hoy = Carbon::today();

    // 1. Consultar lotes por vencer (con stock > 0, fecha de vencimiento futura, hasta 30 días)
    $lotesPorVencerQuery = Lote::with('medicamento')
        ->where('stock_actual', '>', 0)
        ->whereNotNull('fecha_vencimiento')
        ->whereDate('fecha_vencimiento', '>=', $hoy)
        ->whereDate('fecha_vencimiento', '<=', $hoy->copy()->addDays(30))
        ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId));

    $totalVencimientos = $lotesPorVencerQuery->count();
    $listVencimientos = $lotesPorVencerQuery->orderBy('fecha_vencimiento', 'asc')->limit(5)->get();

    // 2. Consultar productos con stock bajo
    $stockBajoQuery = MedicamentoSucursal::with('medicamento')->conStockBajo($sucursalId);
    $totalStockBajo = $stockBajoQuery->count();
    $listStockBajo = $stockBajoQuery->limit(5)->get();

    // Total de alertas de inventario
    $totalAlertas = $totalVencimientos + $totalStockBajo;

    // Criticidad para color de campana: amarillo si hay alertas
    $colorCampana = $totalAlertas > 0 ? 'warning' : 'secondary';
@endphp

{{-- ITEM DE LA CAMPANA EN LA BARRA SUPERIOR --}}
<li class="nav-item dropdown" id="alertasDropdownWrapper">
    <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false" style="position: relative; display: flex; align-items: center;">
        <i class="fas fa-bell text-{{ $colorCampana }}" style="font-size: 1.25rem;"></i>
        @if($totalAlertas > 0)
            <span class="badge badge-warning navbar-badge font-weight-bold" style="font-size: 0.65rem; top: 3px; right: 2px; padding: 2px 4px; border-radius: 50%;">
                {{ $totalAlertas }}
            </span>
        @endif
    </a>
    
    {{-- DROPDOWN MENU PERSONALIZADO PREMIUM (DARK THEME COMO LA REFERENCIA) --}}
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="alertasDropdownMenu" style="width: 380px; max-width: 95vw; background-color: #1e272e; border: 1px solid #2f3542; border-radius: 16px; padding: 0; box-shadow: 0 10px 30px rgba(0,0,0,0.5); overflow: hidden; color: #ecf0f1; margin-top: 10px;">
        
        {{-- Estilos encapsulados para el Dropdown --}}
        <style>
            #alertasDropdownMenu .header-title {
                font-size: 0.95rem;
                font-weight: 800;
                color: #ffffff;
            }
            #alertasDropdownMenu .badge-active-alerts {
                background-color: #2f3542;
                color: #a4b0be;
                font-size: 0.7rem;
                font-weight: 700;
                border-radius: 20px;
                padding: 3px 10px;
            }
            #alertasDropdownMenu .nav-tabs-alerts {
                border-bottom: 1px solid #2f3542;
                display: flex;
                background-color: #1e272e;
            }
            #alertasDropdownMenu .nav-tabs-alerts .nav-item {
                flex: 1;
                text-align: center;
            }
            #alertasDropdownMenu .nav-tabs-alerts .nav-link {
                border: none;
                color: #747d8c;
                font-weight: 700;
                font-size: 0.8rem;
                padding: 0.8rem 0;
                border-radius: 0;
                background: transparent;
                transition: all 0.3s;
                position: relative;
            }
            #alertasDropdownMenu .nav-tabs-alerts .nav-link.active {
                color: #fdcb6e !important;
            }
            #alertasDropdownMenu .nav-tabs-alerts .nav-link.active::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 10%;
                width: 80%;
                height: 3px;
                background-color: #fdcb6e;
                border-radius: 3px 3px 0 0;
            }
            #alertasDropdownMenu .alerts-scroll-container {
                max-height: 300px;
                overflow-y: auto;
                background-color: #1e272e;
            }
            #alertasDropdownMenu .alerts-scroll-container::-webkit-scrollbar {
                width: 6px;
            }
            #alertasDropdownMenu .alerts-scroll-container::-webkit-scrollbar-track {
                background: #1e272e;
            }
            #alertasDropdownMenu .alerts-scroll-container::-webkit-scrollbar-thumb {
                background: #2f3542;
                border-radius: 3px;
            }
            #alertasDropdownMenu .alert-item {
                display: flex;
                padding: 0.9rem 1.2rem;
                border-bottom: 1px solid #2f3542;
                transition: background-color 0.2s;
            }
            #alertasDropdownMenu .alert-item:hover {
                background-color: #2f3542;
                text-decoration: none;
            }
            #alertasDropdownMenu .alert-icon-circle {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                background-color: rgba(235, 94, 85, 0.1);
                color: #ff7675;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin-right: 12px;
                flex-shrink: 0;
                font-size: 0.9rem;
                border: 1px solid rgba(235, 94, 85, 0.2);
            }
            #alertasDropdownMenu .alert-item-content {
                flex-grow: 1;
                min-width: 0; /* Permite truncar texto */
            }
            #alertasDropdownMenu .alert-item-title {
                font-weight: 700;
                color: #ffffff;
                font-size: 0.82rem;
                margin-bottom: 2px;
                text-transform: uppercase;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            #alertasDropdownMenu .alert-item-meta {
                font-size: 0.72rem;
                color: #a4b0be;
                display: flex;
                justify-content: space-between;
                line-height: 1.4;
            }
            #alertasDropdownMenu .alert-item-vence {
                font-size: 0.72rem;
                color: #ff7675;
                font-weight: 700;
            }
            #alertasDropdownMenu .dropdown-footer-modern {
                background-color: #1e272e;
                border-top: 1px solid #2f3542;
                display: flex;
                justify-content: space-between;
                padding: 0.8rem 1.5rem;
            }
            #alertasDropdownMenu .dropdown-footer-modern a {
                color: #fdcb6e;
                font-weight: 700;
                font-size: 0.78rem;
                transition: color 0.2s;
            }
            #alertasDropdownMenu .dropdown-footer-modern a:hover {
                color: #e1b12c;
                text-decoration: none;
            }
        </style>

        {{-- 1. Cabecera del Dropdown --}}
        <div class="d-flex justify-content-between align-items-center px-3 py-2" style="border-bottom: 1px solid #2f3542; background-color: #1e272e;">
            <span class="header-title">Panel de Alertas</span>
            <span class="badge-active-alerts">{{ $totalAlertas }} alertas activas</span>
        </div>

        {{-- 2. Pestañas (Opciones arriba al costado para cambiar) --}}
        <ul class="nav nav-tabs nav-tabs-alerts" id="alertsDropdownTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="dropdown-vencimientos-tab" data-toggle="tab" href="#dropdown-vencimientos" role="tab" aria-controls="dropdown-vencimientos" aria-selected="true">
                    Vencimiento ({{ $totalVencimientos }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="dropdown-stock-tab" data-toggle="tab" href="#dropdown-stock" role="tab" aria-controls="dropdown-stock" aria-selected="false">
                    Stock Bajo ({{ $totalStockBajo }})
                </a>
            </li>
        </ul>

        {{-- 3. Contenedor de Pestañas con Scroll --}}
        <div class="tab-content" id="alertsDropdownTabsContent">
            
            {{-- Pestaña: Vencimientos --}}
            <div class="tab-pane fade show active" id="dropdown-vencimientos" role="tabpanel" aria-labelledby="dropdown-vencimientos-tab">
                <div class="alerts-scroll-container">
                    @forelse($listVencimientos as $lote)
                        @php
                            $dias = $lote->fecha_vencimiento->diffInDays($hoy);
                        @endphp
                        <a href="{{ route('reportes.vencimientos') }}" class="alert-item">
                            <div class="alert-icon-circle" style="color: #ff7675; background-color: rgba(235, 94, 85, 0.1); border-color: rgba(235, 94, 85, 0.2);">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="alert-item-content">
                                <div class="alert-item-title" title="{{ $lote->medicamento->nombre }}">{{ $lote->medicamento->nombre }}</div>
                                <div class="alert-item-meta">
                                    <span>Lote: <strong>{{ $lote->codigo_lote }}</strong></span>
                                    <span>Stock: <strong>{{ $lote->stock_actual }}</strong></span>
                                </div>
                                <div class="alert-item-meta mt-1">
                                    <span class="alert-item-vence">Vence: {{ $lote->fecha_vencimiento->format('d/m/Y') }}</span>
                                    <span class="small font-weight-bold" style="color: #a4b0be;">Quedan {{ $dias }} días</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="fas fa-check-circle mb-2 text-success d-block" style="font-size: 1.5rem; opacity: 0.6;"></i>
                            No hay lotes por vencer en los próximos 30 días.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Pestaña: Stock Bajo --}}
            <div class="tab-pane fade" id="dropdown-stock" role="tabpanel" aria-labelledby="dropdown-stock-tab">
                <div class="alerts-scroll-container">
                    @forelse($listStockBajo as $pivot)
                        <a href="{{ route('reportes.stock_bajo') }}" class="alert-item">
                            <div class="alert-icon-circle" style="color: #fdcb6e; background-color: rgba(253, 203, 110, 0.1); border-color: rgba(253, 203, 110, 0.2);">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="alert-item-content">
                                <div class="alert-item-title" title="{{ $pivot->medicamento->nombre }}">{{ $pivot->medicamento->nombre }}</div>
                                <div class="alert-item-meta">
                                    <span>Stock Actual: <strong class="text-danger">{{ $pivot->stock_computado }}</strong></span>
                                    <span>Stock Mínimo: <strong>{{ $pivot->stock_minimo }}</strong></span>
                                </div>
                                <div class="alert-item-meta mt-1">
                                    <span class="text-warning font-weight-bold">Stock Crítico</span>
                                    <span style="color: #ff7675; font-weight: 700;">Reponer</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="fas fa-check-circle mb-2 text-success d-block" style="font-size: 1.5rem; opacity: 0.6;"></i>
                            Todos los medicamentos tienen stock suficiente.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- 4. Pie de página (Footer) --}}
        <div class="dropdown-footer-modern">
            <a href="{{ route('inventario.lotes.index') }}">Ver Lotes</a>
            <a href="{{ route('inventario.medicamentos.index') }}">Ver Stock de Sucursal</a>
        </div>
    </div>
</li>

{{-- Script JS para evitar que el dropdown se cierre y permitir el cambio de pestañas --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Evitar que el dropdown se cierre al hacer clic adentro (excepto en las pestañas)
        $('#alertasDropdownMenu').on('click', function(e) {
            if ($(e.target).closest('#alertsDropdownTabs').length > 0) {
                return;
            }
            e.stopPropagation();
        });

        // Cambiar de pestaña manualmente al hacer clic en ellas
        $('#alertsDropdownTabs a').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Evita que se cierre el dropdown
            $(this).tab('show'); // Cambia la pestaña de Bootstrap de forma segura
        });
    });
</script>
