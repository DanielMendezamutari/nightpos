<?php

use App\Http\Controllers\Api\V1\AuthController;
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
});
