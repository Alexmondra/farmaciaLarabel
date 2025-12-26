<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use Illuminate\Support\Facades\Gate;
use App\Models\Configuracion;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }


    public function boot()

    {
        Paginator::useBootstrap();
        if (Schema::hasTable('configuraciones')) {

            $empresa = Cache::remember('datos_empresa_config', 1440, function () {
                return Configuracion::first();
            });

            if ($empresa) {
                $nombre = $empresa->empresa_razon_social;
                $rutaLogo = $empresa->ruta_logo;

                $urlLogo = !empty($rutaLogo) ? asset('storage/' . $rutaLogo) : null;
                $urlLogoDefault = 'vendor/adminlte/dist/img/AdminLTELogo.png';
                $nombreMostrar = $nombre;
                if (strlen($nombre) > 15) {
                    $partes = explode(' ', $nombre);
                    $nombreMostrar = (count($partes) >= 2)
                        ? $partes[0] . ' ' . substr($partes[1], 0, 1) . '.'
                        : substr($nombre, 0, 12) . '.';
                }

                if ($urlLogo) {
                    Config::set('adminlte.logo_img', $urlLogo);
                } else {
                    Config::set('adminlte.logo_img', $urlLogoDefault);
                }

                Config::set('adminlte.logo_img_class', 'brand-image img-circle elevation-3');
                Config::set('adminlte.logo', '<b>' . $nombreMostrar . '</b>');
                Config::set('adminlte.title', $nombre);
                if ($urlLogo) {
                    Config::set('adminlte.preloader.img.path', $urlLogo);
                    Config::set('adminlte.preloader.img.alt', 'Cargando...');
                } else {
                    Config::set('adminlte.preloader.img.path', $urlLogoDefault);
                }
            }
        }

        // Define aquí tus "Super Permisos" (CORREGIDO)

        // 1. Operaciones
        Gate::define(
            'ver_operaciones',
            fn($u) =>
            $u->can('ventas.crear') ||
                $u->can('cajas.ver') ||
                $u->can('ventas.ver') ||
                $u->can('guias.ver') ||
                $u->can('clientes.ver')
        );

        // 2. Sunat
        Gate::define(
            'ver_sunat',
            fn($u) =>
            $u->can('sunat.monitor') ||
                $u->can('sunat.archivos')
        );

        // 3. Gestión y Administración (Incluye todos los submenús de esa sección)
        Gate::define(
            'ver_gestion',
            fn($u) =>
            $u->can('medicamentos.ver') ||
                $u->can('categorias.ver') ||
                $u->can('medicamentos.global') ||
                $u->can('compras.crear') ||
                $u->can('compras.ver') ||
                $u->can('proveedores.ver') ||
                $u->can('reportes.ventas') ||
                $u->can('reportes.inventario') ||
                $u->can('usuarios.ver') ||
                $u->can('roles.ver') ||
                $u->can('sucursales.ver') ||
                $u->can('config.ver')
        );
    }
}
