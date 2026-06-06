<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exceptions;

final class EntityNotFoundException extends DomainException
{
    public static function forType(string $entityType, string|int $identifier): self
    {
        return new self(sprintf('%s with identifier [%s] was not found.', $entityType, $identifier));
    }
}
