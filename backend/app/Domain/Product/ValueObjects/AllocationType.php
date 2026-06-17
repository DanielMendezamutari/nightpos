<?php

declare(strict_types=1);

namespace App\Domain\Product\ValueObjects;

use App\Domain\Product\Exceptions\ProductDomainException;

final readonly class AllocationType
{
    public const GIRL_BRACELET_UNITS = 'GIRL_BRACELET_UNITS';

    private const ALLOWED = [
        self::GIRL_BRACELET_UNITS,
    ];

    private function __construct(
        public string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));

        if (! in_array($normalized, self::ALLOWED, true)) {
            throw ProductDomainException::invalidAllocationType($value);
        }

        return new self($normalized);
    }

    public static function girlBraceletUnits(): self
    {
        return new self(self::GIRL_BRACELET_UNITS);
    }
}
