<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Inventario\Lote;
use App\Models\Sucursal;
use App\Services\SucursalResolver;
use Carbon\Carbon;

class LoteController extends Controller
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

        // Obtener lista de sucursales a las que tiene acceso el usuario
        $sucursalesPermitidas = [];
        if ($resolver['es_admin']) {
            $sucursalesPermitidas = Sucursal::orderBy('nombre')->get();
        } else {
            $sucursalesPermitidas = $user->sucursales()->orderBy('nombre')->get();
        }

        $query = Lote::with(['medicamento', 'sucursal']);

        // 1. Filtrado por sucursal
        if ($request->filled('sucursal_id')) {
            $sucId = (int) $request->sucursal_id;
            // Validar acceso si no es admin
            if (!$resolver['es_admin'] && !in_array($sucId, $resolver['ids_filtro'] ?? [])) {
                abort(403, 'No tienes acceso a esta sucursal.');
            }
            $query->where('sucursal_id', $sucId);
        } else {
            // Filtro por defecto según el resolver
            if (is_array($resolver['ids_filtro'])) {
                $query->whereIn('sucursal_id', $resolver['ids_filtro']);
            }
        }

        // 2. Filtro por código de lote
        if ($request->filled('codigo')) {
            $query->where('codigo_lote', 'like', '%' . $request->codigo . '%');
        }

        // 3. Filtro por nombre de medicamento
        if ($request->filled('medicamento')) {
            $nombreMed = $request->medicamento;
            $query->whereHas('medicamento', function ($q) use ($nombreMed) {
                $q->where('nombre', 'like', '%' . $nombreMed . '%');
            });
        }

        // 4. Filtro por estado
        $estado = $request->input('estado', 'todos');
        switch ($estado) {
            case 'vigentes':
                $query->where('stock_actual', '>', 0)
                    ->where(function ($q) use ($hoy) {
                        $q->whereDate('fecha_vencimiento', '>=', $hoy)
                            ->orWhereNull('fecha_vencimiento');
                    });
                break;
            case 'por_vencer':
                $query->where('stock_actual', '>', 0)
                    ->whereNotNull('fecha_vencimiento')
                    ->whereDate('fecha_vencimiento', '>=', $hoy)
                    ->whereDate('fecha_vencimiento', '<=', $hoy->copy()->addDays(30));
                break;
            case 'vencidos':
                $query->where('stock_actual', '>', 0)
                    ->whereNotNull('fecha_vencimiento')
                    ->whereDate('fecha_vencimiento', '<', $hoy);
                break;
            case 'sin_stock':
                $query->where('stock_actual', '<=', 0);
                break;
        }

        // Orden: Primero los lotes vencidos o por vencer (FEFO)
        $lotes = $query->orderByRaw('fecha_vencimiento IS NULL, fecha_vencimiento ASC')
            ->paginate(20)
            ->withQueryString();

        return view('inventario.lotes.index', compact('lotes', 'sucursalesPermitidas', 'resolver', 'estado'));
    }
}
