<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

final class CreateRoomTimeServiceController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if (! $siteId) {
            return response()->json(['message' => 'No se pudo resolver sucursal.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payload = $request->validate([
            'room_label' => ['nullable', 'string', 'max:60'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'companion_id' => ['required', 'integer', 'exists:companions,id'],
            'rate_per_hour' => ['required', 'integer', 'min:1'],
            'planned_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'alert_before_minutes' => ['nullable', 'integer', 'min:1', 'max:120'],
            'grace_minutes' => ['nullable', 'integer', 'min:0', 'max:120'],
            'notes' => ['nullable', 'string', 'max:400'],
            'payment_method' => ['required', Rule::in(['cash', 'qr', 'card'])],
            'payment_amount' => ['required', 'integer', 'min:1'],
        ]);

        $shiftId = (int) DB::table('shift_turns')
            ->where('site_id', $siteId)
            ->where('status', 'open')
            ->orderByDesc('id')
            ->value('id');
        if (! $shiftId) {
            return response()->json(['message' => 'Debe haber caja abierta para registrar pieza.'], 422);
        }

        $startedAt = now();
        $plannedMinutes = isset($payload['planned_minutes']) ? (int) $payload['planned_minutes'] : null;
        $alertBeforeMinutes = (int) ($payload['alert_before_minutes'] ?? 5);
        $alertAt = null;
        if ($plannedMinutes !== null) {
            $minutesToAlert = max(0, $plannedMinutes - $alertBeforeMinutes);
            $alertAt = $startedAt->copy()->addMinutes($minutesToAlert);
        }

        $id = DB::transaction(function () use ($payload, $request, $siteId, $shiftId, $startedAt, $plannedMinutes, $alertBeforeMinutes, $alertAt): int {
            $serviceId = (int) DB::table('room_time_services')->insertGetId([
                'site_id' => $siteId,
                'shift_turn_id' => $shiftId,
                'cashier_user_id' => (int) $request->user()->id,
                'waiter_user_id' => null,
                'companion_id' => (int) $payload['companion_id'],
                'customer_name' => $payload['customer_name'] ?? null,
                'room_label' => $payload['room_label'] ?? null,
                'rate_per_hour' => (int) $payload['rate_per_hour'],
                'planned_minutes' => $plannedMinutes,
                'alert_before_minutes' => $alertBeforeMinutes,
                'grace_minutes' => (int) ($payload['grace_minutes'] ?? 0),
                'started_at' => $startedAt,
                'alert_at' => $alertAt,
                'alert_notified_at' => null,
                'status' => 'open',
                'notes' => $payload['notes'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('room_time_service_payments')->insert([
                'room_time_service_id' => $serviceId,
                'shift_turn_id' => $shiftId,
                'cashier_user_id' => (int) $request->user()->id,
                'method' => $payload['payment_method'],
                'amount' => (int) $payload['payment_amount'],
                'paid_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $serviceId;
        });

        return response()->json([
            'data' => [
                'id' => $id,
                'status' => 'open',
                'prepaid_amount' => (int) $payload['payment_amount'],
                'alert_at' => $alertAt,
            ],
        ], 201);
    }
}

