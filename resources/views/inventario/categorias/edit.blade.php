@extends('adminlte::page')
@section('title', 'Editar Categoría')
@section('content_header')
<h1><i class="fas fa-edit mr-2"></i>Editar Categoría</h1>
@stop
@section('content')
@include('inventario.categorias._form', [
'route' => route('inventario.categorias.update', $categoria),
'method' => 'PUT',
'submitText' => 'Actualizar',
'categoria' => $categoria
])
@stop