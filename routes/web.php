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


Route::get('/debug-greenter', function () {
    // Esta ruta busca la carpeta exacta donde deberían estar las guías
    $path = base_path('vendor/greenter/model/src/Model/Despatch');

    echo "<h1>🕵️ Diagnóstico de Archivos Greenter</h1>";
    echo "<b>Ruta buscada:</b> " . $path . "<br><hr>";

    if (is_dir($path)) {
        echo "<h3 style='color:green'>✅ La carpeta 'Despatch' SI existe.</h3>";
        echo "<b>Archivos encontrados dentro:</b><br>";
        $files = scandir($path);

        // Verificamos si el archivo específico está
        if (in_array('DespatchAdvice.php', $files)) {
            echo "<h2 style='color:blue'>¡EL ARCHIVO DespatchAdvice.php ESTÁ AQUÍ!</h2>";
            echo "Si ves esto, el problema es solo caché. Ejecuta: <code>composer dump-autoload -o</code>";
        } else {
            echo "<h2 style='color:red'>❌ FALTA EL ARCHIVO DespatchAdvice.php</h2>";
            echo "La carpeta existe pero el archivo no. La librería está corrupta.";
        }

        echo "<pre>" . print_r($files, true) . "</pre>";
    } else {
        echo "<h2 style='color:red'>❌ LA CARPETA 'Despatch' NO EXISTE.</h2>";
        echo "Tu instalación de Greenter está incompleta o es una versión muy antigua.";

        // Verificamos si al menos existe Greenter Model
        $parent = base_path('vendor/greenter/model');
        echo "<br><b>¿Existe al menos la carpeta 'model'?</b> " . (is_dir($parent) ? 'SÍ' : 'NO');
    }
});

// =========================================================================
// 1. RUTAS PÚBLICAS
// =========================================================================
Route::get('/', function () {
    return view('auth/login');
});

require __DIR__ . '/auth.php';

// =========================================================================
// 2. RUTAS PROTEGIDAS (SOLO REQUIEREN LOGIN)
// =========================================================================

// Rutas Públicas (fuera del middleware auth)
Route::view('/consulta-comprobante', 'publico.buscar')->name('publico.buscar_vista');
Route::post('/consulta-comprobante', [PublicoController::class, 'buscar'])->name('publico.buscar_post');
Route::get('/descargar-publico/{id}', [PublicoController::class, 'descargar'])->name('publico.descargar')->middleware('signed');





Route::middleware(['auth'])->group(function () {

    // --- Dashboard y Perfil Base ---
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

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

    // =================================================================
    // MÓDULO: VENTAS
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





    Route::post('/configuracion/update', [ClienteController::class, 'updateConfig'])->name('configuracion.update');
    Route::get('guias/lookup-medicamentos', [GuiaRemisionController::class, 'lookupMedicamentos'])->name('guias.lookup_medicamentos');
    Route::get('guias/buscar-venta', [GuiaRemisionController::class, 'buscarVenta'])->name('guias.buscar_venta');


    Route::get('guias/{id}/pdf', [GuiaRemisionController::class, 'imprimir'])->name('guias.pdf');
    Route::resource('guias', GuiaRemisionController::class);
    //iniciamos el modulo de reportes


    Route::get('reportes/ventas-dia', [ReporteVentasController::class, 'ventasDia'])
        ->name('reportes.ventas-dia');

    Route::get('reportes/ventas-historial', [ReporteVentasController::class, 'ventasHistorial'])
        ->name('reportes.ventas-historial');

    Route::get('reportes/ventas-anuladas', [ReporteVentasController::class, 'ventasAnuladas'])
        ->name('reportes.ventas-anuladas');

    Route::get('reportes/venta/{id}/pdf', [ReporteVentasController::class, 'descargarPdf'])
        ->name('reportes.venta.pdf');
});
