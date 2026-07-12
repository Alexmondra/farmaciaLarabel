<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('tienda_password')->nullable();
            $table->timestamp('tienda_email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamp('tienda_last_login_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'tienda_password',
                'tienda_email_verified_at',
                'remember_token',
                'tienda_last_login_at',
            ]);
        });
    }
};
