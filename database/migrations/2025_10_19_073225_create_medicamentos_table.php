<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medicamentos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 180);
            $table->string('forma_farmaceutica', 100)->nullable();
            $table->string('concentracion', 100)->nullable();
            $table->string('presentacion', 120)->nullable();
            $table->string('laboratorio', 120)->nullable();
            $table->string('registro_sanitario', 60)->nullable();
            $table->string('codigo_barra', 50)->nullable();
            $table->text('descripcion')->nullable();
            $table->integer('unidades_por_envase')->default(1);
            $table->string('imagen_path')->nullable();
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
