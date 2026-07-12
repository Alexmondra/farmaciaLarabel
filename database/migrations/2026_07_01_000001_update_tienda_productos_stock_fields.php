<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tienda_productos', function (Blueprint $table) {
            if (!Schema::hasColumn('tienda_productos', 'stock_modo')) {
                $table->string('stock_modo', 30)->default('stock_sucursal')->after('precio_web');
            }

            if (!Schema::hasColumn('tienda_productos', 'stock_web')) {
                $table->unsignedInteger('stock_web')->nullable()->after('stock_modo');
            }
        });

        Schema::table('tienda_productos', function (Blueprint $table) {
            if (Schema::hasColumn('tienda_productos', 'stock_minimo_web')) {
                $table->dropColumn('stock_minimo_web');
            }

            if (Schema::hasColumn('tienda_productos', 'orden')) {
                $table->dropColumn('orden');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tienda_productos', function (Blueprint $table) {
            if (!Schema::hasColumn('tienda_productos', 'stock_minimo_web')) {
                $table->unsignedInteger('stock_minimo_web')->default(1)->after('precio_web');
            }

            if (!Schema::hasColumn('tienda_productos', 'orden')) {
                $table->unsignedInteger('orden')->default(0)->after('destacado');
            }
        });

        Schema::table('tienda_productos', function (Blueprint $table) {
            if (Schema::hasColumn('tienda_productos', 'stock_web')) {
                $table->dropColumn('stock_web');
            }

            if (Schema::hasColumn('tienda_productos', 'stock_modo')) {
                $table->dropColumn('stock_modo');
            }
        });
    }
};
