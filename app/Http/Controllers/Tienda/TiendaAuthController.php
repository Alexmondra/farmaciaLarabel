<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\Ventas\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TiendaAuthController extends Controller
{
    public function loginForm()
    {
        return view('tienda.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string'],
        ]);

        $login = trim($request->input('login'));

        $cliente = Cliente::where('activo', true)
            ->where(function ($query) use ($login) {
                $query->where('documento', $login)
                    ->orWhere('email', $login)
                    ->orWhere('telefono', $login);
            })
            ->first();

        if (!$cliente || !Hash::check($request->password, $cliente->tienda_password)) {
            throw ValidationException::withMessages([
                'login' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        Auth::guard('tienda')->login($cliente, $request->boolean('remember'));

        $cliente->update(['tienda_last_login_at' => now()]);

        $request->session()->regenerate();

        $carritoCtrl = app(CarritoController::class);
        $carritoCtrl->cargarDeBD();
        $carritoCtrl->sincronizarBD();

        return redirect()->intended(route('tienda.index'))
            ->with('success', 'Bienvenido, ' . $cliente->nombre_completo);
    }

    public function registerForm()
    {
        return view('tienda.auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'tipo_documento' => ['required', Rule::in(['DNI', 'RUC', 'CE'])],
            'documento' => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($request) {
                    $tipo = $request->input('tipo_documento');
                    $len = strlen($value);
                    if ($tipo === 'DNI' && $len !== 8) {
                        $fail('El DNI debe tener 8 digitos.');
                    }
                    if ($tipo === 'RUC' && $len !== 11) {
                        $fail('El RUC debe tener 11 digitos.');
                    }
                },
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $documento = trim($data['documento']);
        if (in_array($data['tipo_documento'] ?? '', ['DNI', 'RUC'])) {
            $documento = preg_replace('/\D/', '', $documento);
        }
        $clienteExistente = Cliente::where('documento', $documento)->first();

        if ($clienteExistente) {
            if ($clienteExistente->tienda_password) {
                throw ValidationException::withMessages([
                    'documento' => 'Este documento ya tiene una cuenta activa. Inicia sesion.',
                ]);
            }

            if ($data['email'] && $data['email'] !== $clienteExistente->email) {
                $emailTomado = Cliente::where('email', $data['email'])
                    ->where('id', '!=', $clienteExistente->id)
                    ->exists();
                if ($emailTomado) {
                    throw ValidationException::withMessages([
                        'email' => 'Este correo ya esta registrado por otro cliente.',
                    ]);
                }
            }

            if ($data['telefono'] && $data['telefono'] !== $clienteExistente->telefono) {
                $telefonoTomado = Cliente::where('telefono', $data['telefono'])
                    ->where('id', '!=', $clienteExistente->id)
                    ->exists();
                if ($telefonoTomado) {
                    throw ValidationException::withMessages([
                        'telefono' => 'Este telefono ya esta registrado por otro cliente.',
                    ]);
                }
            }

            $clienteExistente->update([
                'tienda_password' => Hash::make($data['password']),
                'email' => $data['email'] ?? $clienteExistente->email,
                'telefono' => $data['telefono'] ?? $clienteExistente->telefono,
                'activo' => true,
            ]);

            $cliente = $clienteExistente;
            $mensaje = 'Cuenta activada. Bienvenido, ' . $cliente->nombre_completo;
        } else {
            if ($data['email']) {
                $emailTomado = Cliente::where('email', $data['email'])->exists();
                if ($emailTomado) {
                    throw ValidationException::withMessages([
                        'email' => 'Este correo ya esta registrado.',
                    ]);
                }
            }

            if ($data['telefono']) {
                $telefonoTomado = Cliente::where('telefono', $data['telefono'])->exists();
                if ($telefonoTomado) {
                    throw ValidationException::withMessages([
                        'telefono' => 'Este telefono ya esta registrado.',
                    ]);
                }
            }

            $cliente = Cliente::create([
                'documento' => $documento,
                'tipo_documento' => $data['tipo_documento'],
                'nombre' => $data['nombre'],
                'email' => $data['email'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'tienda_password' => Hash::make($data['password']),
                'activo' => true,
            ]);

            $mensaje = 'Cuenta creada. Bienvenido, ' . $cliente->nombre_completo;
        }

        Auth::guard('tienda')->login($cliente);

        $request->session()->regenerate();

        $carritoCtrl = app(CarritoController::class);
        $carritoCtrl->cargarDeBD();
        $carritoCtrl->sincronizarBD();

        return redirect()->intended(route('tienda.index'))->with('success', $mensaje);
    }

    public function checkDocumento(Request $request)
    {
        $request->validate([
            'doc' => ['required', 'string', 'max:20'],
        ]);

        $documento = trim($request->input('doc'));
        $longitud = strlen($documento);

        $cliente = Cliente::where('documento', $documento)->first();

        if ($cliente) {
            return response()->json([
                'found' => true,
                'data' => [
                    'tipo_documento' => $cliente->tipo_documento,
                    'documento' => $cliente->documento,
                    'nombre' => $cliente->nombre,
                    'apellidos' => $cliente->apellidos,
                    'razon_social' => $cliente->razon_social,
                    'nombre_completo' => $cliente->nombre_completo,
                    'email' => $cliente->email,
                    'telefono' => $cliente->telefono,
                    'direccion' => $cliente->direccion,
                    'tiene_cuenta' => !empty($cliente->tienda_password),
                ],
            ]);
        }

        $token = config('services.datos.key');
        $url = null;
        $tipoDoc = null;

        if ($longitud === 8) {
            $url = config('services.datos.dni_url') . $documento;
            $tipoDoc = 'DNI';
        } elseif ($longitud === 11) {
            $url = config('services.datos.ruc_url') . $documento;
            $tipoDoc = 'RUC';
        }

        if (!$url) {
            return response()->json(['found' => false, 'message' => 'El documento debe tener 8 (DNI) o 11 (RUC) digitos.']);
        }

        try {
            $response = Http::timeout(15)
                ->withoutVerifying()
                ->withHeaders([
                    'X-API-KEY' => $token,
                    'Accept' => 'application/json',
                ])->get($url);

            if ($response->successful()) {
                $res = $response->json();

                if (isset($res['nombres']) || isset($res['razon_social'])) {
                    if ($longitud === 8) {
                        return response()->json([
                            'found' => true,
                            'data' => [
                                'tipo_documento' => 'DNI',
                                'documento' => $documento,
                                'nombre' => $res['nombres'] ?? '',
                                'apellidos' => trim(($res['apellido_paterno'] ?? '') . ' ' . ($res['apellido_materno'] ?? '')),
                                'nombre_completo' => trim(($res['nombres'] ?? '') . ' ' . trim(($res['apellido_paterno'] ?? '') . ' ' . ($res['apellido_materno'] ?? ''))),
                                'email' => $res['correo'] ?? null,
                                'telefono' => $res['telefono'] ?? null,
                                'tiene_cuenta' => false,
                            ],
                        ]);
                    } else {
                        return response()->json([
                            'found' => true,
                            'data' => [
                                'tipo_documento' => 'RUC',
                                'documento' => $documento,
                                'razon_social' => $res['razon_social'] ?? '',
                                'nombre_completo' => $res['razon_social'] ?? '',
                                'direccion' => $res['direccion'] ?? null,
                                'email' => $res['correo'] ?? null,
                                'telefono' => $res['telefono'] ?? null,
                                'tiene_cuenta' => false,
                            ],
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error consultando API para tienda: ' . $e->getMessage());
            return response()->json(['found' => false, 'message' => 'No se pudo consultar el documento. Intentalo de nuevo.']);
        }

        return response()->json(['found' => false, 'message' => 'Documento no encontrado.']);
    }

    public function logout(Request $request)
    {
        $carritoCtrl = app(CarritoController::class);
        $carritoCtrl->sincronizarBD();

        Auth::guard('tienda')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tienda.index');
    }

    public function perfil()
    {
        $cliente = auth('tienda')->user();

        return view('tienda.auth.perfil', compact('cliente'));
    }

    public function actualizarPerfil(Request $request)
    {
        $cliente = auth('tienda')->user();

        $data = $request->validate([
            'email' => ['nullable', 'email', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'password_actual' => ['nullable', 'string', 'required_with:password_nueva'],
            'password_nueva' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        if ($data['email'] && $data['email'] !== $cliente->email) {
            $emailTomado = Cliente::where('email', $data['email'])
                ->where('id', '!=', $cliente->id)
                ->exists();
            if ($emailTomado) {
                throw ValidationException::withMessages([
                    'email' => 'Este correo ya esta registrado por otro cliente.',
                ]);
            }
        }

        if ($data['telefono'] && $data['telefono'] !== $cliente->telefono) {
            $telefonoTomado = Cliente::where('telefono', $data['telefono'])
                ->where('id', '!=', $cliente->id)
                ->exists();
            if ($telefonoTomado) {
                throw ValidationException::withMessages([
                    'telefono' => 'Este telefono ya esta registrado por otro cliente.',
                ]);
            }
        }

        $cliente->email = $data['email'] ?? $cliente->email;
        $cliente->telefono = $data['telefono'] ?? $cliente->telefono;

        if ($data['password_nueva']) {
            if (!Hash::check($data['password_actual'], $cliente->tienda_password)) {
                throw ValidationException::withMessages([
                    'password_actual' => 'La contraseña actual no es correcta.',
                ]);
            }
            $cliente->tienda_password = Hash::make($data['password_nueva']);
        }

        $cliente->save();

        return redirect()->route('tienda.perfil')
            ->with('success', 'Perfil actualizado correctamente.');
    }
}
