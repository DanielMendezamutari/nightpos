<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;
use stdClass;

final class ShiftCashTotals
{
    /**
     * @return array{
     *   shift: stdClass,
     *   payment_totals: array<string, int>,
     *   cash_from_sales: int,
     *   drawer_in: int,
     *   drawer_out: int,
     *   expected_cash: int,
     *   companion_payouts_total: int,
     *   payments_all_methods_total: int
     * }
     */
    public static function build(int $shiftTurnId): array
    {
        $shift = DB::table('shift_turns')->where('id', $shiftTurnId)->first();
        if (! $shift) {
            throw new \InvalidArgumentException('Turno no encontrado.');
        }

        $totals = DB::table('payments')
            ->selectRaw('method, SUM(amount) as total')
            ->where('shift_turn_id', $shiftTurnId)
            ->groupBy('method')
            ->pluck('total', 'method');

        $cashFromSales = (int) ($totals['cash'] ?? 0);
        $qrTotal = (int) ($totals['qr'] ?? 0);
        $cardTotal = (int) ($totals['card'] ?? 0);

        $drawerIn = (int) DB::table('cash_drawer_movements')
            ->where('shift_turn_id', $shiftTurnId)
            ->where('direction', 'in')
            ->sum('amount');
        $drawerOut = (int) DB::table('cash_drawer_movements')
            ->where('shift_turn_id', $shiftTurnId)
            ->where('direction', 'out')
            ->sum('amount');

        $opening = (int) $shift->opening_cash;
        $expectedCash = $opening + $cashFromSales + $drawerIn - $drawerOut;

        $companionPayoutsTotal = self::companionPayoutsTotal($shiftTurnId);
        $paymentsAllMethods = $cashFromSales + $qrTotal + $cardTotal;

        return [
            'shift' => $shift,
            'payment_totals' => [
                'cash' => $cashFromSales,
                'qr' => $qrTotal,
                'card' => $cardTotal,
            ],
            'cash_from_sales' => $cashFromSales,
            'drawer_in' => $drawerIn,
            'drawer_out' => $drawerOut,
            'expected_cash' => $expectedCash,
            'companion_payouts_total' => $companionPayoutsTotal,
            'payments_all_methods_total' => $paymentsAllMethods,
        ];
    }

    public static function companionPayoutsTotal(int $shiftTurnId): int
    {
        $v = DB::table('companion_work_session_payouts as p')
            ->join('companion_work_sessions as s', 's.id', '=', 'p.companion_work_session_id')
            ->where('s.shift_turn_id', $shiftTurnId)
            ->sum('p.amount');

        return (int) $v;
    }
}
