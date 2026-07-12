<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pedido_online_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_online_id')->constrained('pedidos_online')->cascadeOnDelete();
            $table->foreignId('medicamento_id')->constrained('medicamentos');
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->nullOnDelete();
            $table->string('descripcion', 255);
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->index(['medicamento_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_online_detalles');
    }
};
