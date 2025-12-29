<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medicamento_sucursal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicamento_id')->constrained('medicamentos')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();

            $table->integer('stock_minimo')->default(10);
            $table->decimal('precio_venta', 12, 2)->default(0);
            $table->decimal('precio_blister', 12, 2)->nullable();
            $table->decimal('precio_caja', 12, 2)->nullable();

            // CONTROL
            $table->boolean('activo')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->unique(['medicamento_id', 'sucursal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicamento_sucursal');
    }
};
