<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class BranchAccessDeniedException extends DomainException
{
    public static function create(): self
    {
        return new self('No tiene acceso a esta sucursal.');
    }

    public static function required(): self
    {
        return new self('Debe indicar una sucursal activa (branch_id o branch_code).');
    }
}
