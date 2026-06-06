<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

final readonly class GetPosCatalogInput
{
    /**
     * @param  list<int>  $productIds
     */
    public function __construct(
        public ?string $search = null,
        public ?int $categoryId = null,
        public bool $sellableOnly = true,
        public bool $unpricedOnly = false,
        public array $productIds = [],
        public int $limit = 20,
        public bool $grouped = false,
    ) {
    }
}
