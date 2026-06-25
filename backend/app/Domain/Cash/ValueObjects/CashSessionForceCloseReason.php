<?php

declare(strict_types=1);

namespace App\Domain\Cash\ValueObjects;

final class CashSessionForceCloseReason
{
    public const CASHIER_LEFT = 'cashier_left';

    public const OPERATIONAL_ERROR = 'operational_error';

    public const BLOCKERS_UNRESOLVED = 'blockers_unresolved';

    public const SHIFT_CHANGE = 'shift_change';

    public const OTHER = 'other';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::CASHIER_LEFT,
            self::OPERATIONAL_ERROR,
            self::BLOCKERS_UNRESOLVED,
            self::SHIFT_CHANGE,
            self::OTHER,
        ];
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::all(), true);
    }
}
