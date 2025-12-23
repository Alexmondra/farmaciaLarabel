<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use App\Listeners\AgregarAlertasMenu;
use App\Models\Configuracion;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }


    public function boot()
    {
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
    }
}
