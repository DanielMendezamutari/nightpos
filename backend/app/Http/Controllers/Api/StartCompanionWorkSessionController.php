<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\EnsuresShiftBelongsToResolvedSite;
use App\Http\Controllers\Controller;
use App\Support\CompanionWorkSessionTotals;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class StartCompanionWorkSessionController extends Controller
{
    use EnsuresShiftBelongsToResolvedSite;

    public function __invoke(int $shiftTurnId, Request $request): JsonResponse
    {
        if ($response = $this->ensureShiftBelongsToResolvedSite($request, $shiftTurnId)) {
            return $response;
        }

        $payload = $request->validate([
            'companion_id' => ['required', 'integer', 'exists:companions,id'],
            'started_at' => ['nullable', 'date'],
        ]);

        $shift = DB::table('shift_turns')->where('id', $shiftTurnId)->first();
        if (! $shift) {
            return response()->json(['message' => 'Turno no encontrado.'], Response::HTTP_NOT_FOUND);
        }
        if ($shift->status !== 'open') {
            return response()->json(['message' => 'Solo se puede fichar con el turno de caja abierto.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $companionId = (int) $payload['companion_id'];
        $active = DB::table('companion_work_sessions')
            ->where('shift_turn_id', $shiftTurnId)
            ->where('companion_id', $companionId)
            ->where('status', 'active')
            ->exists();
        if ($active) {
            return response()->json([
                'message' => 'Esta chica ya tiene una salida activa (fichada) en el turno. Liquidá la salida o cerrá la sesión antes de volver a fichar.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $startedAt = isset($payload['started_at']) ? $payload['started_at'] : now();

        $id = DB::table('companion_work_sessions')->insertGetId([
            'site_id' => (int) $shift->site_id,
            'shift_turn_id' => $shiftTurnId,
            'companion_id' => $companionId,
            'started_at' => $startedAt,
            'ended_at' => null,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('companion_work_sessions as s')
            ->join('companions as c', 'c.id', '=', 's.companion_id')
            ->where('s.id', $id)
            ->first(['s.*', 'c.stage_name']);

        if (! $row) {
            return response()->json(['message' => 'Error al crear la sesión.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $snap = CompanionWorkSessionTotals::snapshot($row);

        return response()->json([
            'data' => [
                'id' => (int) $row->id,
                'companion_id' => (int) $row->companion_id,
                'stage_name' => $row->stage_name,
                'status' => $row->status,
                'started_at' => $row->started_at,
                'ended_at' => $row->ended_at,
                ...$snap,
            ],
        ], Response::HTTP_CREATED);
    }
}
