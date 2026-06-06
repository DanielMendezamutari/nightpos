<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class PermissionDeniedException extends DomainException
{
    public static function forPermission(string $permission): self
    {
        return new self(sprintf('Permiso requerido: %s', $permission));
    }
}
