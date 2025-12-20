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

        $user    = Auth::user();
        $esAdmin = method_exists($user, 'hasRole') ? $user->hasRole('Administrador') : false;

        if (!$esAdmin) {
            $user->load('sucursales');
            if (!$user->sucursales->contains('id', $registro->sucursal_id)) {
                abort(403, 'No tienes permiso para modificar esta sucursal.');
            }
        }

        // 3) (Opcional) Bloquear si aún hay stock en esa sucursal
        $stock = Lote::where('medicamento_id', $medicamentoId)
            ->where('sucursal_id', $sucursalId)
            ->sum('stock_actual');

        if ($stock > 0) {
            return back()->with('error', "No puedes desactivar este medicamento en la sucursal porque aún hay {$stock} unidades en stock.");
        }

        // 4) Soft-delete: marcar deleted_at en el pivot
        $registro->deleted_at = now();
        $registro->save();

        return back()->with('success', 'Medicamento desactivado en esta sucursal.');
    }

    // Asegúrate de importar DB arriba: use Illuminate\Support\Facades\DB;

    public function update(Request $request, $medicamentoId, $sucursalId)
    {
        $user = Auth::user();

        // ... (Tu validación de permisos se queda igual) ...
        if (!$user->hasRole('Administrador')) {
            $permitidas = $user->sucursales()->pluck('sucursales.id')->toArray();
            if (!in_array((int)$sucursalId, $permitidas, true)) {
                // ... lógica de error ...
                return back()->withErrors('No tienes permiso en esta sucursal.');
            }
        }

        // Validación
        $pivotData = $request->validate([
            'precio'        => ['nullable', 'numeric', 'min:0'],
            'stock_minimo'  => ['nullable', 'integer', 'min:0'], // <--- ESTO ES NUEVO
            'ubicacion'     => ['nullable', 'string', 'max:120'],
        ]);

        $datosParaGuardar = [
            'updated_by' => Auth::id() // <--- AGREGADO: Para saber quién editó
        ];

        if (array_key_exists('precio', $pivotData)) {
            $datosParaGuardar['precio_venta'] = $pivotData['precio'];
        }

        if (array_key_exists('stock_minimo', $pivotData)) {
            $datosParaGuardar['stock_minimo'] = $pivotData['stock_minimo'];
        }
        if (array_key_exists('ubicacion', $pivotData)) {
            $datosParaGuardar['ubicacion'] = $pivotData['ubicacion'];
        }

        $m = Medicamento::findOrFail($medicamentoId);

        DB::transaction(function () use ($m, $sucursalId, $datosParaGuardar) {
            if ($m->sucursales()->where('sucursal_id', $sucursalId)->exists()) {
                $m->sucursales()->updateExistingPivot($sucursalId, array_filter($datosParaGuardar, fn($v) => !is_null($v)));
            } else {
                $m->sucursales()->attach($sucursalId, array_filter($datosParaGuardar, fn($v) => !is_null($v)));
            }
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Datos actualizados correctamente.']);
        }

        return back()->with('success', 'Actualizado para la sucursal.');
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
            'codigo_lote'    => 'required|string|max:50',  // El usuario escribe el lote
            'vencimiento'    => 'nullable|date',           // Opcional para ingresos rápidos
            'motivo'         => 'required|string',
            'observacion'    => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        // Resolver sucursal
        $ctx = $this->sucursalResolver->resolverPara($user);
        $sucursal = $ctx['sucursal_seleccionada'];

        if (!$sucursal) {
            return response()->json(['error' => 'Selecciona una sucursal.'], 403);
        }

        DB::beginTransaction();
        try {
            // 1. Buscamos si el lote YA EXISTE en esta sucursal para este producto
            $lote = Lote::where('medicamento_id', $request->medicamento_id)
                ->where('sucursal_id', $sucursal->id)
                ->where('codigo_lote', trim($request->codigo_lote))
                ->first();

            if ($lote) {
                // A) EL LOTE EXISTE: Aumentamos stock
                $lote->stock_actual += $request->cantidad;
                // Opcional: Si el usuario manda nueva fecha, ¿actualizamos? 
                // Mejor mantenemos la original para no mezclar, o actualizamos si estaba null.
                if (!$lote->fecha_vencimiento && $request->filled('vencimiento')) {
                    $lote->fecha_vencimiento = $request->vencimiento;
                }
                $lote->save();
            } else {
                // B) LOTE NUEVO: Lo creamos desde cero
                $lote = new Lote();
                $lote->medicamento_id    = $request->medicamento_id;
                $lote->sucursal_id       = $sucursal->id;
                $lote->codigo_lote       = trim($request->codigo_lote);
                $lote->stock_actual      = $request->cantidad;
                $lote->fecha_vencimiento = $request->vencimiento;

                // Si quieres copiar el precio de compra del medicamento base (opcional)
                // $lote->precio_compra = ... 

                $lote->save();
            }

            // 2. Registrar en Kardex (Historial)
            MovimientoInventario::create([
                'tipo'           => 'entrada', // Importante: Tipo INGRESO
                'medicamento_id' => $request->medicamento_id,
                'sucursal_id'    => $sucursal->id,
                'lote_id'        => $lote->id,
                'cantidad'       => $request->cantidad,
                'motivo'         => $request->motivo,
                'referencia'     => $request->observacion,
                'user_id'        => $user->id,
                'stock_final'    => $lote->stock_actual // Stock después de sumar
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Ingreso registrado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
