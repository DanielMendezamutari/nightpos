<?php

declare(strict_types=1);

namespace App\Application\Reports\Services;

use App\Domain\Reports\Repositories\ReportReadRepositoryInterface;

final class ManagerialReportAssemblerService
{
    public function __construct(
        private readonly ReportReadRepositoryInterface $reports,
    ) {}

    public function assemble(int $tenantId, int $branchId, array $filters): array
    {
        $rankingsLimit = max(1, min(50, (int) ($filters['include_rankings_limit'] ?? 10)));

        $scopeShiftIds = $this->reports->getManagerialScopeShiftIds($tenantId, $branchId, $filters);

        $daily = $this->reports->getDailySummary($tenantId, $branchId, $filters);
        $sales = $this->reports->getSalesReport($tenantId, $branchId, $filters);
        $cash = $this->reports->getCashReport($tenantId, $branchId, $filters);
        $services = $this->reports->getServicesReport($tenantId, $branchId, $filters);
        $rooms = $this->reports->getRoomsReport($tenantId, $branchId, $filters);
        $settlements = $this->reports->getSettlementsReport($tenantId, $branchId, $filters);
        $products = $this->reports->getProductReconciliation($tenantId, $branchId, $filters);
        $hourly = $this->reports->getManagerialHourlyPerformance($tenantId, $branchId, $filters);

        $waiterRankings = $this->buildWaiterRankings($sales, $settlements, $rankingsLimit);
        $girlRankings = $this->buildGirlRankings($services, $settlements, $rankingsLimit);
        $productRankings = $this->buildProductRankings($products, $rankingsLimit);
        $roomPerformance = $this->buildRoomPerformance($rooms, $rankingsLimit);
        $cashHealth = $this->buildCashHealth($cash);
        $alerts = $this->buildAlerts($tenantId, $branchId, $scopeShiftIds);

        $totalSales = $this->toFloat($daily['sales']['total'] ?? 0);
        $totalCash = $this->toFloat($daily['sales']['total_cash'] ?? 0);
        $totalQr = $this->toFloat($daily['sales']['total_qr'] ?? 0);
        $totalCard = $this->toFloat($daily['sales']['total_card'] ?? 0);
        $totalMixed = $this->toFloat($sales['totals']['by_method']['MIXED'] ?? 0);

        $settlementsPaid = $this->toFloat($daily['settlements']['paid'] ?? 0);
        $settlementsPending = $this->toFloat($daily['settlements']['pending'] ?? 0);

        $expectedCash = $this->toFloat($daily['cash']['expected_cash'] ?? 0);
        $declaredCash = $this->toFloat($cashHealth['totals']['declared_total'] ?? 0);
        $cashDifference = $this->toFloat($cashHealth['totals']['difference_total'] ?? 0);

        $manualExpense = $this->toFloat($daily['cash']['manual_expense'] ?? 0);
        $grossRevenue = $totalSales;
        $outflows = $settlementsPaid + $manualExpense;
        $netHouseEstimated = $grossRevenue - $outflows;

        return [
            'scope' => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'official_shift_ids' => $scopeShiftIds,
                'date_from' => $filters['date_from'] ?? null,
                'date_to' => $filters['date_to'] ?? null,
            ],
            'kpis' => [
                'total_sales' => $this->fmt($totalSales),
                'total_cash' => $this->fmt($totalCash),
                'total_qr' => $this->fmt($totalQr),
                'total_card' => $this->fmt($totalCard),
                'total_mixed' => $this->fmt($totalMixed),
                'settlements_paid_total' => $this->fmt($settlementsPaid),
                'settlements_pending_total' => $this->fmt($settlementsPending),
                'expected_cash_total' => $this->fmt($expectedCash),
                'declared_cash_total' => $this->fmt($declaredCash),
                'cash_difference_total' => $this->fmt($cashDifference),
                'net_house_estimated' => $this->fmt($netHouseEstimated),
            ],
            'waiter_rankings' => $waiterRankings,
            'girl_rankings' => $girlRankings,
            'product_rankings' => $productRankings,
            'hourly_performance' => $hourly,
            'room_performance' => $roomPerformance,
            'cash_health' => $cashHealth,
            'alerts' => $alerts,
            'managerial_formula' => [
                'gross_revenue' => $this->fmt($grossRevenue),
                'outflows_settlements' => $this->fmt($settlementsPaid),
                'outflows_cash_expenses' => $this->fmt($manualExpense),
                'net_house_estimated' => $this->fmt($netHouseEstimated),
                'formula_text' => 'net_house_estimated = gross_revenue - outflows_settlements - outflows_cash_expenses',
            ],
        ];
    }

    private function buildWaiterRankings(array $sales, array $settlements, int $limit): array
    {
        $salesByWaiter = collect($sales['sales'] ?? [])->groupBy(fn (array $row): string => (string) ($row['waiter_user_id'] ?? 0));

        $salesRows = $salesByWaiter
            ->map(function ($rows): array {
                $first = $rows->first();
                $salesTotal = (float) $rows->sum(fn (array $row): float => (float) ($row['total'] ?? 0));
                $salesCount = (int) $rows->count();

                return [
                    'waiter_user_id' => (int) ($first['waiter_user_id'] ?? 0),
                    'waiter_name' => (string) ($first['waiter_name'] ?? 'Sin garzon'),
                    'sales_total' => $this->fmt($salesTotal),
                    'sales_count' => $salesCount,
                    'average_ticket' => $this->fmt($salesCount > 0 ? ($salesTotal / $salesCount) : 0),
                ];
            })
            ->values()
            ->sortByDesc(fn (array $row): float => $this->toFloat($row['sales_total']))
            ->take($limit)
            ->values()
            ->all();

        $paidWaiterSettlements = collect($settlements['settlements'] ?? [])->filter(
            fn (array $row): bool => ($row['settlement_type'] ?? null) === 'WAITER' && ($row['status'] ?? null) === 'PAID'
        );

        $compensationByWaiter = $paidWaiterSettlements
            ->groupBy(fn (array $row): string => (string) ($row['staff_user_id'] ?? 0))
            ->map(function ($rows): array {
                $first = $rows->first();
                $autoTotal = (float) $rows
                    ->where('compensation_mode', '!=', 'MANUAL')
                    ->sum(fn (array $row): float => $this->toFloat($row['net_amount'] ?? $row['total_amount'] ?? 0));
                $manualTotal = (float) $rows
                    ->where('compensation_mode', 'MANUAL')
                    ->sum(fn (array $row): float => $this->toFloat($row['manual_amount_input'] ?? $row['net_amount'] ?? $row['total_amount'] ?? 0));

                return [
                    'waiter_user_id' => (int) ($first['staff_user_id'] ?? 0),
                    'waiter_name' => (string) ($first['staff'] ?? 'Sin garzon'),
                    'compensation_total' => $this->fmt($autoTotal + $manualTotal),
                    'compensation_auto_total' => $this->fmt($autoTotal),
                    'compensation_manual_total' => $this->fmt($manualTotal),
                ];
            });

        // Ensure waiters with sales always appear, even with zero compensation.
        $mergedByWaiter = [];
        foreach ($salesByWaiter as $waiterId => $rows) {
            $first = $rows->first();
            $salesTotal = (float) $rows->sum(fn (array $row): float => (float) ($row['total'] ?? 0));

            $comp = $compensationByWaiter->get((string) $waiterId, [
                'waiter_user_id' => (int) ($first['waiter_user_id'] ?? 0),
                'waiter_name' => (string) ($first['waiter_name'] ?? 'Sin garzon'),
                'compensation_total' => '0.00',
                'compensation_auto_total' => '0.00',
                'compensation_manual_total' => '0.00',
            ]);

            $mergedByWaiter[] = array_merge($comp, [
                'sales_total' => $this->fmt($salesTotal),
            ]);
        }

        $compensationRows = collect($mergedByWaiter)
            ->sortByDesc(fn (array $row): float => $this->toFloat($row['compensation_total']))
            ->values()
            ->take($limit)
            ->all();

        return [
            'top_waiters_by_sales' => $salesRows,
            'top_waiters_by_compensation' => $compensationRows,
        ];
    }

    private function buildGirlRankings(array $services, array $settlements, int $limit): array
    {
        $girls = [];

        foreach ($services['room_services'] ?? [] as $row) {
            $name = (string) ($row['girl'] ?? 'Sin chica');
            if (! isset($girls[$name])) {
                $girls[$name] = ['girl_name' => $name, 'generated_income_total' => 0.0, 'settlement_paid_total' => 0.0];
            }
            $girls[$name]['generated_income_total'] += (float) ($row['girl_amount'] ?? 0);
        }

        foreach ($services['bracelets'] ?? [] as $row) {
            $name = (string) ($row['girl'] ?? 'Sin chica');
            if (! isset($girls[$name])) {
                $girls[$name] = ['girl_name' => $name, 'generated_income_total' => 0.0, 'settlement_paid_total' => 0.0];
            }
            $girls[$name]['generated_income_total'] += (float) ($row['total_amount'] ?? 0);
        }

        foreach ($services['shows'] ?? [] as $row) {
            $name = (string) ($row['girl'] ?? 'Sin chica');
            if (! isset($girls[$name])) {
                $girls[$name] = ['girl_name' => $name, 'generated_income_total' => 0.0, 'settlement_paid_total' => 0.0];
            }
            $girls[$name]['generated_income_total'] += (float) ($row['total_amount'] ?? 0);
        }

        foreach ($settlements['settlements'] ?? [] as $row) {
            if (($row['settlement_type'] ?? null) !== 'GIRL' || ($row['status'] ?? null) !== 'PAID') {
                continue;
            }

            $name = (string) ($row['staff'] ?? 'Sin chica');
            if (! isset($girls[$name])) {
                $girls[$name] = ['girl_name' => $name, 'generated_income_total' => 0.0, 'settlement_paid_total' => 0.0];
            }
            $girls[$name]['settlement_paid_total'] += $this->toFloat($row['net_amount'] ?? $row['total_amount'] ?? 0);
        }

        return [
            'top_girls_by_generated_income' => collect($girls)
                ->map(function (array $row): array {
                    return [
                        'girl_name' => $row['girl_name'],
                        'generated_income_total' => $this->fmt($row['generated_income_total']),
                        'settlement_paid_total' => $this->fmt($row['settlement_paid_total']),
                    ];
                })
                ->sortByDesc(fn (array $row): float => $this->toFloat($row['generated_income_total']))
                ->values()
                ->take($limit)
                ->all(),
        ];
    }

    private function buildProductRankings(array $products, int $limit): array
    {
        $sold = collect($products['sold'] ?? []);

        return [
            'top_products_by_revenue' => $sold
                ->sortByDesc(fn (array $row): float => $this->toFloat($row['total_amount'] ?? 0))
                ->take($limit)
                ->map(fn (array $row): array => [
                    'product_id' => (int) ($row['product_id'] ?? 0),
                    'product_name' => (string) ($row['product_name'] ?? 'Producto'),
                    'revenue_total' => $this->fmt($row['total_amount'] ?? 0),
                    'units' => (int) ($row['quantity_sold'] ?? 0),
                ])
                ->values()
                ->all(),
            'top_products_by_units' => $sold
                ->sortByDesc(fn (array $row): int => (int) ($row['quantity_sold'] ?? 0))
                ->take($limit)
                ->map(fn (array $row): array => [
                    'product_id' => (int) ($row['product_id'] ?? 0),
                    'product_name' => (string) ($row['product_name'] ?? 'Producto'),
                    'units' => (int) ($row['quantity_sold'] ?? 0),
                    'revenue_total' => $this->fmt($row['total_amount'] ?? 0),
                ])
                ->values()
                ->all(),
        ];
    }

    private function buildRoomPerformance(array $rooms, int $limit): array
    {
        $rows = collect($rooms['rooms'] ?? []);

        return [
            'top_rooms_by_revenue' => $rows
                ->sortByDesc(fn (array $row): float => $this->toFloat($row['total_income'] ?? 0))
                ->take($limit)
                ->map(fn (array $row): array => [
                    'room_id' => (int) ($row['id'] ?? 0),
                    'room_name' => (string) ($row['name'] ?? $row['code'] ?? 'Habitacion'),
                    'services_count' => (int) ($row['services_count'] ?? 0),
                    'total_income' => $this->fmt($row['total_income'] ?? 0),
                    'avg_duration' => (float) ($row['avg_duration'] ?? 0),
                ])
                ->values()
                ->all(),
            'rooms_summary' => [
                'rooms_count' => (int) ($rooms['totals']['rooms_count'] ?? 0),
                'rooms_used' => (int) ($rooms['totals']['rooms_used'] ?? 0),
                'total_income' => $this->fmt($rooms['totals']['total_income'] ?? 0),
                'total_services' => (int) ($rooms['totals']['total_services'] ?? 0),
                'rooms_in_cleaning' => (int) ($rooms['totals']['rooms_in_cleaning'] ?? 0),
            ],
        ];
    }

    private function buildCashHealth(array $cash): array
    {
        $sessions = collect($cash['sessions'] ?? [])->map(fn (array $row): array => [
            'id' => (int) ($row['id'] ?? 0),
            'status' => (string) ($row['status'] ?? 'UNKNOWN'),
            'opened_at' => $row['opened_at'] ?? null,
            'closed_at' => $row['closed_at'] ?? null,
            'expected_amount' => $this->fmt($row['expected_amount'] ?? 0),
            'declared_amount' => $this->fmt($row['declared_closing'] ?? 0),
            'difference_amount' => $this->fmt($row['difference'] ?? 0),
        ])->values();

        return [
            'sessions' => $sessions->all(),
            'differences' => $sessions
                ->filter(fn (array $row): bool => abs($this->toFloat($row['difference_amount'])) > 0.01)
                ->values()
                ->all(),
            'totals' => [
                'expected_total' => $this->fmt($sessions->sum(fn (array $row): float => $this->toFloat($row['expected_amount']))),
                'declared_total' => $this->fmt($sessions->sum(fn (array $row): float => $this->toFloat($row['declared_amount']))),
                'difference_total' => $this->fmt($sessions->sum(fn (array $row): float => $this->toFloat($row['difference_amount']))),
            ],
        ];
    }

    /**
     * @param list<int> $shiftIds
     */
    private function buildAlerts(int $tenantId, int $branchId, array $shiftIds): array
    {
        $blockers = [];
        $warnings = [];
        $pendingSummary = [
            'open_cash_sessions' => 0,
            'active_room_services' => 0,
            'active_orders' => 0,
            'pending_settlements' => 0,
            'unsettled_sources' => 0,
            'rooms_in_cleaning' => 0,
            'cash_difference' => 0.0,
        ];

        foreach ($shiftIds as $shiftId) {
            $check = $this->reports->getShiftClosureCheck($tenantId, $branchId, (int) $shiftId);

            foreach ($check['blockers'] ?? [] as $blocker) {
                $code = (string) ($blocker['code'] ?? 'unknown_blocker');
                if (! isset($blockers[$code])) {
                    $blockers[$code] = ['code' => $code, 'message' => (string) ($blocker['message'] ?? ''), 'count' => 0];
                }
                $blockers[$code]['count'] += (int) ($blocker['count'] ?? 0);
            }

            foreach ($check['warnings'] ?? [] as $warning) {
                $code = (string) ($warning['code'] ?? 'unknown_warning');
                if (! isset($warnings[$code])) {
                    $warnings[$code] = ['code' => $code, 'message' => (string) ($warning['message'] ?? ''), 'count' => 0];
                }
                $warnings[$code]['count'] += (int) ($warning['count'] ?? 0);
            }

            $summary = $check['summary'] ?? [];
            $pendingSummary['open_cash_sessions'] += (int) ($summary['open_cash_sessions'] ?? 0);
            $pendingSummary['active_room_services'] += (int) ($summary['active_room_services'] ?? 0);
            $pendingSummary['active_orders'] += (int) ($summary['active_orders'] ?? 0);
            $pendingSummary['pending_settlements'] += (int) ($summary['pending_settlements'] ?? 0);
            $pendingSummary['unsettled_sources'] += (int) ($summary['unsettled_sources'] ?? 0);
            $pendingSummary['rooms_in_cleaning'] += (int) ($summary['rooms_in_cleaning'] ?? 0);
            $pendingSummary['cash_difference'] += (float) ($summary['cash_difference'] ?? 0);
        }

        return [
            'blockers' => array_values($blockers),
            'warnings' => array_values($warnings),
            'pending_summary' => [
                'open_cash_sessions' => $pendingSummary['open_cash_sessions'],
                'active_room_services' => $pendingSummary['active_room_services'],
                'active_orders' => $pendingSummary['active_orders'],
                'pending_settlements' => $pendingSummary['pending_settlements'],
                'unsettled_sources' => $pendingSummary['unsettled_sources'],
                'rooms_in_cleaning' => $pendingSummary['rooms_in_cleaning'],
                'cash_difference' => $this->fmt($pendingSummary['cash_difference']),
            ],
        ];
    }

    private function toFloat(mixed $value): float
    {
        return round((float) $value, 2);
    }

    private function fmt(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
