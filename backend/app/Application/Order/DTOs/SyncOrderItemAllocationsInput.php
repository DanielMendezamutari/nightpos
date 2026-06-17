<?php

declare(strict_types=1);

namespace App\Application\Order\DTOs;

final readonly class SyncOrderItemAllocationsInput extends OrderDto
{
    /**
     * @param  list<array{girl_user_id: int, units: int}>  $allocations
     */
    public function __construct(
        public int $orderId,
        public int $itemId,
        public array $allocations,
    ) {
    }
}
