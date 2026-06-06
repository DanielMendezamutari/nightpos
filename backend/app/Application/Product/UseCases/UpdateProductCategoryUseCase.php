<?php

declare(strict_types=1);

namespace App\Application\Product\UseCases;

use App\Application\Product\DTOs\UpdateProductCategoryInput;
use App\Application\Product\Support\ProductMapper;
use App\Domain\Product\Exceptions\ProductCategoryNotFoundException;
use App\Domain\Product\Exceptions\ProductDomainException;
use App\Domain\Product\Repositories\ProductCategoryRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class UpdateProductCategoryUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly ProductCategoryRepositoryInterface $categories,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof UpdateProductCategoryInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            throw ProductDomainException::tenantRequired();
        }

        $existing = $this->categories->findById($input->categoryId, $tenant->id);

        if ($existing === null) {
            throw new ProductCategoryNotFoundException();
        }

        $name = trim($input->name);

        if ($name === '') {
            throw ProductDomainException::emptyCategoryName();
        }

        $category = $this->categories->update(
            id: $input->categoryId,
            tenantId: $tenant->id,
            name: $name,
            type: $input->type,
            status: $input->status,
        );

        return OperationResult::ok('Categoría actualizada correctamente.', [
            'category' => ProductMapper::category($category),
        ]);
    }
}
