<?php

declare(strict_types=1);

namespace App\Application\Product\UseCases;

use App\Application\Product\DTOs\GetProductPricesInput;
use App\Application\Product\Support\BranchScopeResolver;
use App\Application\Product\Support\ProductMapper;
use App\Domain\Product\Exceptions\ProductDomainException;
use App\Domain\Product\Exceptions\ProductNotFoundException;
use App\Domain\Product\Repositories\ProductPriceRepositoryInterface;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetProductPricesUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly ProductRepositoryInterface $products,
        private readonly ProductPriceRepositoryInterface $prices,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof GetProductPricesInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            throw ProductDomainException::tenantRequired();
        }

        $product = $this->products->findById($input->productId, $tenant->id);

        if ($product === null) {
            throw new ProductNotFoundException();
        }

        $items = $this->prices->listForProduct(
            tenantId: $tenant->id,
            productId: $product->id,
            branchId: BranchScopeResolver::resolve(null, $this->branchContext),
        );

        $data = array_map(static fn ($price) => ProductMapper::price($price), $items);

        return OperationResult::ok('Precios del producto.', ['prices' => $data]);
    }
}
