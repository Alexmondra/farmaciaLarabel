<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Medicamento;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoInventario;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MedicamentoSucursalController extends Controller
{
    /**
     * Agregar medicamento a una o varias sucursales
     */
    public function store(Request $request, Medicamento $medicamento)
    {
        $data = $request->validate([
            'sucursales' => 'required|array|min:1',
            'sucursales.*' => 'exists:sucursales,id',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock_inicial' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'ubicacion' => 'nullable|string|max:120',
            'codigo_lote' => 'nullable|string|max:80',
            'fecha_vencimiento' => 'nullable|date|after:today',
        ]);

        DB::beginTransaction();
        try {
            foreach ($data['sucursales'] as $sucursalId) {
                // Verificar si ya existe la relación
                if ($medicamento->sucursales()->where('sucursal_id', $sucursalId)->exists()) {
                    continue; // Saltar si ya existe
                }

                // Crear relación medicamento_sucursal
                $medicamento->sucursales()->attach($sucursalId, [
                    'precio_compra' => $data['precio_compra'],
                    'precio_venta' => $data['precio_venta'],
                    'stock_actual' => $data['stock_inicial'],
                    'stock_minimo' => $data['stock_minimo'],
                    'ubicacion' => $data['ubicacion'] ?? null,
                    'updated_by' => Auth::id()
                ]);

                // Crear lote inicial si hay stock
                if ($data['stock_inicial'] > 0) {
                    $codigoLote = $data['codigo_lote'] ?? 'LOTE-' . $medicamento->id . '-' . $sucursalId . '-' . time();
                    $fechaVencimiento = $data['fecha_vencimiento'] ?? null;
                    $estado = $fechaVencimiento && \Carbon\Carbon::parse($fechaVencimiento)->isPast() ? 'vencido' : 'vigente';
                    
                    $lote = Lote::create([
                        'medicamento_id' => $medicamento->id,
                        'sucursal_id' => $sucursalId,
                        'codigo_lote' => $codigoLote,
                        'fecha_vencimiento' => $fechaVencimiento,
                        'cantidad_inicial' => $data['stock_inicial'],
                        'cantidad_actual' => $data['stock_inicial'],
                        'estado' => $estado
                    ]);

                    // Crear movimiento de inventario
                    MovimientoInventario::create([
                        'tipo' => 'entrada',
                        'medicamento_id' => $medicamento->id,
                        'sucursal_id' => $sucursalId,
                        'lote_id' => $lote->id,
                        'cantidad' => $data['stock_inicial'],
                        'motivo' => 'Stock inicial',
                        'referencia' => 'Agregado a sucursal',
                        'user_id' => Auth::id(),
                        'stock_final' => $data['stock_inicial']
                    ]);
                }
            }

            DB::commit();
            
            $sucursalesNombres = Sucursal::whereIn('id', $data['sucursales'])->pluck('nombre')->toArray();
            return redirect()->back()->with('success', 
                'Medicamento agregado correctamente a: ' . implode(', ', $sucursalesNombres));
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al agregar medicamento a sucursales: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar precios y configuración de medicamento en una sucursal específica
     */
    public function update(Request $request, Medicamento $medicamento, Sucursal $sucursal)
    {
        $data = $request->validate([
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'ubicacion' => 'nullable|string|max:120',
        ]);

        // Verificar que la relación existe
        if (!$medicamento->sucursales()->where('sucursal_id', $sucursal->id)->exists()) {
            return redirect()->back()->with('error', 'El medicamento no está disponible en esta sucursal.');
        }

        // Actualizar datos en medicamento_sucursal
        $medicamento->sucursales()->updateExistingPivot($sucursal->id, [
            'precio_compra' => $data['precio_compra'],
            'precio_venta' => $data['precio_venta'],
            'stock_minimo' => $data['stock_minimo'],
            'ubicacion' => $data['ubicacion'] ?? null,
            'updated_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', 
            'Configuración actualizada correctamente para la sucursal ' . $sucursal->nombre);
    }

    /**
     * Quitar medicamento de una sucursal
     */
    public function destroy(Medicamento $medicamento, Sucursal $sucursal)
    {
        // Verificar que la relación existe
        if (!$medicamento->sucursales()->where('sucursal_id', $sucursal->id)->exists()) {
            return redirect()->back()->with('error', 'El medicamento no está disponible en esta sucursal.');
        }

        // Verificar si hay stock disponible
        $stockActual = $medicamento->lotes()->where('sucursal_id', $sucursal->id)->sum('cantidad_actual');
        if ($stockActual > 0) {
            return redirect()->back()->with('error', 
                'No se puede quitar el medicamento de la sucursal porque aún tiene stock disponible (' . $stockActual . ' unidades).');
        }

        // Eliminar relación
        $medicamento->sucursales()->detach($sucursal->id);

        return redirect()->back()->with('success', 
            'Medicamento removido correctamente de la sucursal ' . $sucursal->nombre);
    }

    /**
     * Agregar lote a un medicamento en una sucursal específica
     */
    public function agregarLote(Request $request, Medicamento $medicamento, Sucursal $sucursal)
    {
        $data = $request->validate([
            'codigo_lote' => 'required|string|max:80',
            'fecha_vencimiento' => 'nullable|date|after:today',
            'cantidad_inicial' => 'required|integer|min:1',
        ]);

        // Verificar que la relación medicamento-sucursal existe
        if (!$medicamento->sucursales()->where('sucursal_id', $sucursal->id)->exists()) {
            return redirect()->back()->with('error', 'El medicamento no está disponible en esta sucursal.');
        }

        DB::beginTransaction();
        try {
            $estado = $data['fecha_vencimiento'] && \Carbon\Carbon::parse($data['fecha_vencimiento'])->isPast() ? 'vencido' : 'vigente';
            
            $lote = Lote::create([
                'medicamento_id' => $medicamento->id,
                'sucursal_id' => $sucursal->id,
                'codigo_lote' => $data['codigo_lote'],
                'fecha_vencimiento' => $data['fecha_vencimiento'],
                'cantidad_inicial' => $data['cantidad_inicial'],
                'cantidad_actual' => $data['cantidad_inicial'],
                'estado' => $estado
            ]);

            // Crear movimiento de inventario
            MovimientoInventario::create([
                'tipo' => 'entrada',
                'medicamento_id' => $medicamento->id,
                'sucursal_id' => $sucursal->id,
                'lote_id' => $lote->id,
                'cantidad' => $data['cantidad_inicial'],
                'motivo' => 'Nuevo lote',
                'referencia' => 'Lote: ' . $data['codigo_lote'],
                'user_id' => Auth::id(),
                'stock_final' => $medicamento->lotes()->where('sucursal_id', $sucursal->id)->sum('cantidad_actual')
            ]);

            // Actualizar stock_actual en medicamento_sucursal
            $stockTotal = $medicamento->lotes()->where('sucursal_id', $sucursal->id)->sum('cantidad_actual');
            $medicamento->sucursales()->updateExistingPivot($sucursal->id, [
                'stock_actual' => $stockTotal,
                'updated_by' => Auth::id()
            ]);

            DB::commit();
            
            return redirect()->back()->with('success', 
                'Lote agregado correctamente: ' . $data['codigo_lote'] . ' (' . $data['cantidad_inicial'] . ' unidades)');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al agregar lote: ' . $e->getMessage());
        }
    }
}

