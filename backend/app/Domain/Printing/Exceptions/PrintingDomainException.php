<?php

declare(strict_types=1);

namespace App\Domain\Printing\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class PrintingDomainException extends DomainException
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 422,
    ) {
        parent::__construct($message);
    }

    public static function invalidDeviceKey(): self
    {
        return new self('Clave de dispositivo inválida.', 401);
    }

    public static function deviceDisabled(): self
    {
        return new self('El dispositivo de impresión está deshabilitado.', 403);
    }

    public static function jobNotFound(): self
    {
        return new self('Trabajo de impresión no encontrado.', 404);
    }

    public static function jobAlreadyClaimed(): self
    {
        return new self('El trabajo de impresión ya fue reclamado por otro agente.', 409);
    }

    public static function jobNotClaimed(): self
    {
        return new self('El trabajo de impresión no está en estado reclamado.', 422);
    }

    public static function branchRequired(): self
    {
        return new self('Debe seleccionar una sucursal.');
    }

    public static function deviceNameTaken(): self
    {
        return new self('Ya existe un dispositivo con ese nombre en la sucursal.');
    }
}
