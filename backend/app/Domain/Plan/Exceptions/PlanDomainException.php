<?php

declare(strict_types=1);

namespace App\Domain\Plan\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class PlanDomainException extends DomainException
{
    public static function duplicateCode(): self
    {
        return new self('Ya existe un plan con ese código.');
    }

    public static function inactive(): self
    {
        return new self('El plan está inactivo.');
    }

    public static function hasTenants(): self
    {
        return new self('No se puede eliminar un plan con empresas asignadas.');
    }

    public static function emptyName(): self
    {
        return new self('El nombre del plan es obligatorio.');
    }
}
