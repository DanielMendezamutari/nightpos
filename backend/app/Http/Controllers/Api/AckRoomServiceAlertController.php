<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class AckRoomServiceAlertController extends Controller
{
    public function __invoke(int $serviceId): JsonResponse
    {
        $updated = DB::table('room_time_services')
            ->where('id', $serviceId)
            ->whereNull('alert_notified_at')
            ->update([
                'alert_notified_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'data' => [
                'service_id' => $serviceId,
                'acknowledged' => $updated > 0,
            ],
        ]);
    }
}
