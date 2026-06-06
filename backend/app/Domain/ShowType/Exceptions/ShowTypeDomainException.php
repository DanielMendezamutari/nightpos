<?php

declare(strict_types=1);

namespace App\Domain\ShowType\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class ShowTypeDomainException extends DomainException
{
    public static function duplicateName(): self
    {
        return new self('Ya existe un tipo de show con ese nombre.');
    }

    public static function notFound(): self
    {
        return new self('Tipo de show no encontrado.');
    }
}
