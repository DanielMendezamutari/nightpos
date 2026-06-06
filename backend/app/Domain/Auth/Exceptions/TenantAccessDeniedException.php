<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class TenantAccessDeniedException extends DomainException
{
    public static function create(): self
    {
        return new self('No tiene acceso a esta empresa.');
    }
}
