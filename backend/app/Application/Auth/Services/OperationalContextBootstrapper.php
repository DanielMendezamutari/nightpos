<?php

declare(strict_types=1);

namespace App\Application\Auth\Services;

use App\Domain\Auth\Exceptions\TenantAccessDeniedException;
use App\Domain\Tenant\Exceptions\TenantNotAvailableException;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Infrastructure\Laravel\Http\Context\RequestOperationalContext;

/**
 * Loads authenticated user staff data into the request operational context.
 */
final class OperationalContextBootstrapper
{
    public function __construct(
        private readonly RequestOperationalContext $context,
        private readonly TenantRepositoryInterface $tenants,
    ) {
    }

    public function bootstrapFromUser(UserModel $user): void
    {
        $user->loadMissing(['role.permissions', 'staffProfile']);

        $permissions = $user->role
            ? $user->role->permissions->pluck('slug')->all()
            : [];

        $this->context->setStaff(
            userId: (int) $user->id,
            roleSlug: $user->role?->slug,
            staffRole: $user->staffProfile?->staff_role,
            permissions: $permissions,
            superAdmin: $user->isSuperAdmin(),
        );

        if ($user->tenant_id !== null) {
            $tenant = $this->tenants->findById((int) $user->tenant_id);

            if ($tenant === null || ! $tenant->isActive() || ! $tenant->hasValidSubscription()) {
                throw TenantNotAvailableException::inactiveOrExpired();
            }

            $this->context->setTenant($tenant);
        }
    }

    public function assertUserMayUseTenant(?int $requestedTenantId, UserModel $user): void
    {
        if ($user->isSuperAdmin()) {
            return;
        }

        if ($requestedTenantId === null) {
            return;
        }

        if ((int) $user->tenant_id !== $requestedTenantId) {
            throw TenantAccessDeniedException::create();
        }
    }
}
