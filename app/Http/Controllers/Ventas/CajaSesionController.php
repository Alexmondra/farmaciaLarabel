<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;     // <--- ¡ERROR 1! FALTABA ESTO
use App\Services\SucursalResolver;
use App\Models\Ventas\CajaSesion;
use App\Models\Sucursal;                 // <--- También necesitas esto
use App\Models\User;

class CajaSesionController extends Controller
{
    protected SucursalResolver $sucursalResolver;

    public function __construct(SucursalResolver $sucursalResolver)
    {
        $this->sucursalResolver = $sucursalResolver;
    }

    // En CajaSesionController.php

    public function index(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->sucursalResolver->resolverPara($user);

        // 1. Verificar caja abierta (para bloquear botón)
        $tieneCajaAbierta = CajaSesion::where('user_id', $user->id)
            ->where('estado', 'ABIERTO')->exists();

        // 2. Query Base con Suma Automática
        $query = CajaSesion::query()
            ->with(['sucursal', 'usuario'])
            ->withSum('ventas', 'total_neto')
            ->orderBy('fecha_apertura', 'desc');

        // 3. Filtros Inteligentes
        if (!$ctx['es_admin']) {
            $query->where('user_id', $user->id);
        }
        if ($ctx['ids_filtro']) {
            $query->whereIn('sucursal_id', $ctx['ids_filtro']);
        }
        if ($request->filled('q')) { // Buscador de Nombre
            $query->whereHas('usuario', fn($q) => $q->where('name', 'LIKE', "%{$request->q}%"));
        }
        if ($request->filled('filtro_fecha')) {
            $query->whereDate('fecha_apertura', $request->filtro_fecha);
        }
        if ($request->filled('filtro_user_id')) {
            $query->where('user_id', $request->filtro_user_id);
        }

        // Filtro de Cuadre (Faltante/Sobrante)
        if ($request->filled('filtro_cuadre')) {
            $fc = $request->filtro_cuadre;
            $query->where('estado', 'CERRADO');
            if ($fc === 'faltante') $query->where('diferencia', '<', 0);
            if ($fc === 'sobrante') $query->where('diferencia', '>', 0);
            if ($fc === 'exacto')   $query->where('diferencia', '=', 0);
        }

        // 4. Datos Auxiliares (SOLUCIÓN ERROR)
        $usuariosFiltro = $ctx['es_admin'] ? User::orderBy('name')->get() : collect();

        $sucursalesParaApertura = $ctx['es_admin']
            ? Sucursal::orderBy('nombre')->get()
            : $user->sucursales()->orderBy('nombre')->get();

        return view('ventas.cajas.index', [
            'cajas'                  => $query->paginate(20),
            'esAdmin'                => $ctx['es_admin'],
            'tieneCajaAbierta'       => $tieneCajaAbierta,
            'usuariosFiltro'         => $usuariosFiltro, // <--- ¡AQUÍ ESTÁ LA CORRECCIÓN!
            'sucursalesParaApertura' => $sucursalesParaApertura,
        ]);
    }
    public function show($id)
    {
        $user = Auth::user();

        // 1. Pre-cargamos todo lo necesario
        $query = CajaSesion::query()->with([
            'sucursal',
            'usuario', // Dueño de la caja
            'ventas' => function ($ventaQuery) {
                // Ordenar ventas por fecha, de más nueva a más vieja
                $ventaQuery->orderBy('fecha_emision', 'desc');
            },
            'ventas.cliente', // Cliente de cada venta
            'ventas.usuario', // Vendedor de cada venta
            'ventas.detalles.medicamento' // Detalles para el MODAL
        ]);

        // 2. Aplicar seguridad: Admin ve todo, Usuario solo lo suyo
        if (!$user->hasRole('Administrador')) {
            $query->where('user_id', $user->id);
        }

        // 3. Buscar o fallar
        try {
            $cajaSesion = $query->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Si no lo encuentra (o no tiene permiso por el 'where'), redirigir
            return redirect()->route('cajas.index')
                ->with('error', 'Sesión de caja no encontrada o no tiene permiso para verla.');
        }

        // 4. Mandar a la vista
        return view('ventas.cajas.show', compact('cajaSesion'));
    }

    //--- TU MÉTODO STORE (QUE YA ESTÁ BIEN EN LÓGICA PERO LE FALTABA EL 'use DB') ---
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Validación (Sin cambios)
        $data = $request->validate([
            'sucursal_id'   => ['required', 'integer', 'exists:sucursales,id'],
            'saldo_inicial' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ]);

        $sucursalId = (int)$data['sucursal_id'];

        // 2. Permiso de Sucursal (Sin cambios)
        if (!$user->hasRole('Administrador')) {
            $permitidas = $user->sucursales()->pluck('sucursales.id')->toArray();
            if (!in_array($sucursalId, $permitidas, true)) {
                return back()->withErrors([
                    'sucursal_id' => 'No tienes permiso para abrir caja en esta sucursal.'
                ])->withInput();
            }
        }

        // 3. *** CORRECCIÓN DE LÓGICA 1 ***
        // Comprobar si ya existe una caja 'ABIERTO' (en mayúsculas)
        $existeAbierta = CajaSesion::where('user_id', $user->id)
            ->where('sucursal_id', $sucursalId)
            ->where('estado', 'ABIERTO') // <-- CORREGIDO
            ->exists();

        if ($existeAbierta) {
            return back()->withErrors([
                'sucursal_id' => 'Ya tienes una caja abierta en esta sucursal. Debes cerrarla antes de abrir una nueva.'
            ])->withInput();
        }

        // 4. Crear la sesión
        try {
            DB::transaction(function () use ($data, $user, $sucursalId) {
                CajaSesion::create([
                    'sucursal_id'    => $sucursalId,
                    'user_id'        => $user->id,
                    'fecha_apertura' => now(),
                    'saldo_inicial'  => $data['saldo_inicial'],

                    // *** CORRECCIÓN DE LÓGICA 2 ***
                    'estado'         => 'ABIERTO', // <-- CORREGIDO

                    'observaciones'  => $data['observaciones'] ?? null,

                    // (Esto sigue siendo una buena idea para evitar errores
                    // si la migración no tuviera nullable, lo dejamos)
                    'saldo_teorico'  => $data['saldo_inicial'],
                    'saldo_real'     => 0,
                    'diferencia'     => 0,
                ]);
            });
        } catch (\Exception $e) {
            // Si sigue fallando, muéstrame el error real:
            return back()->withErrors([
                'general' => $e->getMessage() // Mostramos el error real
            ])->withInput();
        }

        // 5. Redirigir (Sin cambios)
        return redirect()->route('cajas.index')
            ->with('success', '¡Caja abierta exitosamente!');
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // 1. Validamos el saldo y la observación (opcional)
        $data = $request->validate([
            'saldo_real'    => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:500'], // Validamos el texto
        ]);

        try {
            \DB::transaction(function () use ($data, $user, $id) {

                $caja = CajaSesion::findOrFail($id);

                // ... (Tus validaciones de seguridad de siempre) ...
                if (!$user->hasRole('Administrador') && $caja->user_id != $user->id) {
                    throw new \Exception('No tienes permiso para cerrar esta caja.');
                }
                if ($caja->estado === 'CERRADO') {
                    throw new \Exception('Esta caja ya ha sido cerrada.');
                }

                // Cálculos
                $saldo_real = (float)$data['saldo_real'];
                $total_ventas = $caja->ventas()->sum('total_neto');
                $saldo_teorico = $caja->saldo_inicial + $total_ventas;
                $diferencia = $saldo_real - $saldo_teorico;

                // --- LÓGICA DE OBSERVACIONES ---
                // Si el usuario escribió algo al cerrar:
                $textoFinal = $caja->observaciones; // Recuperamos lo que escribió al abrir

                if (!empty($data['observaciones'])) {
                    // Si ya había texto, le agregamos un separador. Si no, lo ponemos directo.
                    if ($textoFinal) {
                        $textoFinal .= " | CIERRE: " . $data['observaciones'];
                    } else {
                        $textoFinal = "CIERRE: " . $data['observaciones'];
                    }
                }

                // Guardamos
                $caja->update([
                    'fecha_cierre'  => now(),
                    'estado'        => 'CERRADO',
                    'saldo_real'    => $saldo_real,
                    'saldo_teorico' => $saldo_teorico,
                    'diferencia'    => $diferencia,
                    'observaciones' => $textoFinal, // Guardamos el texto unido
                ]);
            });
        } catch (\Exception $e) {
            return back()->withErrors(['general_cierre' => $e->getMessage()])->withInput();
        }

        return redirect()->route('cajas.index')->with('success', '¡Caja cerrada exitosamente!');
    }
}
