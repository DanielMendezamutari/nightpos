<?php

declare(strict_types=1);

namespace App\Application\Waiter\Support;

use App\Application\Order\Support\OrderMapper;
use App\Domain\Order\Entities\Order;

final class WaiterOrderMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function card(Order $order, ?int $itemsCount = null): array
    {
        $base = OrderMapper::order($order, false);
        $base['items_count'] = $itemsCount ?? $order->itemsCount;
        $base['opened_at'] = $order->openedAt ?? null;

        return $base;
    }
}
