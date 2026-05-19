<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class PayRoomTimeServiceController extends Controller
{
    public function __invoke(int $serviceId, Request $request): JsonResponse
    {
        $payload = $request->validate([
            'shift_turn_id' => ['required', 'integer', 'exists:shift_turns,id'],
            'method' => ['required', Rule::in(['cash', 'qr', 'card'])],
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        $service = DB::table('room_time_services')->where('id', $serviceId)->first();
        if (! $service || $service->status !== 'closed') {
            return response()->json(['message' => 'La pieza debe estar cerrada antes de cobrar.'], 422);
        }

        $shift = DB::table('shift_turns')->where('id', (int) $payload['shift_turn_id'])->first();
        if (! $shift || $shift->status !== 'open') {
            return response()->json(['message' => 'Turno de caja no válido.'], 422);
        }

        if ((int) $shift->site_id !== (int) $service->site_id) {
            return response()->json(['message' => 'La caja y la pieza deben ser de la misma sucursal.'], 422);
        }

        $paidTotalBefore = (int) DB::table('room_time_service_payments')
            ->where('room_time_service_id', $serviceId)
            ->sum('amount');
        $subtotal = (int) ($service->subtotal ?? 0);
        if ($subtotal > 0 && $paidTotalBefore >= $subtotal) {
            return response()->json(['message' => 'La pieza ya está totalmente pagada.'], 422);
        }

        $paymentId = DB::table('room_time_service_payments')->insertGetId([
            'room_time_service_id' => $serviceId,
            'shift_turn_id' => (int) $payload['shift_turn_id'],
            'cashier_user_id' => (int) $request->user()->id,
            'method' => $payload['method'],
            'amount' => (int) $payload['amount'],
            'paid_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $paidTotalAfter = (int) DB::table('room_time_service_payments')
            ->where('room_time_service_id', $serviceId)
            ->sum('amount');
        $nextStatus = $subtotal > 0 && $paidTotalAfter >= $subtotal ? 'paid' : 'closed';

        DB::table('room_time_services')->where('id', $serviceId)->update([
            'status' => $nextStatus,
            'updated_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'payment_id' => $paymentId,
                'status' => $nextStatus,
                'paid_total' => $paidTotalAfter,
                'balance_due' => max(0, $subtotal - $paidTotalAfter),
            ],
        ], 201);
    }
}

