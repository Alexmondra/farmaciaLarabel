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
            $table->string('nombres', 150);
            $table->string('apellidos', 150)->nullable();
            $table->string('documento', 15)->nullable()->unique()->comment('DNI o RUC');
            $table->string('direccion')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
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
