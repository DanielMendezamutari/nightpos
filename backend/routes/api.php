<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\SalonMesaController;
use App\Http\Controllers\Api\V1\ComandaController;
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
    });

    Route::prefix('menu')->group(function () {
        Route::get('categorias', [ComandaController::class, 'getCategorias']);
        Route::get('productos', [ComandaController::class, 'getProductos']);
        Route::get('observaciones-cocina', [ComandaController::class, 'getObservacionesCocina']);
    });

    Route::delete('visita-detalles/{detalleId}', [ComandaController::class, 'eliminarItem']);
});