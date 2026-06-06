<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Http\Context;

use App\Shared\Contracts\AuthenticatedStaffContextInterface;

final class AuthenticatedStaffContext implements AuthenticatedStaffContextInterface
{
    public function __construct(
        private readonly RequestOperationalContext $context,
    ) {
    }

    public function userId(): ?int
    {
        return $this->context->userId();
    }

    public function roleSlug(): ?string
    {
        return $this->context->roleSlug();
    }

    public function staffRole(): ?string
    {
        return $this->context->staffRole();
    }

    public function permissions(): array
    {
        return $this->context->permissions();
    }

    public function isSuperAdmin(): bool
    {
        return $this->context->isSuperAdmin();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->context->hasPermission($permission);
    }
}
