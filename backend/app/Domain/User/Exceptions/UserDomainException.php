<?php

declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class UserDomainException extends DomainException
{
    public static function duplicatePin(): self
    {
        return new self('El PIN ya está asignado a otro usuario.');
    }

    public static function notFound(): self
    {
        return new self('Usuario no encontrado.');
    }

    public static function crossTenant(): self
    {
        return new self('No puede gestionar usuarios de otra empresa.');
    }

    public static function waiterCommissionRequired(): self
    {
        return new self('El garzón debe tener un porcentaje de comisión.');
    }

    public static function invalidStaffRole(): self
    {
        return new self('Rol operativo no válido.');
    }

    public static function branchNotInTenant(): self
    {
        return new self('La sucursal no pertenece a la empresa.');
    }

    public static function duplicateGirlName(): self
    {
        return new self('Ya existe una chica activa con ese nombre en esta sucursal.');
    }

    public static function duplicateWaiterName(): self
    {
        return new self('Ya existe un garzón activo con ese nombre en esta sucursal.');
    }

    public static function emptyName(): self
    {
        return new self('El nombre es obligatorio.');
    }

    public static function invalidCleaningAmounts(): self
    {
        return new self('Los montos de limpieza deben ser mayores o iguales a cero.');
    }
}
