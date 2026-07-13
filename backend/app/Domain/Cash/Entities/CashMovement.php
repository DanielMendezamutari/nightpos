<?php

declare(strict_types=1);

namespace App\Domain\Cash\Entities;

final readonly class CashMovement
{
    public function __construct(
        public int $id,
        public int $cashSessionId,
        public string $movementType,
        public string $amount,
        public ?string $description,
        public string $paymentMethod,
        public int $createdByUserId,
        public string $createdAt,
        public ?int $cashMovementReasonId = null,
        public ?string $notes = null,
        public ?string $reasonName = null,
        public ?string $movementFamily = null,
        public ?string $movementCategory = null,
        public ?string $sourceType = null,
        public ?int $sourceId = null,
    ) {
    }
}
