<?php

use Illuminate\Support\Facades\Route;

// --- Importación de Controladores ---
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Seguridad\{RolePermissionController, UsuarioController};
use App\Http\Controllers\Inventario\{MedicamentoController, MedicamentoSucursalController, CategoriaController};
use App\Http\Controllers\Compras\{ProveedorController, CompraController};
use App\Http\Controllers\Ventas\{CajaSesionController, VentaController};
use App\Http\Controllers\Configuracion\SucursalController;

// =========================================================================
// 1. RUTAS PÚBLICAS
// =========================================================================
Route::get('/', function () {
    return view('auth/login');
});

require __DIR__ . '/auth.php';

// =========================================================================
// 2. RUTAS PROTEGIDAS (SISTEMA)
// =========================================================================
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

    // --- Utilidades Globales (Usuario y Sucursal actual) ---
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

        // Roles y Permisos (Controller gestiona permisos específicos)
        Route::resource('roles', RolePermissionController::class);

        // Rutas extra de Roles (Sync/Revoke)
        Route::post('/roles/{role}/permisos', [RolePermissionController::class, 'syncRolePermissions'])->name('roles.permisos.sync');
        Route::delete('/roles/{role}/permisos/{permission}', [RolePermissionController::class, 'revokePermissionFromRole'])->name('roles.permisos.revoke');
        Route::post('/permisos', [RolePermissionController::class, 'storePermission'])->name('permisos.store');

        // Usuarios
        Route::patch('usuarios/{usuario}/reset-password', [UsuarioController::class, 'resetPassword'])->name('usuarios.reset_password');
        Route::post('/usuarios/{usuario}/activar', [UsuarioController::class, 'activar'])->name('usuarios.activar');
        Route::resource('usuarios', UsuarioController::class)->except(['show']);
    });

    // =================================================================
    // MÓDULO: INVENTARIO (Medicamentos, Categorías)
    // =================================================================
    Route::prefix('inventario')->name('inventario.')->group(function () {

        Route::resource('categorias', CategoriaController::class);

        // -- Medicamentos (Rutas personalizadas ANTES del resource) --
        Route::controller(MedicamentoController::class)->group(function () {
            Route::get('medicamentos/buscar', 'lookup')->name('medicamentos.lookup');
            Route::post('medicamentos/store-rapido', 'storeRapido')->name('medicamentos.storeRapido');
        });

        // -- Medicamentos x Sucursal --
        Route::controller(MedicamentoSucursalController::class)->group(function () {
            Route::get('medicamentos/{medicamento}/sucursales/{sucursal}/editar', 'edit')->name('medicamentos.editSucursal');
            Route::put('medicamentos/{medicamento}/sucursales/{sucursal}', 'update')->name('medicamentos.updateSucursal');
            Route::delete('medicamentos/{medicamento}/sucursales/{sucursal}', 'destroy')->name('medicamentos.detachSucursal');
            Route::post('medicamentos/{medicamento}/sucursales', 'attach')->name('medicamentos.attachSucursal');
        });

        Route::resource('medicamentos', MedicamentoController::class);
    });

    // =================================================================
    // MÓDULO: COMPRAS (Proveedores)
    // =================================================================
    // NOTA: Mantuve el name('inventario.') para no romper tus vistas,
    // aunque el controlador esté en Compras.
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

    // =================================================================
    // MÓDULO: CONFIGURACIÓN
    // =================================================================
    Route::prefix('configuracion')->name('configuracion.')->group(function () {
        Route::resource('sucursales', SucursalController::class)
            ->parameters(['sucursales' => 'sucursal']);
    });
});
