<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Cashier\Application\UseCases\CloseShiftUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CloseShiftController extends Controller
{
    public function __invoke(int $shiftTurnId, Request $request, CloseShiftUseCase $useCase): JsonResponse
    {
        $payload = $request->validate([
            'closing_cash' => ['required', 'integer', 'min:0'],
        ]);

        $data = $useCase->execute(
            shiftTurnId: $shiftTurnId,
            closingCash: $payload['closing_cash'],
        );

        return response()->json(['data' => $data]);
    }
}
