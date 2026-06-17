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
        public string $settlementBehavior = 'GIRL_LINE',
        public int $braceletUnitsPerLine = 1,
        public bool $requiresAllocation = false,
        public ?string $allocationType = null,
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

    public function requiredBraceletUnits(int $quantity): int
    {
        return $this->braceletUnitsPerLine * max(1, $quantity);
    }
}
