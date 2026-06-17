<?php

declare(strict_types=1);

namespace App\Domain\Product\ValueObjects;

use App\Domain\Product\Exceptions\ProductDomainException;

final readonly class SettlementBehavior
{
    public const GIRL_LINE = 'GIRL_LINE';

    public const GIRL_BRACELET_ALLOCATION = 'GIRL_BRACELET_ALLOCATION';

    public const NONE = 'NONE';

    private const ALLOWED = [
        self::GIRL_LINE,
        self::GIRL_BRACELET_ALLOCATION,
        self::NONE,
    ];

    private function __construct(
        public string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));

        if (! in_array($normalized, self::ALLOWED, true)) {
            throw ProductDomainException::invalidSettlementBehavior($value);
        }

        return new self($normalized);
    }

    public static function default(): self
    {
        return new self(self::GIRL_LINE);
    }

    public function requiresAllocation(): bool
    {
        return $this->value === self::GIRL_BRACELET_ALLOCATION;
    }
}
