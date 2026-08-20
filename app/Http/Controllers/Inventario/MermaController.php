<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoInventario;
use App\Models\Sucursal;
use App\Services\SucursalResolver;
use Carbon\Carbon;

class MermaController extends Controller
{
    protected SucursalResolver $sucursalResolver;

    public function __construct(SucursalResolver $sucursalResolver)
    {
        $this->sucursalResolver = $sucursalResolver;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $resolver = $this->sucursalResolver->resolverPara($user);
        $hoy = Carbon::today();

        // 1. Obtener lista de sucursales autorizadas
        $sucursalesPermitidas = [];
        if ($resolver['es_admin']) {
            $sucursalesPermitidas = Sucursal::orderBy('nombre')->get();
        } else {
            $sucursalesPermitidas = $user->sucursales()->orderBy('nombre')->get();
        }

        // --- PENDIENTES DE MERMA (Lotes Vencidos con Stock > 0) ---
        $queryPendientes = Lote::with(['medicamento', 'sucursal'])
            ->where('stock_actual', '>', 0)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', $hoy);

        // --- HISTORIAL DE MERMAS (Movimientos de salida con motivo de merma) ---
        $queryHistorial = MovimientoInventario::with(['medicamento', 'lote', 'sucursal', 'usuario'])
            ->where('tipo', 'salida')
            ->where(function ($q) {
                $q->where('motivo', 'like', '%MERMA%')
                  ->orWhere('motivo', 'like', '%VENCIDO%')
                  ->orWhere('motivo', 'like', '%VENCIMIENTO%')
                  ->orWhere('motivo', 'like', '%DAÑADO%')
                  ->orWhere('motivo', 'like', '%ROTO%');
            });

        // Filtrado por sucursal si se ha seleccionado
        if ($request->filled('sucursal_id')) {
            $sucId = (int) $request->sucursal_id;
            if (!$resolver['es_admin'] && !in_array($sucId, $resolver['ids_filtro'] ?? [])) {
                abort(403, 'No tienes acceso a esta sucursal.');
            }
            $queryPendientes->where('sucursal_id', $sucId);
            $queryHistorial->where('sucursal_id', $sucId);
        } else {
            if (is_array($resolver['ids_filtro'])) {
                $queryPendientes->whereIn('sucursal_id', $resolver['ids_filtro']);
                $queryHistorial->whereIn('sucursal_id', $resolver['ids_filtro']);
            }
        }

        // Filtro por Medicamento en pendientes e historial
        if ($request->filled('medicamento')) {
            $nombreMed = $request->medicamento;
            $queryPendientes->whereHas('medicamento', function ($q) use ($nombreMed) {
                $q->where('nombre', 'like', '%' . $nombreMed . '%');
            });
            $queryHistorial->whereHas('medicamento', function ($q) use ($nombreMed) {
                $q->where('nombre', 'like', '%' . $nombreMed . '%');
            });
        }

        $pendientes = $queryPendientes->orderBy('fecha_vencimiento', 'asc')->paginate(15, ['*'], 'page_pendientes')->withQueryString();
        $historial = $queryHistorial->orderBy('created_at', 'desc')->paginate(15, ['*'], 'page_historial')->withQueryString();

        return view('inventario.mermas.index', compact('pendientes', 'historial', 'sucursalesPermitidas', 'resolver'));
    }

    public function confirmar(Request $request, $loteId)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
            'motivo'   => 'required|string|max:255',
        ]);

        $lote = Lote::findOrFail($loteId);
        $cantidad = (int) $request->cantidad;

        if ($cantidad > $lote->stock_actual) {
            return response()->json([
                'success' => false,
                'message' => 'La cantidad ingresada supera el stock disponible del lote (' . $lote->stock_actual . ').'
            ], 422);
        }

        try {
            DB::transaction(function () use ($lote, $cantidad, $request) {
                // 1. Descontar stock del lote
                $lote->stock_actual -= $cantidad;
                $lote->save();

                // 2. Registrar movimiento de salida
                MovimientoInventario::create([
                    'tipo'           => 'salida',
                    'medicamento_id' => $lote->medicamento_id,
                    'sucursal_id'    => $lote->sucursal_id,
                    'lote_id'        => $lote->id,
                    'cantidad'       => $cantidad,
                    'motivo'         => 'MERMA - ' . strtoupper($request->motivo),
                    'referencia'     => 'BAJA DE MERMA',
                    'user_id'        => Auth::id(),
                    'stock_final'    => $lote->stock_actual,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Merma confirmada y registrada en el inventario.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la merma: ' . $e->getMessage()
            ], 500);
        }
    }
}
