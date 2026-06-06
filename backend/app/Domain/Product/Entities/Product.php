<?php

declare(strict_types=1);

namespace App\Domain\Product\Entities;

final readonly class Product
{
    public function __construct(
        public int $id,
        public int $tenantId,
        public ?int $branchId,
        public ?int $categoryId,
        public string $name,
        public ?string $sku,
        public ?string $barcode,
        public ?string $description,
        public string $productType,
        public string $unit,
        public bool $trackInventory,
        public string $status,
    ) {
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function belongsToTenant(int $tenantId): bool
    {
        return $this->tenantId === $tenantId;
    }
}
