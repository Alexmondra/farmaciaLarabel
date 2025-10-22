<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // <--- 1. Importar la clase

use App\Services\SucursalContext;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar el servicio de contexto de sucursal
        $this->app->singleton(SucursalContext::class, function ($app) {
            return new SucursalContext();
        });

        // Alias para facilitar el acceso
        $this->app->alias(SucursalContext::class, 'sucursal.context');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        //
    }
}
