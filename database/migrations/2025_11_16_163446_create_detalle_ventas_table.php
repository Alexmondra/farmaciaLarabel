<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('venta_id')->constrained('ventas');
            $table->foreignId('lote_id')->constrained('lotes');
            $table->foreignId('medicamento_id')->constrained('medicamentos');

            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2); // Cambiado a 2 decimales
            $table->decimal('descuento_unitario', 10, 2)->default(0); // Cambiado a 2

            $table->decimal('subtotal_bruto', 10, 2);
            $table->decimal('subtotal_descuento', 10, 2)->default(0);
            $table->decimal('subtotal_neto', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
    }
};
