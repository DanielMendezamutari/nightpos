<?php

declare(strict_types=1);

namespace App\Application\Cash\DTOs;

use App\Shared\Domain\ValueObjects\Money;

final readonly class CashSummaryDTO
{
    public function __construct(
        public Money $opening_cash,
        public Money $cash_income_sales,
        public Money $cash_income_sales_normal,
        public Money $cash_income_sales_room_services,
        public Money $cash_income_sales_shows,
        public Money $cash_income_sales_other,
        public Money $cash_income_manual,
        public Money $cash_expense_settlements,
        public Money $cash_expense_operational,
        public Money $cash_expense_purchases,
        public Money $cash_expense_other,
        public Money $cash_income_total,
        public Money $cash_expense_total,
        public Money $expected_cash,
        public ?Money $counted_cash,
        public ?Money $cash_difference,
        public bool $is_closed,
        public bool $has_declared_count,
    ) {
    }
}