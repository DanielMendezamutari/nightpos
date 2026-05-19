<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ListMaintenanceProductsController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request)
            ?? (int) DB::table('sites')->orderBy('id')->value('id');

        $siteIdForSales = (int) $siteId;

        $soldUnitsExpr = 'COALESCE(SUM(CASE WHEN shift_turns.site_id = '.$siteIdForSales.' THEN COALESCE(order_items.quantity, 0) ELSE 0 END), 0)';

        $products = DB::table('products')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->leftJoin('site_product_stocks as sps', function ($join) use ($siteId): void {
                $join->on('sps.product_id', '=', 'products.id')
                    ->where('sps.site_id', '=', $siteId);
            })
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('shift_turns', 'shift_turns.id', '=', 'orders.shift_turn_id')
            ->groupBy([
                'products.id',
                'products.sku',
                'products.name',
                'products.category_id',
                'products.price_solo',
                'products.price_with_companion',
                'products.purchase_price',
                'products.stock_min',
                'products.stock_max',
                'products.track_stock',
                'products.purchase_units_per_box',
                'products.purchase_units_per_basket',
                'products.is_active',
                'product_categories.name',
            ])
            ->orderBy('products.name')
            ->select([
                'products.id',
                'products.category_id',
                'products.sku',
                'products.name',
                'product_categories.name as category_name',
                DB::raw('COALESCE(MAX(sps.quantity), 0) as stock_actual'),
                'products.price_solo',
                'products.price_with_companion',
                'products.purchase_price',
                'products.stock_min',
                'products.stock_max',
                'products.track_stock',
                'products.purchase_units_per_box',
                'products.purchase_units_per_basket',
                'products.is_active',
                DB::raw($soldUnitsExpr.' as sold_units'),
            ])
            ->get()
            ->map(function ($row) use ($siteId): array {
                $track = (bool) $row->track_stock;
                $stock = (int) $row->stock_actual;
                $min = (int) $row->stock_min;
                $max = $row->stock_max !== null ? (int) $row->stock_max : null;
                $status = 'ok';
                if (! $track) {
                    $status = 'sin_control';
                } elseif ($stock < $min) {
                    $status = 'bajo';
                } elseif ($max !== null && $stock > $max) {
                    $status = 'exceso';
                }

                return [
                    'id' => (int) $row->id,
                    'site_id' => $siteId,
                    'category_id' => $row->category_id !== null ? (int) $row->category_id : null,
                    'sku' => $row->sku,
                    'name' => $row->name,
                    'category_name' => $row->category_name,
                    'stock_actual' => $stock,
                    'price_solo' => (int) $row->price_solo,
                    'price_with_companion' => (int) $row->price_with_companion,
                    'purchase_price' => (int) $row->purchase_price,
                    'stock_min' => $min,
                    'stock_max' => $max,
                    'track_stock' => $track,
                    'purchase_units_per_box' => $row->purchase_units_per_box !== null ? (int) $row->purchase_units_per_box : null,
                    'purchase_units_per_basket' => $row->purchase_units_per_basket !== null ? (int) $row->purchase_units_per_basket : null,
                    'is_active' => (bool) $row->is_active,
                    'sold_units' => (int) $row->sold_units,
                    'stock_status' => $status,
                ];
            })
            ->all();

        $categories = DB::table('product_categories')
            ->select(['id', 'slug', 'name', 'sort_order', 'product_type'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(static function ($row): array {
                return [
                    'id' => (int) $row->id,
                    'slug' => $row->slug,
                    'name' => $row->name,
                    'sort_order' => (int) $row->sort_order,
                    'product_type' => $row->product_type,
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'data' => $products,
            'categories' => $categories,
        ]);
    }
}
