<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\EnsuresShiftBelongsToResolvedSite;
use App\Http\Controllers\Controller;
use App\Support\ShiftCashTotals;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetShiftCashSummaryController extends Controller
{
    use EnsuresShiftBelongsToResolvedSite;

    public function __invoke(int $shiftTurnId, Request $request): JsonResponse
    {
        if ($response = $this->ensureShiftBelongsToResolvedSite($request, $shiftTurnId)) {
            return $response;
        }

        try {
            $built = ShiftCashTotals::build($shiftTurnId);
        } catch (\InvalidArgumentException) {
            return response()->json(['message' => 'Turno no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $shift = $built['shift'];

        return response()->json([
            'data' => [
                'shift_turn_id' => $shiftTurnId,
                'status' => $shift->status,
                'opened_at' => $shift->opened_at,
                'opening_cash' => (int) $shift->opening_cash,
                'payment_totals' => $built['payment_totals'],
                'cash_from_sales' => $built['cash_from_sales'],
                'drawer_in' => $built['drawer_in'],
                'drawer_out' => $built['drawer_out'],
                'expected_cash' => $built['expected_cash'],
                'companion_payouts_total' => $built['companion_payouts_total'],
                'payments_all_methods_total' => $built['payments_all_methods_total'],
            ],
        ]);
    }
}
