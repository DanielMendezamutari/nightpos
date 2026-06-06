<?php

declare(strict_types=1);

namespace App\Domain\Product\Services;

use App\Domain\Product\Entities\ProductPrice;
use App\Domain\Product\Exceptions\ProductDomainException;
use App\Domain\Product\Repositories\ProductPriceRepositoryInterface;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\ValueObjects\SaleMode;

final class ProductPriceResolver
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductPriceRepositoryInterface $prices,
    ) {
    }

    public function resolve(
        int $tenantId,
        int $productId,
        string $saleMode,
        ?int $branchId,
    ): ProductPrice {
        $product = $this->products->findById($productId, $tenantId);

        if ($product === null) {
            throw ProductDomainException::notFound();
        }

        if (! $product->isActive()) {
            throw ProductDomainException::inactiveProduct();
        }

        $mode = SaleMode::fromString($saleMode)->value;

        $price = $this->prices->findActiveForProduct($tenantId, $productId, $branchId, $mode);

        if ($price === null) {
            throw ProductDomainException::priceNotFoundForMode($mode);
        }

        return $price;
    }
}
