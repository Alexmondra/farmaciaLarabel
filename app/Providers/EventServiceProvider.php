<?php

use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use App\Listeners\AgregarAlertasMenu;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // ... otros eventos ...

        // Agregamos esto:
        BuildingMenu::class => [
            AgregarAlertasMenu::class,
        ],
    ];
}
