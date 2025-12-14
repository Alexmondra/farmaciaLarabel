<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guias_remision', function (Blueprint $table) {
            $table->id();

            // --- RELACIONES ---
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('restrict');
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->onDelete('set null');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users');

            // --- IDENTIFICACIÓN ---
            $table->string('serie', 4);        // Ej: T001
            $table->integer('numero');         // Ej: 1, 2, 3...
            $table->dateTime('fecha_emision');
            $table->date('fecha_traslado');    // Cuándo sale el transporte

            // --- DATOS DEL TRASLADO ---
            $table->string('motivo_traslado', 3); // 01: Venta, 04: Traslado, etc.
            $table->string('descripcion_motivo')->nullable();
            $table->string('modalidad_traslado', 2); // 01: Público, 02: Privado

            $table->decimal('peso_bruto', 12, 3);
            $table->string('unidad_medida', 4)->default('KGM');
            $table->integer('numero_bultos')->default(1);

            // --- PUNTOS DE PARTIDA Y LLEGADA ---
            $table->string('ubigeo_partida', 6);
            $table->string('direccion_partida', 200);
            $table->string('codigo_establecimiento_partida', 4)->default('0000');

            $table->string('ubigeo_llegada', 6);
            $table->string('direccion_llegada', 200);
            $table->string('codigo_establecimiento_llegada', 4)->nullable();

            // --- TRANSPORTE (Privado / Público) ---
            $table->string('doc_chofer_tipo', 1)->nullable();
            $table->string('doc_chofer_numero', 15)->nullable();
            $table->string('nombre_chofer', 150)->nullable();
            $table->string('licencia_conducir', 20)->nullable();
            $table->string('placa_vehiculo', 10)->nullable();

            $table->string('doc_transportista_tipo', 1)->nullable();
            $table->string('doc_transportista_numero', 15)->nullable();
            $table->string('razon_social_transportista', 150)->nullable();

            // --- ESTADOS Y SUNAT ---
            $table->string('estado_traslado')->default('EN_TRANSITO');
            $table->string('ruta_xml')->nullable();
            $table->string('ruta_cdr')->nullable();
            $table->string('ruta_pdf')->nullable();
            $table->string('hash')->nullable();
            $table->string('ticket_sunat')->nullable();

            $table->boolean('sunat_exito')->default(false);
            $table->text('mensaje_sunat')->nullable();
            $table->string('codigo_error_sunat')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guias_remision');
    }
};
