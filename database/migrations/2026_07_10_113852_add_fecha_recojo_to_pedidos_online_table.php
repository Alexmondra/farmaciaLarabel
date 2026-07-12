<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pedidos_online', function (Blueprint $table) {
            $table->timestamp('fecha_recojo')->nullable()->after('direccion_entrega');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_online', function (Blueprint $table) {
            $table->dropColumn('fecha_recojo');
        });
    }
};
