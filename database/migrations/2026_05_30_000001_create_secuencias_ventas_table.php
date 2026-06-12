<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secuencias_ventas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sucursal_id');
            $table->string('tipo_comprobante', 20); // BOLETA, FACTURA, TICKET
            $table->string('serie', 10);
            $table->unsignedBigInteger('ultimo_numero')->default(0);
            $table->timestamps();

            // Un registro por sucursal + tipo + serie
            $table->unique(['sucursal_id', 'tipo_comprobante', 'serie'], 'secuencias_ventas_unique');

            $table->foreign('sucursal_id')->references('id')->on('sucursales');
        });

        // Sembrar con los valores actuales de la tabla ventas y notas de credito
        $this->seedFromExistingSales();
        $this->seedFromExistingCreditNotes();
    }

    private function seedFromExistingSales(): void
    {
        $maximos = DB::table('ventas')
            ->select('sucursal_id', 'tipo_comprobante', 'serie', DB::raw('MAX(numero) as maximo'))
            ->whereNotNull('serie')
            ->groupBy('sucursal_id', 'tipo_comprobante', 'serie')
            ->get();

        foreach ($maximos as $row) {
            DB::table('secuencias_ventas')->insert([
                'sucursal_id'      => $row->sucursal_id,
                'tipo_comprobante' => $row->tipo_comprobante,
                'serie'            => $row->serie,
                'ultimo_numero'    => $row->maximo,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }

    private function seedFromExistingCreditNotes(): void
    {
        $tipoComprobante = "CASE WHEN v.tipo_comprobante = 'FACTURA' THEN 'NC_FACTURA' ELSE 'NC_BOLETA' END";

        $maximos = DB::table('notas_credito as nc')
            ->join('ventas as v', 'v.id', '=', 'nc.venta_id')
            ->select('nc.sucursal_id', 'nc.serie')
            ->selectRaw("{$tipoComprobante} as tipo_comprobante")
            ->selectRaw('MAX(nc.numero) as maximo')
            ->whereNotNull('nc.serie')
            ->groupBy('nc.sucursal_id', 'nc.serie', DB::raw($tipoComprobante))
            ->get();

        foreach ($maximos as $row) {
            DB::table('secuencias_ventas')->updateOrInsert(
                [
                    'sucursal_id'      => $row->sucursal_id,
                    'tipo_comprobante' => $row->tipo_comprobante,
                    'serie'            => $row->serie,
                ],
                [
                    'ultimo_numero' => $row->maximo,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('secuencias_ventas');
    }
};
