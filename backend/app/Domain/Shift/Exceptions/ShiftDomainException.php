<?php

declare(strict_types=1);

namespace App\Domain\Shift\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class ShiftDomainException extends DomainException
{
    public static function shiftRequired(): self
    {
        return new self('Debe abrir un turno oficial antes de continuar.');
    }

    public static function shiftAlreadyOpen(): self
    {
        return new self('Ya hay un turno oficial abierto en esta sucursal.');
    }

    public static function shiftAlreadyClosed(): self
    {
        return new self('El turno oficial ya está cerrado.');
    }

    public static function openCashSessionsExist(): self
    {
        return new self('Debe cerrar todas las sesiones de caja antes de cerrar el turno.');
    }

    public static function invalidShiftType(string $value): self
    {
        return new self(sprintf('Tipo de turno inválido: %s.', $value));
    }
}
