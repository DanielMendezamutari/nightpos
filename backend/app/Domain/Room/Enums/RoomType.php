<?php

declare(strict_types=1);

namespace App\Domain\Room\Enums;

enum RoomType: string
{
    case Standard = 'STANDARD';
    case Vip = 'VIP';
    case Suite = 'SUITE';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
