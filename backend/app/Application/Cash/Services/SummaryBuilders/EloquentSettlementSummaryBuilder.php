<?php

declare(strict_types=1);

namespace App\Application\Cash\Services\SummaryBuilders;

use App\Application\Cash\DTOs\SettlementSummaryDTO;
use App\Application\StaffSettlement\Services\CashSessionPersonnelLedgerService;
use App\Domain\Cash\Contracts\SettlementSummaryRepositoryInterface;
use App\Domain\Cash\Contracts\SummaryBuilders\SettlementSummaryBuilder;
use App\Shared\Domain\ValueObjects\Money;
use InvalidArgumentException;

final readonly class EloquentSettlementSummaryBuilder implements SettlementSummaryBuilder
{
    private const ROLES = ['GIRL', 'WAITER', 'CLEANING'];

    private const ADJUSTMENT_GROUPS = ['cleaning_deduction', 'manual_fines', 'manual_discounts', 'other_adjustments'];

    public function __construct(
        private SettlementSummaryRepositoryInterface $settlementRepository,
        private CashSessionPersonnelLedgerService $personnelLedger,
    ) {
    }

    public function build(
        int $tenantId,
        int $branchId,
        ?int $cashSessionId = null,
        ?int $officialShiftId = null,
    ): SettlementSummaryDTO
    {
        if ($cashSessionId !== null && $officialShiftId !== null) {
            throw new InvalidArgumentException('cash_session_id and official_shift_id cannot be combined in settlement summary scope.');
        }

        $breakdown = $this->settlementRepository->getSettlementBreakdown(
            tenantId: $tenantId,
            branchId: $branchId,
            cashSessionId: $cashSessionId,
            officialShiftId: $officialShiftId,
        );

        $roleMetrics = $this->initializeRoleMetrics();
        $totals = $this->initializeTotals();

        foreach (($breakdown['settlements'] ?? []) as $row) {
            $role = strtoupper((string) ($row['settlement_type'] ?? ''));
            $status = strtoupper((string) ($row['status'] ?? ''));

            if (! isset($roleMetrics[$role])) {
                continue;
            }

            if (! in_array($status, ['PENDING', 'PAID'], true)) {
                continue;
            }

            $statusKey = strtolower($status);
            $count = (int) ($row['cnt'] ?? 0);
            $gross = new Money((string) ($row['gross_total'] ?? '0.00'));
            $adjustments = new Money((string) ($row['adjustments_total'] ?? '0.00'));
            $net = new Money((string) ($row['net_total'] ?? '0.00'));

            $roleMetrics[$role][$statusKey]['count'] += $count;
            $roleMetrics[$role][$statusKey]['gross'] = $roleMetrics[$role][$statusKey]['gross']->add($gross);
            $roleMetrics[$role][$statusKey]['adjustments'] = $roleMetrics[$role][$statusKey]['adjustments']->add($adjustments);
            $roleMetrics[$role][$statusKey]['net'] = $roleMetrics[$role][$statusKey]['net']->add($net);

            $totals[$statusKey]['count'] += $count;
            $totals[$statusKey]['gross'] = $totals[$statusKey]['gross']->add($gross);
            $totals[$statusKey]['adjustments'] = $totals[$statusKey]['adjustments']->add($adjustments);
            $totals[$statusKey]['net'] = $totals[$statusKey]['net']->add($net);
        }

        $adjustmentsBreakdown = $this->buildAdjustmentsSummary($breakdown['adjustments'] ?? []);
        $manualCompensation = $this->buildManualCompensationSummary($breakdown['manual_compensation'] ?? []);
        $personnel = $cashSessionId !== null
            ? $this->personnelLedger->build($tenantId, $branchId, $cashSessionId)
            : ['girls' => [], 'waiters' => [], 'cleaning' => [], 'totals' => []];

        return new SettlementSummaryDTO(
            waiters: array_merge($this->flattenRoleMetrics($roleMetrics['WAITER']), [
                'provisional_sales_amount' => (string) ($personnel['totals']['waiters_provisional_sales_total'] ?? '0.00'),
                'confirmed_sales_amount' => (string) ($personnel['totals']['waiters_confirmed_sales_total'] ?? '0.00'),
                'rows' => $personnel['waiters'] ?? [],
            ]),
            girls: array_merge($this->flattenRoleMetrics($roleMetrics['GIRL']), [
                'provisional_pending_amount' => (string) ($personnel['totals']['girls_provisional_total'] ?? '0.00'),
                'rows' => $personnel['girls'] ?? [],
            ]),
            cleaning: array_merge($this->flattenRoleMetrics($roleMetrics['CLEANING']), [
                'provisional_pending_amount' => (string) ($personnel['totals']['cleaning_provisional_total'] ?? '0.00'),
                'rows' => $personnel['cleaning'] ?? [],
            ]),
            totals: [
                'pending_total_count' => $totals['pending']['count'],
                'pending_total_gross' => $totals['pending']['gross']->amount,
                'pending_total_adjustments' => $totals['pending']['adjustments']->amount,
                'pending_total_net' => $totals['pending']['net']->amount,
                'paid_total_count' => $totals['paid']['count'],
                'paid_total_gross' => $totals['paid']['gross']->amount,
                'paid_total_adjustments' => $totals['paid']['adjustments']->amount,
                'paid_total_net' => $totals['paid']['net']->amount,
                'girls_provisional_total' => (string) ($personnel['totals']['girls_provisional_total'] ?? '0.00'),
                'waiters_provisional_sales_total' => (string) ($personnel['totals']['waiters_provisional_sales_total'] ?? '0.00'),
                'cleaning_provisional_total' => (string) ($personnel['totals']['cleaning_provisional_total'] ?? '0.00'),
                'personnel_rows' => [
                    'waiters' => $personnel['waiters'] ?? [],
                    'girls' => $personnel['girls'] ?? [],
                    'cleaning' => $personnel['cleaning'] ?? [],
                ],
            ],
            adjustments_breakdown: $adjustmentsBreakdown,
            manual_compensation: $manualCompensation,
        );
    }

    /**
     * @return array<string, array{pending: array{count: int, gross: Money, adjustments: Money, net: Money}, paid: array{count: int, gross: Money, adjustments: Money, net: Money}}>
     */
    private function initializeRoleMetrics(): array
    {
        $indexed = [];

        foreach (self::ROLES as $role) {
            $indexed[$role] = [
                'pending' => [
                    'count' => 0,
                    'gross' => new Money('0.00'),
                    'adjustments' => new Money('0.00'),
                    'net' => new Money('0.00'),
                ],
                'paid' => [
                    'count' => 0,
                    'gross' => new Money('0.00'),
                    'adjustments' => new Money('0.00'),
                    'net' => new Money('0.00'),
                ],
            ];
        }

        return $indexed;
    }

    /**
     * @return array{pending: array{count: int, gross: Money, adjustments: Money, net: Money}, paid: array{count: int, gross: Money, adjustments: Money, net: Money}}
     */
    private function initializeTotals(): array
    {
        return [
            'pending' => [
                'count' => 0,
                'gross' => new Money('0.00'),
                'adjustments' => new Money('0.00'),
                'net' => new Money('0.00'),
            ],
            'paid' => [
                'count' => 0,
                'gross' => new Money('0.00'),
                'adjustments' => new Money('0.00'),
                'net' => new Money('0.00'),
            ],
        ];
    }

    /**
     * @param  list<array{adjustment_type: string, cnt: int, total: string}>  $rows
     * @return array{cleaning_deduction: string, manual_fines: string, manual_discounts: string, other_adjustments: string}
     */
    private function buildAdjustmentsSummary(array $rows): array
    {
        $summary = [];
        foreach (self::ADJUSTMENT_GROUPS as $group) {
            $summary[$group] = new Money('0.00');
        }

        foreach ($rows as $row) {
            $group = $this->mapAdjustmentGroup((string) ($row['adjustment_type'] ?? ''));
            $summary[$group] = $summary[$group]->add(new Money((string) ($row['total'] ?? '0.00')));
        }

        return [
            'cleaning_deduction' => $summary['cleaning_deduction']->amount,
            'manual_fines' => $summary['manual_fines']->amount,
            'manual_discounts' => $summary['manual_discounts']->amount,
            'other_adjustments' => $summary['other_adjustments']->amount,
        ];
    }

    /**
     * @param  list<array{status: string, cnt: int, total: string}>  $rows
     * @return array{waiter_manual_pending_count: int, waiter_manual_pending_amount: string, waiter_manual_paid_count: int, waiter_manual_paid_amount: string}
     */
    private function buildManualCompensationSummary(array $rows): array
    {
        $pendingCount = 0;
        $paidCount = 0;
        $pendingAmount = new Money('0.00');
        $paidAmount = new Money('0.00');

        foreach ($rows as $row) {
            $status = strtoupper((string) ($row['status'] ?? ''));
            $count = (int) ($row['cnt'] ?? 0);
            $amount = new Money((string) ($row['total'] ?? '0.00'));

            if ($status === 'PENDING') {
                $pendingCount += $count;
                $pendingAmount = $pendingAmount->add($amount);

                continue;
            }

            if ($status === 'PAID') {
                $paidCount += $count;
                $paidAmount = $paidAmount->add($amount);
            }
        }

        return [
            'waiter_manual_pending_count' => $pendingCount,
            'waiter_manual_pending_amount' => $pendingAmount->amount,
            'waiter_manual_paid_count' => $paidCount,
            'waiter_manual_paid_amount' => $paidAmount->amount,
        ];
    }

    /**
     * @param  array{pending: array{count: int, gross: Money, adjustments: Money, net: Money}, paid: array{count: int, gross: Money, adjustments: Money, net: Money}}  $metrics
     * @return array{pending_count: int, pending_gross_amount: string, pending_adjustments_amount: string, pending_net_amount: string, paid_count: int, paid_gross_amount: string, paid_adjustments_amount: string, paid_net_amount: string}
     */
    private function flattenRoleMetrics(array $metrics): array
    {
        return [
            'pending_count' => $metrics['pending']['count'],
            'pending_gross_amount' => $metrics['pending']['gross']->amount,
            'pending_adjustments_amount' => $metrics['pending']['adjustments']->amount,
            'pending_net_amount' => $metrics['pending']['net']->amount,
            'paid_count' => $metrics['paid']['count'],
            'paid_gross_amount' => $metrics['paid']['gross']->amount,
            'paid_adjustments_amount' => $metrics['paid']['adjustments']->amount,
            'paid_net_amount' => $metrics['paid']['net']->amount,
        ];
    }

    private function mapAdjustmentGroup(string $adjustmentType): string
    {
        return match (strtoupper($adjustmentType)) {
            'CLEANING_DEDUCTION' => 'cleaning_deduction',
            'STAFF_FINE', 'FINE', 'MANUAL_FINE' => 'manual_fines',
            'MANUAL_DISCOUNT', 'DISCOUNT' => 'manual_discounts',
            default => 'other_adjustments',
        };
    }
}
