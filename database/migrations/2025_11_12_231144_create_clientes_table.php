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
            $table->string('tipo_documento', 10)->nullable();  // DNI / RUC / CE / PAS
            $table->string('documento', 20)->nullable();       // Número del documento

            $table->string('nombre', 255);
            $table->string('apellidos', 255);
            $table->enum('sexo', ['M', 'F']);
            // NUEVOS CAMPOS
            $table->date('fecha_nacimiento')->nullable();

            // CONTACTO
            $table->string('telefono', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('direccion', 255)->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamps();

            // Índices útiles
            $table->index('documento');
            $table->index('tipo_documento');
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
