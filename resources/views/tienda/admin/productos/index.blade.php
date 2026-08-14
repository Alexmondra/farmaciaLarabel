@extends('adminlte::page')

@section('title', 'Productos tienda')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Productos tienda</h1>
    <a href="{{ route('tienda.admin.productos.create') }}" class="btn btn-primary">Publicar producto</a>
</div>
@endsection

@section('content')
@include('tienda.partials.alerts')
<div class="card shadow-sm border-0 rounded-xl overflow-hidden">
    <div class="card-body p-4">
        <form id="search-form" method="GET" class="row mb-4">
            <div class="col-md-12 position-relative">
                <div class="input-group input-group-lg shadow-xs rounded-lg overflow-hidden border">
                    <div class="input-group-prepend border-0">
                        <span class="input-group-text bg-white border-0 text-muted pl-4 pr-2">
                            <i class="fas fa-search" id="search-icon"></i>
                        </span>
                    </div>
                    <input type="search" name="q" id="search-input" value="{{ request('q') }}" 
                           class="form-control border-0 pl-2 py-3 text-secondary font-weight-medium" 
                           placeholder="Buscar productos en tienda por nombre o código..." autocomplete="off"
                           style="font-size: 1rem;">
                </div>
            </div>
        </form>

        <div class="position-relative">
            <!-- Tabla de productos -->
            <div id="productos-table-container" class="transition-all duration-300">
                @include('tienda.admin.productos.partials.table')
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .rounded-xl { border-radius: 0.75rem !important; }
    .rounded-lg { border-radius: 0.5rem !important; }
    .shadow-xs { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
    .font-weight-medium { font-weight: 500; }
    .text-xs { font-size: 0.75rem; }
    
    /* Estilos clínicos suaves */
    .bg-success-light { background-color: #d1fae5 !important; }
    
    /* Estilo del input al enfocar */
    #search-input:focus {
        outline: none !important;
        box-shadow: none !important;
    }
    .input-group:focus-within {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
        transition: all 0.3s ease-in-out;
    }
    
    /* Botones de acción tipo card minimalistas */
    .btn-white {
        background-color: #fff;
        border: 1px solid #e2e8f0;
        color: #4a5568;
        transition: all 0.2s ease-in-out;
    }
    .btn-white:hover {
        background-color: #f8fafc;
        color: #1a202c;
        border-color: #cbd5e1;
    }
    
    /* Efecto de transición para AJAX */
    .transition-all {
        transition: all 0.3s ease-in-out;
    }
    .duration-300 {
        transition-duration: 300ms;
    }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-input');
    const tableContainer = document.getElementById('productos-table-container');
    
    let timer = null;
    let currentQuery = searchInput.value;

    // Función para obtener los productos vía AJAX
    async function fetchProducts(url, query = '') {
        // Reducir la opacidad para dar feedback de carga
        tableContainer.style.opacity = '0.5';

        try {
            // Construir URL final de forma segura con base absoluta
            const requestUrl = new URL(url, window.location.origin);
            if (query) {
                requestUrl.searchParams.set('q', query);
            } else {
                requestUrl.searchParams.delete('q');
            }

            const response = await fetch(requestUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) throw new Error('Error en la red');
            
            const data = await response.json();
            
            // Actualizar el DOM
            tableContainer.innerHTML = data.html;
            
            // Actualizar la barra de direcciones del navegador sin recargar
            window.history.pushState({}, '', requestUrl.toString());
            
        } catch (error) {
            console.error('Error cargando los productos:', error);
            mostrarNotificacionError('Ocurrió un error al cargar los productos de la tienda.');
        } finally {
            // Restaurar la opacidad
            tableContainer.style.opacity = '1';
        }
    }

    function mostrarNotificacionError(mensaje) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000
            });
        } else if (typeof toastr !== 'undefined') {
            toastr.error(mensaje);
        } else {
            alert(mensaje);
        }
    }

    // Escuchar cambios en la búsqueda con debounce (250ms)
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        
        // Evitar buscar si el término de búsqueda no ha cambiado realmente
        if (query === currentQuery) return;
        currentQuery = query;

        clearTimeout(timer);
        timer = setTimeout(() => {
            fetchProducts(window.location.origin + window.location.pathname, query);
        }, 250);
    });

    // Escuchar la paginación dinámicamente usando delegación de eventos
    tableContainer.addEventListener('click', (e) => {
        const pageLink = e.target.closest('#pagination-links a');
        if (!pageLink) return;

        e.preventDefault();
        const url = pageLink.getAttribute('href');
        if (url) {
            fetchProducts(url, searchInput.value.trim());
            // Hacer scroll suave hacia el tope de la tarjeta
            document.querySelector('.card').scrollIntoView({ behavior: 'smooth' });
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
