<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Importante para insertar el dato inicial

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();

            $table->integer('puntos_por_moneda')->default(1);
            $table->decimal('valor_punto_canje', 10, 4)->default(0.0200);
            $table->integer('minimo_puntos_canje')->default(50);
            $table->integer('dias_vigencia_puntos')->default(365);
            $table->integer('dias_aviso_vencimiento')->default(60);
            $table->string('color_alerta_vencimiento', 7)->default('#FF4444');
            $table->string('color_alerta_stock', 7)->default('#FFBB33');
            $table->string('mensaje_ticket')->nullable()->default('Gracias por su preferencia');

            $table->timestamps();
        });

        // --- INSERTAR DATOS POR DEFECTO AUTOMÁTICAMENTE ---
        // Esto asegura que siempre tengas una fila de configuración lista
        DB::table('configuraciones')->insert([
            'puntos_por_moneda' => 1,
            'valor_punto_canje' => 0.02,
            'minimo_puntos_canje' => 50,
            'dias_vigencia_puntos' => 365,
            'dias_aviso_vencimiento' => 60,
            'color_alerta_vencimiento' => '#FF4444',
            'color_alerta_stock' => '#FFBB33',
            'mensaje_ticket' => 'Gracias por su compra. ¡Cuide su salud!',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }


    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
