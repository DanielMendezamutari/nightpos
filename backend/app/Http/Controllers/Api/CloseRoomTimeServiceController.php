<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class CloseRoomTimeServiceController extends Controller
{
    public function __invoke(int $serviceId, Request $request): JsonResponse
    {
        $payload = $request->validate([
            'manual_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ]);

        $service = DB::table('room_time_services')->where('id', $serviceId)->first();
        if (! $service || $service->status !== 'open') {
            return response()->json(['message' => 'Servicio no encontrado o ya cerrado.'], 422);
        }

        $started = strtotime((string) $service->started_at);
        $nowTs = time();
        $elapsed = max(1, (int) ceil(($nowTs - $started) / 60));
        $extensions = (int) DB::table('room_time_service_extensions')
            ->where('room_time_service_id', $serviceId)
            ->sum('added_minutes');

        $manual = isset($payload['manual_minutes']) ? (int) $payload['manual_minutes'] : null;
        $billable = $manual ?? max(0, $elapsed + $extensions - (int) $service->grace_minutes);
        if ($billable < 1) {
            $billable = 1;
        }

        $subtotal = (int) round(((int) $service->rate_per_hour / 60) * $billable);
        $paidTotal = (int) DB::table('room_time_service_payments')
            ->where('room_time_service_id', $serviceId)
            ->sum('amount');
        $nextStatus = $paidTotal >= $subtotal ? 'paid' : 'closed';
        $balanceDue = max(0, $subtotal - $paidTotal);

        DB::table('room_time_services')->where('id', $serviceId)->update([
            'closed_at' => now(),
            'manual_minutes' => $manual,
            'billed_minutes' => $billable,
            'subtotal' => $subtotal,
            'status' => $nextStatus,
            'updated_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'id' => $serviceId,
                'status' => $nextStatus,
                'billed_minutes' => $billable,
                'subtotal' => $subtotal,
                'paid_total' => $paidTotal,
                'balance_due' => $balanceDue,
            ],
        ]);
    }
}

