<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();

            $table->string('empresa_ruc', 11)->default('20000000001');
            $table->string('empresa_razon_social')->default('MI FARMACIA S.A.C.');
            $table->string('empresa_direccion')->nullable()->default('AV. PRINCIPAL 123');
            $table->boolean('sunat_produccion')->default(false); // false = MODO PRUEBAS
            $table->string('sunat_sol_user')->nullable()->default('MODDATOS');
            $table->string('sunat_sol_pass')->nullable()->default('MODDATOS');
            $table->string('sunat_certificado_path')->nullable()->default('sunat/certificado_prueba.pem');
            $table->string('sunat_certificado_pass')->nullable(); // Por si el certificado tiene clave
            $table->integer('puntos_por_moneda')->default(1);
            $table->decimal('valor_punto_canje', 10, 4)->default(0.0200);
            $table->string('ruta_logo')->nullable();
            // --- 4. EXTRAS ---
            $table->string('mensaje_ticket')->nullable()->default('Gracias por su preferencia');

            $table->timestamps();
        });

        // Insertamos la configuración inicial
        DB::table('configuraciones')->insert([
            'empresa_ruc' => '20000000001',
            'empresa_razon_social' => 'MI FARMACIA S.A.C. (DEMO)',
            'sunat_produccion' => false,
            'sunat_sol_user' => 'MODDATOS',
            'sunat_sol_pass' => 'MODDATOS',
            'sunat_certificado_path' => 'sunat/certificado_prueba.pem',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
