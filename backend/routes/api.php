<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\SalonMesaController;
use App\Http\Controllers\Api\V1\ComandaController;
use App\Http\Controllers\Api\V1\CajaFacturaController;
use App\Http\Controllers\Api\V1\QrPagoController;
use App\Http\Controllers\Api\V1\ClienteController;
use App\Http\Controllers\Api\V1\InventarioController;
use App\Http\Controllers\Api\V1\CompraController;
use App\Http\Controllers\Api\V1\ProveedorController;
use App\Http\Controllers\Api\V1\RecetaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('health', function () {
        return response()->json([
            'status' => 'healthy',
            'product' => 'RiberResto POS',
            'company' => 'Ribersoft',
            'timestamp' => now()->toISOString(),
        ]);
    });

    Route::prefix('auth')->group(function () {
        Route::post('login-pin', [AuthController::class, 'loginPin']);
        Route::post('login-password', [AuthController::class, 'loginPassword']);

        Route::middleware('auth:api')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    Route::prefix('salones')->group(function () {
        Route::get('/', [SalonMesaController::class, 'getSalones']);
        Route::get('{salonId}/mesas', [SalonMesaController::class, 'getMesasBySalon']);
    });

    Route::prefix('mesas')->group(function () {
        Route::get('{mesaId}', [SalonMesaController::class, 'getMesaDetails']);
        Route::post('{mesaId}/abrir', [SalonMesaController::class, 'abrirMesa']);
        Route::post('{mesaId}/cambiar', [SalonMesaController::class, 'cambiarMesa']);
        Route::post('{mesaId}/precuenta', [SalonMesaController::class, 'solicitarPrecuenta']);
        Route::post('{mesaId}/liberar', [SalonMesaController::class, 'liberarMesa']);
        Route::post('{mesaId}/comanda', [ComandaController::class, 'agregarComanda']);
        Route::post('{mesaId}/cobrar-facturar', [CajaFacturaController::class, 'cobrarYFacturar']);
    });

    Route::prefix('menu')->group(function () {
        Route::get('categorias', [ComandaController::class, 'getCategorias']);
        Route::get('productos', [ComandaController::class, 'getProductos']);
        Route::get('observaciones-cocina', [ComandaController::class, 'getObservacionesCocina']);
    });

    Route::delete('visita-detalles/{detalleId}', [ComandaController::class, 'eliminarItem']);

    Route::prefix('caja')->group(function () {
        Route::get('turno-activo', [CajaFacturaController::class, 'getTurnoActivo']);
        Route::post('abrir-turno', [CajaFacturaController::class, 'abrirTurno']);
        Route::post('cerrar-turno', [CajaFacturaController::class, 'cerrarTurno']);
        Route::get('movimientos', [CajaFacturaController::class, 'getMovimientos']);
        Route::post('movimientos', [CajaFacturaController::class, 'registrarMovimiento']);
    });

    Route::prefix('facturas')->group(function () {
        Route::get('/', [CajaFacturaController::class, 'getFacturas']);
        Route::post('{id}/anular', [CajaFacturaController::class, 'anularFactura']);
    });

    Route::prefix('pagos/qr')->group(function () {
        Route::post('generar', [QrPagoController::class, 'generar']);
        Route::get('estado/{codigo}', [QrPagoController::class, 'estado']);
        Route::post('webhook', [QrPagoController::class, 'webhook']);
        Route::post('simular/{codigo}', [QrPagoController::class, 'simular']);
    });

    Route::prefix('clientes')->group(function () {
        Route::get('/', [ClienteController::class, 'index']);
        Route::post('/', [ClienteController::class, 'store']);
        Route::get('{id}', [ClienteController::class, 'show']);
        Route::put('{id}', [ClienteController::class, 'update']);
        Route::delete('{id}', [ClienteController::class, 'destroy']);
        Route::post('{id}/abono', [ClienteController::class, 'registrarAbono']);
        Route::post('{id}/anticipos', [ClienteController::class, 'registrarAnticipo']);
        Route::get('{id}/anticipos-disponibles', [ClienteController::class, 'anticiposDisponibles']);
    });
    // ==========================================
    // BUCLE 6: ALMACENES, INVENTARIO, COMPRAS & RECETAS
    // ==========================================
    Route::prefix('almacenes')->group(function () {
        Route::get('/', [InventarioController::class, 'getAlmacenes']);
        Route::post('/', [InventarioController::class, 'storeAlmacen']);
        Route::put('{id}', [InventarioController::class, 'updateAlmacen']);
        Route::delete('{id}', [InventarioController::class, 'deleteAlmacen']);
    });

    Route::prefix('insumos')->group(function () {
        Route::get('/', [InventarioController::class, 'getInsumos']);
        Route::post('/', [InventarioController::class, 'storeInsumo']);
        Route::put('{id}', [InventarioController::class, 'updateInsumo']);
        Route::delete('{id}', [InventarioController::class, 'deleteInsumo']);
        Route::post('{id}/ajuste', [InventarioController::class, 'ajustarStock']);
    });

    Route::get('kardex', [InventarioController::class, 'getKardex']);

    Route::prefix('compras')->group(function () {
        Route::get('/', [CompraController::class, 'index']);
        Route::post('/', [CompraController::class, 'store']);
    });

    Route::prefix('proveedores')->group(function () {
        Route::get('/', [ProveedorController::class, 'index']);
        Route::post('/', [ProveedorController::class, 'store']);
        Route::put('{id}', [ProveedorController::class, 'update']);
        Route::delete('{id}', [ProveedorController::class, 'delete']);
        Route::post('{id}/pago', [ProveedorController::class, 'registrarPago']);
    });

    Route::prefix('recetas')->group(function () {
        Route::get('producto/{productoId}', [RecetaController::class, 'getReceta']);
        Route::post('producto/{productoId}', [RecetaController::class, 'guardarReceta']);
    });
});