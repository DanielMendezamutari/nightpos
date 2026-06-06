<?php

declare(strict_types=1);

namespace App\Application\Product\UseCases;

use App\Application\Product\Support\ProductMapper;
use App\Domain\Product\Exceptions\ProductCategoryNotFoundException;
use App\Domain\Product\Exceptions\ProductDomainException;
use App\Domain\Product\Repositories\ProductCategoryRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetProductCategoryUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly ProductCategoryRepositoryInterface $categories,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $categoryId = is_object($input) && isset($input->categoryId) ? (int) $input->categoryId : 0;
        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            throw ProductDomainException::tenantRequired();
        }

        $category = $this->categories->findById($categoryId, $tenant->id);

        if ($category === null) {
            throw new ProductCategoryNotFoundException();
        }

        return OperationResult::ok('Categoría obtenida.', [
            'category' => ProductMapper::category($category),
        ]);
    }
}
