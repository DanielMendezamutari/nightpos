<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\EnsuresShiftBelongsToResolvedSite;
use App\Http\Controllers\Controller;
use App\Support\CompanionWorkSessionTotals;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ListCompanionWorkSessionsController extends Controller
{
    use EnsuresShiftBelongsToResolvedSite;

    public function __invoke(int $shiftTurnId, Request $request): JsonResponse
    {
        if ($response = $this->ensureShiftBelongsToResolvedSite($request, $shiftTurnId)) {
            return $response;
        }

        $rows = DB::table('companion_work_sessions as s')
            ->join('companions as c', 'c.id', '=', 's.companion_id')
            ->where('s.shift_turn_id', $shiftTurnId)
            ->orderByDesc('s.started_at')
            ->orderByDesc('s.id')
            ->get([
                's.id',
                's.site_id',
                's.shift_turn_id',
                's.companion_id',
                's.started_at',
                's.ended_at',
                's.status',
                's.created_at',
                'c.stage_name',
            ]);

        $data = $rows->map(static function ($r): array {
            $snap = CompanionWorkSessionTotals::snapshot($r);

            return [
                'id' => (int) $r->id,
                'companion_id' => (int) $r->companion_id,
                'stage_name' => $r->stage_name,
                'status' => $r->status,
                'started_at' => $r->started_at,
                'ended_at' => $r->ended_at,
                ...$snap,
            ];
        });

        return response()->json(['data' => $data]);
    }
}
