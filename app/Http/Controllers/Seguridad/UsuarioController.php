<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    /**
     * Listado de usuarios
     */
    public function index(Request $request)
    {
        // Traemos relaciones para evitar N+1 queries
        $users = User::with('roles', 'sucursales')->orderBy('id', 'desc')->get();
        return view('seguridad.usuarios.index', compact('users'));
    }

    /**
     * Formulario de Creación
     */
    public function create()
    {
        $roles = Role::all();
        // Solo mostramos sucursales activas para asignar
        $sucursales = Sucursal::where('activo', true)->get();
        return view('seguridad.usuarios.create', compact('roles', 'sucursales'));
    }

    /**
     * Guardar Nuevo Usuario
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'password'  => 'required|confirmed|min:8', // Password obligatorio al crear
            'documento' => 'nullable|string|max:20|unique:users',
            'telefono'  => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:200',
            'imagen_perfil' => 'nullable|image|max:2048', // Max 2MB
            'sucursales' => 'nullable|array',
            'roles'     => 'nullable|array',
        ]);

        $data = $request->only(['name', 'email', 'documento', 'telefono', 'direccion']);
        $data['password'] = $request->password;
        $data['activo'] = true; // Por defecto nace activo

        // Subida de Imagen
        if ($request->hasFile('imagen_perfil')) {
            $data['imagen_perfil'] = $request->file('imagen_perfil')->store('usuarios', 'local');
        }

        $user = User::create($data);

        // Asignación de Roles y Sucursales
        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        if ($request->has('sucursales')) {
            $user->sucursales()->sync($request->sucursales);
        }

        return redirect()->route('seguridad.usuarios.index')
            ->with('success', 'Usuario registrado exitosamente.');
    }

    /**
     * Formulario de Edición (Perfil)
     */
    public function edit(User $usuario)
    {
        $roles = Role::all();
        $sucursales = Sucursal::where('activo', true)->get();

        // Arrays simples de IDs para marcar los checkboxes en la vista
        $userRoles = $usuario->roles->pluck('name')->toArray();
        $userSucursales = $usuario->sucursales->pluck('id')->toArray();

        return view('seguridad.usuarios.edit', compact('usuario', 'roles', 'sucursales', 'userRoles', 'userSucursales'));
    }

    /**
     * Actualizar Usuario
     */
    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', Rule::unique('users')->ignore($usuario->id)],
            'documento' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($usuario->id)],
            'telefono'  => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:200',
            'imagen_perfil' => 'nullable|image|max:2048',
            'sucursales' => 'nullable|array',
            'roles'     => 'nullable|array',
            // 'activo' no se valida aquí porque viene del checkbox, lo manejamos manual abajo
        ]);

        $data = $request->only(['name', 'email', 'documento', 'telefono', 'direccion']);
        $data['activo'] = $request->has('activo');

        // Actualizar Imagen (Borrar vieja si existe nueva)
        if ($request->hasFile('imagen_perfil')) {
            if ($usuario->imagen_perfil) {
                Storage::disk('local')->delete($usuario->imagen_perfil);
            }
            $data['imagen_perfil'] = $request->file('imagen_perfil')->store('usuarios', 'local');
        }
        $usuario->update($data);

        // Sincronizar Relaciones (Si mandan vacío, se quitan todos los roles/sucursales)
        $usuario->roles()->sync($request->input('roles', []));
        $usuario->sucursales()->sync($request->input('sucursales', []));

        return redirect()->route('seguridad.usuarios.index')
            ->with('success', 'Datos del usuario actualizados.');
    }

    /**
     * Eliminar Usuario (Solo borrado lógico o físico según prefieras)
     */
    public function destroy(User $usuario)
    {
        if (auth()->id() == $usuario->id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        // CAMBIO AQUÍ: Borrar del disco 'local'
        if ($usuario->imagen_perfil) {
            Storage::disk('local')->delete($usuario->imagen_perfil);
        }

        $usuario->delete();
        return redirect()->route('seguridad.usuarios.index')->with('success', 'Usuario eliminado.');
    }

    /**
     * MÉTODO PERSONALIZADO: Resetear Contraseña
     */
    public function resetPassword(User $usuario)
    {
        $usuario->password = '12345678';
        $usuario->update();
        return back()->with('success', 'Contraseña restablecida correctamente a: 12345678');
    }

    public function mostrarImagen(User $usuario)
    {
        abort_unless(auth()->id() === $usuario->id || auth()->user()->hasRole('Administrador'), 403);

        $ruta = storage_path('app/private/' . $usuario->imagen_perfil);

        if (!file_exists($ruta)) {
            $ruta = storage_path('app/' . $usuario->imagen_perfil);
        }

        return file_exists($ruta) ? response()->file($ruta) : abort(404);
    }


    /// para editar cada quien a su usuario 

    // Muestra la vista de editar perfil (similar a edit, pero limitada)
    public function miPerfil()
    {
        $user = auth()->user();
        return view('seguridad.usuarios.mi_perfil', compact('user'));
    }

    // Guarda los cambios del propio usuario
    public function updateMiPerfil(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', \Illuminate\Validation\Rule::unique('users')->ignore($user->id)],
            'documento' => ['nullable', 'string', 'max:20', \Illuminate\Validation\Rule::unique('users')->ignore($user->id)],
            'telefono'  => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:200',
            'password'  => 'nullable|confirmed|min:8',
            'imagen_perfil' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['name', 'email', 'documento', 'telefono', 'direccion']);

        // 1. CORRECCIÓN DE CONTRASEÑA:
        // Quitamos Hash::make() porque tu modelo User ya tiene 'casts' => 'hashed'.
        // Si lo dejas, se encriptará doble y no podrás entrar.
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        // 2. Lógica de Imagen (Privada)
        if ($request->hasFile('imagen_perfil')) {
            if ($user->imagen_perfil) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($user->imagen_perfil);
            }
            $data['imagen_perfil'] = $request->file('imagen_perfil')->store('usuarios', 'local');
        }

        $user->update($data);

        // 3. CAMBIO SOLICITADO: Redirigir al Dashboard
        return redirect()->route('dashboard')
            ->with('success', 'Tu perfil se ha actualizado correctamente.');
    }
}
