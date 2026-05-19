<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;

final class ProductStockAggregator
{
    public static function syncBaseStock(int $productId): void
    {
        $sum = (int) DB::table('site_product_stocks')
            ->where('product_id', $productId)
            ->sum('quantity');

        DB::table('products')
            ->where('id', $productId)
            ->update([
                'base_stock' => $sum,
                'updated_at' => now(),
            ]);
    }
}
