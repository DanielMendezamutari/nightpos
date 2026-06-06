<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class TenantNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Empresa no encontrada.');
    }
}
