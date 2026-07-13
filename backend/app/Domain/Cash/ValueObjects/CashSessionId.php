<?php

declare(strict_types=1);

namespace App\Domain\Cash\ValueObjects;

final readonly class CashSessionId
{
    public function __construct(
        public int $value,
    ) {
        if ($this->value < 1) {
            throw new \InvalidArgumentException('CashSessionId must be a positive integer.');
        }
    }
}
