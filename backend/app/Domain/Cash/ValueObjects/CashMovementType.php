<?php

declare(strict_types=1);

namespace App\Domain\Cash\ValueObjects;

use App\Domain\Cash\Exceptions\CashDomainException;

final readonly class CashMovementType
{
    public const INCOME = 'INCOME';

    public const EXPENSE = 'EXPENSE';

    private function __construct(
        public string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));

        if (! in_array($normalized, [self::INCOME, self::EXPENSE], true)) {
            throw CashDomainException::invalidMovementType($value);
        }

        return new self($normalized);
    }
}
