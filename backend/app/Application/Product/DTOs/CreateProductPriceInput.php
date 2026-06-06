<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

final readonly class CreateProductPriceInput extends ProductDto
{
    public function __construct(
        public int $productId,
        public string $saleMode,
        public string $price,
        public ?int $branchId = null,
        public ?string $girlAmount = null,
        public ?string $houseAmount = null,
        public string $currency = 'BOB',
        public string $status = 'active',
        public ?string $startsAt = null,
        public ?string $endsAt = null,
    ) {
    }
}
