<?php

declare(strict_types=1);

namespace App\Modules\Sales\Infrastructure\Persistence;

use App\Modules\Sales\Domain\Ports\OrderItemRepository;
use App\Support\ProductStockAggregator;
use Illuminate\Support\Facades\DB;

final class DbOrderItemRepository implements OrderItemRepository
{
    public function store(array $payload): void
    {
        DB::transaction(function () use ($payload): void {
            $orderItemId = DB::table('order_items')->insertGetId([
                ...$payload,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $trackStock = (bool) DB::table('products')
                ->where('id', $payload['product_id'])
                ->value('track_stock');

            if (! $trackStock) {
                return;
            }

            $siteId = DB::table('orders')
                ->join('shift_turns', 'shift_turns.id', '=', 'orders.shift_turn_id')
                ->where('orders.id', $payload['order_id'])
                ->value('shift_turns.site_id');

            DB::table('site_product_stocks')
                ->where('site_id', $siteId)
                ->where('product_id', $payload['product_id'])
                ->decrement('quantity', (int) $payload['quantity']);

            ProductStockAggregator::syncBaseStock((int) $payload['product_id']);

            DB::table('inventory_movements')->insert([
                'product_id' => $payload['product_id'],
                'site_id' => $siteId,
                'movement_type' => 'sale_out',
                'quantity' => $payload['quantity'],
                'reference_type' => 'order_item',
                'reference_id' => $orderItemId,
                'moved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }
}
