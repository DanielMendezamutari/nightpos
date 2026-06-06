<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

final readonly class CreateProductCategoryInput extends ProductDto
{
    public function __construct(
        public string $name,
        public ?int $branchId = null,
        public string $type = 'general',
        public string $status = 'active',
    ) {
    }
}
