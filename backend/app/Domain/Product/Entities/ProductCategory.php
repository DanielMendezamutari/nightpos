<?php

declare(strict_types=1);

namespace App\Domain\Product\Entities;

final readonly class ProductCategory
{
    public function __construct(
        public int $id,
        public int $tenantId,
        public ?int $branchId,
        public string $name,
        public string $type,
        public string $status,
    ) {
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
