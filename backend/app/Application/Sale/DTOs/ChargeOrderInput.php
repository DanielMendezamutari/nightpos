<?php

declare(strict_types=1);

namespace App\Application\Sale\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

final readonly class ChargeOrderInput extends DataTransferObject
{
    /**
     * @param list<array{method: string, amount: float|string}> $payments
     */
    public function __construct(
        public int $orderId,
        public array $payments,
    ) {
    }
}
