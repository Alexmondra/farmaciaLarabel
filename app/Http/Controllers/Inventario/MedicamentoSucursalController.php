<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Inventario\Medicamento;
use App\Models\Inventario\MedicamentoSucursal;       // 👈 IMPORTANTE
use App\Models\Inventario\Lote;
use App\Services\SucursalResolver;
use App\Models\Sucursal;
use Illuminate\Support\Facades\DB;
use App\Models\Inventario\MovimientoInventario;


class MedicamentoSucursalController extends Controller
{
    protected SucursalResolver $sucursalResolver;

    // 2. INYECCIÓN DE DEPENDENCIA
    public function __construct(SucursalResolver $sucursalResolver)
    {
        $this->sucursalResolver = $sucursalResolver;
    }
    public function destroy($medicamentoId, $sucursalId)
    {
        $registro = MedicamentoSucursal::where('medicamento_id', $medicamentoId)
            ->where('sucursal_id', $sucursalId)
            ->whereNull('deleted_at')
            ->firstOrFail();

        // ... (toda tu lógica de permisos de usuario admin/sucursal sigue igual) ...

        // --- CORRECCIÓN AQUÍ ---
        // Solo sumamos stock que sea VIGENTE (Fecha >= Hoy o Fecha Nula)
        // Si está vencido, nos da igual, dejamos que lo borre.
        $stockVigente = Lote::where('medicamento_id', $medicamentoId)
            ->where('sucursal_id', $sucursalId)
            ->where('stock_actual', '>', 0) // Solo si hay stock físico
            ->where(function ($q) {
                $q->whereDate('fecha_vencimiento', '>=', now()) // Que no haya vencido
                    ->orWhereNull('fecha_vencimiento');           // O que no tenga fecha (asumimos vigente)
            })
            ->sum('stock_actual');

        if ($stockVigente > 0) {
            return back()->with('error', "No puedes desactivar este medicamento porque aún hay {$stockVigente} unidades vigentes en stock.");
        }

        // 4) Soft-delete
        $registro->deleted_at = now();
        $registro->save();

        return back()->with('success', 'Medicamento desactivado en esta sucursal.');
    }
    // Asegúrate de importar DB arriba: use Illuminate\Support\Facades\DB;

    public function update(Request $request, $medicamentoId, $sucursalId)
    {
        $user = Auth::user();

        // Validar permisos (Mantenemos tu lógica existente)
        if (!$user->hasRole('Administrador')) {
            $permitidas = $user->sucursales()->pluck('sucursales.id')->toArray();
            if (!in_array((int)$sucursalId, $permitidas, true)) {
                return back()->withErrors('No tienes permiso en esta sucursal.');
            }
        }

        // 1. VALIDACIÓN AMPLIADA PARA LOS 3 PRECIOS
        $pivotData = $request->validate([
            'precio'         => ['nullable', 'numeric', 'min:0'],
            'precio_blister' => ['nullable', 'numeric', 'min:0'],
            'precio_caja'    => ['nullable', 'numeric', 'min:0'],
            'stock_minimo'   => ['nullable', 'integer', 'min:0'],
            'ubicacion'      => ['nullable', 'string', 'max:120'],
        ]);

        $datosParaGuardar = [
            'updated_by' => Auth::id()
        ];

        if (array_key_exists('precio', $pivotData)) $datosParaGuardar['precio_venta'] = $pivotData['precio'];
        if (array_key_exists('precio_blister', $pivotData)) $datosParaGuardar['precio_blister'] = $pivotData['precio_blister'];
        if (array_key_exists('precio_caja', $pivotData)) $datosParaGuardar['precio_caja'] = $pivotData['precio_caja'];

        if (array_key_exists('stock_minimo', $pivotData)) $datosParaGuardar['stock_minimo'] = $pivotData['stock_minimo'];
        if (array_key_exists('ubicacion', $pivotData)) $datosParaGuardar['ubicacion'] = $pivotData['ubicacion'];

        $m = Medicamento::findOrFail($medicamentoId);

        DB::transaction(function () use ($m, $sucursalId, $datosParaGuardar) {
            if ($m->sucursales()->where('sucursal_id', $sucursalId)->exists()) {
                $m->sucursales()->updateExistingPivot($sucursalId, array_filter($datosParaGuardar, fn($v) => !is_null($v)));
            } else {
                $m->sucursales()->attach($sucursalId, array_filter($datosParaGuardar, fn($v) => !is_null($v)));
            }
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Precios actualizados correctamente.']);
        }

        return back()->with('success', 'Actualizado.');
    }

    public function updateLoteUbicacion(Request $request, $loteId)
    {
        $data = $request->validate([
            'ubicacion' => 'nullable|string|max:50',
        ]);

        $lote = Lote::findOrFail($loteId);
        $lote->ubicacion = $data['ubicacion'] ?? null;
        $lote->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Ubicación del lote actualizada.',
                'ubicacion' => $lote->ubicacion,
            ]);
        }

        return back()->with('success', 'Ubicación del lote actualizada.');
    }

    public function updateLoteVencimiento(Request $request, $loteId)
    {
        $data = $request->validate([
            'fecha_vencimiento' => 'nullable|date',
            'precio_oferta'      => 'nullable|numeric|min:0', // <--- AGREGADO
        ]);

        $lote = Lote::findOrFail($loteId);

        if ($request->has('fecha_vencimiento')) $lote->fecha_vencimiento = $data['fecha_vencimiento'];
        if ($request->has('precio_oferta')) $lote->precio_oferta = $data['precio_oferta'];

        $lote->save();

        return response()->json(['success' => true, 'message' => 'Lote actualizado.']);
    }


    public function attach(Request $request, $medicamentoId)
    {
        $user = Auth::user();

        // Validamos 'stock_minimo' también aquí
        $data = $request->validate([
            'sucursal_id'   => ['required', 'integer', 'exists:sucursales,id'],
            'precio'        => ['nullable', 'numeric', 'min:0'],
            'stock_minimo'  => ['nullable', 'integer', 'min:0'], // <--- NUEVO
            'ubicacion'     => ['nullable', 'string', 'max:120'],
        ]);

        // ... (Tu validación de permisos se queda igual) ...

        $m = Medicamento::findOrFail($medicamentoId);

        if ($m->sucursales()->where('sucursal_id', $data['sucursal_id'])->exists()) {
            return back()->withErrors('Ya está asociado a esa sucursal.');
        }

        // CORRECCIÓN DE NOMBRES DE COLUMNA
        $datosPivot = [
            'precio_venta' => $data['precio'] ?? null,
            'stock_minimo' => $data['stock_minimo'] ?? 0,
            'ubicacion'    => $data['ubicacion'] ?? null,
            'updated_by'   => Auth::id(),
            'activo'       => true
        ];

        $m->sucursales()->attach($data['sucursal_id'], array_filter($datosPivot, fn($v) => !is_null($v)));

        return back()->with('success', 'Asociado a la sucursal correctamente.');
    }




    public function historial(Request $request, $medicamentoId, $sucursalId)
    {
        $user = Auth::user();
        $ctx  = $this->sucursalResolver->resolverPara($user);

        if ($ctx['sucursal_seleccionada']) {
            $idGlobal = $ctx['sucursal_seleccionada']->id;

            if ((int)$sucursalId !== (int)$idGlobal) {
                return redirect()->route('inventario.medicamento_sucursal.historial', [
                    'medicamento' => $medicamentoId,
                    'sucursal'    => $idGlobal
                ])->with('warning', 'Se redirigió al historial de la sucursal activa en tu sesión.');
            }
        } else {
            if (!$ctx['es_admin']) {
                $pertenece = $user->sucursales->contains('id', $sucursalId);
                if (!$pertenece) {
                    abort(403, 'No tienes acceso a esta sucursal.');
                }
            }
        }


        $medicamento = Medicamento::findOrFail($medicamentoId);
        $sucursal    = Sucursal::findOrFail($sucursalId);

        // Query Base
        $query = Lote::query()
            ->where('medicamento_id', $medicamentoId)
            ->where('sucursal_id', $sucursalId);

        if ($request->filled('estado')) {
            switch ($request->estado) {
                case 'vencidos':
                    $query->whereDate('fecha_vencimiento', '<', now());
                    break;

                case 'agotados':
                    $query->where('stock_actual', 0);
                    break;

                case 'por_vencer':
                    $query->whereDate('fecha_vencimiento', '>=', now())
                        ->whereDate('fecha_vencimiento', '<=', now()->addMonths(3));
                    break;

                case 'vigentes':
                    $query->where('stock_actual', '>', 0)
                        ->where(function ($q) {
                            $q->whereDate('fecha_vencimiento', '>=', now())
                                ->orWhereNull('fecha_vencimiento');
                        });
                    break;
            }
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_vencimiento', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_vencimiento', '<=', $request->fecha_fin);
        }

        if ($request->filled('q_lote')) {
            $query->where('codigo_lote', 'like', "%{$request->q_lote}%");
        }

        $lotes = $query->with(['sucursal', 'detalleCompra'])
            ->orderBy('fecha_vencimiento', 'desc')
            ->paginate(20)
            ->withQueryString();
        return view('inventario.medicamentos.historial', compact('medicamento', 'sucursal', 'lotes'));
    }


    // validaciones pertinenetes 

    public function verificarLote(Request $request)
    {
        $lote = Lote::where('medicamento_id', $request->medicamento_id)
            ->where('sucursal_id', $request->sucursal_id)
            ->where('codigo_lote', trim($request->codigo_lote))
            ->first();

        return response()->json([
            'existe' => !is_null($lote),
            // Carbon asegura que la fecha sea compatible con el input date de HTML
            'vencimiento' => $lote && $lote->fecha_vencimiento ? \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('Y-m-d') : null
        ]);
    }


    // =========================================================================
    //  NUEVA FUNCIÓN: REGISTRAR BAJA / SALIDA DE STOCK
    // =========================================================================
    public function storeSalida(Request $request)
    {
        // 1. Validamos que nos envíen el ID DEL LOTE (no solo el medicamento)
        $request->validate([
            'lote_id'        => 'required|exists:lotes,id', // <--- CLAVE
            'cantidad'       => 'required|integer|min:1',
            'motivo'         => 'required|string',
            'observacion'    => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            // 2. Buscamos el lote y bloqueamos la fila para evitar errores simultáneos
            $lote = Lote::lockForUpdate()->find($request->lote_id);

            // 3. Validar Stock
            if ($lote->stock_actual < $request->cantidad) {
                return response()->json([
                    'error' => "Stock insuficiente en el lote {$lote->codigo_lote}. Tienes {$lote->stock_actual}, intentas sacar {$request->cantidad}."
                ], 422);
            }

            // 4. Calcular nuevo stock
            $nuevoStock = $lote->stock_actual - $request->cantidad;

            // 5. Registrar en el Kardex (MovimientoInventario)
            MovimientoInventario::create([
                'tipo'           => 'ajuste',
                'medicamento_id' => $lote->medicamento_id,
                'sucursal_id'    => $lote->sucursal_id,
                'lote_id'        => $lote->id,
                'cantidad'       => $request->cantidad,
                'motivo'         => $request->motivo,     // Ej: VENCIMIENTO, MERMA
                'referencia'     => $request->observacion,
                'user_id'        => $user->id,
                'stock_final'    => $nuevoStock
            ]);

            // 6. Actualizar el Lote real
            $lote->stock_actual = $nuevoStock;
            $lote->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Baja registrada correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error en el servidor: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    //  NUEVA FUNCIÓN: REGISTRAR INGRESO / AJUSTE POSITIVO (+)
    // =========================================================================
    public function storeIngreso(Request $request)
    {
        $request->validate([
            'medicamento_id' => 'required|exists:medicamentos,id',
            'cantidad'       => 'required|integer|min:1',
            'codigo_lote'    => 'required|string|max:50',
            'vencimiento'    => 'nullable|date',
            'motivo'         => 'required|string',
            'observacion'    => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $ctx = $this->sucursalResolver->resolverPara($user);
        $sucursal = $ctx['sucursal_seleccionada'];

        if (!$sucursal) {
            return response()->json(['error' => 'Selecciona una sucursal.'], 403);
        }

        DB::beginTransaction();
        try {
            // 1. GESTIÓN DEL LOTE
            $lote = Lote::where('medicamento_id', $request->medicamento_id)
                ->where('sucursal_id', $sucursal->id)
                ->where('codigo_lote', trim($request->codigo_lote))
                ->first();

            if ($lote) {
                $lote->stock_actual += $request->cantidad;
                if (!$lote->fecha_vencimiento && $request->filled('vencimiento')) {
                    $lote->fecha_vencimiento = $request->vencimiento;
                }
                $lote->save();
            } else {
                $lote = new Lote();
                $lote->medicamento_id    = $request->medicamento_id;
                $lote->sucursal_id       = $sucursal->id;
                $lote->codigo_lote       = trim($request->codigo_lote);
                $lote->stock_actual      = $request->cantidad;
                $lote->fecha_vencimiento = $request->vencimiento;
                $lote->save();
            }

            // 2. VINCULACIÓN AUTOMÁTICA CON LA SUCURSAL (SOLUCIÓN AL PROBLEMA)
            // Buscamos si ya existe el registro, incluso si fue eliminado (Soft Delete)
            $pivot = MedicamentoSucursal::withTrashed()
                ->where('medicamento_id', $request->medicamento_id)
                ->where('sucursal_id', $sucursal->id)
                ->first();

            if (!$pivot) {
                // Si no existe el vínculo, lo creamos para que aparezca en el inventario
                MedicamentoSucursal::create([
                    'medicamento_id' => $request->medicamento_id,
                    'sucursal_id'    => $sucursal->id,
                    'stock_minimo'   => 10,
                    'activo'         => true,
                    'updated_by'     => $user->id
                ]);
            } elseif ($pivot->trashed() || !$pivot->activo) {
                // Si estaba eliminado o desactivado, lo restauramos y activamos
                $pivot->restore();
                $pivot->update([
                    'activo'     => true,
                    'updated_by' => $user->id
                ]);
            }

            // 3. REGISTRAR EN KARDEX (MOVIMIENTO)
            MovimientoInventario::create([
                'tipo'           => 'entrada',
                'medicamento_id' => $request->medicamento_id,
                'sucursal_id'    => $sucursal->id,
                'lote_id'        => $lote->id,
                'cantidad'       => $request->cantidad,
                'motivo'         => $request->motivo,
                'referencia'     => $request->observacion,
                'user_id'        => $user->id,
                'stock_final'    => $lote->stock_actual
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Ingreso registrado y producto vinculado a sucursal.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
