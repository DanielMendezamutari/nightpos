<?php

declare(strict_types=1);

namespace App\Application\Order\DTOs;

use App\Application\Order\DTOs\OrderDto;

final readonly class CreateOrderInput extends OrderDto
{
    public function __construct(
        public ?string $tableLabel = null,
        public ?int $serviceAreaId = null,
        public ?int $waiterUserId = null,
        public ?string $notes = null,
    ) {
    }
}
