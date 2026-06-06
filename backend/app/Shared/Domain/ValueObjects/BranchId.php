<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObjects;

use App\Shared\Domain\ValueObject;
use InvalidArgumentException;

final readonly class BranchId extends ValueObject
{
    public function __construct(public string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('BranchId cannot be empty.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
