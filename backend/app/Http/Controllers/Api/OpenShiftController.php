<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Cashier\Application\UseCases\OpenShiftUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OpenShiftController extends Controller
{
    public function __invoke(Request $request, OpenShiftUseCase $useCase): JsonResponse
    {
        $payload = $request->validate([
            'cashier_user_id' => ['required', 'integer', 'exists:users,id'],
            'site_id' => ['required', 'integer', 'exists:sites,id'],
            'period' => ['required', 'in:day,night'],
            'opening_cash' => ['required', 'integer', 'min:0'],
        ]);

        $shiftTurnId = $useCase->execute(
            cashierUserId: $payload['cashier_user_id'],
            siteId: $payload['site_id'],
            period: $payload['period'],
            openingCash: $payload['opening_cash'],
        );

        return response()->json([
            'data' => [
                'shift_turn_id' => $shiftTurnId,
                'status' => 'open',
            ],
        ], 201);
    }
}
