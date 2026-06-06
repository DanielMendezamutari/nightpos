<?php

declare(strict_types=1);

namespace App\Domain\Order\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class OrderNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Comanda no encontrada.');
    }
}
