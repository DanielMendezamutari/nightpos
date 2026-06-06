<?php

declare(strict_types=1);

namespace App\Application\Auth\Services;

use App\Domain\Branch\Entities\Branch;
use App\Domain\Branch\Repositories\BranchRepositoryInterface;
use App\Domain\Tenant\Entities\Tenant;
use App\Domain\Tenant\Exceptions\TenantNotAvailableException;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;

final class TenantAccessGuard
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
        private readonly BranchRepositoryInterface $branches,
    ) {
    }

    public function resolveTenant(?int $tenantId, ?string $tenantSlug): ?Tenant
    {
        if ($tenantId !== null) {
            return $this->tenants->findById($tenantId);
        }

        if ($tenantSlug !== null && $tenantSlug !== '') {
            return $this->tenants->findBySlug($tenantSlug);
        }

        return null;
    }

    public function assertTenantAvailable(?Tenant $tenant): void
    {
        if ($tenant === null) {
            return;
        }

        if (! $tenant->isActive() || ! $tenant->hasValidSubscription()) {
            throw TenantNotAvailableException::inactiveOrExpired();
        }
    }

    public function resolveBranch(?Tenant $tenant, ?int $branchId, ?string $branchCode): ?Branch
    {
        if ($branchId !== null) {
            return $this->branches->findById($branchId);
        }

        if ($tenant !== null && $branchCode !== null && $branchCode !== '') {
            return $this->branches->findByTenantAndCode($tenant->id, $branchCode);
        }

        return null;
    }

    public function assertBranchBelongsToTenant(?Branch $branch, ?Tenant $tenant): void
    {
        if ($branch === null || $tenant === null) {
            return;
        }

        if ($branch->tenantId !== $tenant->id) {
            throw TenantNotAvailableException::inactiveOrExpired();
        }

        if (! $branch->isActive()) {
            throw TenantNotAvailableException::inactiveOrExpired();
        }
    }
}
