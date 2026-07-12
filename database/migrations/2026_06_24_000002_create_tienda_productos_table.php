<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tienda_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicamento_id')->constrained('medicamentos')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->string('slug', 220);
            $table->string('nombre_web', 220)->nullable();
            $table->text('descripcion_web')->nullable();
            $table->decimal('precio_web', 12, 2)->nullable();
            $table->string('stock_modo', 30)->default('stock_sucursal');
            $table->unsignedInteger('stock_web')->nullable();
            $table->boolean('visible')->default(false);
            $table->boolean('destacado')->default(false);
            $table->timestamps();

            $table->unique(['medicamento_id', 'sucursal_id']);
            $table->unique('slug');
            $table->index(['visible', 'destacado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tienda_productos');
    }
};
