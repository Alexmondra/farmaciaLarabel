<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicamento_id')->constrained('medicamentos')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();

            $table->string('codigo_lote', 80);
            $table->integer('cantidad')->default(0);
            $table->date('fecha_vencimiento')->nullable();
            $table->string('ubicacion')->nullable();
            $table->decimal('precio_compra', 10, 4)->nullable(); // costo unitario por lote
            $table->decimal('precio_oferta', 10, 2)->nullable();  // SOLO si hay remate/oferta; si no, NULL
            $table->text('observaciones')->nullable();
            $table->unique(['medicamento_id', 'sucursal_id', 'codigo_lote']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
