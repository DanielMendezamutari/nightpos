<?php

declare(strict_types=1);

namespace App\Domain\GirlIncome\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class ShowNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Show no encontrado.');
    }
}
