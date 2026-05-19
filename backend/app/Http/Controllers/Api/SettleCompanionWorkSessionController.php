<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use App\Support\CompanionWorkSessionTotals;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class SettleCompanionWorkSessionController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(int $sessionId, Request $request): JsonResponse
    {
        $resolved = $this->resolveBranchSiteId($request);
        if (! $resolved) {
            return response()->json([
                'message' => 'No se pudo determinar la sucursal. Si sos administrador global, enviá ?site_id= en la URL.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payload = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:400'],
        ]);

        $session = DB::table('companion_work_sessions as s')
            ->join('companions as c', 'c.id', '=', 's.companion_id')
            ->where('s.id', $sessionId)
            ->first(['s.*', 'c.stage_name as companion_stage_name']);
        if (! $session) {
            return response()->json(['message' => 'Sesión no encontrada.'], Response::HTTP_NOT_FOUND);
        }
        if ((int) $session->site_id !== $resolved) {
            return response()->json(['message' => 'La sesión no pertenece a la sucursal activa.'], Response::HTTP_FORBIDDEN);
        }
        if ($session->status !== 'active') {
            return response()->json(['message' => 'Esta salida ya fue liquidada.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $shift = DB::table('shift_turns')->where('id', (int) $session->shift_turn_id)->first();
        if (! $shift || $shift->status !== 'open') {
            return response()->json(['message' => 'El turno de caja debe estar abierto para pagar la salida.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $shiftTurnId = (int) $session->shift_turn_id;
        $companionLabel = (string) ($session->companion_stage_name ?? 'Chica');
        $drawerNote = 'Salida chica: '.$companionLabel.' (#'.$sessionId.')';
        if (! empty($payload['notes'])) {
            $drawerNote .= ' · '.$payload['notes'];
        }
        $drawerNote = Str::limit($drawerNote, 400, '');

        DB::transaction(function () use ($sessionId, $payload, $user, $shiftTurnId, $drawerNote): void {
            DB::table('companion_work_session_payouts')->insert([
                'companion_work_session_id' => $sessionId,
                'cashier_user_id' => (int) $user->id,
                'amount' => (int) $payload['amount'],
                'paid_at' => now(),
                'notes' => $payload['notes'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('companion_work_sessions')->where('id', $sessionId)->update([
                'ended_at' => now(),
                'status' => 'settled',
                'updated_at' => now(),
            ]);

            DB::table('cash_drawer_movements')->insert([
                'shift_turn_id' => $shiftTurnId,
                'user_id' => (int) $user->id,
                'direction' => 'out',
                'amount' => (int) $payload['amount'],
                'notes' => $drawerNote,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $row = DB::table('companion_work_sessions as s')
            ->join('companions as c', 'c.id', '=', 's.companion_id')
            ->where('s.id', $sessionId)
            ->first(['s.*', 'c.stage_name']);

        $snap = $row ? CompanionWorkSessionTotals::snapshot($row) : [];

        return response()->json([
            'data' => $row ? [
                'id' => (int) $row->id,
                'companion_id' => (int) $row->companion_id,
                'stage_name' => $row->stage_name,
                'status' => $row->status,
                'started_at' => $row->started_at,
                'ended_at' => $row->ended_at,
                ...$snap,
            ] : null,
        ]);
    }
}
