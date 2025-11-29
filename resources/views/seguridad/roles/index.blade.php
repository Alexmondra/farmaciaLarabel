@extends('adminlte::page')

@section('title', 'Roles y Permisos')

@section('content_header')
<h1>Roles y Permisos</h1>
@stop

@section('content')
<div class="row">

    {{-- Columna izquierda: lista de roles + crear/eliminar --}}
    {{-- Columna izquierda: lista de roles + crear --}}
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-tag mr-2"></i> Roles del Sistema</h3>
            </div>
            <div class="card-body p-0">
                @can('roles.ver')
                <ul class="list-group list-group-flush">
                    @forelse($roles as $r)
                    <li class="list-group-item d-flex justify-content-between align-items-center {{ $selectedRole && $selectedRole->id === $r->id ? 'bg-light font-weight-bold' : '' }}">

                        {{-- Enlace para seleccionar el rol --}}
                        <a href="{{ route('seguridad.roles.index', ['role' => $r->id]) }}" class="text-dark" style="text-decoration: none; width: 80%;">
                            <i class="fas fa-user-shield mr-2 text-primary"></i> {{ $r->name }}
                        </a>

                        {{-- Botón de Eliminar (Protegido para NO borrar al Admin) --}}
                        @if($r->name !== 'Administrador')
                        <form method="POST" action="{{ route('seguridad.roles.destroy', $r) }}"
                            onsubmit="return confirm('⚠️ ¿Estás seguro de ELIMINAR el rol {{ $r->name }}?\n\n- Se quitará a todos los usuarios que lo tengan.\n- Esta acción es irreversible.');"
                            style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm text-danger p-0" title="Eliminar este rol">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                        @else
                        {{-- Candado para el Admin --}}
                        <span class="text-muted" title="Rol de Sistema (No se puede borrar)">
                            <i class="fas fa-lock"></i>
                        </span>
                        @endif
                    </li>
                    @empty
                    <li class="list-group-item text-muted text-center py-4">
                        <i class="fas fa-ghost mb-2 d-block" style="font-size: 20px;"></i>
                        No hay roles registrados.
                    </li>
                    @endforelse
                </ul>
                @endcan
            </div>

            {{-- Formulario para CREAR ROL (Esto sí lo dejamos) --}}
            <div class="card-footer bg-light">
                @can('roles.crear')
                <form method="POST" action="{{ route('seguridad.roles.store') }}">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="name" class="form-control" placeholder="Nombre del nuevo rol..." required>
                        <span class="input-group-append">
                            <button class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                            </button>
                        </span>
                    </div>
                </form>
                @endcan
            </div>
        </div>

    </div>

    {{-- Columna derecha: permisos del rol seleccionado --}}

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                @if($selectedRole)
                Permisos del rol: <strong>{{ $selectedRole->name }}</strong>
                @else
                Selecciona un rol
                @endif
            </div>
            <div class="card-body border-bottom py-2">
                <input type="text" id="permissionSearchInput" class="form-control form-control-sm" placeholder="Buscar permiso..." {{ !$selectedRole ? 'disabled' : '' }}>
            </div>
            {{-- resources/views/seguridad/roles/index.blade.php --}}

            @if($selectedRole)
            @php
            // Agrupar permisos por prefijo antes del punto
            $grouped = $permisos->groupBy(function($p){
            return explode('.', $p->name)[0] ?? 'otros';
            });

            // Coleccionamos a quiénes les haremos forms DELETE fuera del form principal
            $revokeTargets = [];
            @endphp

            {{-- FORM PRINCIPAL: Sincronizar permisos del rol (POST) --}}
            <form method="POST" action="{{ route('seguridad.roles.permisos.sync', $selectedRole->id) }}">
                @csrf

                <div class="card-body" id="permissionsContainer">
                    <div class="row">
                        @foreach($grouped as $grupo => $lista)
                        <div class="col-md-6">
                            <div class="card card-outline card-info mb-3">
                                <div class="card-header py-2">
                                    <strong class="text-uppercase">{{ $grupo }}</strong>
                                </div>

                                <div class="card-body">
                                    @foreach($lista as $perm)
                                    @php
                                    $checked = in_array($perm->name, $selectedRolePermissions);
                                    if ($checked) {
                                    // Guardamos destino para pintar su form DELETE luego (fuera del form principal)
                                    $revokeTargets[] = [
                                    'role_id' => $selectedRole->id,
                                    'role_name' => $selectedRole->name,
                                    'perm_id' => $perm->id,
                                    'perm_name' => $perm->name,
                                    ];
                                    }
                                    @endphp

                                    <div class="custom-control custom-checkbox mb-1 d-flex align-items-center">
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="perm_{{ $perm->id }}"
                                            name="permisos[]"
                                            value="{{ $perm->name }}"
                                            {{ $checked ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="perm_{{ $perm->id }}">
                                            {{ $perm->name }}
                                        </label>

                                        {{-- Botón "Quitar" individual: envía el form oculto por ID con method=DELETE --}}
                                        @if($checked)
                                        @php
                                        $formId = "revoke-{$selectedRole->id}-{$perm->id}";
                                        @endphp
                                        <button
                                            type="submit"
                                            form="{{ $formId }}"
                                            class="btn btn-xs btn-outline-danger ml-2"
                                            title="Quitar este permiso del rol"
                                            onclick="return confirm('Quitar {{ $perm->name }} del rol {{ $selectedRole->name }}?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div> {{-- .row --}}
                </div> {{-- .card-body --}}

                <div class="card-footer">
                    <button class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar permisos del rol
                    </button>
                </div>
            </form>

            {{-- FORMS OCULTOS (FUERA DEL FORM PRINCIPAL): uno por cada "Quitar" --}}
            @foreach($revokeTargets as $t)
            @php
            $formId = "revoke-{$t['role_id']}-{$t['perm_id']}";
            @endphp
            <form id="{{ $formId }}"
                class="d-none"
                method="POST"
                action="{{ route('seguridad.roles.permisos.revoke', ['role' => $t['role_id'], 'permission' => $t['perm_id']]) }}">
                @csrf
                @method('DELETE')
                {{-- No inputs extra: solo spoofing DELETE + CSRF --}}
            </form>
            @endforeach

            @else
            <div class="card-body">
                <p>Elige un rol en la columna izquierda para gestionar sus permisos.</p>
            </div>
            @endif

        </div>
    </div>

</div>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // --- BUSCADOR DE PERMISOS ---
        const permissionSearchInput = document.getElementById('permissionSearchInput');
        const permissionsContainer = document.getElementById('permissionsContainer');

        if (permissionSearchInput && permissionsContainer) {
            const permissionGroups = permissionsContainer.querySelectorAll('.card.card-outline');

            permissionSearchInput.addEventListener('keyup', function(event) {
                const searchTerm = event.target.value.toLowerCase();

                permissionGroups.forEach(group => {
                    const permissionCheckboxes = group.querySelectorAll('.custom-control.custom-checkbox');
                    let groupHasVisiblePermission = false;

                    permissionCheckboxes.forEach(checkboxDiv => {
                        const label = checkboxDiv.querySelector('label').textContent.toLowerCase();
                        if (label.includes(searchTerm)) {
                            checkboxDiv.style.display = '';
                            groupHasVisiblePermission = true;
                        } else {
                            checkboxDiv.style.display = 'none';
                        }
                    });
                    group.style.display = groupHasVisiblePermission ? '' : 'none';
                });
            });
        }
    });
</script>
@stop