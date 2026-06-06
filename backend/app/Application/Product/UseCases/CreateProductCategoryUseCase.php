<?php

declare(strict_types=1);

namespace App\Application\Product\UseCases;

use App\Application\Product\DTOs\CreateProductCategoryInput;
use App\Application\Product\Support\BranchScopeResolver;
use App\Application\Product\Support\ProductMapper;
use App\Domain\Product\Exceptions\ProductDomainException;
use App\Domain\Product\Repositories\ProductCategoryRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class CreateProductCategoryUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly ProductCategoryRepositoryInterface $categories,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof CreateProductCategoryInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            throw ProductDomainException::tenantRequired();
        }

        $name = trim($input->name);

        if ($name === '') {
            throw ProductDomainException::emptyName();
        }

        $branchId = BranchScopeResolver::resolve($input->branchId, $this->branchContext);

        $category = $this->categories->create(
            tenantId: $tenant->id,
            branchId: $branchId,
            name: $name,
            type: $input->type,
            status: $input->status,
        );

        return OperationResult::ok('Categoría creada correctamente.', [
            'category' => ProductMapper::category($category),
        ]);
    }
}
