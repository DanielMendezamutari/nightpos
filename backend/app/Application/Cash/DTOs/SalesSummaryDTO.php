<?php

declare(strict_types=1);

namespace App\Application\Cash\DTOs;

use App\Shared\Domain\ValueObjects\Money;

final readonly class SalesSummaryDTO
{
    public function __construct(
        public Money $total_sales_amount,
        public int $sales_count,
        public Money $average_ticket,
        public Money $cash_total,
        public Money $qr_total,
        public Money $card_total,
        public Money $mixed_total,
        public int $mixed_sales_count,
        /** @var array<string, string> */
        public array $by_source,
        /** @var array<string, string> */
        public array $by_method,
        /** @var array<string, string> */
        public array $sales_by_method,
        public int $products_sold_count,
        public int $services_sold_count,
    ) {
    }
}
