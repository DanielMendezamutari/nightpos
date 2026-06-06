<?php

declare(strict_types=1);

namespace App\Domain\Sale\ValueObjects;

use App\Domain\Sale\Exceptions\SaleDomainException;

final readonly class PaymentMethod
{
    public const CASH = 'CASH';

    public const QR = 'QR';

    public const CARD = 'CARD';

    private function __construct(
        public string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));

        if (! in_array($normalized, [self::CASH, self::QR, self::CARD], true)) {
            throw SaleDomainException::invalidPaymentMethod($value);
        }

        return new self($normalized);
    }
}
