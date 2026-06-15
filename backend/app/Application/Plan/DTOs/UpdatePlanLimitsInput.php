<?php

declare(strict_types=1);

namespace App\Application\Plan\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

final readonly class UpdatePlanLimitsInput extends DataTransferObject
{
    /**
     * @param list<array{limit_key: string, limit_value: int}> $limits
     */
    public function __construct(
        public int $planId,
        public array $limits,
    ) {
    }
}
