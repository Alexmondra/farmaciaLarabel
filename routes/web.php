<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Seguridad\RolePermissionController;
use App\Http\Controllers\Seguridad\UsuarioController;
use App\Http\Controllers\Inventario\MedicamentoController;
use App\Http\Controllers\Inventario\MedicamentoSucursalController;
use App\Http\Controllers\Configuracion\SucursalController;
use App\Http\Controllers\Inventario\CategoriaController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // Route::resource('medicamentos', MedicamentoController::class);
});

// ======================================================
// MÓDULO: Ventas 
// ======================================================
Route::middleware(['auth', 'can:ventas.ver'])->prefix('ventas')->name('ventas.')->group(function () {});


// ======================================================
// MÓDULO: Inventario
// ======================================================

Route::middleware(['auth', 'can:medicamentos.ver'])
    ->prefix('inventario')
    ->name('inventario.')
    ->group(function () {

        // web.php
        Route::get('medicamentos/lookup', [\App\Http\Controllers\Inventario\MedicamentoController::class, 'lookup'])
            ->name('medicamentos.lookup');



        Route::get('medicamentos', [MedicamentoController::class, 'index'])->name('medicamentos.index');
        Route::get('medicamentos/crear', [MedicamentoController::class, 'create'])->middleware('can:medicamentos.crear')->name('medicamentos.create');
        Route::post('medicamentos', [MedicamentoController::class, 'store'])->middleware('can:medicamentos.crear')->name('medicamentos.store');

        // VER detalle completo (imagen, info global, pestañas por sucursal)
        Route::get('medicamentos/{medicamento}', [MedicamentoController::class, 'show'])->name('medicamentos.show');

        // EDITAR por sucursal (página propia)
        Route::get('medicamentos/{medicamento}/sucursales/{sucursal}/editar', [MedicamentoSucursalController::class, 'edit'])
            ->middleware('can:medicamentos.editar')->name('medicamentos.editSucursal');
        Route::put('medicamentos/{medicamento}/sucursales/{sucursal}', [MedicamentoSucursalController::class, 'update'])
            ->middleware('can:medicamentos.editar')->name('medicamentos.updateSucursal');

        // ELIMINAR por sucursal
        Route::delete('medicamentos/{medicamento}/sucursales/{sucursal}', [MedicamentoSucursalController::class, 'destroy'])
            ->middleware('can:medicamentos.eliminar')->name('medicamentos.detachSucursal');

        // ASIGNAR (agregar) a otra sucursal
        Route::post('medicamentos/{medicamento}/sucursales', [MedicamentoSucursalController::class, 'attach'])
            ->middleware('can:medicamentos.editar')->name('medicamentos.attachSucursal');


        // CATEGORÍAS (mismo prefijo/nombre del grupo)
        // Si quieres un permiso distinto para categorías, puedes envolverlo en otro group con 'can:categorias.ver'
        Route::middleware('can:categorias.ver')->group(function () {
            Route::resource('categorias', CategoriaController::class);
        });
    });

// ======================================================
// MÓDULO: Configuración - Sucursales
// ======================================================
Route::middleware(['auth', 'can:sucursales.ver'])->prefix('configuracion')->name('configuracion.')->group(function () {
    Route::resource('sucursales', SucursalController::class)->parameters(['sucursales' => 'sucursal']);;
});

// ======================================================
// MÓDULO: Seguridad - Roles y Permisos
// ======================================================
Route::middleware(['auth'])->prefix('seguridad')->name('seguridad.')->group(function () {

    // Roles - requiere permiso de ver roles
    Route::middleware(['can:roles.ver'])->group(function () {
        Route::get('/roles', [RolePermissionController::class, 'index'])->name('roles.index');

        // Crear rol - requiere permiso específico
        Route::post('/roles', [RolePermissionController::class, 'storeRole'])
            ->middleware('can:roles.crear')
            ->name('roles.store');

        // Eliminar rol
        Route::delete('/roles/{role}', [RolePermissionController::class, 'destroyRole'])
            ->middleware('can:roles.editar') // o crear permiso específico para eliminar
            ->name('roles.destroy');

        // Sincronizar permisos
        Route::post('/roles/{role}/permisos', [RolePermissionController::class, 'syncRolePermissions'])
            ->middleware('can:roles.editar')
            ->name('roles.permisos.sync');

        // Revocar permiso
        Route::delete('/roles/{role}/permisos/{permission}', [RolePermissionController::class, 'revokePermissionFromRole'])
            ->middleware('can:roles.editar')
            ->name('roles.permisos.revoke');
    });

    // Permisos - requiere permiso de ver permisos
    Route::post('/permisos', [RolePermissionController::class, 'storePermission'])
        ->middleware('can:permisos.ver')
        ->name('permisos.store');

    // Usuarios - protegido con permisos de usuarios
    Route::middleware(['can:usuarios.ver'])->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except(['show']);

        // Rutas adicionales para usuarios con permisos específicos
        Route::post('/usuarios/{usuario}/activar', [UsuarioController::class, 'activar'])
            ->middleware('can:usuarios.editar')
            ->name('usuarios.activar');
    });
});


require __DIR__ . '/auth.php';
