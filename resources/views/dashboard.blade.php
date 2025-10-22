@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<h1>Panel de Farmacia</h1>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <x-adminlte-small-box title="Productos" text="Inventario activo" icon="fas fa-capsules" />
    </div>
    <div class="col-md-4">
        <x-adminlte-small-box title="Ventas hoy" text="S/ 0.00" icon="fas fa-cash-register" />
    </div>
    <div class="col-md-4">
        <x-adminlte-small-box title="Recetas" text="Pendientes" icon="fas fa-prescription" />
    </div>
</div>
@endsection