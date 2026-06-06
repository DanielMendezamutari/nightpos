<?php

declare(strict_types=1);

namespace App\Application\Product\UseCases;

use App\Application\Product\DTOs\GetPosCatalogInput;
use App\Application\Product\Support\BranchScopeResolver;
use App\Application\Product\Support\ProductMapper;
use App\Domain\Product\Entities\Product;
use App\Domain\Product\Entities\ProductCategory;
use App\Domain\Product\Exceptions\ProductDomainException;
use App\Domain\Product\Repositories\ProductCategoryRepositoryInterface;
use App\Domain\Product\Repositories\ProductPriceRepositoryInterface;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetPosCatalogUseCase implements UseCaseInterface
{
    private const MIN_SEARCH_LENGTH = 2;

    private const DEFAULT_LIMIT = 20;

    private const MAX_LIMIT = 50;

    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly ProductRepositoryInterface $products,
        private readonly ProductPriceRepositoryInterface $prices,
        private readonly ProductCategoryRepositoryInterface $categories,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof GetPosCatalogInput) {
            $input = new GetPosCatalogInput();
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            throw ProductDomainException::tenantRequired();
        }

        $branchId = BranchScopeResolver::resolve(null, $this->branchContext);
        $activeOnly = $this->staffContext->staffRole() === 'WAITER'
            || $this->staffContext->roleSlug() === 'waiter';

        $categoryRows = $this->categories->listForTenant($tenant->id, $branchId);
        $categoryById = [];

        foreach ($categoryRows as $category) {
            $categoryById[$category->id] = $category;
        }

        $productRows = $this->products->listForTenant(
            tenantId: $tenant->id,
            branchId: $branchId,
            activeOnly: $activeOnly,
        );

        $productIds = array_map(static fn (Product $product) => $product->id, $productRows);
        $activeByProduct = $productIds !== []
            ? $this->prices->listActiveGroupedByProduct($tenant->id, $productIds, $branchId)
            : [];

        $enriched = [];

        foreach ($productRows as $product) {
            $activePrices = $activeByProduct[$product->id] ?? [];
            $mapped = ProductMapper::productWithActivePrices($product, $activePrices);
            $mapped['category_name'] = $this->categoryName($product, $categoryById);
            $enriched[] = $mapped;
        }

        $sellableCount = count(array_filter(
            $enriched,
            static fn (array $row) => ($row['has_active_pricing'] ?? false) === true,
        ));

        $unpricedCount = count(array_filter(
            $enriched,
            static fn (array $row) => ($row['status'] ?? '') === 'active'
                && ($row['has_active_pricing'] ?? false) === false,
        ));

        $categoriesPayload = $this->buildCategoryPayload($categoryRows, $enriched);
        $limit = min(max($input->limit, 1), self::MAX_LIMIT);
        $shouldReturnProducts = $this->shouldReturnProducts($input);

        $filtered = $shouldReturnProducts
            ? $this->filterProducts($enriched, $input)
            : [];

        $resultCount = count($filtered);
        $hasMore = $resultCount > $limit;
        $productsPayload = array_slice($filtered, 0, $limit);

        $groupedPayload = null;

        if ($input->grouped && $input->categoryId === null && $productsPayload !== []) {
            $groupedPayload = $this->groupByCategory($productsPayload);
        }

        return OperationResult::ok('Catálogo POS.', [
            'categories' => $categoriesPayload,
            'products' => $productsPayload,
            'grouped' => $groupedPayload,
            'meta' => [
                'total_active' => count($enriched),
                'sellable_count' => $sellableCount,
                'unpriced_count' => $unpricedCount,
                'result_count' => min($resultCount, $limit),
                'matched_count' => $resultCount,
                'limit' => $limit,
                'has_more' => $hasMore,
            ],
        ]);
    }

    /**
     * @param  array<int, ProductCategory>  $categoryById
     */
    private function categoryName(Product $product, array $categoryById): ?string
    {
        if ($product->categoryId === null) {
            return null;
        }

        return $categoryById[$product->categoryId]->name ?? null;
    }

    /**
     * @param  list<ProductCategory>  $categoryRows
     * @param  list<array<string, mixed>>  $enriched
     * @return list<array<string, mixed>>
     */
    private function buildCategoryPayload(array $categoryRows, array $enriched): array
    {
        $counts = [];
        $sellableCounts = [];

        foreach ($enriched as $row) {
            $categoryId = $row['category_id'] ?? null;

            if ($categoryId === null) {
                continue;
            }

            $counts[$categoryId] = ($counts[$categoryId] ?? 0) + 1;

            if (($row['has_active_pricing'] ?? false) === true) {
                $sellableCounts[$categoryId] = ($sellableCounts[$categoryId] ?? 0) + 1;
            }
        }

        $uncategorizedCount = count(array_filter(
            $enriched,
            static fn (array $row) => ($row['category_id'] ?? null) === null,
        ));

        $uncategorizedSellable = count(array_filter(
            $enriched,
            static fn (array $row) => ($row['category_id'] ?? null) === null
                && ($row['has_active_pricing'] ?? false) === true,
        ));

        $payload = array_map(static function (ProductCategory $category) use ($counts, $sellableCounts) {
            return array_merge(ProductMapper::category($category), [
                'product_count' => $counts[$category->id] ?? 0,
                'sellable_count' => $sellableCounts[$category->id] ?? 0,
            ]);
        }, $categoryRows);

        if ($uncategorizedCount > 0) {
            $payload[] = [
                'id' => null,
                'tenant_id' => null,
                'branch_id' => null,
                'name' => 'Sin categoría',
                'type' => 'other',
                'status' => 'active',
                'product_count' => $uncategorizedCount,
                'sellable_count' => $uncategorizedSellable,
            ];
        }

        usort($payload, static fn (array $a, array $b) => strcmp((string) $a['name'], (string) $b['name']));

        return $payload;
    }

    private function shouldReturnProducts(GetPosCatalogInput $input): bool
    {
        if ($input->unpricedOnly) {
            return true;
        }

        if ($input->productIds !== []) {
            return true;
        }

        if ($input->categoryId !== null) {
            return true;
        }

        $search = trim((string) ($input->search ?? ''));

        return mb_strlen($search) >= self::MIN_SEARCH_LENGTH;
    }

    /**
     * @param  list<array<string, mixed>>  $enriched
     * @return list<array<string, mixed>>
     */
    private function filterProducts(array $enriched, GetPosCatalogInput $input): array
    {
        $search = mb_strtolower(trim((string) ($input->search ?? '')));
        $productIdSet = $input->productIds !== []
            ? array_fill_keys(array_map('intval', $input->productIds), true)
            : [];

        $filtered = array_values(array_filter($enriched, function (array $row) use ($input, $search, $productIdSet) {
            if ($input->unpricedOnly) {
                if (($row['status'] ?? '') !== 'active' || ($row['has_active_pricing'] ?? false) === true) {
                    return false;
                }
            } elseif ($input->sellableOnly && ($row['has_active_pricing'] ?? false) !== true) {
                return false;
            }

            if ($input->categoryId !== null) {
                $categoryId = $row['category_id'] ?? null;

                if ($input->categoryId === 0) {
                    if ($categoryId !== null) {
                        return false;
                    }
                } elseif ($categoryId !== $input->categoryId) {
                    return false;
                }
            }

            if ($productIdSet !== [] && ! isset($productIdSet[(int) ($row['id'] ?? 0)])) {
                return false;
            }

            if ($search !== '' && mb_strlen($search) >= self::MIN_SEARCH_LENGTH) {
                $haystack = mb_strtolower(implode(' ', array_filter([
                    (string) ($row['name'] ?? ''),
                    (string) ($row['sku'] ?? ''),
                    (string) ($row['barcode'] ?? ''),
                    (string) ($row['category_name'] ?? ''),
                ])));

                if (! str_contains($haystack, $search)) {
                    return false;
                }
            }

            return true;
        }));

        usort($filtered, static fn (array $a, array $b) => strcmp((string) $a['name'], (string) $b['name']));

        return $filtered;
    }

    /**
     * @param  list<array<string, mixed>>  $products
     * @return array<string, list<array<string, mixed>>>
     */
    private function groupByCategory(array $products): array
    {
        $grouped = [];

        foreach ($products as $product) {
            $label = (string) ($product['category_name'] ?? 'Sin categoría');
            $grouped[$label][] = $product;
        }

        ksort($grouped);

        return $grouped;
    }
}
