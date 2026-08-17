<?php

use App\Http\Controllers\Tienda\AdminPedidoOnlineController;
use App\Http\Controllers\Tienda\AdminTiendaProductoController;
use App\Http\Controllers\Tienda\CarritoController;
use App\Http\Controllers\Tienda\ChatController;
use App\Http\Controllers\Tienda\CheckoutController;
use App\Http\Controllers\Tienda\MisPedidosController;
use App\Http\Controllers\Tienda\PedidoOnlineController;
use App\Http\Controllers\Tienda\TiendaAuthController;
use App\Http\Controllers\Tienda\TiendaController;
use Illuminate\Support\Facades\Route;

Route::prefix('tienda')->name('tienda.')->group(function () {
    Route::get('/', [TiendaController::class, 'index'])->name('index');
    Route::get('/sucursales', [TiendaController::class, 'sucursales'])->name('sucursales');
    Route::get('/producto/{slug}', [TiendaController::class, 'show'])->name('productos.show');

    Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
    Route::post('/carrito/{producto}', [CarritoController::class, 'store'])->name('carrito.store');
    Route::patch('/carrito/{producto}', [CarritoController::class, 'update'])->name('carrito.update');
    Route::delete('/carrito/{producto}', [CarritoController::class, 'destroy'])->name('carrito.destroy');

    Route::middleware('auth:tienda')->group(function () {
        Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    });

    Route::get('/chat', [ChatController::class, 'showChat'])->name('chat');
    Route::get('/chat/history', [ChatController::class, 'getHistory'])->name('chat.history');
    Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/chat/reset', [ChatController::class, 'resetHistory'])->name('chat.reset');
    Route::get('/productos/json/{producto}', [ChatController::class, 'getProductJson'])->name('productos.json');
    Route::get('/chat/conversaciones', [ChatController::class, 'getConversations'])->name('chat.conversaciones');
    Route::post('/chat/conversaciones/{conversacion}/active', [ChatController::class, 'selectConversation'])->name('chat.conversaciones.active');

    Route::get('/pedido/{codigo}', [PedidoOnlineController::class, 'show'])->name('pedidos.show');
    Route::get('/recojo/{token}', [PedidoOnlineController::class, 'recojo'])->name('pedidos.recojo');

    Route::get('/login', [TiendaAuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [TiendaAuthController::class, 'login']);
    Route::get('/register', [TiendaAuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [TiendaAuthController::class, 'register']);
    Route::get('/check-documento', [TiendaAuthController::class, 'checkDocumento'])->name('check_documento');

    Route::middleware('auth:tienda')->group(function () {
        Route::post('/logout', [TiendaAuthController::class, 'logout'])->name('logout');
        Route::get('/mis-pedidos', [MisPedidosController::class, 'index'])->name('mis-pedidos');
        Route::get('/mis-pedidos/venta/{venta}', [MisPedidosController::class, 'detalleVenta'])->name('mis-pedidos.venta_detalle');
        Route::get('/perfil', [TiendaAuthController::class, 'perfil'])->name('perfil');
        Route::put('/perfil', [TiendaAuthController::class, 'actualizarPerfil'])->name('perfil.update');
    });
});

Route::middleware(['auth'])
    ->prefix('tienda-admin/productos')
    ->name('tienda.admin.productos.')
    ->group(function () {
        Route::get('/', [AdminTiendaProductoController::class, 'index'])->name('index');
        Route::get('/buscar-medicamentos', [AdminTiendaProductoController::class, 'buscarMedicamentos'])->name('buscar_medicamentos');
        Route::get('/crear', [AdminTiendaProductoController::class, 'create'])->name('create');
        Route::post('/', [AdminTiendaProductoController::class, 'store'])->name('store');
        Route::get('/{producto}/editar', [AdminTiendaProductoController::class, 'edit'])->name('edit');
        Route::put('/{producto}', [AdminTiendaProductoController::class, 'update'])->name('update');
        Route::delete('/{producto}/imagenes/{imagen}', [AdminTiendaProductoController::class, 'destroyImagen'])->name('imagenes.destroy');
        Route::delete('/{producto}', [AdminTiendaProductoController::class, 'destroy'])->name('destroy');
    });

Route::middleware(['auth'])
    ->prefix('ventas/pedidos-online')
    ->name('tienda.admin.pedidos.')
    ->group(function () {
        Route::get('/', [AdminPedidoOnlineController::class, 'index'])->name('index');
        Route::get('/buscar-ajax', [AdminPedidoOnlineController::class, 'buscarAjax'])->name('buscar_ajax');
        Route::get('/{pedido}', [AdminPedidoOnlineController::class, 'show'])->name('show');
        Route::patch('/{pedido}/estado', [AdminPedidoOnlineController::class, 'updateEstado'])->name('estado');
        Route::post('/{pedido}/entregar-facturar', [AdminPedidoOnlineController::class, 'entregarYFacturar'])->name('entregar_facturar');
    });
