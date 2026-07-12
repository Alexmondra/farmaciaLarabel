<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pagos_online', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_online_id')->constrained('pedidos_online')->cascadeOnDelete();
            $table->string('proveedor', 50)->nullable();
            $table->string('referencia_externa', 120)->nullable();
            $table->string('estado', 30)->default('PENDIENTE');
            $table->decimal('monto', 12, 2)->default(0);
            $table->string('moneda', 10)->default('PEN');
            $table->json('payload')->nullable();
            $table->timestamp('pagado_at')->nullable();
            $table->timestamps();

            $table->index(['proveedor', 'referencia_externa']);
            $table->index(['estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_online');
    }
};
