<?php

namespace App\Repositories;

use App\Models\Inventario\Medicamento;
use App\Models\Inventario\Lote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MedicamentoRepository
{
    /**
     * Búsqueda principal EVOLUCIONADA: Soporta Filtros de Precio + Omnibox
     */
    public function buscarMedicamentos(string $q, array $ctx, int $perPage = 10): LengthAwarePaginator
    {
        $idsFiltro            = $ctx['ids_filtro'];
        $sucursalSeleccionada = $ctx['sucursal_seleccionada'];

        $min = $ctx['min'] ?? null;
        $max = $ctx['max'] ?? null;

        $query = Medicamento::query()->with('categoria');

        /* =======================================================
           ESTRATEGIA A: HAY SUCURSAL SELECCIONADA
           ======================================================= */
        if ($sucursalSeleccionada) {
            $sid = $sucursalSeleccionada->id;

            // 1. Usamos JOIN para el PRECIO (porque el precio sí está en esta tabla)
            $query->join('medicamento_sucursal', 'medicamentos.id', '=', 'medicamento_sucursal.medicamento_id')
                ->where('medicamento_sucursal.sucursal_id', $sid)
                ->whereNull('medicamento_sucursal.deleted_at')
                ->select([
                    'medicamentos.*',
                    'medicamento_sucursal.precio_venta as precio_v' // Este sí existe
                ]);

            // 2. CORRECCIÓN: Usamos una SUBCONSULTA para sumar el STOCK real desde 'lotes'
            // En lugar de pedir una columna 'stock_total' que no existe.
            $query->selectSub(function ($sub) use ($sid) {
                $sub->from('lotes')
                    ->whereColumn('lotes.medicamento_id', 'medicamentos.id')
                    ->where('lotes.sucursal_id', $sid)
                    ->selectRaw('COALESCE(SUM(stock_actual), 0)'); // Suma los lotes o devuelve 0
            }, 'stock_unico');

            // 3. Filtros de Precio
            if ($min) $query->where('medicamento_sucursal.precio_venta', '>=', $min);
            if ($max) $query->where('medicamento_sucursal.precio_venta', '<=', $max);
        }
        /* =======================================================
           ESTRATEGIA B: VISTA GLOBAL
           ======================================================= */ else {
            $this->filtrarPorSucursales($query, $idsFiltro);
        }

        /* =======================================================
           BUSCADOR "OMNIBOX"
           ======================================================= */
        if ($q) {
            $query->where(function (Builder $k) use ($q) {
                $k->where('medicamentos.nombre', 'like', "%$q%")
                    ->orWhere('medicamentos.codigo', 'like', "$q%")
                    ->orWhere('medicamentos.codigo_barra', 'like', "$q%")
                    ->orWhere('medicamentos.laboratorio', 'like', "%$q%")
                    ->orWhere('medicamentos.descripcion', 'like', "%$q%")
                    ->orWhereHas('categoria', function ($cat) use ($q) {
                        $cat->where('nombre', 'like', "%$q%");
                    });
            });
        }

        // Ejecutamos la paginación
        $paginator = $query->orderBy('medicamentos.nombre', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        if ($sucursalSeleccionada) {
            return $paginator;
        } else {
            return $this->postProcesarGlobal($paginator, $idsFiltro);
        }
    }

    /* =======================================================
       ==     MÉTODOS PRIVADOS DE AYUDA                     ==
       ======================================================= */

    private function filtrarPorSucursales(Builder $query, ?array $idsFiltro): void
    {
        if (!is_array($idsFiltro)) {
            $query->with('sucursales'); // Admin total
            return;
        }
        if (empty($idsFiltro)) {
            $query->whereRaw('1=0'); // Usuario sin permisos
            return;
        }
        // Usuario con sucursales específicas
        $query->whereHas('sucursales', fn($w) => $w->whereIn('sucursal_id', $idsFiltro));
        $query->with(['sucursales' => fn($q) => $q->whereIn('sucursales.id', $idsFiltro)]);
    }

    /**
     * Versión simplificada de tu postProcesar, solo para la vista GLOBAL.
     * (Calcula la suma de stocks de varias sucursales)
     */
    private function postProcesarGlobal(LengthAwarePaginator $medicamentos, ?array $idsFiltro): LengthAwarePaginator
    {
        $ids = $medicamentos->pluck('id');

        // Consulta agregada de stocks usando Lotes (Tu lógica original intacta)
        $stocksRaw = Lote::select('medicamento_id', 'sucursal_id', DB::raw('SUM(stock_actual) as stock'))
            ->whereIn('medicamento_id', $ids)
            ->when(is_array($idsFiltro) && count($idsFiltro) > 0, fn($q) => $q->whereIn('sucursal_id', $idsFiltro))
            ->groupBy('medicamento_id', 'sucursal_id')
            ->get()
            ->groupBy('medicamento_id');

        $medicamentos->getCollection()->transform(function ($m) use ($stocksRaw) {
            $rows = $stocksRaw->get($m->id, collect());
            $mapSucursales = $m->sucursales ? $m->sucursales->keyBy('id') : collect();

            $m->stock_por_sucursal = $rows->map(function ($row) use ($mapSucursales) {
                $sucursal = $mapSucursales->get($row->sucursal_id);
                return [
                    'sucursal_id'   => $row->sucursal_id,
                    'sucursal_name' => $sucursal?->nombre ?? ('Sucursal ' . $row->sucursal_id),
                    'stock'         => (int) $row->stock,
                ];
            })->values();

            // En vista global, estos son null para evitar confusión
            $m->precio_v    = null;
            $m->stock_unico = null;

            return $m;
        });

        return $medicamentos;
    }

    /* =======================================================
       ==            MÉTODO DETALLE (SHOW) - INTACTO        ==
       ======================================================= */
    public function detalle(int $id, array $ctx): array
    {
        // Tu código original de detalle estaba perfecto, lo mantenemos igual.
        $idsFiltro = $ctx['ids_filtro'];
        $medicamento = Medicamento::with('categoria')->findOrFail($id);

        $rel = $medicamento->sucursales();
        if (is_array($idsFiltro) && count($idsFiltro) > 0) {
            $rel->whereIn('sucursales.id', $idsFiltro);
        }
        $sucursales = $rel->get();

        $lotes = Lote::where('medicamento_id', $id)
            ->when(is_array($idsFiltro) && count($idsFiltro) > 0, fn($q) => $q->whereIn('sucursal_id', $idsFiltro))
            ->orderBy('sucursal_id')->orderBy('fecha_vencimiento')
            ->get();

        $lotesPorSucursal = $lotes->groupBy('sucursal_id');

        $detalle = $sucursales->map(function ($suc) use ($lotesPorSucursal) {
            $lista = $lotesPorSucursal->get($suc->id, collect());
            return [
                'sucursal'    => $suc,
                'precio'      => $suc->pivot->precio_venta ?? null,
                'stock_total' => $lista->sum('stock_actual'),
                'lotes'       => $lista,
            ];
        });

        return [
            'medicamento'       => $medicamento,
            'sucursalesDetalle' => $detalle,
        ];
    }
}
