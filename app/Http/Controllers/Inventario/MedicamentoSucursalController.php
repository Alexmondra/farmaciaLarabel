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

        // 1. Validación de Permisos (Igual que antes)
        if (!$user->hasRole('Administrador')) {
            $permitidas = $user->sucursales()->pluck('sucursales.id')->toArray();
            if (!in_array((int)$sucursalId, $permitidas, true)) {
                if ($request->ajax()) {
                    return response()->json(['error' => 'No tienes permiso en esta sucursal.'], 403);
                }
                return back()->withErrors('No tienes permiso en esta sucursal.');
            }
        }

        // 2. Validación de Datos (El formulario envía 'precio')
        $pivotData = $request->validate([
            'precio'        => ['nullable', 'numeric', 'min:0'],
            'stock_minimo'  => ['nullable', 'integer', 'min:0'],
            'ubicacion'     => ['nullable', 'string', 'max:120'],
        ]);

        // 3. PREPARAMOS LOS DATOS CORRECTOS (Aquí estaba el error)
        // Convertimos los nombres del formulario a los nombres de la Tabla
        $datosParaGuardar = [];

        if (array_key_exists('precio', $pivotData)) {
            $datosParaGuardar['precio_venta'] = $pivotData['precio']; // <--- LA TRADUCCIÓN CLAVE
        }
        if (array_key_exists('stock_minimo', $pivotData)) {
            $datosParaGuardar['stock_minimo'] = $pivotData['stock_minimo'];
        }
        if (array_key_exists('ubicacion', $pivotData)) {
            $datosParaGuardar['ubicacion'] = $pivotData['ubicacion'];
        }

        $m = Medicamento::findOrFail($medicamentoId);

        // 4. Guardar en Base de Datos usando Eloquent
        DB::transaction(function () use ($m, $sucursalId, $datosParaGuardar) {
            // Usamos updateExistingPivot o attach según corresponda
            if ($m->sucursales()->where('sucursal_id', $sucursalId)->exists()) {
                // array_filter quita los nulos para no borrar datos si no se enviaron
                $m->sucursales()->updateExistingPivot($sucursalId, array_filter($datosParaGuardar, fn($v) => !is_null($v)));
            } else {
                $m->sucursales()->attach($sucursalId, array_filter($datosParaGuardar, fn($v) => !is_null($v)));
            }
        });

        // 5. Respuesta JSON para el Modal
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Precio actualizado correctamente.'
            ]);
        }

        return back()->with('success', 'Actualizado para la sucursal.');
    }

    public function attach(Request $request, $medicamentoId)
    {
        $user = Auth::user();
        $data = $request->validate([
            'sucursal_id'   => ['required', 'integer', 'exists:sucursales,id'],
            'precio'        => ['nullable', 'numeric', 'min:0'],
            'stock_minimo'  => ['nullable', 'integer', 'min:0'],
            'ubicacion'     => ['nullable', 'string', 'max:120'],
        ]);

        if (!$user->hasRole('Administrador')) {
            $permitidas = $user->sucursales()->pluck('sucursales.id')->toArray();
            if (!in_array((int)$data['sucursal_id'], $permitidas, true)) {
                return back()->withErrors('No tienes permiso para esa sucursal.')->withInput();
            }
        }

        $m = Medicamento::findOrFail($medicamentoId);
        if ($m->sucursales()->where('sucursal_id', $data['sucursal_id'])->exists()) {
            return back()->withErrors('Ya está asociado a esa sucursal.');
        }

        $m->sucursales()->attach($data['sucursal_id'], array_filter([
            'precio'       => $data['precio'] ?? null,
            'stock_minimo' => $data['stock_minimo'] ?? null,
            'ubicacion'    => $data['ubicacion'] ?? null,
        ], fn($v) => !is_null($v)));

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
}
