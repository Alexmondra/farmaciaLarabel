<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use App\Providers\RouteServiceProvider;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Cargamos la relación muchos-a-muchos 'sucursales'
        $user->load('sucursales');

        // 2. Limpiamos la sesión de sucursal
        $request->session()->forget(['sucursal_id', 'sucursal_nombre']);

        // 3. Si es Administrador, entra sin sucursal fija
        if ($user->hasRole('Administrador')) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $cantidad = $user->sucursales->count();

        // 4. Si no tiene sucursales
        if ($cantidad === 0) {
            Auth::logout();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'No tienes sucursales asignadas. Contacta al administrador.',
                ]);
        }

        // 5. Si solo tiene 1 sucursal, la asignamos directa
        if ($cantidad === 1) {
            $sucursal = $user->sucursales->first();

            $request->session()->put('sucursal_id', $sucursal->id);
            $request->session()->put('sucursal_nombre', $sucursal->nombre);

            return redirect()->intended(route('dashboard', absolute: false));
        }

        // 6. Si tiene 2 o más sucursales: ir a la pantalla de elección
        return redirect()->route('sucursales.elegir');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
