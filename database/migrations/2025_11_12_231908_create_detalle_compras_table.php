<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detalle_compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();

            // El lote específico que estás alimentando (creando o actualizando)
            $table->foreignId('lote_id')->constrained('lotes');

            $table->integer('cantidad_recibida')->comment('Cuántos entraron');

            // El precio que pagaste ESE DÍA (para el historial y el cálculo promedio)
            $table->decimal('precio_compra_unitario', 10, 4);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_compras');
    }
};
