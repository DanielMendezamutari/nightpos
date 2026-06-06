<?php

declare(strict_types=1);

namespace App\Domain\Product\Repositories;

use App\Domain\Product\Entities\ProductCategory;
use App\Shared\Contracts\RepositoryInterface;

interface ProductCategoryRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id, int $tenantId): ?ProductCategory;

    /**
     * @return list<ProductCategory>
     */
    public function listForTenant(int $tenantId, ?int $branchId): array;

    public function create(
        int $tenantId,
        ?int $branchId,
        string $name,
        string $type,
        string $status,
    ): ProductCategory;

    public function update(
        int $id,
        int $tenantId,
        string $name,
        string $type,
        string $status,
    ): ProductCategory;
}
