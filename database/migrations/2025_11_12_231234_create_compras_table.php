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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            // Clave para multi-sucursal: ¿A qué sucursal entró esta compra?
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores');
            $table->foreignId('user_id')->constrained('users'); // Quién registró

            $table->string('tipo_comprobante', 30)->nullable();
            $table->string('numero_factura_proveedor', 100)->nullable();
            $table->date('fecha_recepcion');

            // El costo total que dice el papel (para verificar)
            $table->decimal('costo_total_factura', 10, 2);
            $table->text('observaciones')->nullable();
            $table->string('archivo_comprobante', 255)->nullable();
            $table->enum('estado', ['registrada', 'recibida', 'pendiente', 'anulada'])->default('recibida');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
