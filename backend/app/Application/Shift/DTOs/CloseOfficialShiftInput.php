<?php

declare(strict_types=1);

namespace App\Application\Shift\DTOs;

use App\Application\Shift\DTOs\ShiftDto;

final readonly class CloseOfficialShiftInput extends ShiftDto
{
    public function __construct(
        public int $shiftId,
        public string $countedCash,
        public ?string $notes,
    ) {
    }
}
