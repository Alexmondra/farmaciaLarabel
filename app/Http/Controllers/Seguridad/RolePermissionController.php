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
        // Para filtrar y enfocarte en un rol seleccionado (opcional)
        $selectedRoleId = $request->query('role');

        $roles = Role::orderBy('name')->get();
        $permisos = Permission::orderBy('name')->get();

        $selectedRole = $selectedRoleId
            ? $roles->firstWhere('id', (int)$selectedRoleId)
            : $roles->first(); // selecciona el primero por defecto

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
            'name' => ['required','string','max:100', Rule::unique('roles','name')],
        ]);

        $role = Role::create(['name' => $validated['name']]);

        return redirect()
            ->route('seguridad.roles.index', ['role' => $role->id])
            ->with('success', 'Rol creado correctamente.');
    }

    public function destroyRole(Role $role)
    {
        // Evita borrar roles críticos si quieres (ej. Administrador)
        if ($role->name === 'Administrador') {
            return back()->with('error', 'No se puede eliminar el rol Administrador.');
        }

        $role->delete();

        return redirect()->route('seguridad.roles.index')->with('success', 'Rol eliminado.');
    }

    public function storePermission(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:150', Rule::unique('permissions','name')],
        ]);

        Permission::create(['name' => $validated['name'], 'guard_name' => 'web']);

        return back()->with('success', 'Permiso creado correctamente.');
    }

    public function syncRolePermissions(Request $request, Role $role)
    {
        // Recibe un array de nombres de permisos marcados
        $data = $request->validate([
            'permisos' => ['nullable','array'],
            'permisos.*' => ['string','exists:permissions,name'],
        ]);

        $role->syncPermissions($data['permisos'] ?? []);

        // importante: resetear cache de spatie
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('seguridad.roles.index', ['role' => $role->id])
            ->with('success', "Permisos del rol \"{$role->name}\" actualizados.");
    }

    public function revokePermissionFromRole(Role $role, Permission $permission)
    {
        $role->revokePermissionTo($permission);

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', "Permiso \"{$permission->name}\" removido del rol \"{$role->name}\".");
    }
}
