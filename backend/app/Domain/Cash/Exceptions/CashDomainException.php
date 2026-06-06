<?php

declare(strict_types=1);

namespace App\Domain\Cash\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class CashDomainException extends DomainException
{
    public static function branchRequired(): self
    {
        return new self('Debe indicar la sucursal en el contexto.');
    }

    public static function sessionAlreadyOpen(): self
    {
        return new self('Ya tiene una sesión de caja abierta en esta sucursal.');
    }

    public static function noOpenSession(): self
    {
        return new self('No hay sesión de caja abierta.');
    }

    public static function sessionClosed(): self
    {
        return new self('La sesión de caja ya está cerrada.');
    }

    public static function invalidOpeningAmount(): self
    {
        return new self('El monto inicial debe ser mayor o igual a cero.');
    }

    public static function invalidAmount(): self
    {
        return new self('El monto debe ser mayor a cero.');
    }

    public static function invalidStatus(string $value): self
    {
        return new self(sprintf('Estado de caja inválido: %s.', $value));
    }

    public static function invalidMovementType(string $value): self
    {
        return new self(sprintf('Tipo de movimiento inválido: %s.', $value));
    }
}
