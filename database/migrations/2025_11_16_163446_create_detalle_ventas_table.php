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

            // Relaciones
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('lote_id')->constrained('lotes');
            $table->foreignId('medicamento_id')->constrained('medicamentos');
            $table->integer('cantidad');

            // --- PRECIOS Y VALORES UNITARIOS (SUNAT) ---
            // 1. Lo que ve el cliente
            $table->decimal('precio_unitario', 12, 2); // Precio con IGV (ej: 11.80)

            // 2. Lo que ve la SUNAT (Cálculos internos)
            $table->decimal('valor_unitario', 12, 2);  // Precio SIN IGV (ej: 10.00)
            $table->decimal('igv', 12, 2)->default(0); // Impuesto de este ítem (ej: 1.80)

            // 3. Código de Afectación (Vital para Selva vs Costa)
            // '10': Gravado - Operación Onerosa (Lima)
            // '20': Exonerado - Operación Onerosa (Selva)
            // '30': Inafecto (Muestras médicas, bonificaciones)
            $table->string('tipo_afectacion', 5)->default('10');

            $table->decimal('descuento_unitario', 12, 2)->default(0);

            // --- SUBTOTALES ---
            $table->decimal('subtotal_bruto', 12, 2);     // Cantidad * Precio
            $table->decimal('subtotal_descuento', 12, 2)->default(0);
            $table->decimal('subtotal_neto', 12, 2);      // (Bruto - Dscto)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
    }
};
