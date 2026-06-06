<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObjects;

use App\Shared\Domain\ValueObject;
use InvalidArgumentException;

final readonly class CommissionPercent extends ValueObject
{
    public function __construct(public string $value)
    {
        if (! is_numeric($value)) {
            throw new InvalidArgumentException('CommissionPercent must be numeric.');
        }

        $float = (float) $value;

        if ($float < 0 || $float > 100) {
            throw new InvalidArgumentException('CommissionPercent must be between 0 and 100.');
        }
    }
}
