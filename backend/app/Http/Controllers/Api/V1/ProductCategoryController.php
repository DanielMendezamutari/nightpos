<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Product\DTOs\CreateProductCategoryInput;
use App\Application\Product\DTOs\UpdateProductCategoryInput;
use App\Application\Product\UseCases\CreateProductCategoryUseCase;
use App\Application\Product\UseCases\GetProductCategoryUseCase;
use App\Application\Product\UseCases\ListProductCategoriesUseCase;
use App\Application\Product\UseCases\UpdateProductCategoryUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\CreateProductCategoryRequest;
use App\Http\Requests\Api\V1\Product\UpdateProductCategoryRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class ProductCategoryController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListProductCategoriesUseCase $listCategories,
        private readonly CreateProductCategoryUseCase $createCategory,
        private readonly GetProductCategoryUseCase $getCategory,
        private readonly UpdateProductCategoryUseCase $updateCategory,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listCategories->execute());
    }

    public function store(CreateProductCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->createCategory->execute(new CreateProductCategoryInput(
            name: $validated['name'],
            branchId: isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            type: $validated['type'] ?? 'general',
            status: $validated['status'] ?? 'active',
        ));

        return $this->presenter->present($result, 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->presenter->present($this->getCategory->execute((object) ['categoryId' => $id]));
    }

    public function update(int $id, UpdateProductCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->updateCategory->execute(new UpdateProductCategoryInput(
            categoryId: $id,
            name: $validated['name'],
            type: $validated['type'],
            status: $validated['status'],
        ));

        return $this->presenter->present($result);
    }
}
