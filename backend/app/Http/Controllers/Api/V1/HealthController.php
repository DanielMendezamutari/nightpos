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

        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $dbOk = true;
        }
        catch (\Throwable) {
            $dbOk = false;
        }

        return response()->json([
            'ok' => $dbOk,
            'time' => now()->toIso8601String(),
            'version' => (string) config('nightpos.platform_operations.backend_version', '1.0.0'),
            'db' => $dbOk ? 'up' : 'down',
        ], $dbOk ? 200 : 503);
    }
}
