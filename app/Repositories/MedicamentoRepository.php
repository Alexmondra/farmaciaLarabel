<?php

namespace App\Repositories;

use App\Models\Inventario\Medicamento;
use App\Models\Inventario\Lote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MedicamentoRepository
{


    public function buscarMedicamentos(string $q, array $ctx, bool $soloConStock = false, int $perPage = 10): LengthAwarePaginator
    {
        $idsFiltro            = $ctx['ids_filtro'];
        $sucursalSeleccionada = $ctx['sucursal_seleccionada'];

        $min = $ctx['min'] ?? null;
        $max = $ctx['max'] ?? null;
        $hoy = Carbon::today();

        $query = Medicamento::query()->with('categoria');

        // Variable auxiliar para usar en el scope del filtro de stock
        $targetSucursalId = $sucursalSeleccionada ? $sucursalSeleccionada->id : null;

        /* =======================================================
       ESTRATEGIA A: HAY SUCURSAL SELECCIONADA
       ======================================================= */
        if ($sucursalSeleccionada) {
            $sid = $sucursalSeleccionada->id;

            $query->join('medicamento_sucursal', 'medicamentos.id', '=', 'medicamento_sucursal.medicamento_id')
                ->where('medicamento_sucursal.sucursal_id', $sid)
                ->whereNull('medicamento_sucursal.deleted_at')
                ->select([
                    'medicamentos.*',
                    'medicamento_sucursal.precio_venta as precio_v'
                ]);

            $query->selectSub(function ($sub) use ($sid, $hoy) {
                $sub->from('lotes')
                    ->whereColumn('lotes.medicamento_id', 'medicamentos.id')
                    ->where('lotes.sucursal_id', $sid)
                    ->where(function ($qDate) use ($hoy) {
                        $qDate->whereDate('fecha_vencimiento', '>=', $hoy)
                            ->orWhereNull('fecha_vencimiento');
                    })
                    ->selectRaw('COALESCE(SUM(stock_actual), 0)');
            }, 'stock_unico');

            if ($min) $query->where('medicamento_sucursal.precio_venta', '>=', $min);
            if ($max) $query->where('medicamento_sucursal.precio_venta', '<=', $max);
        }
        /* =======================================================
       ESTRATEGIA B: VISTA GLOBAL
       ======================================================= */ else {
            $this->filtrarPorSucursales($query, $idsFiltro);
        }

        /* =======================================================
       BUSCADOR
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

        /* =======================================================
       FILTRO: SOLO CON STOCK > 0
       ======================================================= */
        if ($soloConStock) {
            // Aquí está la magia: Usamos whereHas contra 'lotes' aplicando la misma regla de fecha
            $query->whereHas('lotes', function ($subQuery) use ($sucursalSeleccionada, $idsFiltro, $hoy) {

                // 1. Filtrar por sucursal(es)
                if ($sucursalSeleccionada) {
                    $subQuery->where('sucursal_id', $sucursalSeleccionada->id);
                } else {
                    $subQuery->whereIn('sucursal_id', $idsFiltro);
                }

                // 2. Solo stock físico real
                $subQuery->where('stock_actual', '>', 0);

                // 3. REGLA DE ORO: No Vencidos (Fecha >= Hoy o Nula)
                $subQuery->where(function ($qDate) use ($hoy) {
                    $qDate->whereDate('fecha_vencimiento', '>=', $hoy)
                        ->orWhereNull('fecha_vencimiento');
                });
            });
        }

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
        $query->whereHas('sucursales', fn($w) => $w->whereIn('sucursal_id', $idsFiltro));
        $query->with(['sucursales' => fn($q) => $q->whereIn('sucursales.id', $idsFiltro)]);
    }

    /**
     * Versión simplificada de tu postProcesar, solo para la vista GLOBAL.
     * (Calcula la suma de stocks de varias sucursales SIN contar vencidos)
     */
    private function postProcesarGlobal(LengthAwarePaginator $medicamentos, ?array $idsFiltro): LengthAwarePaginator
    {
        $ids = $medicamentos->pluck('id');
        $hoy = Carbon::today();

        // CORRECCIÓN GLOBAL: Sumar stock ignorando vencidos
        $stocksRaw = Lote::select('medicamento_id', 'sucursal_id', DB::raw('SUM(stock_actual) as stock'))
            ->whereIn('medicamento_id', $ids)
            ->when(is_array($idsFiltro) && count($idsFiltro) > 0, fn($q) => $q->whereIn('sucursal_id', $idsFiltro))
            // FILTRO CLAVE:
            ->where(function ($qDate) use ($hoy) {
                $qDate->whereDate('fecha_vencimiento', '>=', $hoy)
                    ->orWhereNull('fecha_vencimiento');
            })
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

            $m->precio_v    = null;
            $m->stock_unico = null;

            return $m;
        });

        return $medicamentos;
    }

    /* =======================================================
       ==            MÉTODO DETALLE (SHOW)                  ==
       ======================================================= */
    public function detalle(int $id, array $ctx): array
    {
        $idsFiltro = $ctx['ids_filtro'] ?? [];
        $medicamento = Medicamento::with('categoria')->findOrFail($id);
        $hoy = Carbon::today();

        $rel = $medicamento->sucursales();
        if (!empty($idsFiltro)) {
            $rel->whereIn('sucursales.id', $idsFiltro);
        }
        $sucursales = $rel->get();

        $lotes = Lote::where('medicamento_id', $id)
            ->where('stock_actual', '>', 0) // Solo lotes con existencia
            ->where(function ($q) use ($hoy) {
                $q->whereNull('fecha_vencimiento')
                    ->orWhere('fecha_vencimiento', '>=', $hoy);
            })
            ->when(!empty($idsFiltro), function ($q) use ($idsFiltro) {
                return $q->whereIn('sucursal_id', $idsFiltro);
            })
            ->orderBy('sucursal_id')
            ->orderBy('fecha_vencimiento', 'asc') // Primero los que vencen pronto
            ->get();

        $lotesPorSucursal = $lotes->groupBy('sucursal_id');

        $detalle = $sucursales->map(function ($suc) use ($lotesPorSucursal) {
            $todosLosLotesActivos = $lotesPorSucursal->get($suc->id, collect());

            $stockVendible = $todosLosLotesActivos->sum('stock_actual');

            $lotesLimitados = $todosLosLotesActivos->take(5);

            return [
                'sucursal'    => $suc,
                'precio'      => $suc->pivot->precio_venta ?? null,
                'stock_total' => $stockVendible,
                'lotes'       => $lotesLimitados,
                'total_lotes_activos' => $todosLosLotesActivos->count()
            ];
        });

        return [
            'medicamento'       => $medicamento,
            'sucursalesDetalle' => $detalle,
        ];
    }
}
