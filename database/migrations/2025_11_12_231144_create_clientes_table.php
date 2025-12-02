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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();

            // IDENTIFICACIÓN
            $table->string('tipo_documento', 10)->nullable();  // DNI, RUC, CE
            $table->string('documento', 20)->unique();         // EL NÚMERO (7234..., 2010...)

            // DATOS
            $table->string('nombre', 255)->nullable();         // Para DNI (Nombres)
            $table->string('apellidos', 255)->nullable();      // Para DNI (Apellidos)
            $table->string('razon_social', 255)->nullable();   // NUEVO: Para RUC (Nombre de empresa)

            $table->enum('sexo', ['M', 'F'])->nullable();
            $table->date('fecha_nacimiento')->nullable();

            // PUNTOS (Lo que pediste)
            $table->integer('puntos')->default(0);             // Aquí guardas el saldo acumulado

            // CONTACTO
            $table->string('telefono', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('direccion', 255)->nullable();      // Importante para Facturas RUC

            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índices para búsqueda rápida
            $table->index('documento');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
