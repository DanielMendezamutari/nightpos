<?php

declare(strict_types=1);

namespace App\Domain\Room\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class RoomDomainException extends DomainException
{
    public static function branchRequired(): self
    {
        return new self('Debe seleccionar sucursal operativa.');
    }

    public static function notFound(): self
    {
        return new self('Habitación no encontrada.');
    }

    public static function duplicateCode(): self
    {
        return new self('Ya existe una habitación con ese código en la sucursal.');
    }

    public static function notAvailable(): self
    {
        return new self('La habitación no está disponible para asignar.');
    }

    public static function invalidStatusTransition(): self
    {
        return new self('No se puede cambiar el estado de la habitación en esta operación.');
    }

    public static function invalidType(): self
    {
        return new self('Tipo de habitación no válido.');
    }

    public static function invalidStatus(): self
    {
        return new self('Estado de habitación no válido.');
    }

    public static function invalidDuration(): self
    {
        return new self('La duración debe estar entre 1 y 1440 minutos.');
    }
}
