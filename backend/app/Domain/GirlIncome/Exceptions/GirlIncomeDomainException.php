<?php

declare(strict_types=1);

namespace App\Domain\GirlIncome\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class GirlIncomeDomainException extends DomainException
{
    public static function branchRequired(): self
    {
        return new self('Debe seleccionar sucursal operativa.');
    }

    public static function girlRequired(): self
    {
        return new self('Debe indicar la chica.');
    }

    public static function girlNotFound(): self
    {
        return new self('La chica no existe o no pertenece a esta empresa.');
    }

    public static function invalidGirl(): self
    {
        return new self('El usuario indicado no es una chica activa.');
    }

    public static function invalidWaiter(): self
    {
        return new self('El garzón indicado no es válido.');
    }

    public static function invalidAmount(): self
    {
        return new self('El monto debe ser mayor a cero.');
    }

    public static function invalidAmountSplit(): self
    {
        return new self('La suma de monto chica y monto casa debe igualar el total cobrado.');
    }

    public static function invalidGirlPercent(): self
    {
        return new self('El porcentaje para la chica debe estar entre 0 y 100.');
    }

    public static function cashSessionRequired(): self
    {
        return new self('Debe abrir caja antes de registrar este servicio.');
    }

    public static function invalidQuantity(): self
    {
        return new self('La cantidad debe ser al menos 1.');
    }

    public static function notFound(): self
    {
        return new self('Registro no encontrado.');
    }

    public static function invalidDuration(): self
    {
        return new self('La duración debe estar entre 1 y 1440 minutos.');
    }

    public static function roomNotActive(): self
    {
        return new self('La pieza no está activa o ya fue cerrada.');
    }

    public static function roomNotAvailable(): self
    {
        return new self('La habitación no está disponible para asignar.');
    }

    public static function cleaningExceedsGirlAmount(): self
    {
        return new self('El monto de limpieza no puede superar el monto bruto de la chica.');
    }
}
