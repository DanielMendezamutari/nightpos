<?php

declare(strict_types=1);

namespace App\Domain\Room\Enums;

enum RoomStatus: string
{
    case Available = 'AVAILABLE';
    case Occupied = 'OCCUPIED';
    case Cleaning = 'CLEANING';
    case Maintenance = 'MAINTENANCE';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }

    public function canAssign(): bool
    {
        return $this === self::Available;
    }
}
