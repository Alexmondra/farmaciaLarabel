<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pedidos_online', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('qr_token', 80)->unique();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->nullOnDelete();

            $table->string('cliente_tipo_documento', 10)->nullable();
            $table->string('cliente_documento', 20)->nullable();
            $table->string('cliente_nombre', 255);
            $table->string('cliente_telefono', 30)->nullable();
            $table->string('cliente_email', 120)->nullable();
            $table->string('direccion_entrega')->nullable();

            $table->string('tipo_entrega', 30)->default('RECOJO_SUCURSAL');
            $table->string('metodo_pago', 30)->default('PAGO_AL_RECOGER');
            $table->string('estado_pago', 30)->default('PENDIENTE');
            $table->string('estado', 30)->default('PENDIENTE');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('costo_envio', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamp('confirmado_at')->nullable();
            $table->timestamp('entregado_at')->nullable();
            $table->timestamps();

            $table->index(['sucursal_id', 'estado']);
            $table->index(['cliente_id', 'created_at']);
            $table->index(['estado_pago', 'metodo_pago']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos_online');
    }
};
