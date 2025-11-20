<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja_sesiones', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('user_id');

            $table->dateTime('fecha_apertura');
            $table->decimal('saldo_inicial', 10, 2);

            $table->dateTime('fecha_cierre')->nullable();
            $table->decimal('saldo_teorico', 10, 2)->nullable();
            $table->decimal('saldo_real', 10, 2)->nullable();
            $table->decimal('diferencia', 10, 2)->nullable();
            $table->enum('estado', ['ABIERTO', 'CERRADO'])->default('ABIERTO');
            $table->text('observaciones')->nullable();

            $table->timestamps();

            // FKs
            $table->foreign('sucursal_id')->references('id')->on('sucursales');
            $table->foreign('user_id')->references('id')->on('users');

            // Índices útiles
            $table->index(['sucursal_id', 'user_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_sesiones');
    }
};
