<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Inventario\Medicamento;
use App\Models\Inventario\Lote;
use App\Models\Sucursal;

class MedicamentoSucursalController extends Controller
{
    public function edit($medicamentoId, $sucursalId)
    {
        $user = Auth::user();
        if (!$user->hasRole('Administrador')) {
            $permitidas = $user->sucursales()->pluck('sucursales.id')->toArray();
            if (!in_array((int)$sucursalId, $permitidas, true)) {
                abort(403, 'No tienes permiso en esta sucursal.');
            }
        }

        $m = Medicamento::with(['sucursales', 'lotes' => fn($q) => $q->where('sucursal_id', $sucursalId)])
            ->findOrFail($medicamentoId);

        $pivot = $m->sucursales()->where('sucursal_id', $sucursalId)->first()?->pivot;
        $sucursal = Sucursal::findOrFail($sucursalId);

        return view('inventario.medicamentos.edit', compact('m', 'pivot', 'sucursal'));
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

    public function destroy(Request $request, $medicamentoId, $sucursalId)
    {
        $user = Auth::user();
        if (!$user->hasRole('Administrador')) {
            $permitidas = $user->sucursales()->pluck('sucursales.id')->toArray();
            if (!in_array((int)$sucursalId, $permitidas, true)) {
                return back()->withErrors('No tienes permiso en esta sucursal.');
            }
        }

        $m = Medicamento::findOrFail($medicamentoId);

        DB::transaction(function () use ($m, $sucursalId) {
            Lote::where('medicamento_id', $m->id)->where('sucursal_id', $sucursalId)->delete();
            $m->sucursales()->detach($sucursalId);
        });

        return back()->with('success', 'Eliminado de la sucursal.');
    }
}
