<?php

declare(strict_types=1);

namespace App\Application\Product\UseCases;

use App\Application\Product\Support\BranchScopeResolver;
use App\Application\Product\Support\ProductMapper;
use App\Domain\Product\Exceptions\ProductDomainException;
use App\Domain\Product\Repositories\ProductCategoryRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ListProductCategoriesUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly ProductCategoryRepositoryInterface $categories,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            throw ProductDomainException::tenantRequired();
        }

        $items = $this->categories->listForTenant(
            tenantId: $tenant->id,
            branchId: BranchScopeResolver::resolve(null, $this->branchContext),
        );

        $data = array_map(static fn ($category) => ProductMapper::category($category), $items);

        return OperationResult::ok('Listado de categorías.', ['categories' => $data]);
    }
}
