<?php

declare(strict_types=1);

namespace App\Modules\Sales\Infrastructure\Persistence;

use App\Modules\Sales\Domain\Ports\ProductPricingRepository;
use Illuminate\Support\Facades\DB;

final class DbProductPricingRepository implements ProductPricingRepository
{
    public function getPricingByProductId(int $productId): array
    {
        $product = DB::table('products')
            ->select(['price_solo', 'price_with_companion'])
            ->where('id', $productId)
            ->first();

        return [
            'price_solo' => (int) $product->price_solo,
            'price_with_companion' => (int) $product->price_with_companion,
        ];
    }
}
