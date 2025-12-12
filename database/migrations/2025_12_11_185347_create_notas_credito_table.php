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
        Schema::create('notas_credito', function (Blueprint $table) {
            $table->id();

            // --- RELACIONES ---
            // Vinculamos con la venta original
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('restrict');
            // Vinculamos con la sucursal para reportes y control de series
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('restrict');

            // --- DATOS DEL COMPROBANTE (NOTA DE CRÉDITO) ---
            $table->string('serie', 4);        // Ej: FC01, BC01
            $table->integer('numero');         // Correlativo: 1, 2, 3...
            $table->dateTime('fecha_emision');
            $table->string('tipo_nota', 2)->default('07'); // Siempre '07' para Nota de Crédito
            $table->string('tipo_moneda', 3)->default('PEN');

            // --- MOTIVO DE ANULACIÓN ---
            $table->string('cod_motivo', 2);   // Ej: '01' (Anulación de la operación)
            $table->string('descripcion_motivo'); // Ej: "Error en el precio", "Devolución total"

            // --- ARCHIVOS DE SUNAT (De la anulación) ---
            $table->string('ruta_xml')->nullable();
            $table->string('ruta_cdr')->nullable();
            $table->string('ruta_pdf')->nullable(); // Opcional, si generas PDF de la nota
            $table->string('hash')->nullable();

            // --- RESPUESTA DE SUNAT ---
            $table->boolean('sunat_exito')->default(false); // true = Aceptada, false = Rechazada/Error
            $table->text('mensaje_sunat')->nullable();      // Respuesta legible
            $table->string('codigo_error_sunat')->nullable(); // Código de error si falla

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_credito');
    }
};
