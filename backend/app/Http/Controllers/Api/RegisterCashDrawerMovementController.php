<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\EnsuresShiftBelongsToResolvedSite;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

final class RegisterCashDrawerMovementController extends Controller
{
    use EnsuresShiftBelongsToResolvedSite;

    public function __invoke(int $shiftTurnId, Request $request): JsonResponse
    {
        if ($response = $this->ensureShiftBelongsToResolvedSite($request, $shiftTurnId)) {
            return $response;
        }

        $status = (string) DB::table('shift_turns')->where('id', $shiftTurnId)->value('status');
        if ($status !== 'open') {
            return response()->json(['message' => 'Solo se pueden registrar movimientos con el turno abierto.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payload = $request->validate([
            'direction' => ['required', Rule::in(['in', 'out'])],
            'amount' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:400'],
        ]);

        $id = DB::table('cash_drawer_movements')->insertGetId([
            'shift_turn_id' => $shiftTurnId,
            'user_id' => (int) $request->user()->id,
            'direction' => $payload['direction'],
            'amount' => (int) $payload['amount'],
            'notes' => $payload['notes'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'id' => $id,
                'direction' => $payload['direction'],
                'amount' => (int) $payload['amount'],
            ],
        ], Response::HTTP_CREATED);
    }
}
