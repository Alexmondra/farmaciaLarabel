<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tienda_producto_imagenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tienda_producto_id')->constrained('tienda_productos')->cascadeOnDelete();
            $table->string('imagen_path');
            $table->string('alt', 220)->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('visible')->default(true);
            $table->timestamps();

            $table->index(['tienda_producto_id', 'visible', 'orden']);
        });

        if (Schema::hasColumn('tienda_productos', 'imagen_web')) {
            Schema::table('tienda_productos', function (Blueprint $table) {
                $table->dropColumn('imagen_web');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('tienda_productos', 'imagen_web')) {
            Schema::table('tienda_productos', function (Blueprint $table) {
                $table->string('imagen_web')->nullable()->after('descripcion_web');
            });
        }

        Schema::dropIfExists('tienda_producto_imagenes');
    }
};
