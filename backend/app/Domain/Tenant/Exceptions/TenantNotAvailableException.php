<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class TenantNotAvailableException extends DomainException
{
    public static function inactiveOrExpired(): self
    {
        return new self('La empresa no está activa o la suscripción ha vencido.');
    }
}
