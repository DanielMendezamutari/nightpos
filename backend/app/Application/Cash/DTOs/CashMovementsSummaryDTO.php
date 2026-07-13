<?php

declare(strict_types=1);

namespace App\Application\Cash\DTOs;

use App\Shared\Domain\ValueObjects\Money;

final readonly class CashMovementsSummaryDTO
{
    public function __construct(
        public Money $total_incomes,
        public Money $total_expenses,
        public Money $net_cash_flow,
        public int $incomes_count,
        public int $expenses_count,
        public Money $incomes_cash,
        public Money $incomes_qr,
        public Money $incomes_card,
        public Money $expenses_cash,
        public Money $expenses_qr,
        public Money $expenses_card,
    ) {
    }
}
