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
        // Obtener la sucursal actual de la sesión
        $sucursalId = Session::get('sucursal_id');

        // -------------------------------------------------------------------------
        // 1. CONSULTAR ALERTAS
        // -------------------------------------------------------------------------

        // Cuenta los lotes que vencen en los próximos 30 días (sin contar los ya vencidos)
        // Usa el scope corregido: Lote::porVencer
        $countVencimientos = Lote::porVencer($sucursalId)->count();

        // Cuenta los productos con stock menor o igual al mínimo (incluso si es 0)
        // Usa el scope corregido: MedicamentoSucursal::conStockBajo
        $countStockBajo = MedicamentoSucursal::conStockBajo($sucursalId)->count();

        // -------------------------------------------------------------------------
        // 2. CONFIGURAR COLORES Y BADGES
        // -------------------------------------------------------------------------

        // Alerta Vencimientos: Rojo ('danger') si hay alertas, Gris ('secondary') si no.
        $badgeVenc = $countVencimientos > 0 ? $countVencimientos : null;
        $colorVenc = $countVencimientos > 0 ? 'danger' : 'secondary';

        // Alerta Stock: Amarillo ('warning') si hay alertas, Gris ('secondary') si no.
        $badgeStock = $countStockBajo > 0 ? $countStockBajo : null;
        $colorStock = $countStockBajo > 0 ? 'warning' : 'secondary';

        // -------------------------------------------------------------------------
        // 3. AGREGAR ITEMS AL MENÚ (TOPNAV)
        // -------------------------------------------------------------------------

        // Item 1: Vencimientos Próximos
        $event->menu->add([
            'key'          => 'alert_vencimientos',
            'text'         => '', // Sin texto, solo ícono
            'icon'         => 'fas fa-calendar-times',
            'icon_color'   => $colorVenc,
            'label'        => $badgeVenc,
            'label_color'  => 'danger',
            'topnav_right' => true,
            'url'          => 'reportes/vencimientos', // Asegúrate de que esta ruta exista
            'title'        => 'Vencimientos Próximos', // Tooltip
        ]);

        // Item 2: Stock Bajo
        $event->menu->add([
            'key'          => 'alert_stock',
            'text'         => '',
            'icon'         => 'fas fa-boxes',
            'icon_color'   => $colorStock,
            'label'        => $badgeStock,
            'label_color'  => 'warning',
            'topnav_right' => true,
            'url'          => 'reportes/stock-bajo', // Asegúrate de que esta ruta exista
            'title'        => 'Stock Bajo', // Tooltip
        ]);
    }
}
