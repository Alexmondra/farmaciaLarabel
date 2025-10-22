@extends('adminlte::page')
@section('title', 'Nueva Categoría')
@section('content_header')
<h1><i class="fas fa-plus-circle mr-2"></i>Nueva Categoría</h1>
@stop
@section('content')
@include('inventario.categorias._form', [
'route' => route('inventario.categorias.store'),
'method' => 'POST',
'submitText' => 'Guardar',
'categoria' => new \App\Models\Inventario\Categoria(['activo' => true])
])
@stop