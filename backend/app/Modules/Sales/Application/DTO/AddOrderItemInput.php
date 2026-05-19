<?php

declare(strict_types=1);

namespace App\Modules\Sales\Application\DTO;

use App\Modules\Sales\Domain\Enums\ConsumptionType;

final readonly class AddOrderItemInput
{
    public function __construct(
        public int $orderId,
        public int $productId,
        public int $waiterId,
        public ?int $companionId,
        public int $quantity,
        public ConsumptionType $consumptionType,
    ) {
    }
}
