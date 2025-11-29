@extends('adminlte::page')

@section('title', 'Dashboard Gerencial')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Tablero de Control</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">

    {{-- BARRA DE FILTRO DE SUCURSAL (Simulación) --}}
    <div class="card card-teal card-outline">
        <div class="card-body py-2">
            <div class="col-md-6 text-right text-muted">
                <small><i class="fas fa-sync-alt fa-spin mr-1"></i> Actualizado hace 1 minuto</small>
            </div>

        </div>
    </div>

    {{-- FILA 1: Resumen Global --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>S/ 5,840.00</h3>
                    <p>Ventas Globales (Hoy)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <a href="#" class="small-box-footer">Ver reporte detallado <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>3 Sucursales</h3>
                    <p>Operando Ahora</p>
                </div>
                <div class="icon">
                    <i class="fas fa-store-alt"></i>
                </div>
                <a href="#" class="small-box-footer">Gestionar locales <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>8</h3>
                    <p>Productos sin Stock (Global)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <a href="#" class="small-box-footer">Ver faltantes <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>12</h3>
                    <p>Traslados entre locales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck-loading"></i>
                </div>
                <a href="#" class="small-box-footer">Ver logística <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    {{-- FILA 2: Detalle por Sucursal y Alertas --}}
    <div class="row">

        {{-- COMPARATIVA DE SUCURSALES --}}
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Rendimiento por Sucursal (Hoy)</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Sucursal</th>
                                <th>Ventas</th>
                                <th>Meta Diaria</th>
                                <th>Progreso</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1. Principal (Centro)</td>
                                <td class="text-success font-weight-bold">S/ 3,200</td>
                                <td>S/ 4,000</td>
                                <td>
                                    <div class="progress progress-xs">
                                        <div class="progress-bar bg-success" style="width: 80%"></div>
                                    </div>
                                    <small>80%</small>
                                </td>
                            </tr>
                            <tr>
                                <td>2. Av. Balta</td>
                                <td class="text-primary font-weight-bold">S/ 1,500</td>
                                <td>S/ 2,000</td>
                                <td>
                                    <div class="progress progress-xs">
                                        <div class="progress-bar bg-primary" style="width: 75%"></div>
                                    </div>
                                    <small>75%</small>
                                </td>
                            </tr>
                            <tr>
                                <td>3. Mall Aventura</td>
                                <td class="text-warning font-weight-bold">S/ 1,140</td>
                                <td>S/ 3,500</td>
                                <td>
                                    <div class="progress progress-xs">
                                        <div class="progress-bar bg-warning" style="width: 30%"></div>
                                    </div>
                                    <small>30% (Bajo)</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ALERTAS DE STOCK CON UBICACIÓN --}}
            <div class="card card-danger card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bullhorn mr-1"></i>
                        Alertas de Stock Crítico
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Ubicación (Sucursal)</th>
                                <th style="width: 40px">Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Paracetamol 500mg</td>
                                <td><span class="badge badge-light">Sede Centro</span></td>
                                <td><span class="badge bg-danger">0</span></td>
                            </tr>
                            <tr>
                                <td>Panadol Antigripal</td>
                                <td><span class="badge badge-light">Mall Aventura</span></td>
                                <td><span class="badge bg-danger">2</span></td>
                            </tr>
                            <tr>
                                <td>Ensure Vainilla</td>
                                <td><span class="badge badge-light">Av. Balta</span></td>
                                <td><span class="badge bg-warning">1</span></td>
                            </tr>
                            <tr>
                                <td>Amoxicilina 500mg</td>
                                <td><span class="badge badge-light">Sede Centro</span></td>
                                <td><span class="badge bg-danger">0</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ÚLTIMAS VENTAS GLOBALES --}}
        <div class="col-md-6">
            <div class="card card-teal card-outline">
                <div class="card-header">
                    <h3 class="card-title">Últimas Ventas (Tiempo Real)</h3>
                    <div class="card-tools">
                        <span class="badge badge-teal">Hoy</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-responsive-sm table-hover">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>Sucursal</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><a href="#">F001-4023</a></td>
                                <td><small class="badge badge-success">Centro</small></td>
                                <td>S/ 145.50</td>
                                <td><i class="fas fa-check-circle text-success"></i></td>
                            </tr>
                            <tr>
                                <td><a href="#">B002-0912</a></td>
                                <td><small class="badge badge-primary">Balta</small></td>
                                <td>S/ 12.00</td>
                                <td><i class="fas fa-check-circle text-success"></i></td>
                            </tr>
                            <tr>
                                <td><a href="#">F003-0101</a></td>
                                <td><small class="badge badge-warning">Mall</small></td>
                                <td>S/ 350.00</td>
                                <td><i class="fas fa-clock text-secondary"></i></td>
                            </tr>
                            <tr>
                                <td><a href="#">B001-4024</a></td>
                                <td><small class="badge badge-success">Centro</small></td>
                                <td>S/ 25.90</td>
                                <td><i class="fas fa-check-circle text-success"></i></td>
                            </tr>
                            <tr>
                                <td><a href="#">B002-0913</a></td>
                                <td><small class="badge badge-primary">Balta</small></td>
                                <td>S/ 8.50</td>
                                <td><i class="fas fa-check-circle text-success"></i></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-center">
                    <a href="#" class="uppercase">Ver todas las transacciones</a>
                </div>
            </div>

            {{-- Widget de Productos Más Vendidos --}}
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">Productos Estrella (Global)</h3>
                    <div class="card-tools">
                        <a href="#" class="btn btn-tool btn-sm">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-valign-middle">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Ventas</th>
                                <th>Tendencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <img src="https://via.placeholder.com/30" alt="Product 1" class="img-circle img-size-32 mr-2">
                                    Panadol Forte
                                </td>
                                <td>S/ 1.50</td>
                                <td>
                                    <small class="text-success mr-1">
                                        <i class="fas fa-arrow-up"></i>
                                        12%
                                    </small>
                                    12,000 Sold
                                </td>
                                <td>
                                    <a href="#" class="text-muted">
                                        <i class="fas fa-search"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="https://via.placeholder.com/30" alt="Product 1" class="img-circle img-size-32 mr-2">
                                    Alcohol 70°
                                </td>
                                <td>S/ 8.00</td>
                                <td>
                                    <small class="text-warning mr-1">
                                        <i class="fas fa-arrow-down"></i>
                                        0.5%
                                    </small>
                                    5,000 Sold
                                </td>
                                <td>
                                    <a href="#" class="text-muted">
                                        <i class="fas fa-search"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop