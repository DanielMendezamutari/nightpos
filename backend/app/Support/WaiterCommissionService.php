<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;

final class WaiterCommissionService
{
    public static function registerForPayment(int $paymentId): void
    {
        $payment = DB::table('payments')->where('id', $paymentId)->first();
        if (! $payment) {
            return;
        }

        $items = DB::table('order_items')
            ->where('order_id', (int) $payment->order_id)
            ->select(['id', 'waiter_user_id', 'subtotal'])
            ->get();

        $orderSubtotal = (int) $items->sum('subtotal');
        if ($orderSubtotal <= 0) {
            return;
        }

        $globalRatePct = self::resolveGlobalRatePct();

        $waiterIds = $items->pluck('waiter_user_id')->filter()->unique()->values()->all();
        /** @var list<int> $payrollWaiterIds */
        $payrollWaiterIds = [];
        /** @var array<int, float> $effectiveRateByWaiterId */
        $effectiveRateByWaiterId = [];

        if ($waiterIds !== []) {
            $userRows = DB::table('users')
                ->whereIn('id', $waiterIds)
                ->get(['id', 'waiter_compensation_type', 'waiter_commission_rate_pct']);

            foreach ($userRows as $u) {
                $id = (int) $u->id;
                if (in_array($u->waiter_compensation_type, ['payroll_monthly', 'payroll_weekly'], true)) {
                    $payrollWaiterIds[] = $id;

                    continue;
                }
                $custom = $u->waiter_commission_rate_pct;
                $effectiveRateByWaiterId[$id] = ($custom !== null && is_numeric($custom))
                    ? self::clampPct((float) $custom)
                    : $globalRatePct;
            }
        }

        foreach ($items as $item) {
            $wid = (int) $item->waiter_user_id;
            if ($wid > 0 && in_array($wid, $payrollWaiterIds, true)) {
                continue;
            }

            $base = (int) round(((int) $item->subtotal / $orderSubtotal) * (int) $payment->amount);
            if ($base <= 0) {
                continue;
            }

            $ratePct = $effectiveRateByWaiterId[$wid] ?? $globalRatePct;
            $commission = (int) round($base * ($ratePct / 100));

            DB::table('waiter_commissions')->insert([
                'payment_id' => $paymentId,
                'order_item_id' => (int) $item->id,
                'waiter_user_id' => (int) $item->waiter_user_id,
                'base_amount' => $base,
                'rate_pct' => $ratePct,
                'commission_amount' => $commission,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public static function currentRatePct(): float
    {
        return self::resolveGlobalRatePct();
    }

    /**
     * Reaplica el régimen y % vigente a las comisiones ya guardadas (ventas anteriores).
     * Sueldo fijo: elimina filas de comisión por cobro. Comisión: recalcula monto a partir de base_amount.
     */
    public static function recalculateStoredCommissionsForWaiter(int $waiterUserId): void
    {
        $u = DB::table('users')
            ->where('id', $waiterUserId)
            ->first(['id', 'role', 'waiter_compensation_type', 'waiter_commission_rate_pct']);

        if (! $u || $u->role !== 'waiter') {
            return;
        }

        if (in_array($u->waiter_compensation_type, ['payroll_monthly', 'payroll_weekly'], true)) {
            DB::table('waiter_commissions')->where('waiter_user_id', $waiterUserId)->delete();

            return;
        }

        $globalRatePct = self::resolveGlobalRatePct();
        $custom = $u->waiter_commission_rate_pct;
        $effective = ($custom !== null && is_numeric($custom))
            ? self::clampPct((float) $custom)
            : $globalRatePct;

        $now = now();
        DB::table('waiter_commissions')
            ->where('waiter_user_id', $waiterUserId)
            ->orderBy('id')
            ->get(['id', 'base_amount'])
            ->each(function ($row) use ($effective, $now): void {
                $base = (int) $row->base_amount;
                $commission = (int) round($base * ($effective / 100));
                DB::table('waiter_commissions')->where('id', (int) $row->id)->update([
                    'rate_pct' => $effective,
                    'commission_amount' => $commission,
                    'updated_at' => $now,
                ]);
            });
    }

    private static function resolveGlobalRatePct(): float
    {
        $raw = DB::table('system_settings')->where('key', 'waiter_commission_rate_pct')->value('reason');
        $rate = is_numeric($raw) ? (float) $raw : 10.0;

        return self::clampPct($rate);
    }

    private static function clampPct(float $rate): float
    {
        if ($rate < 0) {
            return 0.0;
        }

        if ($rate > 100) {
            return 100.0;
        }

        return round($rate, 2);
    }
}
