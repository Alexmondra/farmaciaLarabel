<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sucursales', function (Blueprint $table) {
            $table->id();

            // --- IDENTIFICACIÓN (Código SUNAT Anexo) ---
            // IMPORTANTE: Debe ser 4 dígitos. Ej: '0000' (Principal), '0001' (Sucursal)
            $table->string('codigo', 4)->default('0000')->unique();
            $table->string('nombre', 120);
            $table->string('ubigeo', 6)->nullable();         // Ej: 140101
            $table->string('departamento', 30)->nullable();  // Ej: LAMBAYEQUE
            $table->string('provincia', 30)->nullable();     // Ej: CHICLAYO
            $table->string('distrito', 30)->nullable();      // Ej: CHICLAYO
            $table->string('direccion', 200)->nullable();    // Dirección completa
            $table->string('telefono', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('imagen_sucursal', 255)->nullable();

            // --- CONFIGURACIÓN TRIBUTARIA LOCAL ---
            $table->decimal('impuesto_porcentaje', 5, 2)->default(18.00);

            // --- SERIES DE FACTURACIÓN ---
            $table->string('serie_boleta', 4)->nullable();  // Ej: B001
            $table->string('serie_factura', 4)->nullable(); // Ej: F001
            $table->string('serie_nc_boleta', 4)->nullable();  // Ej: BC01
            $table->string('serie_nc_factura', 4)->nullable(); // Ej: FC01
            $table->string('serie_guia', 4)->nullable();    // Ej: T001 
            $table->string('serie_ticket', 4)->nullable();  // Ej: TK01

            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sucursales');
    }
};
