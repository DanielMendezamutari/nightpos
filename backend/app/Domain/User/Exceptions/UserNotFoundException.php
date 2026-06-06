<?php

declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class UserNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Usuario no encontrado.');
    }
}
