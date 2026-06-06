<?php

declare(strict_types=1);

namespace App\Domain\Sale\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class SaleNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Venta no encontrada.');
    }
}
