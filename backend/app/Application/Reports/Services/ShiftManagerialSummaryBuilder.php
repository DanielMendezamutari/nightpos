<?php

declare(strict_types=1);

namespace App\Application\Reports\Services;

use App\Domain\Reports\Repositories\ReportReadRepositoryInterface;
use App\Domain\Shift\Entities\ShiftClosure;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\PrintJobModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\ShiftClosureModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementAdjustmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Domain\Enums\SettlementAdjustmentType;
use Illuminate\Support\Facades\DB;

/**
 * Resumen gerencial del cierre de turno — ensambla datos persistidos vía repositorio de reportes.
 */
final class ShiftManagerialSummaryBuilder
{
    public function __construct(
        private readonly ReportReadRepositoryInterface $reports,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function forShift(
        int $tenantId,
        int $branchId,
        int $officialShiftId,
        ?ShiftClosure $closure = null,
    ): array {
        $filters = ['official_shift_id' => $officialShiftId];

        $salesReport = $this->reports->getSalesReport($tenantId, $branchId, $filters);
        $settlementsReport = $this->reports->getSettlementsReport($tenantId, $branchId, $filters);
        $servicesReport = $this->reports->getServicesReport($tenantId, $branchId, $filters);
        $recon = $this->reports->getProductReconciliation($tenantId, $branchId, $filters);

        $closureModel = ShiftClosureModel::query()
            ->where('official_shift_id', $officialShiftId)
            ->where('tenant_id', $tenantId)
            ->first();

        $totalSales = (float) ($salesReport['totals']['total'] ?? 0);
        $salesCount = (int) ($salesReport['totals']['count'] ?? 0);
        $averageTicket = $salesCount > 0
            ? number_format($totalSales / $salesCount, 2, '.', '')
            : '0.00';

        $paymentStats = $this->paymentStatsFromShift(
            $officialShiftId,
            collect($salesReport['totals']['by_method'] ?? [])->all(),
            $totalSales,
        );

        $waiterPayouts = (float) ($closureModel?->total_waiter_payouts ?? $closure?->totalWaiterPayouts ?? 0);
        $girlPayouts = (float) ($closureModel?->total_girl_payouts ?? $closure?->totalGirlPayouts ?? 0);
        $cleaningPayouts = (float) ($closureModel?->total_cleaning_payouts ?? 0);
        $cashExpenses = (float) ($closureModel?->total_manual_expense ?? $closure?->totalManualExpense ?? 0);

        $totalOutflows = $waiterPayouts + $girlPayouts + $cleaningPayouts + $cashExpenses;
        $netSales = max(0, $totalSales - $totalOutflows);

        $settlementsPaid = $this->groupPaidSettlements(
            StaffSettlementModel::query()
                ->with('staffUser')
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->where('official_shift_id', $officialShiftId)
                ->where('status', 'PAID')
                ->orderBy('settlement_type')
                ->orderBy('staff_user_id')
                ->get(),
        );

        $settlementIds = StaffSettlementModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->where('status', 'PAID')
            ->pluck('id')
            ->all();

        $adjustments = $this->summarizeAdjustments($settlementIds);

        $sold = $recon['sold'] ?? [];
        usort(
            $sold,
            static fn (array $a, array $b): int => (float) ($b['total_amount'] ?? 0) <=> (float) ($a['total_amount'] ?? 0)
                ?: (int) ($b['quantity_sold'] ?? 0) <=> (int) ($a['quantity_sold'] ?? 0),
        );

        $topProducts = array_map(
            static fn (array $row): array => [
                'product_name' => (string) ($row['product_name'] ?? 'Producto'),
                'quantity_sold' => (int) ($row['quantity_sold'] ?? 0),
                'total_amount' => (string) ($row['total_amount'] ?? '0.00'),
            ],
            array_slice($sold, 0, 20),
        );

        $categories = $this->categoryBreakdown($tenantId, $branchId, $officialShiftId, $servicesReport['totals'] ?? []);

        $waiters = $this->waiterRanking($tenantId, $branchId, $officialShiftId, $settlementsReport['settlements'] ?? []);
        $girls = $this->girlRanking($tenantId, $branchId, $officialShiftId, $settlementsReport['settlements'] ?? []);

        $sessions = CashSessionModel::query()
            ->with(['opener', 'closer'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->get();

        $cashierNames = $sessions
            ->flatMap(fn ($s) => [$s->opener?->name, $s->closer?->name])
            ->filter()
            ->unique()
            ->values()
            ->all();

        $orders = $this->orderStats($tenantId, $branchId, $officialShiftId);
        $incidents = $this->incidents($tenantId, $branchId, $officialShiftId, $sessions);

        $servicesTotals = $servicesReport['totals'] ?? [];
        $roomServices = $servicesReport['room_services'] ?? [];
        $shows = $servicesReport['shows'] ?? [];

        $topRoom = collect($roomServices)
            ->groupBy(fn ($r) => (string) ($r['room'] ?? '—'))
            ->map(fn ($grp) => $grp->count())
            ->sortDesc()
            ->keys()
            ->first();

        $kpis = [
            'top_waiter' => $waiters[0] ?? null,
            'top_girl' => $girls[0] ?? null,
            'top_product' => $topProducts[0] ?? null,
            'top_room' => $topRoom !== null ? [
                'name' => $topRoom,
                'uses' => (int) collect($roomServices)->where('room', $topRoom)->count(),
            ] : null,
        ];

        return [
            'general' => [
                'closed_cash_sessions' => $sessions->where('status', 'CLOSED')->count(),
                'cashiers' => $cashierNames,
            ],
            'sales' => [
                'total' => number_format($totalSales, 2, '.', ''),
                'count' => $salesCount,
                'average_ticket' => $averageTicket,
            ],
            'payment_stats' => $paymentStats,
            'financial_result' => [
                'sales' => number_format($totalSales, 2, '.', ''),
                'paid_waiters' => number_format($waiterPayouts, 2, '.', ''),
                'paid_girls' => number_format($girlPayouts, 2, '.', ''),
                'paid_cleaning' => number_format($cleaningPayouts, 2, '.', ''),
                'cash_expenses' => number_format($cashExpenses, 2, '.', ''),
                'total_outflows' => number_format($totalOutflows, 2, '.', ''),
                'net_sales' => number_format($netSales, 2, '.', ''),
            ],
            'settlements_paid' => $settlementsPaid,
            'settlement_adjustments' => $adjustments,
            'top_products' => $topProducts,
            'categories' => $categories,
            'waiters' => $waiters,
            'girls' => $girls,
            'room_services' => [
                'count' => count($roomServices),
                'total' => (string) ($servicesTotals['room_services_total'] ?? '0.00'),
            ],
            'shows' => [
                'count' => count($shows),
                'total' => (string) ($servicesTotals['shows_total'] ?? '0.00'),
            ],
            'orders' => $orders,
            'incidents' => $incidents,
            'kpis' => $kpis,
            'settlement_totals' => $settlementsReport['totals'] ?? [],
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, StaffSettlementModel>  $paidSettlements
     * @return array<string, mixed>
     */
    private function groupPaidSettlements($paidSettlements): array
    {
        $groups = [
            'WAITER' => ['label' => 'Garzones', 'count' => 0, 'total' => 0.0, 'people' => []],
            'GIRL' => ['label' => 'Chicas', 'count' => 0, 'total' => 0.0, 'people' => []],
            'CLEANING' => ['label' => 'Limpieza', 'count' => 0, 'total' => 0.0, 'people' => []],
        ];

        foreach ($paidSettlements as $settlement) {
            $type = (string) $settlement->settlement_type;
            if (! isset($groups[$type])) {
                continue;
            }

            $amount = (float) ($settlement->net_amount ?? $settlement->total_amount);
            $groups[$type]['count']++;
            $groups[$type]['total'] += $amount;
            $groups[$type]['people'][] = [
                'name' => $settlement->staffUser?->name ?? '—',
                'amount' => number_format($amount, 2, '.', ''),
            ];
        }

        $grandTotal = 0.0;
        $result = [];

        foreach ($groups as $key => $group) {
            $result[$key] = [
                'label' => $group['label'],
                'count' => $group['count'],
                'total' => number_format($group['total'], 2, '.', ''),
                'people' => $group['people'],
            ];
            $grandTotal += $group['total'];
        }

        $result['grand_total'] = number_format($grandTotal, 2, '.', '');

        return $result;
    }

    /**
     * @param  list<int>  $settlementIds
     * @return array<string, mixed>
     */
    private function summarizeAdjustments(array $settlementIds): array
    {
        if ($settlementIds === []) {
            return [
                'fines' => ['count' => 0, 'amount' => '0.00'],
                'cleaning' => ['count' => 0, 'amount' => '0.00'],
                'manual_discount' => ['count' => 0, 'amount' => '0.00'],
                'total_discounted' => '0.00',
            ];
        }

        $rows = StaffSettlementAdjustmentModel::query()
            ->whereIn('staff_settlement_id', $settlementIds)
            ->get();

        $fines = $rows->where('adjustment_type', SettlementAdjustmentType::ManualFine->value);
        $cleaning = $rows->where('adjustment_type', SettlementAdjustmentType::CleaningDeduction->value);
        $discount = $rows->where('adjustment_type', SettlementAdjustmentType::ManualDiscount->value);

        $totalDiscounted = (float) $fines->sum('amount')
            + (float) $cleaning->sum('amount')
            + (float) $discount->sum('amount');

        return [
            'fines' => [
                'count' => $fines->count(),
                'amount' => number_format((float) $fines->sum('amount'), 2, '.', ''),
            ],
            'cleaning' => [
                'count' => $cleaning->count(),
                'amount' => number_format((float) $cleaning->sum('amount'), 2, '.', ''),
            ],
            'manual_discount' => [
                'count' => $discount->count(),
                'amount' => number_format((float) $discount->sum('amount'), 2, '.', ''),
            ],
            'total_discounted' => number_format($totalDiscounted, 2, '.', ''),
        ];
    }

    /**
     * @return array<string, array{count: int, amount: string, percent: string}>
     */
    private function paymentStatsFromShift(int $officialShiftId, array $byMethod, float $totalSales): array
    {
        $methods = [
            'CASH' => ['count' => 0, 'amount' => 0.0],
            'QR' => ['count' => 0, 'amount' => 0.0],
            'CARD' => ['count' => 0, 'amount' => 0.0],
            'MIXED' => ['count' => 0, 'amount' => 0.0],
        ];

        $sales = SaleModel::query()
            ->where('official_shift_id', $officialShiftId)
            ->get(['payment_mode', 'total']);

        foreach ($sales as $sale) {
            $mode = strtoupper((string) ($sale->payment_mode ?? 'CASH'));
            $key = $mode === 'MIXED' ? 'MIXED' : match ($mode) {
                'QR' => 'QR',
                'CARD' => 'CARD',
                default => 'CASH',
            };
            $methods[$key]['count']++;
            $methods[$key]['amount'] += (float) $sale->total;
        }

        $result = [];
        foreach ($methods as $key => $row) {
            $amount = $row['amount'] > 0 ? $row['amount'] : (float) ($byMethod[$key] ?? $byMethod[strtolower($key)] ?? 0);
            $percent = $totalSales > 0 ? ($amount / $totalSales) * 100 : 0;
            $result[$key] = [
                'count' => $row['count'],
                'amount' => number_format($amount, 2, '.', ''),
                'percent' => number_format($percent, 1, '.', ''),
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, string>  $servicesTotals
     * @return array<string, string>
     */
    private function categoryBreakdown(int $tenantId, int $branchId, int $officialShiftId, array $servicesTotals): array
    {
        $productRows = SaleItemModel::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->where('sales.tenant_id', $tenantId)
            ->where('sales.branch_id', $branchId)
            ->where('sales.official_shift_id', $officialShiftId)
            ->select(
                DB::raw("COALESCE(product_categories.name, 'Otros') as category_name"),
                DB::raw('SUM(sale_items.line_total) as total'),
            )
            ->groupBy('category_name')
            ->pluck('total', 'category_name');

        $categories = [];
        foreach ($productRows as $name => $total) {
            $categories[(string) $name] = number_format((float) $total, 2, '.', '');
        }

        if (isset($servicesTotals['shows_total']) && (float) $servicesTotals['shows_total'] > 0) {
            $categories['Shows'] = (string) $servicesTotals['shows_total'];
        }

        if (isset($servicesTotals['room_services_total']) && (float) $servicesTotals['room_services_total'] > 0) {
            $categories['Piezas / habitaciones'] = (string) $servicesTotals['room_services_total'];
        }

        if (isset($servicesTotals['bracelets_total']) && (float) $servicesTotals['bracelets_total'] > 0) {
            $categories['Servicios / manillas'] = (string) $servicesTotals['bracelets_total'];
        }

        return $categories;
    }

    /**
     * @param  list<array<string, mixed>>  $settlementRows
     * @return list<array{name: string, sales: string, commission: string, settlement: string}>
     */
    private function waiterRanking(int $tenantId, int $branchId, int $officialShiftId, array $settlementRows): array
    {
        $salesByWaiter = SaleModel::query()
            ->join('users', 'users.id', '=', 'sales.waiter_user_id')
            ->where('sales.tenant_id', $tenantId)
            ->where('sales.branch_id', $branchId)
            ->where('sales.official_shift_id', $officialShiftId)
            ->whereNotNull('sales.waiter_user_id')
            ->select('users.name', DB::raw('SUM(sales.total) as sales_total'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('sales_total')
            ->get()
            ->keyBy('name');

        $paidByStaff = collect($settlementRows)
            ->where('settlement_type', 'WAITER')
            ->where('status', 'PAID')
            ->groupBy('staff')
            ->map(fn ($grp) => (float) $grp->sum('total_amount'));

        $names = $salesByWaiter->keys()->merge($paidByStaff->keys())->unique();

        $rows = [];
        foreach ($names as $name) {
            $salesTotal = (float) ($salesByWaiter->get($name)?->sales_total ?? 0);
            $settlement = (float) ($paidByStaff->get($name) ?? 0);
            $rows[] = [
                'name' => (string) $name,
                'sales' => number_format($salesTotal, 2, '.', ''),
                'commission' => number_format($settlement, 2, '.', ''),
                'settlement' => number_format($settlement, 2, '.', ''),
            ];
        }

        usort($rows, static fn (array $a, array $b): int => (float) $b['sales'] <=> (float) $a['sales']);

        return $rows;
    }

    /**
     * @param  list<array<string, mixed>>  $settlementRows
     * @return list<array{name: string, income: string, settlement: string}>
     */
    private function girlRanking(int $tenantId, int $branchId, int $officialShiftId, array $settlementRows): array
    {
        $paidByStaff = collect($settlementRows)
            ->where('settlement_type', 'GIRL')
            ->where('status', 'PAID')
            ->groupBy('staff')
            ->map(fn ($grp) => (float) $grp->sum('total_amount'));

        $rows = [];
        foreach ($paidByStaff as $name => $amount) {
            $rows[] = [
                'name' => (string) $name,
                'income' => number_format($amount, 2, '.', ''),
                'settlement' => number_format($amount, 2, '.', ''),
            ];
        }

        usort($rows, static fn (array $a, array $b): int => (float) $b['settlement'] <=> (float) $a['settlement']);

        return $rows;
    }

    /**
     * @return array<string, int>
     */
    private function orderStats(int $tenantId, int $branchId, int $officialShiftId): array
    {
        $base = OrderModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId);

        return [
            'created' => (clone $base)->count(),
            'sent_to_bar' => (clone $base)->whereIn('status', ['SENT_TO_BAR', 'IN_PREPARATION', 'READY'])->count(),
            'billed' => (clone $base)->where('status', 'BILLED')->count(),
            'cancelled' => (clone $base)->where('status', 'CANCELLED')->count(),
            'corrected' => (clone $base)->where('bar_correction_count', '>', 0)->count(),
            'pending' => (clone $base)->whereIn('status', ['OPEN', 'SENT_TO_BAR', 'IN_PREPARATION', 'READY', 'PENDING_CHARGE'])->count(),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, CashSessionModel>  $sessions
     * @return array<string, int>
     */
    private function incidents(int $tenantId, int $branchId, int $officialShiftId, $sessions): array
    {
        $sessionIds = $sessions->pluck('id')->all();

        $forcedCloses = $sessions->where('is_forced_close', true)->count();

        $corrections = OrderModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->where('bar_correction_count', '>', 0)
            ->count();

        $reprints = PrintJobModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where(function ($q) use ($officialShiftId, $sessionIds) {
                $q->where(function ($inner) use ($sessionIds) {
                    $inner->where('source_type', 'cash_session')
                        ->whereIn('source_id', $sessionIds !== [] ? $sessionIds : [0]);
                })->orWhere(function ($inner) use ($officialShiftId) {
                    $inner->where('source_type', 'shift')
                        ->where('source_id', $officialShiftId);
                });
            })
            ->where('idempotency_key', 'like', '%reprint%')
            ->count();

        $printErrors = PrintJobModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'FAILED')
            ->when($sessions->isNotEmpty(), fn ($q) => $q->where('created_at', '>=', $sessions->min('opened_at')))
            ->count();

        return [
            'force_close' => $forcedCloses,
            'corrections' => $corrections,
            'reprints' => $reprints,
            'print_errors' => $printErrors,
            'cancellations' => 0,
        ];
    }
}
