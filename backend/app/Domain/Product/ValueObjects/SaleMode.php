<?php

declare(strict_types=1);

namespace App\Domain\Product\ValueObjects;

use App\Domain\Product\Exceptions\ProductDomainException;

final readonly class SaleMode
{
    public const SOLO_CLIENTE = 'SOLO_CLIENTE';

    public const CON_ACOMPANANTE = 'CON_ACOMPANANTE';

    private const ALLOWED = [
        self::SOLO_CLIENTE,
        self::CON_ACOMPANANTE,
    ];

    private function __construct(
        public string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));

        if (! in_array($normalized, self::ALLOWED, true)) {
            throw ProductDomainException::invalidSaleMode($value);
        }

        return new self($normalized);
    }

    public function isConAcompanante(): bool
    {
        return $this->value === self::CON_ACOMPANANTE;
    }
}
