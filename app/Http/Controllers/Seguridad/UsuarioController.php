<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $users = User::query()
            ->with('roles', 'sucursales')
            ->when($q, fn($w) => $w->where(function($s) use ($q){
                $s->where('name','like',"%$q%")
                  ->orWhere('email','like',"%$q%");
            }))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('seguridad.usuarios.index', compact('users','q'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $sucursales = \App\Models\Sucursal::orderBy('nombre')->get();
        return view('seguridad.usuarios.create', compact('roles','sucursales'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required','string','max:120'],
            'email'    => ['required','email','max:150','unique:users,email'],
            'password' => ['required','confirmed','min:6'],
            'roles'    => ['nullable','array'],
            'roles.*'  => ['string','exists:roles,name'],
            'sucursales'   => ['nullable','array'],
            'sucursales.*' => ['integer','exists:sucursales,id'],

        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email'=> $validated['email'],
            'password' => Hash::make($validated['password']),
            
        ]);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        if (!empty($validated['sucursales'])) {
            $user->sucursales()->sync($validated['sucursales']);
        }

        return redirect()->route('seguridad.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        $roles = Role::orderBy('name')->get();
        $userRoles = $usuario->roles->pluck('name')->toArray();
        $sucursales = \App\Models\Sucursal::orderBy('nombre')->get();
        $userSucursales = $usuario->sucursales->pluck('id')->toArray();

        return view('seguridad.usuarios.edit', [
            'usuario' => $usuario,
            'roles' => $roles,
            'userRoles' => $userRoles,
            'sucursales' => $sucursales,
            'userSucursales' => $userSucursales,
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name'     => ['required','string','max:120'],
            'email'    => ['required','email','max:150', Rule::unique('users','email')->ignore($usuario->id)],
            'password' => ['nullable','confirmed','min:6'],
            'roles'    => ['nullable','array'],
            'roles.*'  => ['string','exists:roles,name'],
            'sucursales'   => ['nullable','array'],
            'sucursales.*' => ['integer','exists:sucursales,id'],
        ]);

        $usuario->name  = $validated['name'];
        $usuario->email = $validated['email'];
        $usuario->sucursales()->sync($validated['sucursales'] ?? []);
        
        if (!empty($validated['password'])) {
            $usuario->password = Hash::make($validated['password']);
        }
        $usuario->save();

        $usuario->syncRoles($validated['roles'] ?? []);

        return redirect()->route('seguridad.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario)
    {
        // Evita que un usuario se elimine a sí mismo
        if (auth()->id() === $usuario->id) {
            return back()->with('error','No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();
        return redirect()->route('seguridad.usuarios.index')
            ->with('success','Usuario eliminado.');
    }
}
