<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

/**
 * JWT_SECRET (o claves asimétricas) no configuradas en el servidor.
 */
final class JwtNotConfiguredException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            'La autenticación del servidor no está configurada. Contacte al administrador del sistema.',
        );
    }
}
