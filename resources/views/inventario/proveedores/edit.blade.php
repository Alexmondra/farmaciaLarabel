@extends('adminlte::page')
@section('title', 'Editar Proveedor')
@section('content_header')
<h1><i class="fas fa-edit mr-2"></i>Editar Proveedor</h1>
@stop
@section('content')
@include('inventario.proveedores._form', [
'route' => route('proveedores.update', $proveedor),
'method' => 'PUT',
'submitText' => 'Actualizar',
'proveedor' => $proveedor
])
@stop