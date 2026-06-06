<?php

declare(strict_types=1);

namespace App\Domain\StaffSettlement\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class StaffSettlementDomainException extends DomainException
{
    public static function shiftRequired(): self
    {
        return new self('Debe haber un turno oficial para liquidar.');
    }

    public static function notFound(): self
    {
        return new self('Liquidación no encontrada.');
    }

    public static function alreadyPaid(): self
    {
        return new self('La liquidación ya está pagada.');
    }

    public static function cannotPayCancelled(): self
    {
        return new self('No se puede pagar una liquidación cancelada.');
    }

    public static function accessDenied(): self
    {
        return new self('No tiene permiso para ver esta liquidación.');
    }

    public static function cashRequiredForPayment(): self
    {
        return new self('Debe abrir caja para pagar esta liquidación.');
    }
}
