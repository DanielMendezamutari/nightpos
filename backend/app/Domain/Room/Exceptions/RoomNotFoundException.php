<?php

declare(strict_types=1);

namespace App\Domain\Room\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class RoomNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Habitación no encontrada.');
    }
}
