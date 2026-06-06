<?php

declare(strict_types=1);

namespace App\Application\Order\DTOs;

final readonly class RemoveOrderItemInput
{
    public function __construct(
        public int $orderId,
        public int $itemId,
    ) {
    }
}
