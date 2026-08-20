@extends('tienda.layout')

@section('title', 'Redireccionando...')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-md-5 text-center py-5">
        <div class="spinner-border text-emerald-600 mb-3" role="status" style="width: 3rem; height: 3rem; border-width: 0.25em;"></div>
        <h2 class="h5 font-extrabold text-slate-800 mb-2">Redireccionando al registro seguro...</h2>
        <p class="text-xs text-slate-400 font-medium">Por favor, espera un momento mientras te conectamos.</p>
    </div>
</div>

<script>
    window.location.href = "{{ route('tienda.login', ['tab' => 'register']) }}";
</script>
@endsection
