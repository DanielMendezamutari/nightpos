<?php

declare(strict_types=1);

namespace App\Application\Shift\Services;

use App\Domain\Shift\ValueObjects\ShiftType;
use DateTimeImmutable;

final class OfficialShiftWindowBuilder
{
    /**
     * @return array{starts_at: string, ends_at: string, name: string}
     */
    public function build(string $shiftType, string $businessDate): array
    {
        $type = ShiftType::fromString($shiftType);
        $date = new DateTimeImmutable($businessDate.' 00:00:00');

        if ($type->isDay()) {
            $starts = $date->setTime(9, 0);
            $ends = $date->setTime(21, 0);
        } else {
            $starts = $date->setTime(21, 0);
            $ends = $date->modify('+1 day')->setTime(9, 0);
        }

        return [
            'starts_at' => $starts->format('Y-m-d H:i:s'),
            'ends_at' => $ends->format('Y-m-d H:i:s'),
            'name' => $type->defaultName(),
        ];
    }
}
