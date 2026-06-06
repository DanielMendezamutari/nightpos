<?php

declare(strict_types=1);

namespace App\Domain\Product\Entities;

final readonly class ProductPrice
{
    public function __construct(
        public int $id,
        public int $tenantId,
        public ?int $branchId,
        public int $productId,
        public string $saleMode,
        public string $price,
        public ?string $girlAmount,
        public ?string $houseAmount,
        public string $currency,
        public string $status,
        public ?string $startsAt,
        public ?string $endsAt,
    ) {
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
