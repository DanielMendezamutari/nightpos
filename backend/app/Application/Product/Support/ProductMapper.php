<?php

declare(strict_types=1);

namespace App\Application\Product\Support;

use App\Domain\Product\Entities\Product;
use App\Domain\Product\Entities\ProductCategory;
use App\Domain\Product\Entities\ProductPrice;

final class ProductMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function product(Product $product): array
    {
        return [
            'id' => $product->id,
            'tenant_id' => $product->tenantId,
            'branch_id' => $product->branchId,
            'category_id' => $product->categoryId,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'description' => $product->description,
            'product_type' => $product->productType,
            'unit' => $product->unit,
            'track_inventory' => $product->trackInventory,
            'status' => $product->status,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function price(ProductPrice $price): array
    {
        return [
            'id' => $price->id,
            'tenant_id' => $price->tenantId,
            'branch_id' => $price->branchId,
            'product_id' => $price->productId,
            'sale_mode' => $price->saleMode,
            'price' => $price->price,
            'girl_amount' => $price->girlAmount,
            'house_amount' => $price->houseAmount,
            'currency' => $price->currency,
            'status' => $price->status,
            'starts_at' => $price->startsAt,
            'ends_at' => $price->endsAt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function category(ProductCategory $category): array
    {
        return [
            'id' => $category->id,
            'tenant_id' => $category->tenantId,
            'branch_id' => $category->branchId,
            'name' => $category->name,
            'type' => $category->type,
            'status' => $category->status,
        ];
    }

    /**
     * @param  list<ProductPrice>  $activePrices
     * @return array<string, mixed>
     */
    public static function productWithActivePrices(Product $product, array $activePrices): array
    {
        $mapped = array_map(static fn (ProductPrice $price) => self::price($price), $activePrices);

        return array_merge(self::product($product), [
            'active_prices' => $mapped,
            'has_active_pricing' => $mapped !== [],
        ]);
    }
}
