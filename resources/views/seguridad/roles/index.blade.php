@extends('adminlte::page')

@section('title', 'Roles y Permisos')

@section('content_header')
<h1>Roles y Permisos</h1>
@stop

@section('content')
<div class="row">

    {{-- ========================================================== --}}
    {{-- COLUMNA IZQUIERDA: LISTA DE ROLES --}}
    {{-- ========================================================== --}}
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-tag mr-2"></i> Roles del Sistema</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($roles as $r)
                    <li class="list-group-item d-flex justify-content-between align-items-center {{ $selectedRole && $selectedRole->id === $r->id ? 'bg-light font-weight-bold' : '' }}">

                        {{-- Solo mostramos enlace si tiene permiso de ver --}}
                        <a href="{{ route('seguridad.roles.index', ['role' => $r->id]) }}" class="text-dark" style="text-decoration: none; width: 70%;">
                            <i class="fas fa-user-shield mr-2 text-primary"></i> {{ $r->name }}
                        </a>

                        <div class="d-flex align-items-center">
                            @if($r->name !== 'Administrador')
                            @can('roles.eliminar')
                            <form method="POST" action="{{ route('seguridad.roles.destroy', $r) }}"
                                onsubmit="return confirm('⚠️ ¿Estás seguro de ELIMINAR el rol {{ $r->name }}?');"
                                style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm text-danger" title="Eliminar Rol">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                            @endcan
                            @else
                            <span class="badge badge-secondary" title="Rol protegido"><i class="fas fa-lock"></i> Sistema</span>
                            @endif
                        </div>
                    </li>
                    @empty
                    <li class="list-group-item text-muted text-center py-4">
                        No hay roles registrados.
                    </li>
                    @endforelse
                </ul>
            </div>

            {{-- Formulario CREAR ROL --}}
            @can('roles.crear')
            <div class="card-footer bg-light">
                <form method="POST" action="{{ route('seguridad.roles.store') }}">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="name" class="form-control" placeholder="Nuevo rol..." required>
                        <span class="input-group-append">
                            <button class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                            </button>
                        </span>
                    </div>
                </form>
            </div>
            @endcan
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- COLUMNA DERECHA: GESTIÓN DE PERMISOS --}}
    {{-- ========================================================== --}}
    @can('permisos.ver')
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                @if($selectedRole)
                <h3 class="card-title mt-1">
                    Permisos para: <span class="text-primary font-weight-bold">{{ $selectedRole->name }}</span>
                </h3>
                @else
                <span class="text-muted">Selecciona un rol a la izquierda</span>
                @endif
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" id="permissionSearchInput" class="form-control" placeholder="Buscar permiso..." {{ !$selectedRole ? 'disabled' : '' }}>
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            @if($selectedRole)
            @php
            // Agrupar permisos por prefijo (ej: users.crear -> users)
            $grouped = $permisos->groupBy(function($p){
            return explode('.', $p->name)[0] ?? 'otros';
            });
            $revokeTargets = [];

            $canAsignar = auth()->user()->can('permisos.asignar');
            $canRevocar = auth()->user()->can('permisos.revocar');
            @endphp

            {{-- FORMULARIO DE SINCRONIZACIÓN (ASIGNAR) --}}
            <form method="POST" action="{{ route('seguridad.roles.permisos.sync', $selectedRole->id) }}">
                @csrf
                <div class="card-body p-3" id="permissionsContainer" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row">
                        @foreach($grouped as $grupo => $lista)
                        <div class="col-md-6">
                            <div class="card card-outline card-secondary mb-3 shadow-none border">
                                <div class="card-header py-1 px-3 bg-light">
                                    <strong class="text-uppercase text-xs text-muted">{{ $grupo }}</strong>
                                </div>

                                <div class="card-body p-2">
                                    @foreach($lista as $perm)
                                    @php
                                    $isChecked = in_array($perm->name, $selectedRolePermissions);

                                    // Si está marcado, lo guardamos para generar el form de revocar individual abajo
                                    if ($isChecked) {
                                    $revokeTargets[] = [
                                    'role_id' => $selectedRole->id,
                                    'perm_id' => $perm->id,
                                    'perm_name' => $perm->name
                                    ];
                                    }
                                    @endphp

                                    <div class="d-flex justify-content-between align-items-center mb-1 px-1 rounded hover-bg">
                                        {{-- CHECKBOX (ASIGNAR) --}}
                                        <div class="custom-control custom-checkbox">
                                            <input
                                                type="checkbox"
                                                class="custom-control-input"
                                                id="perm_{{ $perm->id }}"
                                                name="permisos[]"
                                                value="{{ $perm->name }}"
                                                {{ $isChecked ? 'checked' : '' }}
                                                {{-- Si NO tiene permiso de asignar, deshabilitamos el input --}}
                                                {{ !$canAsignar ? 'disabled' : '' }}>

                                            <label class="custom-control-label font-weight-normal {{ $isChecked ? 'text-dark' : 'text-secondary' }}"
                                                for="perm_{{ $perm->id }}"
                                                style="font-size: 0.9rem; cursor: pointer;">
                                                {{ $perm->name }}
                                            </label>
                                        </div>

                                        {{-- BOTÓN REVOCAR (SOLO SI ESTÁ ASIGNADO Y TIENE PERMISO) --}}
                                        @if($isChecked && $canRevocar)
                                        @php $formId = "revoke-{$selectedRole->id}-{$perm->id}"; @endphp
                                        <button
                                            type="submit"
                                            form="{{ $formId }}"
                                            class="btn btn-xs btn-default text-danger border-0"
                                            title="Revocar permiso"
                                            onclick="return confirm('¿Quitar {{ $perm->name }} de {{ $selectedRole->name }}?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- FOOTER CON BOTÓN GUARDAR (SOLO SI PUEDE ASIGNAR) --}}
                @if($canAsignar)
                <div class="card-footer bg-light text-right">
                    <button class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Guardar Cambios
                    </button>
                </div>
                @endif
            </form>

            {{-- FORMS OCULTOS PARA REVOCAR INDIVIDUALMENTE --}}
            @foreach($revokeTargets as $t)
            <form id="revoke-{{ $t['role_id'] }}-{{ $t['perm_id'] }}"
                class="d-none"
                method="POST"
                action="{{ route('seguridad.roles.permisos.revoke', ['role' => $t['role_id'], 'permission' => $t['perm_id']]) }}">
                @csrf
                @method('DELETE')
            </form>
            @endforeach

            @else
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-muted" style="height: 300px;">
                <i class="fas fa-user-tag fa-3x mb-3"></i>
                <h5>Selecciona un rol para gestionar sus permisos</h5>
            </div>
            @endif
        </div>
    </div>
    @endcan

</div>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const permissionSearchInput = document.getElementById('permissionSearchInput');
        const permissionsContainer = document.getElementById('permissionsContainer');

        if (permissionSearchInput && permissionsContainer) {
            const permissionCards = permissionsContainer.querySelectorAll('.card'); // Tarjetas de grupos

            permissionSearchInput.addEventListener('keyup', function(event) {
                const searchTerm = event.target.value.toLowerCase();

                permissionCards.forEach(card => {
                    const checkboxes = card.querySelectorAll('.d-flex'); // Contenedores de cada permiso
                    let hasVisible = false;

                    checkboxes.forEach(box => {
                        const label = box.querySelector('label').textContent.toLowerCase();
                        if (label.includes(searchTerm)) {
                            box.style.display = 'flex'; // Usamos flex para mantener alineación
                            box.classList.remove('d-none');
                            hasVisible = true;
                        } else {
                            box.style.display = 'none';
                            box.classList.add('d-none');
                        }
                    });

                    if (hasVisible) {
                        card.parentElement.style.display = '';
                    } else {
                        card.parentElement.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
<style>
    .hover-bg:hover {
        background-color: #f4f6f9;
    }
</style>
@stop