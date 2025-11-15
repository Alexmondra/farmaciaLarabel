<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Inventario\Medicamento;
use App\Models\Inventario\MedicamentoSucursal;       // 👈 IMPORTANTE
use App\Models\Inventario\Lote;


class MedicamentoSucursalController extends Controller
{
    /**
     * "Eliminar" un medicamento SOLO en una sucursal (soft delete por deleted_at).
     */
    public function destroy($medicamentoId, $sucursalId)
    {
        // 1) Buscar el registro pivot con esas dos llaves
        $registro = MedicamentoSucursal::where('medicamento_id', $medicamentoId)
            ->where('sucursal_id', $sucursalId)
            ->whereNull('deleted_at')
            ->firstOrFail();

        // 2) Seguridad básica
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

    public function update(Request $request, $medicamentoId, $sucursalId)
    {
        $user = Auth::user();
        if (!$user->hasRole('Administrador')) {
            $permitidas = $user->sucursales()->pluck('sucursales.id')->toArray();
            if (!in_array((int)$sucursalId, $permitidas, true)) {
                return back()->withErrors('No tienes permiso en esta sucursal.');
            }
        }

        $pivotData = $request->validate([
            'precio'        => ['nullable', 'numeric', 'min:0'],
            'stock_minimo'  => ['nullable', 'integer', 'min:0'],
            'ubicacion'     => ['nullable', 'string', 'max:120'],
        ]);

        $m = Medicamento::findOrFail($medicamentoId);

        DB::transaction(function () use ($m, $sucursalId, $pivotData) {
            if ($m->sucursales()->where('sucursal_id', $sucursalId)->exists()) {
                $m->sucursales()->updateExistingPivot($sucursalId, array_filter($pivotData, fn($v) => !is_null($v)));
            } else {
                $m->sucursales()->attach($sucursalId, array_filter($pivotData, fn($v) => !is_null($v)));
            }
        });

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
}
