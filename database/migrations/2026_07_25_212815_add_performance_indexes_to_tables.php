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
        // 1. Índices en la tabla medicamentos (Búsquedas rápidas en Punto de Venta)
        Schema::table('medicamentos', function (Blueprint $table) {
            $table->index('codigo_barra'); // Escaneo de código de barra principal
            $table->index('codigo_barra_blister'); // Escaneo de código de blíster
        });

        // 2. Índices en la tabla lotes (Alertas y vencimientos rápidos)
        Schema::table('lotes', function (Blueprint $table) {
            $table->index('fecha_vencimiento'); // Alertas de productos por vencer
            $table->index('sucursal_id'); // Búsquedas por sucursal individuales
        });

        // 3. Índices en la tabla compras (Reportes de compras por fecha)
        Schema::table('compras', function (Blueprint $table) {
            $table->index('fecha_recepcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicamentos', function (Blueprint $table) {
            $table->dropIndex(['codigo_barra']);
            $table->dropIndex(['codigo_barra_blister']);
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->dropIndex(['fecha_vencimiento']);
            $table->dropIndex(['sucursal_id']);
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->dropIndex(['fecha_recepcion']);
        });
    }
};
