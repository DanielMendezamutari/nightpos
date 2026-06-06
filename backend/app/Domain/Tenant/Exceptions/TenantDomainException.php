<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class TenantDomainException extends DomainException
{
    public static function emptyName(): self
    {
        return new self('La empresa debe tener un nombre.');
    }

    public static function duplicateSlug(): self
    {
        return new self('El slug ya está en uso por otra empresa.');
    }

    public static function invalidSubscriptionRange(): self
    {
        return new self('La fecha de fin de suscripción debe ser posterior o igual al inicio.');
    }
}
