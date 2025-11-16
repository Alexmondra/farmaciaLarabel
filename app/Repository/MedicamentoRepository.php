<?php

namespace App\Repository;

use App\Models\Inventario\Medicamento;
use App\Models\Inventario\Lote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MedicamentoRepository
{
    /**
     * Búsqueda principal para index con optimizaciones de BD.
     */
    public function buscarMedicamentos(string $q, array $ctx, int $perPage = 12): LengthAwarePaginator
    {
        $idsFiltro            = $ctx['ids_filtro'];
        $sucursalSeleccionada = $ctx['sucursal_seleccionada'];

        $query = Medicamento::query()
            ->with('categoria')
            ->when(
                $q,
                fn($s) =>
                $s->where(
                    fn($w) =>
                    $w->where('nombre', 'like', "$q%")
                        ->orWhere('codigo', 'like', "$q%")
                        ->orWhere('codigo_barra', 'like', "$q%")
                        ->orWhere('laboratorio', 'like', "$q%")
                )
            );

        // Filtro por sucursal (usuario o admin)
        $this->filtrarPorSucursales($query, $idsFiltro);

        // Si una sucursal está seleccionada → subconsulta con withSum
        if ($sucursalSeleccionada) {
            $this->agregarStockUnico($query, $sucursalSeleccionada->id);
        }

        $medicamentos = $query
            ->orderBy('nombre')
            ->paginate($perPage)
            ->withQueryString();

        return $this->postProcesar($medicamentos, $ctx);
    }


    /* =======================================================
       ==     MÉTODOS PRIVADOS (CORE LÓGICO OPTIMIZADO)     ==
       ======================================================= */

    /**
     * Aplica filtro de sucursales verificadas.
     */
    private function filtrarPorSucursales(Builder $query, ?array $idsFiltro): void
    {
        if (!is_array($idsFiltro)) {
            // admin sin filtro → cargamos todas las sucursales
            $query->with('sucursales');
            return;
        }

        if (empty($idsFiltro)) {
            // usuario sin sucursales asignadas
            $query->whereRaw('1=0');
            return;
        }

        // filtrar por las sucursales asignadas
        $query->whereHas(
            'sucursales',
            fn($w) =>
            $w->whereIn('sucursal_id', $idsFiltro)
        );

        $query->with([
            'sucursales' => fn($q) =>
            $q->whereIn('sucursales.id', $idsFiltro)
        ]);
    }

    /**
     * Agrega stock_unico usando subconsulta optimizada.
     */
    private function agregarStockUnico(Builder $query, int $sid): void
    {
        $query->withSum(
            [
                'lotes as stock_unico' => fn($q) =>
                $q->where('sucursal_id', $sid)
            ],
            'stock_actual'
        );

        // cargar solo la sucursal correspondiente
        $query->with([
            'sucursales' => fn($q) =>
            $q->where('sucursales.id', $sid)
        ]);
    }

    /**
     * Post procesamiento de medicamentos según contexto.
     */
    private function postProcesar(LengthAwarePaginator $medicamentos, array $ctx): LengthAwarePaginator
    {
        $ids                  = $medicamentos->pluck('id');
        $idsFiltro            = $ctx['ids_filtro'];
        $sucursalSeleccionada = $ctx['sucursal_seleccionada'];

        /* ------------------------------------------
           CASO A: HAY UNA SUCURSAL SELECCIONADA
           ------------------------------------------ */
        if ($sucursalSeleccionada) {
            $sid = $sucursalSeleccionada->id;

            $medicamentos->getCollection()->transform(function ($m) use ($sid, $sucursalSeleccionada) {

                $suc = $m->sucursales->firstWhere('id', $sid);
                $pivot = $suc?->pivot;

                $m->precio_v    = $pivot?->precio_venta;
                $m->stock_unico = (int) ($m->stock_unico ?? 0);

                $m->stock_por_sucursal = collect([[
                    'sucursal_id'   => $sid,
                    'sucursal_name' => $sucursalSeleccionada->nombre,
                    'stock'         => $m->stock_unico,
                ]]);

                return $m;
            });

            return $medicamentos;
        }


        /* ------------------------------------------
           CASO B: NO HAY SUCURSAL SELECCIONADA
                 → Mostrar desglose multi-sucursal
           ------------------------------------------ */

        // Consulta agregada de stocks
        $stocksRaw = Lote::select(
            'medicamento_id',
            'sucursal_id',
            DB::raw('SUM(stock_actual) as stock')
        )
            ->whereIn('medicamento_id', $ids)
            ->when(
                is_array($idsFiltro) && count($idsFiltro) > 0,
                fn($q) =>
                $q->whereIn('sucursal_id', $idsFiltro)
            )
            ->groupBy('medicamento_id', 'sucursal_id')
            ->get()
            ->groupBy('medicamento_id');

        // asignar datos a cada medicamento
        $medicamentos->getCollection()->transform(function ($m) use ($stocksRaw) {

            $rows = $stocksRaw->get($m->id, collect());
            $mapSucursales = $m->sucursales->keyBy('id');

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
       ==            MÉTODO DETALLE (SHOW) OPTIMIZADO       ==
       ======================================================= */

    public function detalle(int $id, array $ctx): array
    {
        $idsFiltro = $ctx['ids_filtro'];
        $medicamento = Medicamento::with('categoria')->findOrFail($id);

        // sucursales visibles para este usuario
        $rel = $medicamento->sucursales();

        if (is_array($idsFiltro) && count($idsFiltro) > 0) {
            $rel->whereIn('sucursales.id', $idsFiltro);
        }

        $sucursales = $rel->get();

        // lotes filtrados
        $lotes = Lote::where('medicamento_id', $id)
            ->when(
                is_array($idsFiltro) && count($idsFiltro) > 0,
                fn($q) =>
                $q->whereIn('sucursal_id', $idsFiltro)
            )
            ->orderBy('sucursal_id')
            ->orderBy('fecha_vencimiento')
            ->get();

        $lotesPorSucursal = $lotes->groupBy('sucursal_id');

        // detalle final
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
