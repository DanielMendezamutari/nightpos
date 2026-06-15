<?php

declare(strict_types=1);

namespace App\Application\Plan\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

final readonly class UpdatePlanInput extends DataTransferObject
{
    public function __construct(
        public int $planId,
        public string $name,
        public string $code,
        public ?string $description,
        public string $monthlyPrice,
        public string $yearlyPrice,
        public bool $isActive,
        public int $displayOrder,
    ) {
    }
}
