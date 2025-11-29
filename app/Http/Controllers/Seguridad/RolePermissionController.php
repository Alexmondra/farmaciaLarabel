<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;



class RolePermissionController extends Controller
{
    public function index(Request $request)
    {
        $selectedRoleId = $request->query('role');
        $roles = Role::orderBy('name')->get();

        // Filtramos solo permisos que existan (por si quedaron sucios en BD)
        $permisos = Permission::orderBy('name')->get();

        $selectedRole = $selectedRoleId
            ? $roles->firstWhere('id', (int)$selectedRoleId)
            : $roles->first();

        $selectedRolePermissions = $selectedRole
            ? $selectedRole->permissions->pluck('name')->toArray()
            : [];

        return view('seguridad.roles.index', compact(
            'roles',
            'permisos',
            'selectedRole',
            'selectedRolePermissions'
        ));
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')],
        ]);

        $role = Role::create(['name' => $validated['name']]);

        // Redirigir al rol recién creado para asignarle permisos
        return redirect()
            ->route('seguridad.roles.index', ['role' => $role->id])
            ->with('success', 'Rol creado. Ahora asígnale permisos.');
    }

    public function destroyRole(Role $role)
    {
        if ($role->name === 'Administrador') {
            return back()->with('error', 'El rol Administrador es intocable.');
        }

        $role->delete();
        return redirect()->route('seguridad.roles.index')->with('success', 'Rol eliminado.');
    }

    // --- FUNCIÓN storePermission ELIMINADA --- 
    // Los permisos se gestionan desde el Seeder.

    public function syncRolePermissions(Request $request, Role $role)
    {
        $data = $request->validate([
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($data['permisos'] ?? []);

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', "Permisos actualizados para: {$role->name}");
    }

    public function revokePermissionFromRole(Role $role, Permission $permission)
    {
        $role->revokePermissionTo($permission);
        return back()->with('success', "Permiso quitado.");
    }
}
