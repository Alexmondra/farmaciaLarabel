<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Relaciones principales
            $table->unsignedBigInteger('caja_sesion_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('user_id'); // Vendedor

            // Datos del Comprobante
            $table->string('tipo_comprobante', 20);   // BOLETA, FACTURA, TICKET
            $table->string('serie', 10)->nullable();  // B001, F001
            $table->string('numero', 20)->nullable(); // 0000045
            $table->dateTime('fecha_emision');

            // --- BLOQUE ECONÓMICO (TOTALES) ---
            $table->decimal('total_bruto', 12, 2)->default(0);      // Suma de precios normales
            $table->decimal('total_descuento', 12, 2)->default(0);  // Descuentos aplicados
            $table->decimal('total_neto', 12, 2)->default(0);       // Lo que paga el cliente (P. Final)

            // --- BLOQUE DE IMPUESTOS (SUNAT - DESGLOSE) ---
            // Estos campos son obligatorios para generar el XML legal
            $table->decimal('op_gravada', 12, 2)->default(0);    // Base imponible (Valor Venta sin IGV)
            $table->decimal('op_exonerada', 12, 2)->default(0);  // Para Selva o productos especiales (Cáncer, etc.)
            $table->decimal('op_inafecta', 12, 2)->default(0);   // Operaciones inafectas
            $table->decimal('total_igv', 12, 2)->default(0);     // El dinero que es para SUNAT
            $table->decimal('porcentaje_igv', 5, 2)->default(18.00); // Guardamos el % histórico (ej. 18% o 0%)

            // Datos de Pago y Estado
            $table->string('medio_pago', 30)->nullable(); // EFECTIVO, YAPE, PLIN, TARJETA
            $table->enum('estado', ['EMITIDA', 'ANULADA', 'ACEPTADA_SUNAT', 'RECHAZADA_SUNAT'])->default('EMITIDA');
            $table->text('observaciones')->nullable();

            // --- BLOQUE DE RESPUESTA DE FACTURACIÓN ELECTRÓNICA ---
            $table->text('ruta_xml')->nullable();         // Ruta del XML firmado
            $table->text('ruta_cdr')->nullable();         // Ruta del ZIP de respuesta (CDR)
            $table->text('ruta_pdf')->nullable();         // (Opcional) Si guardas el PDF generado
            $table->string('codigo_error_sunat', 10)->nullable(); // '0'=Éxito, Otros=Error
            $table->text('mensaje_sunat')->nullable();    // Mensaje de respuesta de SUNAT
            $table->text('hash')->nullable();             // CÓDIGO CLAVE para generar el QR

            $table->timestamps();

            // Foreign Keys
            $table->foreign('caja_sesion_id')->references('id')->on('caja_sesiones');
            $table->foreign('sucursal_id')->references('id')->on('sucursales');
            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->foreign('user_id')->references('id')->on('users');

            // Índices para optimizar reportes
            $table->index(['sucursal_id', 'fecha_emision']);
            $table->index(['caja_sesion_id']);
            $table->index(['cliente_id']);
            $table->index(['tipo_comprobante', 'serie', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
