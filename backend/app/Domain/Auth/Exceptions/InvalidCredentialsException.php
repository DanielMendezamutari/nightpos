<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class InvalidCredentialsException extends DomainException
{
    public static function create(): self
    {
        return new self('Credenciales inválidas.');
    }
}
