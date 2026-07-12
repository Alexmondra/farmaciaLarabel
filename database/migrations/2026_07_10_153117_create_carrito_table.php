<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carrito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('tienda_producto_id')->constrained('tienda_productos');
            $table->unsignedInteger('cantidad')->default(1);
            $table->timestamps();

            $table->unique(['cliente_id', 'tienda_producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrito');
    }
};
