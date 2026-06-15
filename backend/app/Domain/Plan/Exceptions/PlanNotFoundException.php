<?php

declare(strict_types=1);

namespace App\Domain\Plan\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class PlanNotFoundException extends DomainException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Plan %d no encontrado.', $id));
    }
}
