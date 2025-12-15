<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_guias_remision', function (Blueprint $table) {
            $table->id();

            $table->foreignId('guia_remision_id')
                ->constrained('guias_remision')
                ->onDelete('cascade');

            // PRODUCTO
            $table->unsignedBigInteger('medicamento_id')->nullable();
            $table->unsignedBigInteger('lote_id')->nullable();
            $table->string('codigo_producto', 50)->nullable();
            $table->string('descripcion', 250);

            $table->decimal('cantidad', 12, 4);
            $table->string('unidad_medida', 4)->default('NIU'); // NIU = Unidad

            $table->timestamps();

            $table->foreign('lote_id')->references('id')->on('lotes')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_guias_remision');
    }
};
