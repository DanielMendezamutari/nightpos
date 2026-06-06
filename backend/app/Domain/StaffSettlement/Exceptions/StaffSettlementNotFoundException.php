<?php

declare(strict_types=1);

namespace App\Domain\StaffSettlement\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class StaffSettlementNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Liquidación no encontrada.');
    }
}
