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
            $table->integer('stock_minimo')->default(0);
            $table->decimal('precio_venta', 10, 2)->nullable(); // ÚNICO precio normal por sucursal
            $table->timestamp('deleted_at')->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['medicamento_id', 'sucursal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicamento_sucursal');
    }
};
