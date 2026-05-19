<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use App\Support\ProductStockAggregator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class UpdateProductController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(int $productId, Request $request): JsonResponse
    {
        $payload = $request->validate([
            'sku' => ['sometimes', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'name' => ['sometimes', 'string', 'max:255'],
            'category_id' => ['sometimes', 'integer', 'exists:product_categories,id'],
            'price_solo' => ['sometimes', 'integer', 'min:0'],
            'price_with_companion' => ['sometimes', 'integer', 'min:0'],
            'base_stock' => ['sometimes', 'integer', 'min:0'],
            'purchase_price' => ['sometimes', 'integer', 'min:0'],
            'stock_min' => ['sometimes', 'integer', 'min:0'],
            'stock_max' => ['nullable', 'integer', 'min:0'],
            'track_stock' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'purchase_units_per_box' => ['nullable', 'integer', 'min:1'],
            'purchase_units_per_basket' => ['nullable', 'integer', 'min:1'],
        ]);

        $current = DB::table('products')->where('id', $productId)->first();
        if (! $current) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        $mergedMin = $payload['stock_min'] ?? (int) $current->stock_min;
        $mergedMax = array_key_exists('stock_max', $payload) ? $payload['stock_max'] : $current->stock_max;
        if ($mergedMax !== null && (int) $mergedMax < (int) $mergedMin) {
            return response()->json([
                'message' => 'El stock máximo no puede ser menor que el stock mínimo.',
            ], 422);
        }

        $update = [...$payload];

        if (array_key_exists('base_stock', $payload)) {
            $siteId = $this->resolveBranchSiteId($request)
                ?? (int) DB::table('sites')->orderBy('id')->value('id');
            if (! $siteId) {
                return response()->json(['message' => 'No hay sucursal para ajustar stock.'], 422);
            }
            unset($update['base_stock']);
            DB::table('site_product_stocks')->updateOrInsert(
                [
                    'site_id' => $siteId,
                    'product_id' => $productId,
                ],
                [
                    'quantity' => (int) $payload['base_stock'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            ProductStockAggregator::syncBaseStock($productId);
        }

        if (array_key_exists('category_id', $payload)) {
            $category = DB::table('product_categories')
                ->where('id', $payload['category_id'])
                ->first();
            $update['product_type'] = $category->product_type;
        }

        if (count($update) > 0) {
            DB::table('products')
                ->where('id', $productId)
                ->update([
                    ...$update,
                    'updated_at' => now(),
                ]);
        }

        $product = DB::table('products')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->where('products.id', $productId)
            ->select([
                'products.id',
                'products.category_id',
                'product_categories.slug as category_slug',
                'product_categories.name as category_name',
                'products.price_solo',
                'products.price_with_companion',
                'products.purchase_price',
                'products.stock_min',
                'products.stock_max',
                'products.track_stock',
                'products.purchase_units_per_box',
                'products.purchase_units_per_basket',
                'products.is_active',
            ])
            ->first();

        return response()->json([
            'data' => [
                'id' => $product->id,
                'category_id' => $product->category_id !== null ? (int) $product->category_id : null,
                'category_slug' => $product->category_slug,
                'category_name' => $product->category_name,
                'price_solo' => $product->price_solo,
                'price_with_companion' => $product->price_with_companion,
                'purchase_price' => (int) $product->purchase_price,
                'stock_min' => (int) $product->stock_min,
                'stock_max' => $product->stock_max !== null ? (int) $product->stock_max : null,
                'track_stock' => (bool) $product->track_stock,
                'purchase_units_per_box' => $product->purchase_units_per_box !== null ? (int) $product->purchase_units_per_box : null,
                'purchase_units_per_basket' => $product->purchase_units_per_basket !== null ? (int) $product->purchase_units_per_basket : null,
                'is_active' => (bool) $product->is_active,
            ],
        ]);
    }
}
