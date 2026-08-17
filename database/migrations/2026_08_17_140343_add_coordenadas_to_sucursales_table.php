<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->decimal('latitud', 10, 8)->nullable()->after('imagen_sucursal');
            $table->decimal('longitud', 11, 8)->nullable()->after('latitud');
        });

        // Pre-cargar coordenadas de las sucursales existentes (ciudades principales de Perú)
        DB::table('sucursales')->where('codigo', '0000')->update(['latitud' => -12.1226154, 'longitud' => -77.030588]); // Lima (Miraflores)
        DB::table('sucursales')->where('codigo', '0001')->update(['latitud' => -6.7718018, 'longitud' => -79.8407421]); // Chiclayo
        DB::table('sucursales')->where('codigo', '0002')->update(['latitud' => -16.3908865, 'longitud' => -71.5471477]); // Arequipa
        DB::table('sucursales')->where('codigo', '0003')->update(['latitud' => -8.1130644, 'longitud' => -79.0300267]); // Trujillo
        DB::table('sucursales')->where('codigo', '0004')->update(['latitud' => -13.5218779, 'longitud' => -71.9723709]); // Cusco
        DB::table('sucursales')->where('codigo', '0005')->update(['latitud' => -5.1960249, 'longitud' => -80.6300438]); // Piura
    }

    public function down(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->dropColumn(['latitud', 'longitud']);
        });
    }
};
