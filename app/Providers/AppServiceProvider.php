<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MedicamentoSucursal;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Event::listen(BuildingMenu::class, function (BuildingMenu $event) {

            // 1. Obtener Sucursal de la sesión
            $sucursalId = Session::get('sucursal_id');

            // --------------------------------------------------------------------
            // ALERTA 1: VENCIMIENTOS (Aquí sí contamos LOTES individuales)
            // --------------------------------------------------------------------
            // Buscamos lotes con stock > 0 que venzan en los próximos 30 días
            $queryVencimiento = Lote::where('stock_actual', '>', 0)
                ->whereDate('fecha_vencimiento', '<=', now()->addDays(30));

            if ($sucursalId) {
                $queryVencimiento->where('sucursal_id', $sucursalId);
            }
            $countVencimiento = $queryVencimiento->count();

            // --------------------------------------------------------------------
            // ALERTA 2: STOCK BAJO (Aquí sumamos lotes por Medicamento)
            // --------------------------------------------------------------------
            // Queremos saber cuántos PRODUCTOS tienen un total acumulado bajo (ej: <= 10)

            $queryStock = MedicamentoSucursal::query();

            if ($sucursalId) {
                $queryStock->where('sucursal_id', $sucursalId);
            }

            // LOGICA CLAVE: Usamos una subconsulta para sumar el stock de los lotes de ese producto
            // y filtramos aquellos cuya suma sea <= 10 y mayor a 0 (para no contar los agotados si no quieres)
            $queryStock->whereRaw(
                '
                (SELECT SUM(stock_actual) 
                 FROM lotes 
                 WHERE lotes.medicamento_id = medicamento_sucursal.medicamento_id 
                 AND lotes.sucursal_id = medicamento_sucursal.sucursal_id) <= ?',
                [10]
            );

            // Opcional: Si NO quieres contar los que tienen 0 stock absoluto, descomenta esto:

            $queryStock->whereRaw(
                '
                (SELECT SUM(stock_actual) 
                 FROM lotes 
                 WHERE lotes.medicamento_id = medicamento_sucursal.medicamento_id 
                 AND lotes.sucursal_id = medicamento_sucursal.sucursal_id) > 0'
            );


            $countStock = $queryStock->count();


            // --------------------------------------------------------------------
            // CONSTRUCCIÓN DEL MENÚ
            // --------------------------------------------------------------------
            $totalAlertas = $countVencimiento + $countStock;
            $colorAlerta = $totalAlertas > 0 ? 'danger' : 'secondary'; // Rojo si hay alertas

            // Agregamos el ítem al menú superior derecho
            $event->menu->add([
                'key'          => 'alertas_globales',
                'text'         => 'Notificaciones',
                'icon'         => 'fas fa-bell',
                'icon_color'   => $totalAlertas > 0 ? 'warning' : 'white',
                'label'        => $totalAlertas > 0 ? $totalAlertas : null,
                'label_color'  => $colorAlerta,
                'topnav_right' => true,
                'submenu'      => [
                    [
                        'header' => 'ALERTAS DE INVENTARIO',
                    ],
                    [
                        'text'        => $countVencimiento . ' Lotes por Vencer',
                        'url'         => '#', // Cambia por tu ruta real ej: route('reportes.vencimientos')
                        'icon'        => 'fas fa-calendar-times text-danger',
                        'label'       => $countVencimiento > 0 ? '!' : null,
                        'label_color' => 'danger',
                    ],
                    [
                        'text'        => $countStock . ' Prod. Stock Bajo',
                        'url'         => '#', // Cambia por tu ruta real ej: route('reportes.stock_bajo')
                        'icon'        => 'fas fa-sort-amount-down text-warning',
                        'label'       => $countStock > 0 ? 'Revisar' : null,
                        'label_color' => 'warning',
                    ],
                ],
            ]);
        });
    }
}
