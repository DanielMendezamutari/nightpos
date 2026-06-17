<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

final readonly class QuickCreateProductInput
{
    public function __construct(
        public string $name,
        public int $categoryId,
        public string $soloPrice,
        public ?string $companionPrice = null,
        public ?string $girlAmount = null,
        public ?string $houseAmount = null,
        public string $productType = 'beverage',
        public string $unit = 'unit',
        public string $status = 'active',
        public ?string $settlementBehavior = null,
        public int $braceletUnitsPerLine = 1,
        public ?string $sku = null,
    ) {
    }
}
