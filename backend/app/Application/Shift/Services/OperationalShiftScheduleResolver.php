<?php

declare(strict_types=1);

namespace App\Application\Shift\Services;

use App\Domain\Shift\ValueObjects\ShiftType;
use DateTimeInterface;
use Illuminate\Support\Carbon;

/**
 * Determina tipo de turno y fecha de negocio según la hora local de operación.
 */
final class OperationalShiftScheduleResolver
{
    public function __construct(
        private readonly OfficialShiftWindowBuilder $windowBuilder,
    ) {
    }

    /**
     * @return array{
     *   shift_type: string,
     *   business_date: string,
     *   starts_at: string,
     *   ends_at: string,
     *   name: string
     * }
     */
    public function resolveFor(?DateTimeInterface $moment = null): array
    {
        $now = $moment !== null
            ? Carbon::instance($moment)
            : Carbon::now();

        $hour = (int) $now->format('H');
        $minute = (int) $now->format('i');
        $minutesOfDay = ($hour * 60) + $minute;

        // 00:00 – 08:59 → Noche (fecha de negocio = día anterior)
        if ($minutesOfDay < (9 * 60)) {
            return $this->buildWindow(ShiftType::NIGHT, $now->modify('-1 day')->format('Y-m-d'));
        }

        // 09:00 – 20:59 → Día
        if ($minutesOfDay < (21 * 60)) {
            return $this->buildWindow(ShiftType::DAY, $now->format('Y-m-d'));
        }

        // 21:00 – 23:59 → Noche (fecha de negocio = hoy)
        return $this->buildWindow(ShiftType::NIGHT, $now->format('Y-m-d'));
    }

    /**
     * @return array{
     *   shift_type: string,
     *   business_date: string,
     *   starts_at: string,
     *   ends_at: string,
     *   name: string
     * }
     */
    private function buildWindow(string $shiftType, string $businessDate): array
    {
        $window = $this->windowBuilder->build($shiftType, $businessDate);

        return array_merge($window, [
            'shift_type' => $shiftType,
            'business_date' => $businessDate,
        ]);
    }
}
