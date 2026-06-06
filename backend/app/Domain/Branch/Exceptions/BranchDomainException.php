<?php

declare(strict_types=1);

namespace App\Domain\Branch\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class BranchDomainException extends DomainException
{
    public static function emptyName(): self
    {
        return new self('La sucursal debe tener un nombre.');
    }

    public static function duplicateCode(): self
    {
        return new self('El código ya está en uso en esta empresa.');
    }
}
