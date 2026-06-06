<?php

declare(strict_types=1);

namespace App\Application\Product\UseCases;

use App\Application\Product\DTOs\GetProductInput;
use App\Application\Product\DTOs\GetProductsListInput;
use App\Application\Product\Support\BranchScopeResolver;
use App\Application\Product\Support\ProductMapper;
use App\Domain\Product\Exceptions\ProductDomainException;
use App\Domain\Product\Exceptions\ProductNotFoundException;
use App\Domain\Product\Repositories\ProductPriceRepositoryInterface;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetProductsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly ProductRepositoryInterface $products,
        private readonly ProductPriceRepositoryInterface $prices,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            throw ProductDomainException::tenantRequired();
        }

        $branchId = BranchScopeResolver::resolve(null, $this->branchContext);
        $activeOnly = $this->staffContext->staffRole() === 'WAITER'
            || $this->staffContext->roleSlug() === 'waiter';

        if ($input instanceof GetProductInput) {
            $product = $this->products->findById($input->productId, $tenant->id);

            if ($product === null) {
                throw new ProductNotFoundException();
            }

            if ($activeOnly && ! $product->isActive()) {
                throw new ProductNotFoundException();
            }

            $activePrices = $this->prices->listActiveGroupedByProduct(
                $tenant->id,
                [$product->id],
                $branchId,
            )[$product->id] ?? [];

            return OperationResult::ok('Producto obtenido.', [
                'product' => ProductMapper::productWithActivePrices($product, $activePrices),
            ]);
        }

        $items = $this->products->listForTenant(
            tenantId: $tenant->id,
            branchId: $branchId,
            activeOnly: $activeOnly,
        );

        $includeActivePrices = $input instanceof GetProductsListInput && $input->includeActivePrices;

        $activeByProduct = [];

        if ($includeActivePrices && $items !== []) {
            $ids = array_map(static fn ($product) => $product->id, $items);
            $activeByProduct = $this->prices->listActiveGroupedByProduct($tenant->id, $ids, $branchId);
        }

        $data = array_map(function ($product) use ($includeActivePrices, $activeByProduct) {
            if (! $includeActivePrices) {
                return ProductMapper::product($product);
            }

            $activePrices = $activeByProduct[$product->id] ?? [];

            return ProductMapper::productWithActivePrices($product, $activePrices);
        }, $items);

        return OperationResult::ok('Listado de productos.', ['products' => $data]);
    }
}
