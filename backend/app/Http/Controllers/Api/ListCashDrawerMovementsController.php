<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\EnsuresShiftBelongsToResolvedSite;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ListCashDrawerMovementsController extends Controller
{
    use EnsuresShiftBelongsToResolvedSite;

    public function __invoke(int $shiftTurnId, Request $request): JsonResponse
    {
        if ($response = $this->ensureShiftBelongsToResolvedSite($request, $shiftTurnId)) {
            return $response;
        }

        $rows = DB::table('cash_drawer_movements')
            ->where('shift_turn_id', $shiftTurnId)
            ->orderByDesc('id')
            ->get()
            ->map(static function ($r): array {
                return [
                    'id' => (int) $r->id,
                    'direction' => $r->direction,
                    'amount' => (int) $r->amount,
                    'notes' => $r->notes,
                    'user_id' => (int) $r->user_id,
                    'created_at' => $r->created_at,
                ];
            })
            ->values()
            ->all();

        return response()->json(['data' => $rows]);
    }
}
