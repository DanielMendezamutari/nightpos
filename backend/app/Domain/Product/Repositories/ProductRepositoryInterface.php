<?php

declare(strict_types=1);

namespace App\Domain\Product\Repositories;

use App\Domain\Product\Entities\Product;
use App\Shared\Contracts\RepositoryInterface;

interface ProductRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id, int $tenantId): ?Product;

    /**
     * @return list<Product>
     */
    public function listForTenant(
        int $tenantId,
        ?int $branchId,
        bool $activeOnly,
    ): array;

    public function create(
        int $tenantId,
        ?int $branchId,
        ?int $categoryId,
        string $name,
        ?string $sku,
        ?string $barcode,
        ?string $description,
        string $productType,
        string $unit,
        bool $trackInventory,
        string $status,
        string $settlementBehavior = 'GIRL_LINE',
        int $braceletUnitsPerLine = 1,
        bool $requiresAllocation = false,
        ?string $allocationType = null,
    ): Product;

    public function update(
        int $id,
        int $tenantId,
        ?int $branchId,
        ?int $categoryId,
        string $name,
        ?string $sku,
        ?string $barcode,
        ?string $description,
        string $productType,
        string $unit,
        bool $trackInventory,
        string $status,
        string $settlementBehavior = 'GIRL_LINE',
        int $braceletUnitsPerLine = 1,
        bool $requiresAllocation = false,
        ?string $allocationType = null,
    ): Product;
}
