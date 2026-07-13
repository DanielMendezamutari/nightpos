<?php

declare(strict_types=1);

namespace App\Application\Cash\Services\SummaryBuilders;

use App\Application\Cash\DTOs\MovementSummaryDTO;
use App\Domain\Cash\Contracts\CashMovementRepositoryInterface;
use App\Domain\Cash\Contracts\SummaryBuilders\MovementSummaryBuilder;
use App\Domain\Cash\ValueObjects\CashSessionId;
use App\Shared\Domain\ValueObjects\Money;

final readonly class EloquentMovementSummaryBuilder implements MovementSummaryBuilder
{
    private const FAMILIES = ['SALE', 'MANUAL', 'SETTLEMENT', 'EXPENSE', 'ADJUSTMENT'];

    private const CATEGORIES = [
        'SALE_COLLECTION',
        'DIRECT_SALE_COLLECTION',
        'BRACELET_COLLECTION',
        'ROOM_SERVICE_COLLECTION',
        'SHOW_COLLECTION',
        'MANUAL_INCOME',
        'SETTLEMENT_GIRL_PAYMENT',
        'SETTLEMENT_WAITER_PAYMENT',
        'SETTLEMENT_CLEANING_PAYMENT',
        'OPERATING_EXPENSE',
        'PURCHASE',
        'OTHER_INCOME',
        'OTHER_EXPENSE',
    ];

    private const PAYMENT_METHODS = ['cash', 'qr', 'card'];

    public function __construct(
        private CashMovementRepositoryInterface $cashMovementRepository,
    ) {
    }

    public function build(CashSessionId $cashSessionId): MovementSummaryDTO
    {
        $breakdown = $this->cashMovementRepository->getMovementsBreakdown($cashSessionId);
        $rows = $breakdown['rows'] ?? [];
        $timelineRows = $breakdown['timeline_rows'] ?? [];

        $totalMovements = 0;
        $totalIncome = new Money('0.00');
        $totalExpense = new Money('0.00');

        $incomeByFamily = [];
        $expenseByFamily = [];
        foreach (self::FAMILIES as $family) {
            $incomeByFamily[$family] = new Money('0.00');
            $expenseByFamily[$family] = new Money('0.00');
        }

        $incomeByCategory = [];
        $expenseByCategory = [];
        $movementCountByCategory = [];
        foreach (self::CATEGORIES as $category) {
            $incomeByCategory[$category] = new Money('0.00');
            $expenseByCategory[$category] = new Money('0.00');
            $movementCountByCategory[$category] = 0;
        }

        $incomeByPaymentMethod = [];
        $expenseByPaymentMethod = [];
        foreach (self::PAYMENT_METHODS as $method) {
            $incomeByPaymentMethod[$method] = new Money('0.00');
            $expenseByPaymentMethod[$method] = new Money('0.00');
        }

        foreach ($rows as $row) {
            $type = strtoupper((string) ($row['movement_type'] ?? ''));
            $family = strtoupper((string) ($row['movement_family'] ?? ''));
            $category = strtoupper((string) ($row['movement_category'] ?? ''));
            $method = strtolower((string) ($row['payment_method'] ?? ''));
            $count = (int) ($row['cnt'] ?? 0);
            $amount = new Money((string) ($row['total'] ?? '0'));

            $isIncome = $type === 'INCOME';
            $totalMovements += $count;

            if ($isIncome) {
                $totalIncome = $totalIncome->add($amount);
            } else {
                $totalExpense = $totalExpense->add($amount);
            }

            if (isset($incomeByFamily[$family])) {
                if ($isIncome) {
                    $incomeByFamily[$family] = $incomeByFamily[$family]->add($amount);
                } else {
                    $expenseByFamily[$family] = $expenseByFamily[$family]->add($amount);
                }
            }

            if (isset($incomeByCategory[$category])) {
                if ($isIncome) {
                    $incomeByCategory[$category] = $incomeByCategory[$category]->add($amount);
                } else {
                    $expenseByCategory[$category] = $expenseByCategory[$category]->add($amount);
                }

                $movementCountByCategory[$category] += $count;
            }

            if (isset($incomeByPaymentMethod[$method])) {
                if ($isIncome) {
                    $incomeByPaymentMethod[$method] = $incomeByPaymentMethod[$method]->add($amount);
                } else {
                    $expenseByPaymentMethod[$method] = $expenseByPaymentMethod[$method]->add($amount);
                }
            }
        }

        /** @var array<string, array{income: Money, expense: Money, count: int}> $timelineByHour */
        $timelineByHour = [];
        foreach ($timelineRows as $row) {
            $bucket = (string) ($row['hour_bucket'] ?? '');
            if ($bucket === '') {
                continue;
            }

            if (! isset($timelineByHour[$bucket])) {
                $timelineByHour[$bucket] = [
                    'income' => new Money('0.00'),
                    'expense' => new Money('0.00'),
                    'count' => 0,
                ];
            }

            $movementType = strtoupper((string) ($row['movement_type'] ?? ''));
            $amount = new Money((string) ($row['total'] ?? '0'));
            $count = (int) ($row['cnt'] ?? 0);

            if ($movementType === 'INCOME') {
                $timelineByHour[$bucket]['income'] = $timelineByHour[$bucket]['income']->add($amount);
            } else {
                $timelineByHour[$bucket]['expense'] = $timelineByHour[$bucket]['expense']->add($amount);
            }

            $timelineByHour[$bucket]['count'] += $count;
        }

        ksort($timelineByHour);

        return new MovementSummaryDTO(
            total_movements: $totalMovements,
            total_income: $totalIncome,
            total_expense: $totalExpense,
            income_by_family: $this->flattenMoneyMap($incomeByFamily),
            expense_by_family: $this->flattenMoneyMap($expenseByFamily),
            income_by_category: $this->flattenMoneyMap($incomeByCategory),
            expense_by_category: $this->flattenMoneyMap($expenseByCategory),
            income_by_payment_method: $this->flattenMoneyMap($incomeByPaymentMethod),
            expense_by_payment_method: $this->flattenMoneyMap($expenseByPaymentMethod),
            timeline_by_hour: $this->flattenTimelineMap($timelineByHour),
            largest_income_category: $this->resolveLargestCategory($incomeByCategory),
            largest_expense_category: $this->resolveLargestCategory($expenseByCategory),
            movement_count_by_category: $movementCountByCategory,
        );
    }

    /**
     * @param  array<string, Money>  $groups
     * @return array<string, string>
     */
    private function flattenMoneyMap(array $groups): array
    {
        return array_map(
            static fn (Money $amount) => $amount->amount,
            $groups
        );
    }

    /**
     * @param  array<string, array{income: Money, expense: Money, count: int}>  $timeline
     * @return array<string, array{income: string, expense: string, count: int}>
     */
    private function flattenTimelineMap(array $timeline): array
    {
        return array_map(
            static fn (array $row) => [
                'income' => $row['income']->amount,
                'expense' => $row['expense']->amount,
                'count' => $row['count'],
            ],
            $timeline
        );
    }

    /**
     * @param  array<string, Money>  $groups
     * @return null|array{category: string, amount: string}
     */
    private function resolveLargestCategory(array $groups): ?array
    {
        $largestCategory = null;
        $largestAmount = 0.0;

        foreach ($groups as $category => $amount) {
            $value = (float) $amount->amount;
            if ($value <= $largestAmount) {
                continue;
            }

            $largestAmount = $value;
            $largestCategory = [
                'category' => $category,
                'amount' => $amount->amount,
            ];
        }

        return $largestCategory;
    }
}
