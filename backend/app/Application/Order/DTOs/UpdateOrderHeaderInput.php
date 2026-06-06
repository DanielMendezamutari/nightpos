<?php

declare(strict_types=1);

namespace App\Application\Order\DTOs;

final readonly class UpdateOrderHeaderInput
{
    public function __construct(
        public int $orderId,
        public ?string $tableLabel = null,
        public ?int $serviceAreaId = null,
        public ?string $notes = null,
        public bool $clearServiceArea = false,
    ) {
    }
}
