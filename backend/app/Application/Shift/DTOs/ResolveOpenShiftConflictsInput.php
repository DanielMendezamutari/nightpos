<?php

declare(strict_types=1);

namespace App\Application\Shift\DTOs;

final readonly class ResolveOpenShiftConflictsInput
{
    public function __construct(
        public ?int $keepShiftId = null,
    ) {
    }
}
