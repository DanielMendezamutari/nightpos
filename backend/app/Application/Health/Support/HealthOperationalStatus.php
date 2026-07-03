<?php

declare(strict_types=1);

namespace App\Application\Health\Support;

final class HealthOperationalStatus
{
    public const HEALTHY = 'HEALTHY';

    public const WARNING = 'WARNING';

    public const DEGRADED = 'DEGRADED';

    public const CRITICAL = 'CRITICAL';

    public static function fromScore(int $score): string
    {
        return match (true) {
            $score >= 90 => self::HEALTHY,
            $score >= 75 => self::WARNING,
            $score >= 50 => self::DEGRADED,
            default => self::CRITICAL,
        };
    }
}
