<?php

declare(strict_types=1);

namespace App\Application\Cash\DTOs;

use App\Shared\Domain\ValueObjects\Money;

final readonly class MovementSummaryDTO
{
    public function __construct(
        public int $total_movements,
        public Money $total_income,
        public Money $total_expense,
        /** @var array<string, string> */
        public array $income_by_family,
        /** @var array<string, string> */
        public array $expense_by_family,
        /** @var array<string, string> */
        public array $income_by_category,
        /** @var array<string, string> */
        public array $expense_by_category,
        /** @var array<string, string> */
        public array $income_by_payment_method,
        /** @var array<string, string> */
        public array $expense_by_payment_method,
        /** @var array<string, array{income: string, expense: string, count: int}> */
        public array $timeline_by_hour,
        /** @var null|array{category: string, amount: string} */
        public ?array $largest_income_category,
        /** @var null|array{category: string, amount: string} */
        public ?array $largest_expense_category,
        /** @var array<string, int> */
        public array $movement_count_by_category,
    ) {
    }
}
