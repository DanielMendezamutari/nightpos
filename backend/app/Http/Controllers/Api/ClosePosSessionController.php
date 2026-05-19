<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class ClosePosSessionController extends Controller
{
    public function __invoke(int $sessionId): JsonResponse
    {
        $updated = DB::table('customer_sessions')
            ->where('id', $sessionId)
            ->where('status', 'open')
            ->update([
                'status' => 'closed',
                'closed_at' => now(),
                'updated_at' => now(),
            ]);

        if (! $updated) {
            return response()->json(['message' => 'Sesion no encontrada o ya cerrada.'], 404);
        }

        return response()->json(['data' => ['id' => $sessionId, 'status' => 'closed']]);
    }
}

