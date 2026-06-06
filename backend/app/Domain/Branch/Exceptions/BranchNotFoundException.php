<?php

declare(strict_types=1);

namespace App\Domain\Branch\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class BranchNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Sucursal no encontrada.');
    }
}
