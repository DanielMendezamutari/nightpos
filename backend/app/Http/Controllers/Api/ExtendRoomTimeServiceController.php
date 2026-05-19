<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ExtendRoomTimeServiceController extends Controller
{
    public function __invoke(int $serviceId, Request $request): JsonResponse
    {
        $payload = $request->validate([
            'added_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        $service = DB::table('room_time_services')->where('id', $serviceId)->first();
        if (! $service || $service->status !== 'open') {
            return response()->json(['message' => 'Servicio no encontrado o ya cerrado.'], 422);
        }

        DB::table('room_time_service_extensions')->insert([
            'room_time_service_id' => $serviceId,
            'user_id' => (int) $request->user()->id,
            'added_minutes' => (int) $payload['added_minutes'],
            'notes' => $payload['notes'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['data' => ['id' => $serviceId, 'extended_minutes' => (int) $payload['added_minutes']]]);
    }
}

