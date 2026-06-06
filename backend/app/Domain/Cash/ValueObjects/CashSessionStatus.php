<?php

declare(strict_types=1);

namespace App\Domain\Cash\ValueObjects;

use App\Domain\Cash\Exceptions\CashDomainException;

final readonly class CashSessionStatus
{
    public const OPEN = 'OPEN';

    public const CLOSED = 'CLOSED';

    private function __construct(
        public string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));

        if (! in_array($normalized, [self::OPEN, self::CLOSED], true)) {
            throw CashDomainException::invalidStatus($value);
        }

        return new self($normalized);
    }

    public function isOpen(): bool
    {
        return $this->value === self::OPEN;
    }
}
