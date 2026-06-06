<?php

declare(strict_types=1);

namespace App\Application\Room\DTOs;

final readonly class UpdateRoomInput
{
    public function __construct(
        public int $roomId,
        public string $code,
        public string $name,
        public string $roomType,
        public ?int $defaultDurationMinutes = null,
        public ?string $suggestedPrice = null,
        public ?string $notes = null,
        public ?int $roomTypeId = null,
    ) {
    }
}
