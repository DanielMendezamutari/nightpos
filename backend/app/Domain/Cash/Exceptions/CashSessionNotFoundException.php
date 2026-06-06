<?php

declare(strict_types=1);

namespace App\Domain\Cash\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class CashSessionNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Sesión de caja no encontrada.');
    }
}
