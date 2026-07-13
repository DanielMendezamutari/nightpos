<?php

declare(strict_types=1);

namespace App\Application\Cash\DTOs;

use App\Shared\Domain\ValueObjects\Money;

final readonly class SalesSummaryDTO
{
    public function __construct(
        public Money $total_sales,
        public int $sales_count,
        public Money $average_ticket,
        public Money $sales_cash,
        public Money $sales_qr,
        public Money $sales_card,
        public int $mixed_sales_count,
        /** @var array<string, string> */
        public array $sales_by_method,
        public int $products_sold_count,
        public int $services_sold_count,
    ) {
    }
}
