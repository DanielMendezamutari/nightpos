<?php

declare(strict_types=1);

namespace App\Domain\GirlIncome\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class RoomServiceNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Pieza no encontrada.');
    }
}
