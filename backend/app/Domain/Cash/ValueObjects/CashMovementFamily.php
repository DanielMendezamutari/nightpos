<?php

declare(strict_types=1);

namespace App\Domain\Cash\ValueObjects;

use App\Domain\Cash\Exceptions\CashDomainException;

final readonly class CashMovementFamily
{
    public const SALE = 'SALE';

    public const MANUAL = 'MANUAL';

    public const SETTLEMENT = 'SETTLEMENT';

    public const EXPENSE = 'EXPENSE';

    public const ADJUSTMENT = 'ADJUSTMENT';

    private function __construct(
        public string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));

        if (! in_array($normalized, self::values(), true)) {
            throw CashDomainException::invalidMovementType($value);
        }

        return new self($normalized);
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return [
            self::SALE,
            self::MANUAL,
            self::SETTLEMENT,
            self::EXPENSE,
            self::ADJUSTMENT,
        ];
    }
}