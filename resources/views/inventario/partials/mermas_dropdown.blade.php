@php
    use App\Models\Inventario\Lote;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Session;

    $sucursalId = Session::get('sucursal_id');
    $hoy = Carbon::today();

    // 1. Mermas por Confirmar (Lotes vencidos con stock actual > 0)
    $mermasQuery = Lote::with('medicamento')
        ->where('stock_actual', '>', 0)
        ->whereNotNull('fecha_vencimiento')
        ->whereDate('fecha_vencimiento', '<', $hoy)
        ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId));

    $totalMermas = $mermasQuery->count();
    $listMermas = $mermasQuery->orderBy('fecha_vencimiento', 'asc')->limit(5)->get();

    // Criticidad para color del icono: rojo si hay mermas
    $colorMermas = $totalMermas > 0 ? 'danger' : 'secondary';
@endphp

{{-- ITEM DE MERMAS EN LA BARRA SUPERIOR --}}
<li class="nav-item dropdown" id="mermasDropdownWrapper">
    <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false" style="position: relative; display: flex; align-items: center;" title="Mermas Pendientes">
        <i class="fas fa-exclamation-triangle text-{{ $colorMermas }}" style="font-size: 1.25rem;"></i>
        @if($totalMermas > 0)
            <span class="badge badge-danger navbar-badge font-weight-bold" style="font-size: 0.65rem; top: 3px; right: 2px; padding: 2px 4px; border-radius: 50%;">
                {{ $totalMermas }}
            </span>
        @endif
    </a>
    
    {{-- DROPDOWN MENU PERSONALIZADO PREMIUM (DARK THEME COMO LA REFERENCIA) --}}
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="mermasDropdownMenu" style="width: 380px; max-width: 95vw; background-color: #1e272e; border: 1px solid #2f3542; border-radius: 16px; padding: 0; box-shadow: 0 10px 30px rgba(0,0,0,0.5); overflow: hidden; color: #ecf0f1; margin-top: 10px;">
        
        {{-- Estilos encapsulados para el Dropdown --}}
        <style>
            #mermasDropdownMenu .header-title {
                font-size: 0.95rem;
                font-weight: 800;
                color: #ffffff;
            }
            #mermasDropdownMenu .badge-active-alerts {
                background-color: #2f3542;
                color: #a4b0be;
                font-size: 0.7rem;
                font-weight: 700;
                border-radius: 20px;
                padding: 3px 10px;
            }
            #mermasDropdownMenu .alerts-scroll-container {
                max-height: 380px;
                overflow-y: auto;
                background-color: #1e272e;
            }
            #mermasDropdownMenu .alerts-scroll-container::-webkit-scrollbar {
                width: 6px;
            }
            #mermasDropdownMenu .alerts-scroll-container::-webkit-scrollbar-track {
                background: #1e272e;
            }
            #mermasDropdownMenu .alerts-scroll-container::-webkit-scrollbar-thumb {
                background: #2f3542;
                border-radius: 3px;
            }
            #mermasDropdownMenu .alert-item {
                display: flex;
                padding: 0.8rem 1.2rem;
                border-bottom: 1px solid #2f3542;
                transition: background-color 0.2s;
            }
            #mermasDropdownMenu .alert-item:hover {
                background-color: #2f3542;
                text-decoration: none;
            }
            #mermasDropdownMenu .alert-icon-circle {
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
            #mermasDropdownMenu .alert-item-content {
                flex-grow: 1;
                min-width: 0; /* Permite truncar texto */
            }
            #mermasDropdownMenu .alert-item-title {
                font-weight: 700;
                color: #ffffff;
                font-size: 0.82rem;
                margin-bottom: 2px;
                text-transform: uppercase;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            #mermasDropdownMenu .alert-item-meta {
                font-size: 0.72rem;
                color: #a4b0be;
                display: flex;
                justify-content: space-between;
                line-height: 1.4;
            }
            #mermasDropdownMenu .alert-item-vence {
                font-size: 0.72rem;
                color: #ff7675;
                font-weight: 700;
            }
            #mermasDropdownMenu .dropdown-footer-modern {
                background-color: #1e272e;
                border-top: 1px solid #2f3542;
                display: flex;
                justify-content: space-between;
                padding: 0.8rem 1.5rem;
            }
            #mermasDropdownMenu .dropdown-footer-modern a {
                color: #fdcb6e;
                font-weight: 700;
                font-size: 0.78rem;
                transition: color 0.2s;
            }
            #mermasDropdownMenu .dropdown-footer-modern a:hover {
                color: #e1b12c;
                text-decoration: none;
            }
        </style>

        {{-- 1. Cabecera del Dropdown --}}
        <div class="d-flex justify-content-between align-items-center px-3 py-2" style="border-bottom: 1px solid #2f3542; background-color: #1e272e;">
            <span class="header-title">Mermas por Confirmar</span>
            <span class="badge-active-alerts">{{ $totalMermas }} mermas</span>
        </div>

        {{-- 2. Contenedor de Listado Vertical con Scroll --}}
        <div class="alerts-scroll-container">
            @forelse($listMermas as $lote)
                @php
                    $diasVencido = $hoy->diffInDays($lote->fecha_vencimiento);
                @endphp
                <a href="{{ route('inventario.mermas.index') }}" class="alert-item">
                    <div class="alert-icon-circle">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="alert-item-content">
                        <div class="alert-item-title" title="{{ $lote->medicamento->nombre }}">{{ $lote->medicamento->nombre }}</div>
                        <div class="alert-item-meta">
                            <span>Lote: <strong>{{ $lote->codigo_lote }}</strong></span>
                            <span>Stock Físico: <strong class="text-danger">{{ $lote->stock_actual }}</strong></span>
                        </div>
                        <div class="alert-item-meta mt-1">
                            <span class="alert-item-vence">Venció: {{ $lote->fecha_vencimiento->format('d/m/Y') }}</span>
                            <span class="small font-weight-bold text-muted">Hace {{ $diasVencido }} días</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-5 text-muted small" style="background-color: #1e272e;">
                    <i class="fas fa-check-circle mb-2 text-success d-block" style="font-size: 1.8rem; opacity: 0.6;"></i>
                    No hay mermas vencidas pendientes de confirmar.
                </div>
            @endforelse
        </div>

        {{-- 3. Pie de página (Footer) --}}
        <div class="dropdown-footer-modern" style="justify-content: center;">
            <a href="{{ route('inventario.mermas.index') }}">Gestionar todas las Mermas</a>
        </div>
    </div>
</li>

{{-- Script JS para evitar que el dropdown de Bootstrap se cierre al hacer clic adentro --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#mermasDropdownMenu').on('click', function(e) {
            e.stopPropagation();
        });
    });
</script>
