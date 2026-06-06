<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

interface AuthenticatedStaffContextInterface
{
    public function userId(): ?int;

    public function roleSlug(): ?string;

    public function staffRole(): ?string;

    /** @return list<string> */
    public function permissions(): array;

    public function isSuperAdmin(): bool;

    public function hasPermission(string $permission): bool;
}
