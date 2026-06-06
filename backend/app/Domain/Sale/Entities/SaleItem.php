<?php

declare(strict_types=1);

namespace App\Domain\Sale\Entities;

final readonly class SaleItem
{
    public function __construct(
        public int $id,
        public int $productId,
        public string $productNameSnapshot,
        public string $saleMode,
        public int $quantity,
        public string $unitPriceSnapshot,
        public string $lineTotal,
        public ?int $girlUserId,
        public ?string $girlAmountSnapshot,
        public ?string $houseAmountSnapshot,
        public ?string $waiterCommissionPercentSnapshot,
        public ?string $waiterCommissionAmountSnapshot,
    ) {
    }
}
