<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObjects;

use App\Shared\Domain\ValueObject;
use InvalidArgumentException;

final readonly class Money extends ValueObject
{
    public function __construct(
        public string $amount,
        public string $currency = 'BOB',
    ) {
        if (! is_numeric($amount)) {
            throw new InvalidArgumentException('Money amount must be numeric.');
        }

        if ($currency === '') {
            throw new InvalidArgumentException('Money currency cannot be empty.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }
}
