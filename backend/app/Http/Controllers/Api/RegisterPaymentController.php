<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\PaymentRegistered;
use App\Http\Controllers\Controller;
use App\Modules\Cashier\Application\UseCases\RegisterPaymentUseCase;
use App\Support\WaiterCommissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class RegisterPaymentController extends Controller
{
    public function __invoke(Request $request, RegisterPaymentUseCase $useCase): JsonResponse
    {
        $payload = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'shift_turn_id' => ['required', 'integer', 'exists:shift_turns,id'],
            'method' => ['required', 'in:cash,qr,card'],
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        $shift = DB::table('shift_turns')->where('id', (int) $payload['shift_turn_id'])->first();
        if (! $shift || $shift->status !== 'open') {
            return response()->json(['message' => 'El turno de caja debe estar abierto para cobrar.'], 422);
        }

        $order = DB::table('orders')->where('id', (int) $payload['order_id'])->first();
        if (! $order) {
            return response()->json(['message' => 'Orden no encontrada.'], 404);
        }

        if ((int) $order->shift_turn_id !== (int) $payload['shift_turn_id']) {
            return response()->json(['message' => 'La orden no pertenece al turno seleccionado.'], 422);
        }

        $paymentId = $useCase->execute(
            orderId: $payload['order_id'],
            shiftTurnId: $payload['shift_turn_id'],
            method: $payload['method'],
            amount: $payload['amount'],
        );

        WaiterCommissionService::registerForPayment($paymentId);

        DB::table('orders')->where('id', (int) $payload['order_id'])->update([
            'status' => 'paid',
            'updated_at' => now(),
        ]);

        event(new PaymentRegistered(
            paymentId: $paymentId,
            siteId: (int) $shift->site_id,
            amount: (int) $payload['amount'],
            method: (string) $payload['method'],
        ));

        return response()->json([
            'data' => [
                'payment_id' => $paymentId,
                'method' => $payload['method'],
                'amount' => $payload['amount'],
            ],
        ], 201);
    }
}
