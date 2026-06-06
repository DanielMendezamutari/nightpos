<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

final readonly class UpdateProductCategoryInput extends ProductDto
{
    public function __construct(
        public int $categoryId,
        public string $name,
        public string $type,
        public string $status,
    ) {
    }
}
