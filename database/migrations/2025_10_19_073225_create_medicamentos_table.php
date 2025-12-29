<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medicamentos', function (Blueprint $table) {
            $table->id();

            // IDENTIFICADORES
            $table->string('codigo', 30)->unique(); // Tu SKU interno
            $table->string('codigo_digemid', 20)->nullable(); // Clave para API DIGEMID

            // CÓDIGOS DE BARRA (SCANNER)
            $table->string('codigo_barra', 50)->nullable(); // EAN-13 de la CAJA (Principal)
            $table->string('codigo_barra_blister', 50)->nullable(); // EAN del BLÍSTER (Nuevo)

            // DETALLES DEL PRODUCTO
            $table->string('nombre', 180);
            $table->string('forma_farmaceutica', 100)->nullable();
            $table->string('concentracion', 100)->nullable();
            $table->string('presentacion', 120)->nullable();
            $table->string('laboratorio', 120)->nullable();
            $table->string('registro_sanitario', 60)->nullable();
            $table->text('descripcion')->nullable();

            // Ejemplo Amoxicilina: envase=100, blister=10.
            $table->integer('unidades_por_envase')->default(1);
            $table->integer('unidades_por_blister')->nullable();

            // EXTRAS
            $table->boolean('afecto_igv')->default(true);
            $table->boolean('receta_medica')->default(false);
            $table->string('imagen_path')->nullable();

            // RELACIONES
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicamentos');
    }
};
