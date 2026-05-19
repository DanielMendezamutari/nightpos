<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;

final class CompanionWorkSessionTotals
{
    /**
     * @return array{
     *   manilla_lines: int,
     *   manilla_units: int,
     *   manilla_subtotal: int,
     *   pieza_count: int,
     *   pieza_subtotal: int,
     *   pieza_paid_total: int,
     *   suggested_payout_manillas: int|null,
     *   suggested_payout_piezas: int|null,
     *   suggested_payout_total: int|null,
     *   paid_out_total: int,
     *   balance_due: int
     * }
     */
    public static function snapshot(stdClass $session): array
    {
        $start = Carbon::parse((string) $session->started_at);
        $end = $session->ended_at !== null
            ? Carbon::parse((string) $session->ended_at)
            : now();

        $shiftTurnId = (int) $session->shift_turn_id;
        $companionId = (int) $session->companion_id;

        $man = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('o.shift_turn_id', $shiftTurnId)
            ->where('oi.companion_id', $companionId)
            ->where('oi.consumption_type', 'with_companion')
            ->where('oi.registered_at', '>=', $start)
            ->where('oi.registered_at', '<=', $end)
            ->selectRaw('COUNT(oi.id) as lines, COALESCE(SUM(oi.quantity),0) as units, COALESCE(SUM(oi.subtotal),0) as subtotal')
            ->first();

        $piezaRows = DB::table('room_time_services as r')
            ->where('r.shift_turn_id', $shiftTurnId)
            ->where('r.companion_id', $companionId)
            ->where('r.created_at', '>=', $start)
            ->where('r.created_at', '<=', $end)
            ->get(['r.id', 'r.subtotal']);

        $piezaCount = $piezaRows->count();
        $piezaSubtotal = (int) $piezaRows->sum('subtotal');
        $serviceIds = $piezaRows->pluck('id')->all();
        $piezaPaid = $serviceIds === [] ? 0 : (int) DB::table('room_time_service_payments')
            ->whereIn('room_time_service_id', $serviceIds)
            ->sum('amount');

        $manillaSubtotal = (int) ($man->subtotal ?? 0);
        $manillaLines = (int) ($man->lines ?? 0);
        $manillaUnits = (int) ($man->units ?? 0);

        $manillaPct = self::readOptionalPct('companion_manilla_commission_pct');
        $piezaPct = self::readOptionalPct('companion_pieza_commission_pct');

        $sugM = $manillaPct !== null ? (int) round($manillaSubtotal * ($manillaPct / 100.0)) : null;
        $sugP = $piezaPct !== null ? (int) round($piezaSubtotal * ($piezaPct / 100.0)) : null;
        $sugTotal = ($sugM !== null || $sugP !== null) ? (int) (($sugM ?? 0) + ($sugP ?? 0)) : null;

        $paidOut = (int) DB::table('companion_work_session_payouts')
            ->where('companion_work_session_id', (int) $session->id)
            ->sum('amount');

        $balance = $sugTotal !== null ? max(0, $sugTotal - $paidOut) : 0;

        return [
            'manilla_lines' => $manillaLines,
            'manilla_units' => $manillaUnits,
            'manilla_subtotal' => $manillaSubtotal,
            'pieza_count' => $piezaCount,
            'pieza_subtotal' => $piezaSubtotal,
            'pieza_paid_total' => $piezaPaid,
            'suggested_payout_manillas' => $sugM,
            'suggested_payout_piezas' => $sugP,
            'suggested_payout_total' => $sugTotal,
            'paid_out_total' => $paidOut,
            'balance_due' => $balance,
        ];
    }

    private static function readOptionalPct(string $key): ?float
    {
        $raw = DB::table('system_settings')->where('key', $key)->value('reason');
        if ($raw === null || $raw === '') {
            return null;
        }
        if (! is_numeric($raw)) {
            return null;
        }
        $v = (float) $raw;
        if ($v < 0) {
            return 0.0;
        }
        if ($v > 100) {
            return 100.0;
        }

        return round($v, 2);
    }
}
