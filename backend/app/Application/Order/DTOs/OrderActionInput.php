<?php

declare(strict_types=1);

namespace App\Application\Order\DTOs;

final readonly class OrderActionInput extends OrderDto
{
    public function __construct(
        public int $orderId,
    ) {
    }
}
