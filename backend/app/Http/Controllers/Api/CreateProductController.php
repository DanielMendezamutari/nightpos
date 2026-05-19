<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use App\Support\ProductStockAggregator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class CreateProductController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'price_solo' => ['required', 'integer', 'min:0'],
            'price_with_companion' => ['required', 'integer', 'min:0'],
            'base_stock' => ['required', 'integer', 'min:0'],
            'purchase_price' => ['sometimes', 'integer', 'min:0'],
            'stock_min' => ['sometimes', 'integer', 'min:0'],
            'stock_max' => ['nullable', 'integer', 'min:0'],
            'track_stock' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'purchase_units_per_box' => ['nullable', 'integer', 'min:1'],
            'purchase_units_per_basket' => ['nullable', 'integer', 'min:1'],
        ]);

        $stockMin = $payload['stock_min'] ?? 0;
        $stockMax = array_key_exists('stock_max', $payload) ? $payload['stock_max'] : null;
        if ($stockMax !== null && $stockMax < $stockMin) {
            return response()->json([
                'message' => 'El stock máximo no puede ser menor que el stock mínimo.',
            ], 422);
        }

        $siteId = $this->resolveBranchSiteId($request)
            ?? (int) DB::table('sites')->orderBy('id')->value('id');
        if (! $siteId) {
            return response()->json([
                'message' => 'No hay sucursal para asignar stock inicial.',
            ], 422);
        }

        $category = DB::table('product_categories')
            ->where('id', $payload['category_id'])
            ->first();

        $productId = DB::table('products')->insertGetId([
            'sku' => $payload['sku'],
            'name' => $payload['name'],
            'category_id' => $payload['category_id'],
            'product_type' => $category->product_type,
            'price_solo' => $payload['price_solo'],
            'price_with_companion' => $payload['price_with_companion'],
            'base_stock' => 0,
            'purchase_price' => $payload['purchase_price'] ?? 0,
            'stock_min' => $stockMin,
            'stock_max' => $stockMax,
            'track_stock' => $payload['track_stock'] ?? true,
            'is_active' => $payload['is_active'] ?? true,
            'purchase_units_per_box' => $payload['purchase_units_per_box'] ?? null,
            'purchase_units_per_basket' => $payload['purchase_units_per_basket'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('site_product_stocks')->insert([
            'site_id' => $siteId,
            'product_id' => $productId,
            'quantity' => $payload['base_stock'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ProductStockAggregator::syncBaseStock($productId);

        return response()->json([
            'data' => [
                'id' => $productId,
                'sku' => $payload['sku'],
                'category_id' => (int) $payload['category_id'],
                'product_type' => $category->product_type,
                'price_solo' => $payload['price_solo'],
                'price_with_companion' => $payload['price_with_companion'],
                'purchase_price' => $payload['purchase_price'] ?? 0,
                'stock_min' => $stockMin,
                'stock_max' => $stockMax,
                'track_stock' => $payload['track_stock'] ?? true,
                'site_id' => $siteId,
                'stock_initial' => $payload['base_stock'],
            ],
        ], 201);
    }
}
