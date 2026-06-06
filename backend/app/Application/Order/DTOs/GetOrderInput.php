<?php

declare(strict_types=1);

namespace App\Application\Order\DTOs;

use App\Application\Order\DTOs\OrderDto;

final readonly class GetOrderInput extends OrderDto
{
    public function __construct(
        public int $orderId,
    ) {
    }
}
