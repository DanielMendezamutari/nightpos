<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $dbOk = false;
        $jwtOk = false;

        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $dbOk = true;
        }
        catch (\Throwable) {
            $dbOk = false;
        }

        $jwtSecret = config('jwt.secret');
        $jwtOk = is_string($jwtSecret) && trim($jwtSecret) !== '';

        return response()->json([
            'ok' => $dbOk && $jwtOk,
            'time' => now()->toIso8601String(),
            'version' => (string) config('nightpos.platform_operations.backend_version', '1.0.0'),
            'db' => $dbOk ? 'up' : 'down',
            'jwt' => $jwtOk ? 'up' : 'down',
        ], ($dbOk && $jwtOk) ? 200 : 503);
    }
}
