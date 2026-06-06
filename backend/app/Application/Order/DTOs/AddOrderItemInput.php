<?php

declare(strict_types=1);

namespace App\Application\Order\DTOs;

use App\Application\Order\DTOs\OrderDto;

final readonly class AddOrderItemInput extends OrderDto
{
    public function __construct(
        public int $orderId,
        public int $productId,
        public string $saleMode,
        public int $quantity = 1,
        public ?int $girlUserId = null,
        public ?string $notes = null,
    ) {
    }
}
