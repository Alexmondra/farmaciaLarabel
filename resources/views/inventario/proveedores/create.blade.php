@extends('adminlte::page')
@section('title', 'Nuevo Proveedor')
@section('content_header')
<h1><i class="fas fa-plus-circle mr-2"></i>Nuevo Proveedor</h1>
@stop
@section('content')
@include('inventario.proveedores._form', [
'route' => route('inventario.proveedores.store'),
'method' => 'POST',
'submitText' => 'Guardar',
'proveedor' => new \App\Models\Inventario\Proveedor(['activo' => true])
])
@stop