<?php

declare(strict_types=1);

namespace App\Domain\Role\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class RoleAdminException extends DomainException
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 422,
    ) {
        parent::__construct($message);
    }

    public static function globalRoleNotManageable(): self
    {
        return new self('Los roles globales de plataforma no pueden gestionarse desde este módulo.');
    }

    public static function roleNotFound(): self
    {
        return new self('Rol no encontrado en el tenant actual.', 404);
    }

    public static function protectedRole(): self
    {
        return new self('Este rol está protegido y no puede modificarse ni eliminarse.');
    }

    public static function roleHasUsers(): self
    {
        return new self('No se puede eliminar un rol con usuarios asignados.');
    }

    public static function forbiddenPermission(string $slug): self
    {
        return new self(sprintf('El permiso "%s" no puede asignarse desde administración local.', $slug));
    }

    public static function lastRolePermissionAdmin(): self
    {
        return new self('Debe quedar al menos un rol con permiso para administrar roles en el tenant.');
    }

    public static function selfRevokeRoleAdmin(): self
    {
        return new self('No puede quitarse el último permiso de administración de roles.');
    }

    public static function slugTaken(): self
    {
        return new self('Ya existe un rol con ese slug en el tenant.');
    }

    public static function reservedSlug(): self
    {
        return new self('El slug está reservado para roles de sistema.');
    }
}
