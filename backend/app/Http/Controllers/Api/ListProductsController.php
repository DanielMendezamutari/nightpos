<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ListProductsController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request)
            ?? (int) DB::table('sites')->orderBy('id')->value('id');

        $query = DB::table('products')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->leftJoin('site_product_stocks as sps', function ($join) use ($siteId): void {
                $join->on('sps.product_id', '=', 'products.id')
                    ->where('sps.site_id', '=', $siteId);
            })
            ->select([
                'products.id',
                'products.sku',
                'products.name',
                'products.category_id',
                'product_categories.slug as category_slug',
                'product_categories.name as category_name',
                'products.product_type',
                'products.price_solo',
                'products.price_with_companion',
                'products.purchase_price',
                DB::raw('COALESCE(sps.quantity, 0) as branch_stock'),
                'products.stock_min',
                'products.stock_max',
                'products.track_stock',
                'products.is_active',
                'products.base_stock',
            ])
            ->orderBy('products.name');

        if ($request->user()?->role === 'waiter') {
            $query->where('products.is_active', true);
        }

        $rows = $query->get()->map(function ($p) use ($siteId): array {
            $track = (bool) $p->track_stock;
            $stock = (int) $p->branch_stock;
            $min = (int) $p->stock_min;
            $max = $p->stock_max !== null ? (int) $p->stock_max : null;
            $alert = 'ok';
            if ($track) {
                if ($stock < $min) {
                    $alert = 'low';
                } elseif ($max !== null && $stock > $max) {
                    $alert = 'over';
                }
            } else {
                $alert = 'untracked';
            }

            return [
                'id' => (int) $p->id,
                'sku' => $p->sku,
                'name' => $p->name,
                'category_id' => $p->category_id !== null ? (int) $p->category_id : null,
                'category_slug' => $p->category_slug,
                'category_name' => $p->category_name,
                'product_type' => $p->product_type,
                'price_solo' => (int) $p->price_solo,
                'price_with_companion' => (int) $p->price_with_companion,
                'purchase_price' => (int) $p->purchase_price,
                'base_stock' => $stock,
                'site_id' => $siteId ?: null,
                'stock_min' => $min,
                'stock_max' => $max,
                'track_stock' => $track,
                'is_active' => (bool) $p->is_active,
                'stock_alert' => $alert,
            ];
        })->all();

        return response()->json([
            'data' => $rows,
        ]);
    }
}
