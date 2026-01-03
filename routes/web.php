<?php

use Illuminate\Support\Facades\Route;

// --- Importación de Controladores ---
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Seguridad\{RolePermissionController, UsuarioController};
use App\Http\Controllers\Inventario\{MedicamentoController, MedicamentoSucursalController, CategoriaController};
use App\Http\Controllers\Compras\{ProveedorController, CompraController};
use App\Http\Controllers\Ventas\{CajaSesionController, VentaController};
use App\Http\Controllers\Configuracion\SucursalController;
use App\Http\Controllers\Ventas\ClienteController;
use App\Http\Controllers\Configuracion\ConfiguracionController;
use App\Http\Controllers\PublicoController;
use App\Http\Controllers\Reportes\ReporteVentasController;
use App\Http\Controllers\Guias\GuiaRemisionController;
use App\Http\Controllers\Reportes\ReporteInventarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SunatVerificacion\FacturacionController;
use App\Http\Controllers\Reportes\DigemidReporteController;
use App\Http\Controllers\SunatVerificacion\SunatArchivosController;
use App\Http\Controllers\SunatVerificacion\NotasCredito\NotaCreditoMonitorController;
// =========================================================================
// 1. RUTAS PÚBLICAS
// =========================================================================
Route::get('/', function () {
    return view('auth/login');
});

require __DIR__ . '/auth.php';


Route::view('/consultar', 'publico.buscar')->name('publico.buscar_vista');
Route::post('/consultar', [PublicoController::class, 'buscar'])->name('publico.buscar_post');
Route::get('/descargar-comprobante/{id}', [PublicoController::class, 'descargar'])
    ->name('publico.descargar')
    ->middleware('signed');

// =========================================================================
// 2. RUTAS PROTEGIDAS (SOLO REQUIEREN LOGIN)
// =========================================================================

Route::middleware(['auth'])->group(function () {

    // --- Dashboard y Perfil Base ---
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    // --- Utilidades Globales ---
    Route::controller(SucursalController::class)->group(function () {
        Route::get('/elegir-sucursal', 'elegir')->name('sucursales.elegir');
        Route::post('/elegir-sucursal', 'guardarEleccion')->name('sucursales.guardar');
        Route::get('/cambiar-sucursal-select', 'cambiarDesdeSelect')->name('cambiar.sucursal.desdeSelect');
    });

    Route::controller(UsuarioController::class)->group(function () {
        Route::get('/mi-perfil', 'miPerfil')->name('perfil.editar');
        Route::put('/mi-perfil', 'updateMiPerfil')->name('perfil.update');
        Route::get('seguridad/usuarios/{usuario}/imagen', 'mostrarImagen')->name('seguridad.usuarios.imagen');
    });

    // =================================================================
    // MÓDULO: SEGURIDAD
    // =================================================================
    Route::prefix('seguridad')->name('seguridad.')->group(function () {

        // Roles y Permisos
        Route::resource('roles', RolePermissionController::class);

        // Rutas extra de Roles (Sync/Revoke)
        Route::controller(RolePermissionController::class)->group(function () {
            Route::post('/roles/{role}/permisos', 'syncRolePermissions')->name('roles.permisos.sync');
            Route::delete('/roles/{role}/permisos/{permission}', 'revokePermissionFromRole')->name('roles.permisos.revoke');
            Route::post('/permisos', 'storePermission')->name('permisos.store');
        });

        // Usuarios
        Route::controller(UsuarioController::class)->group(function () {
            Route::patch('usuarios/{usuario}/reset-password', 'resetPassword')->name('usuarios.reset_password');
            Route::post('/usuarios/{usuario}/activar', 'activar')->name('usuarios.activar');
        });
        Route::resource('usuarios', UsuarioController::class)->except(['show']);
    });



    // =================================================================
    // MÓDULO: INVENTARIO
    // =================================================================
    Route::prefix('inventario')->name('inventario.')->group(function () {

        Route::resource('categorias', CategoriaController::class);

        Route::get('medicamentos-general', [MedicamentoController::class, 'indexGeneral'])
            ->name('medicamentos.general');
        // Medicamentos: Rutas Custom
        Route::controller(MedicamentoController::class)->group(function () {
            Route::get('medicamentos/buscar', 'lookup')->name('medicamentos.lookup');
            Route::post('medicamentos/store-rapido', 'storeRapido')->name('medicamentos.storeRapido');
            Route::put('medicamentos/{id}/update-rapido', 'updateRapido')->name('medicamentos.updateRapido');
        });

        // Medicamentos: Sucursales
        Route::controller(MedicamentoSucursalController::class)->group(function () {
            Route::get('medicamentos/{medicamento}/sucursales/{sucursal}/editar', 'edit')->name('medicamento_sucursal.edit');
            Route::put('medicamentos/{medicamento}/sucursales/{sucursal}', 'update')->name('medicamento_sucursal.update');
            Route::delete('medicamentos/{medicamento}/sucursales/{sucursal}', 'destroy')
                ->name('medicamento_sucursal.destroy');

            Route::post('medicamentos/{medicamento}/sucursales', 'attach')->name('medicamento_sucursal.store');

            Route::get('medicamentos/{medicamento}/sucursales/{sucursal}/historial', 'historial')
                ->name('medicamento_sucursal.historial');
            Route::put('medicamentos/{medicamento}/sucursales/{sucursal}', 'update')->name('medicamentos.updateSucursal');
        });

        Route::post('movimientos/salida', [MedicamentoSucursalController::class, 'storeSalida'])
            ->name('movimientos.store_salida');
        Route::post('movimientos/ingreso', [MedicamentoSucursalController::class, 'storeIngreso'])
            ->name('movimientos.store_ingreso');

        Route::resource('medicamentos', MedicamentoController::class);
    });

    // =================================================================
    // MÓDULO: COMPRAS
    // =================================================================
    // Nota: Usamos name('inventario.') para mantener compatibilidad con tus vistas
    Route::resource('proveedores', ProveedorController::class)
        ->names('inventario.proveedores')
        ->parameters(['proveedores' => 'proveedor']);

    Route::resource('compras', CompraController::class);
    Route::get('compras/{id}/archivo', [CompraController::class, 'mostrarComprobante'])->name('compras.archivo.ver');
    Route::get('compras/{id}/descargar', [CompraController::class, 'descargarComprobante'])->name('compras.archivo.descargar');

    Route::put('inventario/lotes/{lote}/ubicacion', [MedicamentoSucursalController::class, 'updateLoteUbicacion'])
        ->name('inventario.lotes.update_ubicacion');

    Route::put('inventario/lotes/{lote}/vencimiento', [App\Http\Controllers\Inventario\MedicamentoSucursalController::class, 'updateLoteVencimiento'])
        ->name('inventario.lotes.update_vencimiento');

    // =================================================================
    // MÓDULO: VENTASz
    // =================================================================
    Route::controller(CajaSesionController::class)->group(function () {
        Route::get('cajas', 'index')->name('cajas.index');
        Route::post('cajas', 'store')->name('cajas.store');
        Route::get('cajas/{id}', 'show')->name('cajas.show');
        Route::patch('cajas/{id}', 'update')->name('cajas.update');
    });

    Route::controller(VentaController::class)->prefix('ventas')->name('ventas.')->group(function () {
        Route::get('lookup-medicamentos', 'lookupMedicamentos')->name('lookup_medicamentos');
        Route::get('lookup-lotes', 'lookupLotes')->name('lookup_lotes');
        Route::get('lookup-cliente', 'buscarCliente')->name('buscar_cliente');
    });

    Route::resource('ventas', VentaController::class);
    Route::post('/ventas/{venta}/anular', [VentaController::class, 'anular'])->name('ventas.anular');

    Route::post('/ventas/{id}/enviar-email', [VentaController::class, 'enviarEmail'])
        ->name('ventas.email');

    Route::get('ventas/{id}/print-ticket', [VentaController::class, 'printTicket'])->name('ventas.print_ticket');
    Route::get('ventas/{id}/print-a4', [VentaController::class, 'printA4'])->name('ventas.print_a4');




    Route::get('clientes/check-documento', [ClienteController::class, 'checkDocumento'])->name('clientes.check');
    Route::get('clientes/search', [ClienteController::class, 'search'])->name('clientes.search');
    Route::resource('clientes', ClienteController::class);
    // =================================================================
    // MÓDULO: CONFIGURACIÓN
    // =================================================================
    Route::prefix('configuracion')->name('configuracion.')->group(function () {
        Route::resource('sucursales', SucursalController::class)
            ->parameters(['sucursales' => 'sucursal']);

        Route::get('/general', [ConfiguracionController::class, 'index'])->name('general.index');
        Route::put('/general', [ConfiguracionController::class, 'update'])->name('general.update');
    });

    Route::prefix('facturacion')->name('facturacion.')->group(function () {

        Route::get('/pendientes', [FacturacionController::class, 'indexPendientes'])->name('pendientes');
        Route::post('/reenviar/{id}', [FacturacionController::class, 'reenviar'])->name('reenviar');

        Route::get('/clientes/buscar', [FacturacionController::class, 'buscarClienteVenta'])
            ->name('clientes.buscar');

        Route::get('/editar/{id}', [FacturacionController::class, 'edit'])->name('edit');

        Route::put('/rectificar/{id}', [FacturacionController::class, 'rectificar'])->name('rectificar');


        // lo de los cdr y xlm 

        Route::get('/comprobantes', [SunatArchivosController::class, 'index'])
            ->name('comprobantes.index');

        Route::get('/download-xml/{venta}', [SunatArchivosController::class, 'descargarXml'])
            ->name('download.xml');

        Route::get('/download-cdr/{venta}', [SunatArchivosController::class, 'descargarCdr'])
            ->name('download.cdr');
    });
    // Rutas para Notas de Crédito
    Route::prefix('facturacion')->name('notas.')->group(function () {
        Route::get('monitor-notas', [NotaCreditoMonitorController::class, 'monitor'])->name('monitor.index');
        Route::get('archivos-notas', [NotaCreditoMonitorController::class, 'visor'])->name('visor.index');

        Route::get('download-xml/nc/{nota}', [NotaCreditoMonitorController::class, 'descargarXml'])->name('download.xml');
        Route::get('download-cdr/nc/{nota}', [NotaCreditoMonitorController::class, 'descargarCdr'])->name('download.cdr');


        Route::post('monitor-notas/reenviar/{nota}', [NotaCreditoMonitorController::class, 'reenviar'])->name('monitor.reenviar');
    });




    Route::post('/configuracion/update', [ClienteController::class, 'updateConfig'])->name('configuracion.update');
    Route::get('guias/lookup-medicamentos', [GuiaRemisionController::class, 'lookupMedicamentos'])->name('guias.lookup_medicamentos');
    Route::get('guias/buscar-venta', [GuiaRemisionController::class, 'buscarVenta'])->name('guias.buscar_venta');


    Route::get('/guias/{guia}/pdf', [GuiaRemisionController::class, 'verPdf'])
        ->name('guias.ver_pdf');
    Route::resource('guias', GuiaRemisionController::class);
    Route::put('/guias/{guia}/recibir', [GuiaRemisionController::class, 'recibir'])
        ->name('guias.recibir');

    Route::patch('/guias/{guia}/anular', [GuiaRemisionController::class, 'anular'])
        ->name('guias.anular');


    //iniciamos el modulo de reportes


    Route::get('reportes/ventas-dia', [ReporteVentasController::class, 'ventasDia'])
        ->name('reportes.ventas-dia');

    Route::get('reportes/ventas-historial', [ReporteVentasController::class, 'ventasHistorial'])
        ->name('reportes.ventas-historial');

    Route::get('/reportes/ventas/historial/export-excel', [ReporteVentasController::class, 'exportarExcelHistorial'])
        ->name('reportes.ventas-historial.export-excel');

    Route::get('reportes/ventas-anuladas', [ReporteVentasController::class, 'ventasAnuladas'])
        ->name('reportes.ventas-anuladas');

    Route::get('reportes/venta/{id}/pdf', [ReporteVentasController::class, 'descargarPdf'])
        ->name('reportes.venta.pdf');



    // de aqui las de inventario 

    Route::get('reportes/vencimientos', [ReporteInventarioController::class, 'vencimientos'])
        ->name('reportes.vencimientos');

    Route::get('reportes/stock-bajo', [ReporteInventarioController::class, 'stockBajo'])
        ->name('reportes.stock_bajo');

    Route::middleware(['auth'])->prefix('reportes')->group(function () {
        Route::get('reporte-digemid', [DigemidReporteController::class, 'index'])->name('digemid.index');
        Route::get('reporte-digemid/exportar', [DigemidReporteController::class, 'exportar'])->name('digemid.exportar');
    });
});
