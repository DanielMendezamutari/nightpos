<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

final readonly class GetProductPricesInput extends ProductDto
{
    public function __construct(
        public int $productId,
    ) {
    }
}
