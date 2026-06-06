<?php

declare(strict_types=1);

namespace App\Domain\Shift\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class OfficialShiftNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Turno oficial no encontrado.');
    }
}
