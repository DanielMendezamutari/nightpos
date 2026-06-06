<?php

declare(strict_types=1);

namespace App\Application\Shift\DTOs;

use App\Application\Shift\DTOs\ShiftDto;

final readonly class OpenOfficialShiftInput extends ShiftDto
{
    public function __construct(
        public string $shiftType,
        public string $businessDate,
        public ?string $notes,
    ) {
    }
}
