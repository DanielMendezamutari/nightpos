<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Http\Context;

use App\Domain\Branch\Entities\Branch;
use App\Domain\Tenant\Entities\Tenant;

/**
 * Request-scoped operational state (populated by middleware, read by context facades).
 */
final class RequestOperationalContext
{
    private ?Tenant $tenant = null;

    private ?Branch $branch = null;

    private ?int $userId = null;

    private ?string $roleSlug = null;

    private ?string $staffRole = null;

    /** @var list<string> */
    private array $permissions = [];

    private bool $superAdmin = false;

    public function reset(): void
    {
        $this->tenant = null;
        $this->branch = null;
        $this->userId = null;
        $this->roleSlug = null;
        $this->staffRole = null;
        $this->permissions = [];
        $this->superAdmin = false;
    }

    public function setTenant(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function setBranch(?Branch $branch): void
    {
        $this->branch = $branch;
    }

    public function setStaff(
        int $userId,
        ?string $roleSlug,
        ?string $staffRole,
        array $permissions,
        bool $superAdmin,
    ): void {
        $this->userId = $userId;
        $this->roleSlug = $roleSlug;
        $this->staffRole = $staffRole;
        $this->permissions = $permissions;
        $this->superAdmin = $superAdmin;
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function branch(): ?Branch
    {
        return $this->branch;
    }

    public function userId(): ?int
    {
        return $this->userId;
    }

    public function roleSlug(): ?string
    {
        return $this->roleSlug;
    }

    public function staffRole(): ?string
    {
        return $this->staffRole;
    }

    /** @return list<string> */
    public function permissions(): array
    {
        return $this->permissions;
    }

    public function isSuperAdmin(): bool
    {
        return $this->superAdmin;
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->superAdmin) {
            return true;
        }

        return in_array($permission, $this->permissions, true);
    }
}
