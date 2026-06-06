<?php

declare(strict_types=1);

namespace App\Domain\Product\Repositories;

use App\Domain\Product\Entities\ProductPrice;
use App\Shared\Contracts\RepositoryInterface;

interface ProductPriceRepositoryInterface extends RepositoryInterface
{
    public function findActiveForProduct(
        int $tenantId,
        int $productId,
        ?int $branchId,
        string $saleMode,
    ): ?ProductPrice;

    public function hasActiveSaleMode(
        int $tenantId,
        int $productId,
        ?int $branchId,
        string $saleMode,
        ?int $excludePriceId = null,
    ): bool;

    /**
     * @return list<ProductPrice>
     */
    public function listForProduct(int $tenantId, int $productId, ?int $branchId): array;

    public function create(
        int $tenantId,
        ?int $branchId,
        int $productId,
        string $saleMode,
        string $price,
        ?string $girlAmount,
        ?string $houseAmount,
        string $currency,
        string $status,
        ?string $startsAt,
        ?string $endsAt,
    ): ProductPrice;

    public function deactivateActiveForSaleMode(
        int $tenantId,
        int $productId,
        ?int $branchId,
        string $saleMode,
    ): void;

    /**
     * @param  list<int>  $productIds
     * @return array<int, list<ProductPrice>>
     */
    public function listActiveGroupedByProduct(
        int $tenantId,
        array $productIds,
        ?int $branchId,
    ): array;
}
