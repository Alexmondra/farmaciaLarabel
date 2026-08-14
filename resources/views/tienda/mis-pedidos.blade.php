@extends('tienda.layout')

@section('title', 'Mis Pedidos y Compras')

@push('styles')
<style>
    .mis-pedidos-tabs {
        background: white;
        border-radius: 999px;
        box-shadow: 0 2px 12px rgba(15, 23, 42, .06);
        display: inline-flex;
        gap: 0.25rem;
        padding: 0.35rem;
    }
    .mis-pedidos-tabs .nav-link {
        border-radius: 999px;
        color: var(--store-muted);
        font-size: .92rem;
        font-weight: 700;
        padding: 0.6rem 1.3rem;
        transition: all .22s ease;
        white-space: nowrap;
    }
    .mis-pedidos-tabs .nav-link:hover {
        color: var(--store-green-dark);
        background: var(--store-green-soft);
    }
    .mis-pedidos-tabs .nav-link.active {
        background: var(--store-green);
        color: white;
        box-shadow: 0 4px 14px rgba(0, 143, 95, .28);
    }
    .mis-pedidos-tabs .nav-link.active:hover {
        background: var(--store-green-dark);
        color: white;
    }
    .tab-badge {
        background: var(--store-green-soft);
        border-radius: 999px;
        color: var(--store-green-dark);
        display: inline-block;
        font-size: .72rem;
        font-weight: 800;
        line-height: 1;
        margin-left: .35rem;
        min-width: 21px;
        padding: .15rem .42rem;
        text-align: center;
        transition: all .22s ease;
    }
    .mis-pedidos-tabs .nav-link.active .tab-badge {
        background: rgba(255, 255, 255, .22);
        color: white;
    }
    .tab-empty {
        align-items: center;
        background: #f9fbfa;
        border: 1px dashed #d4e2da;
        border-radius: 1rem;
        color: var(--store-muted);
        display: flex;
        flex-direction: column;
        font-weight: 600;
        gap: .5rem;
        justify-content: center;
        padding: 2.5rem 1rem;
        text-align: center;
    }
    .tab-empty-icon {
        align-items: center;
        background: var(--store-green-soft);
        border-radius: 50%;
        color: var(--store-green-dark);
        display: flex;
        font-size: 1.4rem;
        font-weight: 800;
        height: 52px;
        justify-content: center;
        width: 52px;
    }
    @media (max-width: 575px) {
        .mis-pedidos-tabs { width: 100%; }
        .mis-pedidos-tabs .nav-link { flex: 1; padding: .55rem .8rem; font-size: .82rem; text-align: center; }
    }
</style>
@endpush

@section('content')
<h1 class="h3 mb-4">Mis Pedidos y Compras</h1>

@if($pedidos->isEmpty() && $ventas->isEmpty())
    <div class="store-card bg-white p-5 text-center">
        <div class="tab-empty-icon mx-auto mb-3">+</div>
        <h2 class="h5">Aun no tienes pedidos ni compras</h2>
        <p class="text-muted mb-3">Cuando realices tu primer pedido aparecera aqui.</p>
        <a href="{{ route('tienda.index') }}" class="btn btn-store">Ver catalogo</a>
    </div>
@else
    <div class="mis-pedidos-tabs mb-4" role="tablist">
        <a class="nav-link {{ $pedidos->isNotEmpty() ? 'active' : '' }}" data-tab="pedidos" href="#" role="tab">
            Pedidos web
            <span class="tab-badge">{{ $pedidos->count() }}</span>
        </a>
        <a class="nav-link {{ $pedidos->isEmpty() && $ventas->isNotEmpty() ? 'active' : '' }}" data-tab="ventas" href="#" role="tab">
            Compras en farmacia
            <span class="tab-badge">{{ $ventas->count() }}</span>
        </a>
    </div>

    <div id="tab-pedidos" class="tab-pane {{ $pedidos->isNotEmpty() ? '' : 'd-none' }}">
        @if($pedidos->isNotEmpty())
            <div class="store-card bg-white p-4">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Codigo</th>
                                <th>Fecha</th>
                                <th>Sucursal</th>
                                <th>Total</th>
                                <th>Pago</th>
                                <th>Estado Pago</th>
                                <th>Entrega</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="pedidos-tbody" data-current-page="1">
                            @foreach($pedidos as $pedido)
                                @include('tienda.partials.pedido-row', compact('pedido'))
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($pedidos->hasMorePages())
                    <div class="text-center mt-3 pt-2 border-top">
                        <button type="button" id="btn-cargar-pedidos" class="btn btn-sm btn-store-outline px-4">
                            Ver más pedidos
                        </button>
                    </div>
                @endif
            </div>
        @else
            <div class="tab-empty">
                <div class="tab-empty-icon">+</div>
                <p class="mb-0">No tienes pedidos en la tienda web.</p>
            </div>
        @endif
    </div>

    <div id="tab-ventas" class="tab-pane {{ $pedidos->isEmpty() ? '' : 'd-none' }}">
        @if($ventas->isNotEmpty())
            <div class="store-card bg-white p-4">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Comprobante</th>
                                <th>Fecha</th>
                                <th>Sucursal</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="ventas-tbody" data-current-page="1">
                            @foreach($ventas as $venta)
                                @include('tienda.partials.venta-row', compact('venta'))
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($ventas->hasMorePages())
                    <div class="text-center mt-3 pt-2 border-top">
                        <button type="button" id="btn-cargar-ventas" class="btn btn-sm btn-store-outline px-4">
                            Ver más compras
                        </button>
                    </div>
                @endif
            </div>
        @else
            <div class="tab-empty">
                <div class="tab-empty-icon">+</div>
                <p class="mb-0">No tienes compras en farmacia.</p>
            </div>
        @endif
    </div>
@endif

<div class="modal fade" id="modalVentaDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0 px-4 pt-4 pb-2">
                <h5 class="modal-title fw-bold" id="modal-venta-titulo">Detalle de compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4" id="modal-venta-body">
                <div class="text-center py-4">
                    <div class="spinner-border text-success"></div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0 d-none" id="modal-venta-footer">
                <a href="#" id="btn-descargar-pdf" target="_blank" class="btn btn-store font-weight-bold">
                    <i class="fas fa-file-pdf mr-1"></i> Imprimir / Descargar PDF
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.mis-pedidos-tabs .nav-link').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.mis-pedidos-tabs .nav-link').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.add('d-none'));
            document.getElementById('tab-' + this.dataset.tab).classList.remove('d-none');
        });
    });

    const modal = document.getElementById('modalVentaDetalle');
    const modalTitulo = document.getElementById('modal-venta-titulo');
    const modalBody = document.getElementById('modal-venta-body');

    function showModal() {
        modal.classList.add('show');
        modal.style.display = 'block';
        modal.setAttribute('aria-hidden', 'false');
        modal.setAttribute('aria-modal', 'true');
        document.body.classList.add('modal-open');
        if (!document.querySelector('.modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
    }

    function hideModal() {
        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
        document.body.classList.remove('modal-open');
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    }

    document.querySelector('.btn-close')?.addEventListener('click', hideModal);
    modal?.addEventListener('click', function(e) {
        if (e.target === modal) hideModal();
    });

    // Carga de Pedidos Incremental (Ver más)
    const btnCargarPedidos = document.getElementById('btn-cargar-pedidos');
    const pedidosTbody = document.getElementById('pedidos-tbody');
    if (btnCargarPedidos && pedidosTbody) {
        btnCargarPedidos.addEventListener('click', async function() {
            const offset = pedidosTbody.children.length;
            btnCargarPedidos.disabled = true;
            btnCargarPedidos.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...';
            
            try {
                const response = await fetch(`{{ route('tienda.mis-pedidos') }}?type=pedidos&offset=${offset}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                
                // Anexar las nuevas filas
                pedidosTbody.insertAdjacentHTML('beforeend', data.html);
                
                if (!data.hasMore) {
                    btnCargarPedidos.parentElement.remove();
                } else {
                    btnCargarPedidos.disabled = false;
                    btnCargarPedidos.innerHTML = 'Ver más pedidos';
                }
            } catch (err) {
                console.error(err);
                btnCargarPedidos.disabled = false;
                btnCargarPedidos.innerHTML = 'Ver más pedidos';
            }
        });
    }

    // Carga de Ventas Incremental (Ver más)
    const btnCargarVentas = document.getElementById('btn-cargar-ventas');
    const ventasTbody = document.getElementById('ventas-tbody');
    if (btnCargarVentas && ventasTbody) {
        btnCargarVentas.addEventListener('click', async function() {
            const offset = ventasTbody.children.length;
            btnCargarVentas.disabled = true;
            btnCargarVentas.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...';
            
            try {
                const response = await fetch(`{{ route('tienda.mis-pedidos') }}?type=ventas&offset=${offset}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                
                // Anexar las nuevas filas
                ventasTbody.insertAdjacentHTML('beforeend', data.html);
                
                if (!data.hasMore) {
                    btnCargarVentas.parentElement.remove();
                } else {
                    btnCargarVentas.disabled = false;
                    btnCargarVentas.innerHTML = 'Ver más compras';
                }
            } catch (err) {
                console.error(err);
                btnCargarVentas.disabled = false;
                btnCargarVentas.innerHTML = 'Ver más compras';
            }
        });
    }

    // Delegación de eventos para .btn-ver-venta
    const modalFooter = document.getElementById('modal-venta-footer');
    const btnDescargarPdf = document.getElementById('btn-descargar-pdf');

    document.addEventListener('click', async function(e) {
        const btn = e.target.closest('.btn-ver-venta');
        if (!btn) return;
        
        const ventaId = btn.dataset.ventaId;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '...';

        modalTitulo.textContent = 'Detalle de compra';
        modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div></div>';
        
        // Ocultar footer durante la carga
        if (modalFooter) modalFooter.classList.add('d-none');
        
        showModal();

        try {
            const resp = await fetch(`{{ route('tienda.mis-pedidos') }}/venta/${ventaId}`);
            const data = await resp.json();

            modalTitulo.textContent = `${data.tipo_comprobante} ${data.serie || ''}-${data.numero || ''}`;

            let html = `
                <p class="text-muted mb-2">Fecha: ${data.fecha} | Sucursal: ${data.sucursal}</p>
                <div class="table-responsive">
                    <table class="table table-sm text-secondary">
                        <thead class="table-light"><tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr></thead>
                        <tbody>`;

            data.detalles.forEach(d => {
                html += `<tr><td>${d.producto}</td><td>${d.cantidad}</td><td>S/ ${d.precio}</td><td>S/ ${d.subtotal}</td></tr>`;
            });

            html += `</tbody><tfoot><tr class="fw-bold"><td colspan="3" class="text-end text-dark">Total</td><td class="text-dark">S/ ${data.total}</td></tr></tfoot></table></div>`;

            modalBody.innerHTML = html;
            
            // Setear href firmado y mostrar botón en el footer
            if (btnDescargarPdf && data.url_pdf) {
                btnDescargarPdf.href = data.url_pdf;
            }
            if (modalFooter) modalFooter.classList.remove('d-none');

        } catch (err) {
            modalBody.innerHTML = '<div class="alert alert-danger">Error al cargar los detalles.</div>';
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
</script>
@endpush
