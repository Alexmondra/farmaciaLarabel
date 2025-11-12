<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class LotesSeeder extends Seeder
{
    public function run(): void
    {
        // Recuperar combos medicamento-sucursal
        $meds = DB::table('medicamentos')->select('id', 'codigo')->orderBy('id')->get();
        $sucursales = DB::table('sucursales')->orderBy('id')->limit(3)->pluck('id')->all();

        if ($meds->count() < 5 || count($sucursales) < 3) {
            $this->command->warn('Se requieren 5 medicamentos y 3 sucursales para este seeder.');
            return;
        }

        Schema::disableForeignKeyConstraints();
        DB::table('lotes')->truncate();
        Schema::enableForeignKeyConstraints();

        $ahora = Carbon::now();
        $lotesInsert = [];

        // Helper para ubicaciones bonitas
        $ubicaciones = ['Estante A1', 'Estante B2', 'Estante C3', 'Nevera N1', 'Vitrina V1'];

        // Costos base por medicamento (para construir precios_compra)
        $costosBase = [
            'PARA500' => 1.10,
            'IBU400'  => 1.95,
            'AMOX500' => 3.00,
            'LORA10'  => 1.05,
            'OMEP20'  => 2.10,
        ];

        // Para cada medicamento y sucursal: 1–3 lotes
        foreach ($meds as $m) {
            foreach ($sucursales as $sidx => $sucursalId) {
                $numLotes = rand(1, 3);

                for ($i = 1; $i <= $numLotes; $i++) {
                    $codigoLote = sprintf('%s-S%02d-L%02d', $m->codigo, $sidx + 1, $i);

                    // Cantidad y costos con pequeñas variaciones
                    $cantidad = [60, 80, 100, 120, 150][array_rand([0, 1, 2, 3, 4])];
                    $precioCompra = $costosBase[$m->codigo] + (rand(0, 20) / 100); // +0.00 a +0.20

                    // Vencimiento entre 4 y 14 meses (y algunos más cortos)
                    $meses = [4, 6, 8, 10, 12, 14][array_rand([0, 1, 2, 3, 4, 5])];
                    $fechaVto = $ahora->copy()->addMonths($meses);

                    // 1 de cada 5 lotes en oferta por cercanía a vencimiento
                    $enOferta = (rand(1, 5) === 1);
                    $precioOferta = $enOferta ? round(($precioCompra + 0.30), 2) : null; // oferta leve sobre costo

                    $lotesInsert[] = [
                        'medicamento_id'    => $m->id,
                        'sucursal_id'       => $sucursalId,
                        'codigo_lote'       => $codigoLote,
                        'cantidad'          => $cantidad,
                        'fecha_vencimiento' => $fechaVto,
                        'ubicacion'         => $ubicaciones[array_rand($ubicaciones)],
                        'precio_compra'     => $precioCompra,
                        'precio_oferta'     => $precioOferta,
                        'observaciones'     => $enOferta ? 'Remate por fecha próxima' : null,
                        'created_at'        => $ahora,
                        'updated_at'        => $ahora,
                    ];
                }
            }
        }

        DB::table('lotes')->insert($lotesInsert);

        // Recalcular stock_total en medicamento_sucursal
        $saldos = DB::table('lotes')
            ->select('medicamento_id', 'sucursal_id', DB::raw('SUM(cantidad) as total'))
            ->groupBy('medicamento_id', 'sucursal_id')
            ->get();

        foreach ($saldos as $s) {
            DB::table('medicamento_sucursal')
                ->where('medicamento_id', $s->medicamento_id)
                ->where('sucursal_id', $s->sucursal_id)
                ->update([
                    'stock_total' => (int) $s->total,
                    'updated_at'  => Carbon::now(),
                ]);
        }
    }
}
