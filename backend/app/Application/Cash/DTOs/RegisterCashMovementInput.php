<?php

declare(strict_types=1);

namespace App\Application\Cash\DTOs;

final readonly class RegisterCashMovementInput extends CashDto
{
    public function __construct(
        public string $movementType,
        public string $amount,
        public int $cashMovementReasonId,
        public ?string $notes = null,
        public ?string $description = null,
        public string $paymentMethod = 'CASH',
    ) {
    }
}
