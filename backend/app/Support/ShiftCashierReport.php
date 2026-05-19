<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;
use stdClass;

final class ShiftCashierReport
{
    /**
     * @return array<string, mixed>
     */
    public static function build(int $shiftTurnId): array
    {
        $cash = ShiftCashTotals::build($shiftTurnId);
        $shift = $cash['shift'];

        $waiterRows = self::waiterSales($shiftTurnId);
        $commissionRows = self::waiterCommissions($shiftTurnId);
        $waiters = self::mergeWaiters($waiterRows, $commissionRows);

        $productsSold = self::productsSold($shiftTurnId);
        $companionsManillas = self::companionsManillas($shiftTurnId);
        $piezasByCompanion = self::piezasAggregatedByCompanion($shiftTurnId);
        $piezaServices = self::piezaServicesDetail($shiftTurnId);

        $manillaPct = self::readOptionalPct('companion_manilla_commission_pct');
        $piezaPct = self::readOptionalPct('companion_pieza_commission_pct');

        $companionsOverview = self::buildCompanionsOverview(
            $companionsManillas,
            $piezasByCompanion,
            $manillaPct,
            $piezaPct
        );

        $drawerMovements = DB::table('cash_drawer_movements as m')
            ->join('users as u', 'u.id', '=', 'm.user_id')
            ->where('m.shift_turn_id', $shiftTurnId)
            ->orderByDesc('m.id')
            ->limit(200)
            ->get([
                'm.id',
                'm.direction',
                'm.amount',
                'm.notes',
                'm.created_at',
                'u.name as user_name',
            ])
            ->map(static function (stdClass $r): array {
                return [
                    'id' => (int) $r->id,
                    'direction' => $r->direction,
                    'amount' => (int) $r->amount,
                    'notes' => $r->notes,
                    'created_at' => $r->created_at,
                    'user_name' => $r->user_name,
                ];
            })
            ->values()
            ->all();

        $productSalesSubtotal = self::productSalesSubtotal($shiftTurnId);
        $waiterCommissionsTotal = self::waiterCommissionsTotal($shiftTurnId);
        $companionPayouts = self::companionPayoutsForShift($shiftTurnId);
        $companionPayoutsTotal = array_sum(array_column($companionPayouts, 'amount'));
        $pt = $cash['payment_totals'];
        $paymentsCollectedTotal = (int) (($pt['cash'] ?? 0) + ($pt['qr'] ?? 0) + ($pt['card'] ?? 0));

        $gastosPersonalTotal = $waiterCommissionsTotal + $companionPayoutsTotal;
        $erpSummary = [
            'product_sales_subtotal' => $productSalesSubtotal,
            'payments_collected' => [
                'cash' => (int) ($pt['cash'] ?? 0),
                'qr' => (int) ($pt['qr'] ?? 0),
                'card' => (int) ($pt['card'] ?? 0),
                'total' => $paymentsCollectedTotal,
            ],
            'non_cash_in_register' => [
                'qr' => (int) ($pt['qr'] ?? 0),
                'card' => (int) ($pt['card'] ?? 0),
                'total' => (int) (($pt['qr'] ?? 0) + ($pt['card'] ?? 0)),
            ],
            'waiter_commissions_total' => $waiterCommissionsTotal,
            'companion_payouts_total' => $companionPayoutsTotal,
            'companion_payouts' => $companionPayouts,
            'drawer_manual_in' => $cash['drawer_in'],
            'drawer_all_out' => $cash['drawer_out'],
            'expected_cash_in_drawer' => $cash['expected_cash'],
            'executive' => [
                'total_vendido' => $productSalesSubtotal,
                'total_cobrado' => $paymentsCollectedTotal,
                'total_gastos_personal' => $gastosPersonalTotal,
                'total_egresos_caja' => (int) $cash['drawer_out'],
            ],
        ];

        return [
            'shift_turn_id' => $shiftTurnId,
            'shift' => [
                'status' => $shift->status,
                'opened_at' => $shift->opened_at,
                'closed_at' => $shift->closed_at ?? null,
                'period' => $shift->period,
                'opening_cash' => (int) $shift->opening_cash,
                'closing_cash' => $shift->closing_cash !== null ? (int) $shift->closing_cash : null,
            ],
            'cash_totals' => [
                'payment_totals' => $cash['payment_totals'],
                'cash_from_sales' => $cash['cash_from_sales'],
                'drawer_in' => $cash['drawer_in'],
                'drawer_out' => $cash['drawer_out'],
                'expected_cash' => $cash['expected_cash'],
            ],
            'payout_settings' => [
                'waiter_commission_rate_pct' => WaiterCommissionService::currentRatePct(),
                'companion_manilla_commission_pct' => $manillaPct,
                'companion_pieza_commission_pct' => $piezaPct,
            ],
            'waiters' => $waiters,
            'products_sold' => $productsSold,
            'companions_manillas' => $companionsManillas,
            'companions_piezas' => $piezasByCompanion,
            'companions_overview' => $companionsOverview,
            'pieza_services' => $piezaServices,
            'cash_drawer_movements' => $drawerMovements,
            'erp_summary' => $erpSummary,
        ];
    }

    private static function productSalesSubtotal(int $shiftTurnId): int
    {
        $v = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('o.shift_turn_id', $shiftTurnId)
            ->sum('oi.subtotal');

        return (int) $v;
    }

    private static function waiterCommissionsTotal(int $shiftTurnId): int
    {
        $v = DB::table('waiter_commissions as wc')
            ->join('payments as p', 'p.id', '=', 'wc.payment_id')
            ->where('p.shift_turn_id', $shiftTurnId)
            ->sum('wc.commission_amount');

        return (int) $v;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function companionPayoutsForShift(int $shiftTurnId): array
    {
        return DB::table('companion_work_session_payouts as p')
            ->join('companion_work_sessions as s', 's.id', '=', 'p.companion_work_session_id')
            ->join('companions as c', 'c.id', '=', 's.companion_id')
            ->where('s.shift_turn_id', $shiftTurnId)
            ->orderByDesc('p.paid_at')
            ->orderByDesc('p.id')
            ->get([
                'p.id',
                'p.amount',
                'p.paid_at',
                'p.notes',
                's.id as session_id',
                'c.stage_name',
            ])
            ->map(static function (stdClass $r): array {
                return [
                    'id' => (int) $r->id,
                    'amount' => (int) $r->amount,
                    'paid_at' => $r->paid_at,
                    'notes' => $r->notes,
                    'session_id' => (int) $r->session_id,
                    'stage_name' => $r->stage_name,
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function waiterSales(int $shiftTurnId): array
    {
        return DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('users as u', 'u.id', '=', 'oi.waiter_user_id')
            ->where('o.shift_turn_id', $shiftTurnId)
            ->groupBy('oi.waiter_user_id', 'u.name')
            ->orderByDesc(DB::raw('SUM(oi.subtotal)'))
            ->get([
                'oi.waiter_user_id',
                'u.name as waiter_name',
                DB::raw('COUNT(DISTINCT o.id) as orders_count'),
                DB::raw('COALESCE(SUM(oi.subtotal), 0) as items_subtotal'),
                DB::raw('COALESCE(SUM(oi.quantity), 0) as units_sold'),
            ])
            ->map(static function (stdClass $r): array {
                return [
                    'waiter_user_id' => (int) $r->waiter_user_id,
                    'waiter_name' => $r->waiter_name ?? '—',
                    'orders_count' => (int) $r->orders_count,
                    'items_subtotal' => (int) $r->items_subtotal,
                    'units_sold' => (int) $r->units_sold,
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function waiterCommissions(int $shiftTurnId): array
    {
        return DB::table('waiter_commissions as wc')
            ->join('payments as p', 'p.id', '=', 'wc.payment_id')
            ->leftJoin('users as u', 'u.id', '=', 'wc.waiter_user_id')
            ->where('p.shift_turn_id', $shiftTurnId)
            ->groupBy('wc.waiter_user_id', 'u.name')
            ->orderByDesc(DB::raw('SUM(wc.commission_amount)'))
            ->get([
                'wc.waiter_user_id',
                'u.name as waiter_name',
                DB::raw('COALESCE(SUM(wc.commission_amount), 0) as commission_owed'),
                DB::raw('COALESCE(SUM(wc.base_amount), 0) as commission_base'),
            ])
            ->map(static function (stdClass $r): array {
                return [
                    'waiter_user_id' => (int) $r->waiter_user_id,
                    'waiter_name' => $r->waiter_name ?? '—',
                    'commission_owed' => (int) $r->commission_owed,
                    'commission_base' => (int) $r->commission_base,
                ];
            })
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $sales
     * @param  list<array<string, mixed>>  $commissions
     * @return list<array<string, mixed>>
     */
    private static function mergeWaiters(array $sales, array $commissions): array
    {
        /** @var array<int, array<string, mixed>> $byId */
        $byId = [];

        foreach ($sales as $row) {
            $id = (int) $row['waiter_user_id'];
            $byId[$id] = [
                'waiter_user_id' => $id,
                'waiter_name' => $row['waiter_name'],
                'orders_count' => $row['orders_count'],
                'items_subtotal' => $row['items_subtotal'],
                'units_sold' => $row['units_sold'],
                'commission_owed' => 0,
                'commission_base' => 0,
            ];
        }

        foreach ($commissions as $row) {
            $id = (int) $row['waiter_user_id'];
            if (! isset($byId[$id])) {
                $byId[$id] = [
                    'waiter_user_id' => $id,
                    'waiter_name' => $row['waiter_name'],
                    'orders_count' => 0,
                    'items_subtotal' => 0,
                    'units_sold' => 0,
                    'commission_owed' => 0,
                    'commission_base' => 0,
                ];
            }
            $byId[$id]['commission_owed'] = $row['commission_owed'];
            $byId[$id]['commission_base'] = $row['commission_base'];
            if (($byId[$id]['waiter_name'] === '—' || $byId[$id]['waiter_name'] === '') && $row['waiter_name'] !== '—') {
                $byId[$id]['waiter_name'] = $row['waiter_name'];
            }
        }

        return array_values($byId);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function productsSold(int $shiftTurnId): array
    {
        return DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'p.id', '=', 'oi.product_id')
            ->where('o.shift_turn_id', $shiftTurnId)
            ->groupBy('p.id', 'p.sku', 'p.name')
            ->orderByDesc(DB::raw('SUM(oi.subtotal)'))
            ->get([
                'p.id as product_id',
                'p.sku',
                'p.name',
                DB::raw('SUM(oi.quantity) as quantity'),
                DB::raw('SUM(oi.subtotal) as subtotal'),
            ])
            ->map(static function (stdClass $r): array {
                return [
                    'product_id' => (int) $r->product_id,
                    'sku' => $r->sku,
                    'name' => $r->name,
                    'quantity' => (int) $r->quantity,
                    'subtotal' => (int) $r->subtotal,
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function companionsManillas(int $shiftTurnId): array
    {
        return DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('companions as c', 'c.id', '=', 'oi.companion_id')
            ->where('o.shift_turn_id', $shiftTurnId)
            ->where('oi.consumption_type', 'with_companion')
            ->whereNotNull('oi.companion_id')
            ->groupBy('c.id', 'c.stage_name')
            ->orderByDesc(DB::raw('SUM(oi.subtotal)'))
            ->get([
                'c.id as companion_id',
                'c.stage_name',
                DB::raw('COUNT(oi.id) as manilla_lines'),
                DB::raw('SUM(oi.quantity) as manilla_units'),
                DB::raw('SUM(oi.subtotal) as manilla_subtotal'),
            ])
            ->map(static function (stdClass $r): array {
                return [
                    'companion_id' => (int) $r->companion_id,
                    'stage_name' => $r->stage_name,
                    'manilla_lines' => (int) $r->manilla_lines,
                    'manilla_units' => (int) $r->manilla_units,
                    'manilla_subtotal' => (int) $r->manilla_subtotal,
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function piezasAggregatedByCompanion(int $shiftTurnId): array
    {
        $services = DB::table('room_time_services as r')
            ->leftJoin('companions as c', 'c.id', '=', 'r.companion_id')
            ->where('r.shift_turn_id', $shiftTurnId)
            ->whereNotNull('r.companion_id')
            ->get([
                'r.id',
                'r.companion_id',
                'r.subtotal',
                'c.stage_name',
            ]);

        if ($services->isEmpty()) {
            return [];
        }

        $serviceIds = $services->pluck('id')->all();
        $paidByService = DB::table('room_time_service_payments')
            ->whereIn('room_time_service_id', $serviceIds)
            ->groupBy('room_time_service_id')
            ->selectRaw('room_time_service_id, SUM(amount) as paid_total')
            ->pluck('paid_total', 'room_time_service_id');

        /** @var array<int, array{companion_id:int, stage_name:string, pieza_count:int, pieza_subtotal:int, pieza_paid_total:int}> $agg */
        $agg = [];

        foreach ($services as $row) {
            $cid = (int) $row->companion_id;
            if (! isset($agg[$cid])) {
                $agg[$cid] = [
                    'companion_id' => $cid,
                    'stage_name' => $row->stage_name ?? '—',
                    'pieza_count' => 0,
                    'pieza_subtotal' => 0,
                    'pieza_paid_total' => 0,
                ];
            }
            $agg[$cid]['pieza_count']++;
            $agg[$cid]['pieza_subtotal'] += (int) $row->subtotal;
            $agg[$cid]['pieza_paid_total'] += (int) ($paidByService[(int) $row->id] ?? 0);
        }

        $out = [];
        foreach ($agg as $row) {
            $sub = $row['pieza_subtotal'];
            $paid = $row['pieza_paid_total'];
            $out[] = [
                'companion_id' => $row['companion_id'],
                'stage_name' => $row['stage_name'],
                'pieza_count' => $row['pieza_count'],
                'pieza_subtotal' => $sub,
                'pieza_paid_total' => $paid,
                'pieza_balance_due' => max(0, $sub - $paid),
            ];
        }

        usort($out, static fn (array $a, array $b): int => $b['pieza_subtotal'] <=> $a['pieza_subtotal']);

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function piezaServicesDetail(int $shiftTurnId): array
    {
        $rows = DB::table('room_time_services as r')
            ->leftJoin('companions as c', 'c.id', '=', 'r.companion_id')
            ->where('r.shift_turn_id', $shiftTurnId)
            ->orderByDesc('r.id')
            ->get([
                'r.id',
                'r.companion_id',
                'c.stage_name as companion_name',
                'r.room_label',
                'r.subtotal',
                'r.status',
                'r.billed_minutes',
            ]);

        if ($rows->isEmpty()) {
            return [];
        }

        $paidByService = DB::table('room_time_service_payments')
            ->whereIn('room_time_service_id', $rows->pluck('id')->all())
            ->groupBy('room_time_service_id')
            ->selectRaw('room_time_service_id, SUM(amount) as paid_total')
            ->pluck('paid_total', 'room_time_service_id');

        return $rows->map(static function (stdClass $r) use ($paidByService): array {
            $sub = (int) $r->subtotal;
            $paid = (int) ($paidByService[(int) $r->id] ?? 0);

            return [
                'id' => (int) $r->id,
                'companion_id' => $r->companion_id !== null ? (int) $r->companion_id : null,
                'companion_name' => $r->companion_name,
                'room_label' => $r->room_label,
                'billed_minutes' => (int) $r->billed_minutes,
                'subtotal' => $sub,
                'paid_total' => $paid,
                'balance_due' => max(0, $sub - $paid),
                'status' => $r->status,
                'did_pieza' => true,
            ];
        })->all();
    }

    /**
     * @param  list<array<string, mixed>>  $manillas
     * @param  list<array<string, mixed>>  $piezas
     * @return list<array<string, mixed>>
     */
    private static function buildCompanionsOverview(
        array $manillas,
        array $piezas,
        ?float $manillaPct,
        ?float $piezaPct
    ): array {
        /** @var array<int, array<string, mixed>> $by */
        $by = [];

        foreach ($manillas as $row) {
            $id = (int) $row['companion_id'];
            $by[$id] = [
                'companion_id' => $id,
                'stage_name' => $row['stage_name'],
                'manilla_lines' => $row['manilla_lines'],
                'manilla_units' => $row['manilla_units'],
                'manilla_subtotal' => $row['manilla_subtotal'],
                'pieza_count' => 0,
                'pieza_subtotal' => 0,
                'pieza_paid_total' => 0,
                'pieza_balance_due' => 0,
                'suggested_payout_manillas' => null,
                'suggested_payout_piezas' => null,
                'suggested_payout_total' => null,
            ];
        }

        foreach ($piezas as $row) {
            $id = (int) $row['companion_id'];
            if (! isset($by[$id])) {
                $by[$id] = [
                    'companion_id' => $id,
                    'stage_name' => $row['stage_name'],
                    'manilla_lines' => 0,
                    'manilla_units' => 0,
                    'manilla_subtotal' => 0,
                    'pieza_count' => 0,
                    'pieza_subtotal' => 0,
                    'pieza_paid_total' => 0,
                    'pieza_balance_due' => 0,
                    'suggested_payout_manillas' => null,
                    'suggested_payout_piezas' => null,
                    'suggested_payout_total' => null,
                ];
            }
            $by[$id]['pieza_count'] = $row['pieza_count'];
            $by[$id]['pieza_subtotal'] = $row['pieza_subtotal'];
            $by[$id]['pieza_paid_total'] = $row['pieza_paid_total'];
            $by[$id]['pieza_balance_due'] = $row['pieza_balance_due'];
            if (($by[$id]['stage_name'] === '—' || $by[$id]['stage_name'] === '') && $row['stage_name'] !== '—') {
                $by[$id]['stage_name'] = $row['stage_name'];
            }
        }

        foreach ($by as $id => &$row) {
            $mSub = (int) $row['manilla_subtotal'];
            $pSub = (int) $row['pieza_subtotal'];
            $sm = $manillaPct !== null ? (int) round($mSub * ($manillaPct / 100.0)) : null;
            $sp = $piezaPct !== null ? (int) round($pSub * ($piezaPct / 100.0)) : null;
            $row['suggested_payout_manillas'] = $sm;
            $row['suggested_payout_piezas'] = $sp;
            $row['suggested_payout_total'] = ($sm !== null || $sp !== null)
                ? (int) (($sm ?? 0) + ($sp ?? 0))
                : null;
        }
        unset($row);

        $list = array_values($by);
        usort($list, static function (array $a, array $b): int {
            return ($b['manilla_subtotal'] + $b['pieza_subtotal']) <=> ($a['manilla_subtotal'] + $a['pieza_subtotal']);
        });

        return $list;
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
