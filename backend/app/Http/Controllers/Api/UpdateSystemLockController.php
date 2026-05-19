<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class UpdateSystemLockController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'is_locked' => ['required', 'boolean'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        DB::table('system_settings')->updateOrInsert(
            ['key' => 'global_lock'],
            [
                'is_locked' => $payload['is_locked'],
                'reason' => $payload['reason'] ?? null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json([
            'data' => [
                'is_locked' => (bool) $payload['is_locked'],
                'reason' => $payload['reason'] ?? null,
            ],
        ]);
    }
}
