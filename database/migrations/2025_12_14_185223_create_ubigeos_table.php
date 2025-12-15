<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubigeos', function (Blueprint $table) {
            // IDDIST: El código oficial de Ubigeo (6 dígitos). Lo usaremos como PRIMARY KEY.
            $table->string('codigo', 6)->primary();

            // NOMBDEP, NOMBPROV, NOMBDIST: Para la descripción y consulta.
            $table->string('departamento', 50);
            $table->string('provincia', 50);
            $table->string('distrito', 50);

            // Campos adicionales si los necesitas, aunque no son cruciales para la validación SUNAT.
            $table->string('capital', 50)->nullable();
            $table->string('region_natural', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubigeos');
    }
};
