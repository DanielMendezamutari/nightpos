<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

final readonly class UpdateProductInput extends ProductDto
{
    public function __construct(
        public int $productId,
        public string $name,
        public ?int $branchId = null,
        public ?int $categoryId = null,
        public ?string $sku = null,
        public ?string $barcode = null,
        public ?string $description = null,
        public string $productType = 'beverage',
        public string $unit = 'unit',
        public bool $trackInventory = false,
        public string $status = 'active',
    ) {
    }
}
