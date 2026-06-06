<?php

declare(strict_types=1);

namespace App\Domain\Settings\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class MasterDataDomainException extends DomainException
{
    public static function notFound(): self
    {
        return new self('Registro no encontrado.');
    }

    public static function duplicate(): self
    {
        return new self('Ya existe un registro con esos datos.');
    }

    public static function cashRequired(): self
    {
        return new self('Debe existir al menos un método de pago en efectivo (CASH) activo.');
    }

    public static function invalidReasonType(): self
    {
        return new self('El motivo no corresponde al tipo de movimiento.');
    }

    public static function invalidPaymentCode(): self
    {
        return new self('Método de pago no habilitado.');
    }
}
