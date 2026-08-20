<?php

namespace App\Listeners;

use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use Illuminate\Support\Facades\Session;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MedicamentoSucursal;

class AgregarAlertasMenu
{
    /**
     * Handle the event.
     *
     * @param  BuildingMenu  $event
     * @return void
     */
    public function handle(BuildingMenu $event)
    {
        // La campana de alertas se inyecta de forma nativa en resources/views/vendor/adminlte/partials/navbar/navbar.blade.php
    }
}
