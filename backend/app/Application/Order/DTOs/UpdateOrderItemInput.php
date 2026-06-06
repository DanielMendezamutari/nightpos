<?php

declare(strict_types=1);

namespace App\Application\Order\DTOs;

final readonly class UpdateOrderItemInput
{
    public function __construct(
        public int $orderId,
        public int $itemId,
        public ?int $productId = null,
        public ?int $quantity = null,
        public ?string $saleMode = null,
        public ?int $girlUserId = null,
        public bool $clearGirl = false,
        public ?string $reason = null,
    ) {
    }
}
