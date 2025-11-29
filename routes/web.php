<?php

use Illuminate\Support\Facades\Route;

// Controladores Generales
use App\Http\Controllers\ProfileController;

// Controladores de Seguridad
use App\Http\Controllers\Seguridad\RolePermissionController;
use App\Http\Controllers\Seguridad\UsuarioController;

// Controladores de Inventario
use App\Http\Controllers\Inventario\MedicamentoController;
use App\Http\Controllers\Inventario\MedicamentoSucursalController;
use App\Http\Controllers\Inventario\CategoriaController;

// Controladores de Compras
use App\Http\Controllers\Compras\ProveedorController;
use App\Http\Controllers\Compras\CompraController;

// Controladores de Ventas
use App\Http\Controllers\Ventas\CajaSesionController;
use App\Http\Controllers\Ventas\VentaController;

// Controladores de Configuración
use App\Http\Controllers\Configuracion\SucursalController;


// =========================================================================
// RUTAS PÚBLICAS
// =========================================================================

Route::get('/', function () {
    return view('welcome');
});


// =========================================================================
// DASHBOARD Y PERFIL BASE (Breeze/Laravel)
// =========================================================================

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// =========================================================================
// UTILIDADES GLOBALES (Requieren Autenticación)
// =========================================================================
Route::middleware('auth')->group(function () {

    // --- Selector de Sucursal (Sesión) ---
    Route::get('/elegir-sucursal', [SucursalController::class, 'elegir'])
        ->name('sucursales.elegir');
    Route::post('/elegir-sucursal', [SucursalController::class, 'guardarEleccion'])
        ->name('sucursales.guardar');
    Route::get('/cambiar-sucursal-select', [SucursalController::class, 'cambiarDesdeSelect'])
        ->name('cambiar.sucursal.desdeSelect');

    // --- Mi Perfil Personalizado (Usuario actual) ---
    Route::get('/mi-perfil', [UsuarioController::class, 'miPerfil'])
        ->name('perfil.editar');
    Route::put('/mi-perfil', [UsuarioController::class, 'updateMiPerfil'])
        ->name('perfil.update');

    // --- Visualización de Imágenes Protegidas ---
    Route::get('seguridad/usuarios/{usuario}/imagen', [UsuarioController::class, 'mostrarImagen'])
        ->name('seguridad.usuarios.imagen');
});


// =========================================================================
// MÓDULO: SEGURIDAD (Roles, Permisos y Usuarios)
// =========================================================================
Route::middleware(['auth'])->prefix('seguridad')->name('seguridad.')->group(function () {

    // --- Roles ---
    Route::middleware(['can:roles.ver'])->group(function () {
        Route::get('/roles', [RolePermissionController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RolePermissionController::class, 'storeRole'])
            ->middleware('can:roles.crear')->name('roles.store');
        Route::delete('/roles/{role}', [RolePermissionController::class, 'destroyRole'])
            ->middleware('can:roles.editar')->name('roles.destroy');

        // Permisos dentro de Roles
        Route::post('/roles/{role}/permisos', [RolePermissionController::class, 'syncRolePermissions'])
            ->middleware('can:roles.editar')->name('roles.permisos.sync');
        Route::delete('/roles/{role}/permisos/{permission}', [RolePermissionController::class, 'revokePermissionFromRole'])
            ->middleware('can:roles.editar')->name('roles.permisos.revoke');
    });

    // --- Permisos (Solo creación interna) ---
    Route::post('/permisos', [RolePermissionController::class, 'storePermission'])
        ->middleware('can:permisos.ver')->name('permisos.store');

    // --- Usuarios ---
    Route::middleware(['can:usuarios.ver'])->group(function () {

        // Reset Password (Ruta específica antes del resource)
        Route::patch('usuarios/{usuario}/reset-password', [UsuarioController::class, 'resetPassword'])
            ->name('usuarios.reset_password');

        // Activar Usuario
        Route::post('/usuarios/{usuario}/activar', [UsuarioController::class, 'activar'])
            ->middleware('can:usuarios.editar')->name('usuarios.activar');

        // Resource estándar (excluyendo show si no se usa)
        Route::resource('usuarios', UsuarioController::class)->except(['show']);
    });
});


// =========================================================================
// MÓDULO: INVENTARIO (Medicamentos y Categorías)
// =========================================================================
Route::middleware(['auth', 'can:medicamentos.ver'])->prefix('inventario')->name('inventario.')->group(function () {

    // --- Categorías ---
    Route::middleware('can:categorias.ver')->group(function () {
        Route::resource('categorias', CategoriaController::class);
    });

    // --- Medicamentos: Rutas Específicas (Deben ir antes de resource) ---

    // Búsqueda para autocompletado
    Route::get('medicamentos/buscar', [MedicamentoController::class, 'lookup'])
        ->name('medicamentos.lookup');

    // Creación Rápida
    Route::post('medicamentos/store-rapido', [MedicamentoController::class, 'storeRapido'])
        ->name('medicamentos.storeRapido');

    // Gestión por Sucursal (Stocks/Precios específicos)
    Route::get('medicamentos/{medicamento}/sucursales/{sucursal}/editar', [MedicamentoSucursalController::class, 'edit'])
        ->middleware('can:medicamentos.editar')->name('medicamentos.editSucursal');

    Route::put('medicamentos/{medicamento}/sucursales/{sucursal}', [MedicamentoSucursalController::class, 'update'])
        ->middleware('can:medicamentos.editar')->name('medicamentos.updateSucursal');

    Route::delete('medicamentos/{medicamento}/sucursales/{sucursal}', [MedicamentoSucursalController::class, 'destroy'])
        ->middleware('can:medicamentos.eliminar')->name('medicamentos.detachSucursal');

    Route::post('medicamentos/{medicamento}/sucursales', [MedicamentoSucursalController::class, 'attach'])
        ->middleware('can:medicamentos.editar')->name('medicamentos.attachSucursal');

    // --- Medicamentos: Resource Principal ---
    // Sobrescribimos store para validar permiso específico
    Route::post('medicamentos', [MedicamentoController::class, 'store'])
        ->middleware('can:medicamentos.crear')->name('medicamentos.store');

    Route::resource('medicamentos', MedicamentoController::class)->except(['store']);
});


// =========================================================================
// MÓDULO: COMPRAS (Proveedores y Órdenes)
// =========================================================================
Route::middleware('auth')->group(function () {

    // --- Proveedores ---
    Route::middleware('can:proveedores.ver')->name('inventario.')->group(function () {

        Route::resource('proveedores', ProveedorController::class)
            ->parameters(['proveedores' => 'proveedor']);
    });

    // --- Compras ---
    Route::resource('compras', CompraController::class);
});


// =========================================================================
// MÓDULO: VENTAS (Cajas y Punto de Venta)
// =========================================================================
Route::middleware('auth')->group(function () {

    // --- Control de Caja (Sesiones) ---
    Route::get('cajas', [CajaSesionController::class, 'index'])->name('cajas.index');
    Route::post('cajas', [CajaSesionController::class, 'store'])->name('cajas.store');
    Route::get('cajas/{id}', [CajaSesionController::class, 'show'])->name('cajas.show');
    Route::patch('cajas/{id}', [CajaSesionController::class, 'update'])->name('cajas.update');

    // --- Ventas: Lookups (AJAX) ---
    Route::get('ventas/lookup-medicamentos', [VentaController::class, 'lookupMedicamentos'])
        ->name('ventas.lookup_medicamentos');

    Route::get('ventas/lookup-lotes', [VentaController::class, 'lookupLotes'])
        ->name('ventas.lookup_lotes');

    Route::get('ventas/lookup-cliente', [VentaController::class, 'buscarCliente'])
        ->name('ventas.buscar_cliente');

    // --- Ventas: Resource Principal ---
    Route::resource('ventas', VentaController::class);
});


// =========================================================================
// MÓDULO: CONFIGURACIÓN (Sucursales)
// =========================================================================
Route::middleware(['auth', 'can:sucursales.ver'])->prefix('configuracion')->name('configuracion.')->group(function () {
    Route::resource('sucursales', SucursalController::class)
        ->parameters(['sucursales' => 'sucursal']);
});


// =========================================================================
// RUTAS DE AUTENTICACIÓN (Breeze)
// =========================================================================
require __DIR__ . '/auth.php';
