@extends('adminlte::page')

@section('title', 'Directorio de Clientes')

@section('content')

{{-- 1. CARGAMOS CSS (MODULARIZADO) --}}
@include('ventas.clientes.partials.css')

<div class="container-fluid pt-4">

    {{-- HEADER CON PERMISO DE CREAR --}}
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="font-weight-bold mb-0">
                <i class="fas fa-users text-info mr-2"></i>Directorio de Clientes
            </h2>
            <p class="text-muted mb-0">Gestiona tu cartera de pacientes y empresas.</p>
        </div>
        <div class="col-md-6 text-right">
            @can('clientes.crear')
            <button class="btn btn-new-client" onclick="openCreateModal()">
                <i class="fas fa-plus mr-2"></i> Nuevo Cliente
            </button>
            @endcan
        </div>
    </div>

    {{-- KPIS Y FILTROS --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-6 mb-2">
            <div class="filter-card active" onclick="setFilter('all', this)">
                <div class="d-flex align-items-center">
                    <i class="fas fa-layer-group filter-icon mr-3"></i>
                    <div>
                        <div class="filter-title">Total Clientes</div>
                        <span class="filter-count">{{ $total ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6 mb-2">
            <div class="filter-card" onclick="setFilter('persona', this)">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-injured filter-icon mr-3"></i>
                    <div>
                        <div class="filter-title">Personas</div>
                        <span class="filter-count">{{ $personas ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6 mb-2">
            <div class="filter-card" onclick="setFilter('RUC', this)">
                <div class="d-flex align-items-center">
                    <i class="fas fa-building filter-icon mr-3"></i>
                    <div>
                        <div class="filter-title">Empresas</div>
                        <span class="filter-count">{{ $empresas ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6 mb-2">
            <div class="filter-card bonus-card position-relative" style="background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%); color: white; border:none;">
                @can('config.editar')
                <button class="btn btn-sm btn-light position-absolute"
                    onclick="openConfigModal()"
                    style="top: 10px; right: 10px; border-radius: 50%; width: 30px; height: 30px; padding: 0; color: #6c5ce7;">
                    <i class="fas fa-pencil-alt" style="font-size: 0.8rem;"></i>
                </button>
                @endcan


                <div class="d-flex align-items-center">
                    <i class="fas fa-coins mr-3" style="font-size: 2rem; opacity: 0.8;"></i>
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; opacity: 0.9;">Regla de Canje</div>
                        <div class="font-weight-bold" style="font-size: 1.1rem;">
                            <span id="lbl_puntos">100</span> Pts = <span id="lbl_moneda">S/ 2.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BUSCADOR --}}
    <div class="row justify-content-center mb-4">
        <div class="col-md-10">
            <div class="position-relative">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="searchInput" class="form-control search-input" placeholder="Buscar por Nombre, DNI, RUC o Puntos...">
            </div>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="row">
        <div class="col-md-12">
            <div class="table-card position-relative">
                <div id="table-container" class="p-0">
                    @include('ventas.clientes.partials.table')
                </div>
                <div class="overlay d-none" id="loadingOverlay" style="position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.7); z-index:10; display:flex; justify-content:center; align-items:center;">
                    <div class="text-center">
                        <i class="fas fa-circle-notch fa-spin fa-3x text-info"></i>
                        <p class="mt-2 font-weight-bold">Buscando...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- MODALES --}}
@include('ventas.clientes.partials.modal_config_puntos')
@include('ventas.clientes.modal-create-edit')
@include('ventas.clientes.modal-show')

@endsection

@section('js')
{{-- 2. CARGAMOS JS (MODULARIZADO) --}}
@include('ventas.clientes.partials.scripts')
@endsection