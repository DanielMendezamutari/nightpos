<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\EnsuresShiftBelongsToResolvedSite;
use App\Http\Controllers\Controller;
use App\Support\ShiftCashierReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetShiftCashierReportController extends Controller
{
    use EnsuresShiftBelongsToResolvedSite;

    public function __invoke(int $shiftTurnId, Request $request): JsonResponse
    {
        if ($response = $this->ensureShiftBelongsToResolvedSite($request, $shiftTurnId)) {
            return $response;
        }

        try {
            $data = ShiftCashierReport::build($shiftTurnId);
        } catch (\InvalidArgumentException) {
            return response()->json(['message' => 'Turno no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => $data,
        ]);
    }
}
