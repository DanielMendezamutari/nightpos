<?php

declare(strict_types=1);

namespace App\Application\Cash\Services\SummaryBuilders;

use App\Application\Cash\DTOs\CashSummaryDTO;
use App\Domain\Cash\Contracts\SummaryBuilders\CashSummaryBuilder;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Domain\Cash\ValueObjects\CashSessionId;
use App\Shared\Domain\ValueObjects\Money;

final readonly class EloquentCashSummaryBuilder implements CashSummaryBuilder
{
    public function __construct(
        private CashSessionRepositoryInterface $cashSessionRepository,
    ) {
    }

    public function build(CashSessionId $cashSessionId): CashSummaryDTO
    {
        $summary = $this->cashSessionRepository->getStructuredCashSummary($cashSessionId);

        $openingCash = new Money((string) ($summary['opening_cash'] ?? '0'));
        $cashIncomeSales = new Money((string) ($summary['cash_income_sales'] ?? '0'));
        $cashIncomeManual = new Money((string) ($summary['cash_income_manual'] ?? '0'));
        $cashExpenseSettlements = new Money((string) ($summary['cash_expense_settlements'] ?? '0'));
        $cashExpenseOperational = new Money((string) ($summary['cash_expense_operational'] ?? '0'));
        $cashExpensePurchases = new Money((string) ($summary['cash_expense_purchases'] ?? '0'));
        $cashExpenseOther = new Money((string) ($summary['cash_expense_other'] ?? '0'));

        $cashIncomeTotal = $cashIncomeSales->add($cashIncomeManual);
        $cashExpenseTotal = $cashExpenseSettlements
            ->add($cashExpenseOperational)
            ->add($cashExpensePurchases)
            ->add($cashExpenseOther);

        $expectedCash = $openingCash
            ->add($cashIncomeTotal)
            ->subtract($cashExpenseTotal);

        $isClosed = (bool) ($summary['is_closed'] ?? false);
        $hasDeclaredCount = (bool) ($summary['has_declared_count'] ?? false);

        $countedCash = null;
        if (($isClosed || $hasDeclaredCount) && ($summary['counted_cash'] ?? null) !== null) {
            $countedCash = new Money((string) $summary['counted_cash']);
        }

        $cashDifference = null;
        if (($isClosed || $hasDeclaredCount) && $countedCash !== null) {
            if (($summary['cash_difference'] ?? null) !== null) {
                $cashDifference = new Money((string) $summary['cash_difference']);
            } else {
                $cashDifference = $countedCash->subtract($expectedCash);
            }
        }

        return new CashSummaryDTO(
            opening_cash: $openingCash,
            cash_income_sales: $cashIncomeSales,
            cash_income_manual: $cashIncomeManual,
            cash_expense_settlements: $cashExpenseSettlements,
            cash_expense_operational: $cashExpenseOperational,
            cash_expense_purchases: $cashExpensePurchases,
            cash_expense_other: $cashExpenseOther,
            cash_income_total: $cashIncomeTotal,
            cash_expense_total: $cashExpenseTotal,
            expected_cash: $expectedCash,
            counted_cash: $countedCash,
            cash_difference: $cashDifference,
            is_closed: $isClosed,
            has_declared_count: $hasDeclaredCount,
        );
    }
}