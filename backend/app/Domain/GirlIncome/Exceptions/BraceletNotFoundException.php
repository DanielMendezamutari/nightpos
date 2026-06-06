<?php

declare(strict_types=1);

namespace App\Domain\GirlIncome\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class BraceletNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Manilla no encontrada.');
    }
}
