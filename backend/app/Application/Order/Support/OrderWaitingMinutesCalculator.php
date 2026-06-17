<?php

declare(strict_types=1);

namespace App\Application\Order\Support;

use App\Domain\Order\Entities\Order;
use Illuminate\Support\Carbon;

final class OrderWaitingMinutesCalculator
{
    public static function fromOrder(Order $order): int
    {
        $reference = $order->sentToBarAt ?? $order->openedAt;

        if ($reference === null || $reference === '') {
            return 0;
        }

        $startedAt = Carbon::parse($reference);

        if ($startedAt->isFuture()) {
            return 0;
        }

        return max(0, (int) $startedAt->diffInMinutes(Carbon::now()));
    }
}
