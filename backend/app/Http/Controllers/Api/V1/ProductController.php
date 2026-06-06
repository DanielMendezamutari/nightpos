<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Product\DTOs\CreateProductInput;
use App\Application\Product\DTOs\CreateProductPriceInput;
use App\Application\Product\DTOs\QuickCreateProductInput;
use App\Application\Product\DTOs\GetPosCatalogInput;
use App\Application\Product\DTOs\GetProductInput;
use App\Application\Product\DTOs\GetProductsListInput;
use App\Application\Product\DTOs\GetProductPricesInput;
use App\Application\Product\DTOs\UpdateProductInput;
use App\Application\Product\UseCases\CreateProductPriceUseCase;
use App\Application\Product\UseCases\CreateProductUseCase;
use App\Application\Product\UseCases\QuickCreateProductUseCase;
use App\Application\Product\UseCases\ReplaceActiveProductPriceUseCase;
use App\Application\Product\UseCases\GetProductPricesUseCase;
use App\Application\Product\UseCases\GetPosCatalogUseCase;
use App\Application\Product\UseCases\GetProductsUseCase;
use App\Application\Product\UseCases\UpdateProductUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\CreateProductPriceRequest;
use App\Http\Requests\Api\V1\Product\CreateProductRequest;
use App\Http\Requests\Api\V1\Product\QuickCreateProductRequest;
use App\Http\Requests\Api\V1\Product\UpdateProductRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class ProductController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly GetProductsUseCase $getProducts,
        private readonly GetPosCatalogUseCase $getPosCatalog,
        private readonly CreateProductUseCase $createProduct,
        private readonly UpdateProductUseCase $updateProduct,
        private readonly GetProductPricesUseCase $getProductPrices,
        private readonly CreateProductPriceUseCase $createProductPrice,
        private readonly ReplaceActiveProductPriceUseCase $replaceActiveProductPrice,
        private readonly QuickCreateProductUseCase $quickCreateProduct,
    ) {
    }

    public function index(): JsonResponse
    {
        $include = request()->query('include');
        $includeActivePrices = is_string($include) && str_contains($include, 'active_prices');

        return $this->presenter->present($this->getProducts->execute(
            new GetProductsListInput(includeActivePrices: $includeActivePrices),
        ));
    }

    public function posCatalog(): JsonResponse
    {
        $productIds = [];

        if (is_string(request()->query('product_ids')) && request()->query('product_ids') !== '') {
            $productIds = array_values(array_filter(array_map(
                static fn (string $id) => (int) trim($id),
                explode(',', request()->query('product_ids')),
            ), static fn (int $id) => $id > 0));
        }

        $categoryId = request()->query('category_id');

        return $this->presenter->present($this->getPosCatalog->execute(new GetPosCatalogInput(
            search: is_string(request()->query('search')) ? request()->query('search') : null,
            categoryId: $categoryId !== null && $categoryId !== '' ? (int) $categoryId : null,
            sellableOnly: filter_var(request()->query('sellable_only', 'true'), FILTER_VALIDATE_BOOLEAN),
            unpricedOnly: filter_var(request()->query('unpriced_only', 'false'), FILTER_VALIDATE_BOOLEAN),
            productIds: $productIds,
            limit: (int) request()->query('limit', 20),
            grouped: filter_var(request()->query('grouped', 'false'), FILTER_VALIDATE_BOOLEAN),
        )));
    }

    public function show(int $id): JsonResponse
    {
        return $this->presenter->present($this->getProducts->execute(new GetProductInput($id)));
    }

    public function store(CreateProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->createProduct->execute(new CreateProductInput(
            name: $validated['name'],
            branchId: isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            categoryId: isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            sku: $validated['sku'] ?? null,
            barcode: $validated['barcode'] ?? null,
            description: $validated['description'] ?? null,
            productType: $validated['product_type'] ?? 'beverage',
            unit: $validated['unit'] ?? 'unit',
            trackInventory: (bool) ($validated['track_inventory'] ?? false),
            status: $validated['status'] ?? 'active',
        ));

        return $this->presenter->present($result, 201);
    }

    public function quickStore(QuickCreateProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->quickCreateProduct->execute(new QuickCreateProductInput(
            name: $validated['name'],
            categoryId: (int) $validated['category_id'],
            soloPrice: (string) $validated['solo_price'],
            companionPrice: isset($validated['companion_price']) ? (string) $validated['companion_price'] : null,
            girlAmount: isset($validated['girl_amount']) ? (string) $validated['girl_amount'] : null,
            houseAmount: isset($validated['house_amount']) ? (string) $validated['house_amount'] : null,
        ));

        return $this->presenter->present($result, 201);
    }

    public function update(int $id, UpdateProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->updateProduct->execute(new UpdateProductInput(
            productId: $id,
            name: $validated['name'],
            branchId: isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            categoryId: isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            sku: $validated['sku'] ?? null,
            barcode: $validated['barcode'] ?? null,
            description: $validated['description'] ?? null,
            productType: $validated['product_type'] ?? 'beverage',
            unit: $validated['unit'] ?? 'unit',
            trackInventory: (bool) ($validated['track_inventory'] ?? false),
            status: $validated['status'] ?? 'active',
        ));

        return $this->presenter->present($result);
    }

    public function prices(int $id): JsonResponse
    {
        return $this->presenter->present($this->getProductPrices->execute(new GetProductPricesInput($id)));
    }

    public function storePrice(int $id, CreateProductPriceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->createProductPrice->execute(new CreateProductPriceInput(
            productId: $id,
            saleMode: $validated['sale_mode'],
            price: (string) $validated['price'],
            branchId: isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            girlAmount: isset($validated['girl_amount']) ? (string) $validated['girl_amount'] : null,
            houseAmount: isset($validated['house_amount']) ? (string) $validated['house_amount'] : null,
            currency: $validated['currency'] ?? 'BOB',
            status: $validated['status'] ?? 'active',
            startsAt: $validated['starts_at'] ?? null,
            endsAt: $validated['ends_at'] ?? null,
        ));

        return $this->presenter->present($result, 201);
    }

    public function replaceActivePrice(int $id, CreateProductPriceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->replaceActiveProductPrice->execute(new CreateProductPriceInput(
            productId: $id,
            saleMode: $validated['sale_mode'],
            price: (string) $validated['price'],
            branchId: isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            girlAmount: isset($validated['girl_amount']) ? (string) $validated['girl_amount'] : null,
            houseAmount: isset($validated['house_amount']) ? (string) $validated['house_amount'] : null,
            currency: $validated['currency'] ?? 'BOB',
            status: $validated['status'] ?? 'active',
            startsAt: $validated['starts_at'] ?? null,
            endsAt: $validated['ends_at'] ?? null,
        ));

        return $this->presenter->present($result);
    }
}
