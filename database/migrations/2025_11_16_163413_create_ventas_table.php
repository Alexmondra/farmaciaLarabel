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

            $table->unsignedBigInteger('caja_sesion_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('user_id'); // vendedor

            $table->string('tipo_comprobante', 20);   // BOLETA, FACTURA...
            $table->string('serie', 10)->nullable();
            $table->string('numero', 20)->nullable();
            $table->dateTime('fecha_emision');

            $table->decimal('total_bruto', 10, 2)->default(0);
            $table->decimal('total_descuento', 10, 2)->default(0);
            $table->decimal('total_neto', 10, 2)->default(0);

            $table->string('medio_pago', 30)->nullable(); // EFECTIVO, YAPE...
            $table->enum('estado', ['EMITIDA', 'ANULADA'])->default('EMITIDA');
            $table->text('observaciones')->nullable();

            $table->timestamps();

            // FKs
            $table->foreign('caja_sesion_id')->references('id')->on('caja_sesiones');
            $table->foreign('sucursal_id')->references('id')->on('sucursales');
            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->foreign('user_id')->references('id')->on('users');

            // Índices útiles
            $table->index(['sucursal_id', 'fecha_emision']);
            $table->index(['caja_sesion_id']);
            $table->index(['cliente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
