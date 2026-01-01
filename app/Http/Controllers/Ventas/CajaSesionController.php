<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\SucursalResolver;
use App\Models\Ventas\CajaSesion;
use App\Models\Sucursal;
use App\Models\User;

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
        $ctx = $this->sucursalResolver->resolverPara($user);

        // 1. CAPTURAR SUCURSAL ACTUAL
        $sucursalActualId = session('sucursal_id');

        // 2. VALIDAR SI TIENE CAJA ABIERTA
        $tieneCajaAbierta = false;
        if ($sucursalActualId) {
            $tieneCajaAbierta = CajaSesion::where('user_id', $user->id)
                ->where('estado', 'ABIERTO')
                ->where('sucursal_id', $sucursalActualId)
                ->exists();
        }

        // 3. CONSULTA (CORREGIDA)
        $query = CajaSesion::query()
            ->with(['sucursal', 'usuario'])
            // === CORRECCIÓN AQUÍ: SUMAR SOLO SI NO ESTÁ ANULADO ===
            ->withSum(['ventas' => function ($q) {
                $q->where('estado', '!=', 'ANULADO');
            }], 'total_neto')
            // ======================================================
            ->orderBy('fecha_apertura', 'desc');

        if (!$ctx['es_admin']) {
            $query->where('user_id', $user->id);
        }
        if ($ctx['ids_filtro']) {
            $query->whereIn('sucursal_id', $ctx['ids_filtro']);
        }

        // Filtros
        if ($request->filled('q')) {
            $query->whereHas('usuario', fn($q) => $q->where('name', 'LIKE', "%{$request->q}%"));
        }
        if ($request->filled('filtro_fecha')) {
            $query->whereDate('fecha_apertura', $request->filtro_fecha);
        }
        if ($request->filled('filtro_user_id')) {
            $query->where('user_id', $request->filtro_user_id);
        }
        if ($request->filled('filtro_cuadre')) {
            $fc = $request->filtro_cuadre;
            $query->where('estado', 'CERRADO');
            if ($fc === 'faltante') $query->where('diferencia', '<', 0);
            if ($fc === 'sobrante') $query->where('diferencia', '>', 0);
            if ($fc === 'exacto')   $query->where('diferencia', '=', 0);
        }

        $usuariosFiltro = $ctx['es_admin'] ? User::orderBy('name')->get() : collect();
        $sucursalesParaApertura = $ctx['es_admin']
            ? Sucursal::orderBy('nombre')->get()
            : $user->sucursales()->orderBy('nombre')->get();

        return view('ventas.cajas.index', [
            'cajas'                  => $query->paginate(20),
            'esAdmin'                => $ctx['es_admin'],
            'tieneCajaAbierta'       => $tieneCajaAbierta,
            'usuariosFiltro'         => $usuariosFiltro,
            'sucursalesParaApertura' => $sucursalesParaApertura,
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();

        // 1. Pre-cargamos todo
        $query = CajaSesion::query()->with([
            'sucursal',
            'usuario',
            'ventas' => function ($ventaQuery) {
                $ventaQuery->orderBy('fecha_emision', 'desc');
            },
            'ventas.cliente',
            'ventas.usuario',
            'ventas.detalles.medicamento'
        ]);

        if (!$user->hasRole('Administrador')) {
            $query->where('user_id', $user->id);
        }

        try {
            $cajaSesion = $query->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('cajas.index')
                ->with('error', 'Sesión no encontrada o sin permiso.');
        }

        return view('ventas.cajas.show', compact('cajaSesion'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'sucursal_id'   => ['required', 'integer', 'exists:sucursales,id'],
            'saldo_inicial' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ]);

        $sucursalId = (int)$data['sucursal_id'];

        if (!$user->hasRole('Administrador')) {
            if (!$user->tieneSucursal($sucursalId)) {
                return back()->withErrors(['sucursal_id' => 'No tienes acceso a esta sucursal.']);
            }
        }

        $existeEnEstaSucursal = CajaSesion::where('user_id', $user->id)
            ->where('estado', 'ABIERTO')
            ->where('sucursal_id', $sucursalId)
            ->exists();

        if ($existeEnEstaSucursal) {
            return back()->withErrors([
                'sucursal_id' => 'Ya tienes una caja abierta en ESTA sucursal.'
            ])->withInput();
        }

        try {
            DB::transaction(function () use ($data, $user, $sucursalId) {
                CajaSesion::create([
                    'sucursal_id'    => $sucursalId,
                    'user_id'        => $user->id,
                    'fecha_apertura' => now(),
                    'saldo_inicial'  => $data['saldo_inicial'],
                    'estado'         => 'ABIERTO',
                    'observaciones'  => $data['observaciones'] ?? null,
                    'saldo_teorico'  => $data['saldo_inicial'],
                    'saldo_real'     => 0,
                    'diferencia'     => 0,
                ]);
            });
        } catch (\Exception $e) {
            return back()->withErrors(['general' => $e->getMessage()])->withInput();
        }

        return redirect()->route('cajas.index')->with('success', '¡Caja abierta exitosamente!');
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $data = $request->validate([
            'saldo_real'    => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            \DB::transaction(function () use ($data, $user, $id) {

                $caja = CajaSesion::findOrFail($id);

                if (!$user->hasRole('Administrador') && $caja->user_id != $user->id) {
                    throw new \Exception('No tienes permiso para cerrar esta caja.');
                }
                if ($caja->estado === 'CERRADO') {
                    throw new \Exception('Esta caja ya ha sido cerrada.');
                }

                $saldo_real = (float)$data['saldo_real'];

                // === CORRECCIÓN AQUÍ: SUMAR SOLO VENTAS VALIDAS (NO ANULADAS) ===
                $total_ventas = $caja->ventas()
                    ->where('estado', '!=', 'ANULADO') // <--- FILTRO CRÍTICO
                    ->sum('total_neto');
                // ==============================================================

                $saldo_teorico = $caja->saldo_inicial + $total_ventas;
                $diferencia = $saldo_real - $saldo_teorico;

                $textoFinal = $caja->observaciones;
                if (!empty($data['observaciones'])) {
                    if ($textoFinal) {
                        $textoFinal .= " | CIERRE: " . $data['observaciones'];
                    } else {
                        $textoFinal = "CIERRE: " . $data['observaciones'];
                    }
                }

                $caja->update([
                    'fecha_cierre'  => now(),
                    'estado'        => 'CERRADO',
                    'saldo_real'    => $saldo_real,
                    'saldo_teorico' => $saldo_teorico,
                    'diferencia'    => $diferencia,
                    'observaciones' => $textoFinal,
                ]);
            });
        } catch (\Exception $e) {
            return back()->withErrors(['general_cierre' => $e->getMessage()])->withInput();
        }

        return redirect()->route('cajas.index')->with('success', '¡Caja cerrada exitosamente!');
    }
}
