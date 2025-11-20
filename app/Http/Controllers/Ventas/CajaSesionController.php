<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;     // <--- ¡ERROR 1! FALTABA ESTO
use App\Services\SucursalResolver;
use App\Models\Ventas\CajaSesion;
use App\Models\Sucursal;                 // <--- También necesitas esto

class CajaSesionController extends Controller
{
    protected SucursalResolver $sucursalResolver;

    public function __construct(SucursalResolver $sucursalResolver)
    {
        $this->sucursalResolver = $sucursalResolver;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        // 1. Obtenemos el contexto
        $ctx = $this->sucursalResolver->resolverPara($user);

        // 2. Consulta de Cajas (con la lógica que preferiste)
        $query = CajaSesion::query()->with(['sucursal', 'usuario']);

        if (!$ctx['es_admin']) {
            // Si NO es admin, filtra por su ID
            $query->where('user_id', $user->id);
        }

        // 4. Filtro de sucursal
        if ($ctx['ids_filtro'] !== null) {
            $query->whereIn('sucursal_id', $ctx['ids_filtro']);
        }

        $cajas = $query->orderBy('fecha_apertura', 'desc')->paginate(20);

        // 5. [CORRECCIÓN 1] Definir $sucursalesParaApertura
        // (Esto faltaba en el código que pegaste)
        $sucursalesParaApertura = $ctx['es_admin']
            ? Sucursal::orderBy('nombre')->get()
            : $user->sucursales()->select('sucursales.*')->orderBy('sucursales.nombre')->get();

        // 6. Devolver la vista
        return view('ventas.cajas.index', [
            'cajas'                => $cajas,
            'esAdmin'              => $ctx['es_admin'],

            // [CORRECCIÓN 2] Arregla el error de la imagen
            // La vista espera 'sucursalSeleccionada' (camelCase)
            // El resolver entrega 'sucursal_seleccionada' (snake_case)
            'sucursalSeleccionada' => $ctx['sucursal_seleccionada'],

            'idsFiltroSucursales'  => $ctx['ids_filtro'],
            'sucursalesParaApertura' => $sucursalesParaApertura, // Ahora sí está definida
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

        // 1. Validación: Solo necesitamos el saldo real
        $data = $request->validate([
            'saldo_real' => ['required', 'numeric', 'min:0'],
        ]);

        // 2. Usar una transacción es CRUCIAL aquí
        try {
            DB::transaction(function () use ($data, $user, $id) {

                // 3. Buscar la caja
                $caja = CajaSesion::findOrFail($id);

                // 4. Seguridad: O eres admin, o eres el dueño
                if (!$user->hasRole('Administrador') && $caja->user_id != $user->id) {
                    throw new \Exception('No tienes permiso para cerrar esta caja.');
                }

                // 5. Lógica: No puedes cerrar una caja ya cerrada
                if ($caja->estado === 'CERRADO') {
                    throw new \Exception('Esta caja ya ha sido cerrada.');
                }

                // 6. *** LÓGICA DE CIERRE ***
                $saldo_real = (float)$data['saldo_real'];

                // 7. Calcular Saldo Teórico: Inicial + Suma de Ventas
                // (Usamos la relación 'ventas' del modelo)
                $total_ventas = $caja->ventas()->sum('total_neto');
                $saldo_teorico = $caja->saldo_inicial + $total_ventas;

                // 8. Calcular Diferencia
                $diferencia = $saldo_real - $saldo_teorico;

                // 9. Actualizar el registro
                $caja->update([
                    'fecha_cierre'  => now(),
                    'estado'        => 'CERRADO', // Coincide con tu migración
                    'saldo_real'    => $saldo_real,
                    'saldo_teorico' => $saldo_teorico,
                    'diferencia'    => $diferencia,
                ]);
            });
        } catch (\Exception $e) {
            // Si algo falla, volver atrás con el error real
            return back()->withErrors([
                'general_cierre' => $e->getMessage()
            ])->withInput();
        }

        // 10. Redirigir
        return redirect()->route('cajas.index')
            ->with('success', '¡Caja cerrada exitosamente!');
    }
}
